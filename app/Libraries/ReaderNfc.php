<?php

namespace App\Libraries;

use App\Models\PresensiSiswaModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\I18n\Time;

/**
 * Logika absensi dari reader kartu NFC Si-ABDI.
 *
 * Dipakai oleh API reader (Api\Reader) dan halaman admin (proses ulang tap
 * kartu yang baru didaftarkan). Aturan masuk/pulang/dzuhur/ashar sengaja
 * disamakan dengan scan QR (Controllers\Scan) supaya hasilnya konsisten:
 * - masuk  : ditolak bila hari itu sudah ada baris presensi
 * - pulang : butuh absen masuk, tidak menimpa jam pulang yang sudah ada
 * - shalat : butuh absen masuk, tidak menimpa jam yang sudah ada
 *
 * Bedanya dengan scan QR: tanggal & jam diambil dari waktu tap di reader
 * (bisa saja tap terjadi saat reader offline dan baru terkirim kemudian),
 * bukan dari waktu server.
 */
class ReaderNfc
{
    public const MODE = ['otomatis', 'masuk', 'pulang', 'dzuhur', 'ashar'];

    public const LABEL_JENIS = [
        'masuk'  => 'Masuk',
        'dzuhur' => 'Dzuhur',
        'ashar'  => 'Ashar',
        'pulang' => 'Pulang',
    ];

    /** Notifikasi WA hanya untuk tap yang belum lama (bukan antrian offline lama) */
    private const BATAS_UMUR_NOTIF = 900;

    private BaseConnection $db;
    private PresensiSiswaModel $presensi;
    private ?object $settings;

    public function __construct(?object $generalSettings = null)
    {
        $this->db = db_connect();
        $this->presensi = new PresensiSiswaModel();
        $this->settings = $generalSettings
            ?? $this->db->table('general_settings')->where('id', 1)->get()->getRow();
    }

    public static function uidValid(string $uid): bool
    {
        return (bool) preg_match('/^[0-9A-F]{2}( [0-9A-F]{2}){3,9}$/', $uid);
    }

    public static function normalisasiUid(string $uid): string
    {
        // terima "deadbeef", "DE:AD:BE:EF", "de ad be ef" -> "DE AD BE EF"
        $hex = strtoupper(preg_replace('/[^0-9A-Fa-f]/', '', $uid));
        return trim(implode(' ', str_split($hex, 2)));
    }

    /** Jadwal otomatis dalam format HH:MM:SS */
    public function jadwal(): array
    {
        $ambil = fn(string $k, string $def) => substr((string) ($this->settings->$k ?? $def), 0, 8) ?: $def;

        return [
            'akhir_masuk'  => $ambil('nfc_akhir_masuk', '10:00:00'),
            'mulai_dzuhur' => $ambil('nfc_mulai_dzuhur', '12:00:00'),
            'akhir_dzuhur' => $ambil('nfc_akhir_dzuhur', '13:30:00'),
            'mulai_ashar'  => $ambil('nfc_mulai_ashar', '15:15:00'),
            'akhir_ashar'  => $ambil('nfc_akhir_ashar', '16:00:00'),
            'mulai_pulang' => $ambil('nfc_mulai_pulang', '13:30:00'),
        ];
    }

    /**
     * Jenis absen untuk mode "otomatis". Diperiksa berurutan:
     * masuk -> dzuhur -> ashar -> pulang. Null = di luar semua jadwal.
     */
    public function jenisDariJadwal(string $jam): ?string
    {
        $j = $this->jadwal();

        if ($jam <= $j['akhir_masuk']) return 'masuk';
        if ($jam >= $j['mulai_dzuhur'] && $jam <= $j['akhir_dzuhur']) return 'dzuhur';
        if ($jam >= $j['mulai_ashar'] && $jam <= $j['akhir_ashar']) return 'ashar';
        if ($jam >= $j['mulai_pulang']) return 'pulang';

        return null;
    }

    /**
     * Terima satu tap dari reader: catat ke log (sekali saja untuk pasangan
     * uid+waktu yang sama), lalu proses absennya.
     *
     * @return string hasil, mis. "berhasil", "sudah_absen", "duplikat"
     */
    public function terimaTap(string $uid, int $epoch, string $mode, string $perangkat): string
    {
        $sekarang = time();
        // jam reader belum diset (epoch 0) / tidak masuk akal -> pakai waktu server
        $ts = ($epoch > 1700000000 && $epoch < $sekarang + 3600) ? $epoch : $sekarang;
        $waktu = Time::createFromTimestamp($ts, 'Asia/Jakarta');

        $this->db->query(
            'INSERT IGNORE INTO tb_nfc_log (uid, waktu, mode, hasil, perangkat, diterima) VALUES (?, ?, ?, ?, ?, ?)',
            [$uid, $waktu->toDateTimeString(), $mode, 'diproses', $perangkat, Time::now('Asia/Jakarta')->toDateTimeString()]
        );

        if ($this->db->affectedRows() === 0) {
            return 'duplikat'; // sudah pernah diterima (reader mengirim ulang)
        }

        $idLog = $this->db->insertID();
        [$hasil, $jenis, $idSiswa] = $this->prosesTap($uid, $waktu, $mode, $perangkat, ($sekarang - $ts) <= self::BATAS_UMUR_NOTIF);

        $this->db->table('tb_nfc_log')->where('id', $idLog)->update([
            'hasil'    => $hasil,
            'jenis'    => $jenis,
            'id_siswa' => $idSiswa,
        ]);

        return $hasil;
    }

    /**
     * Proses ulang tap hari ini dari kartu yang tadinya belum terdaftar
     * (dipanggil setelah admin mendaftarkan kartu).
     *
     * @return int jumlah tap yang berhasil dicatat sebagai absen
     */
    public function prosesUlangTidakTerdaftar(string $uid): int
    {
        $log = $this->db->table('tb_nfc_log')
            ->where('uid', $uid)
            ->where('hasil', 'tidak_terdaftar')
            ->where('waktu >=', Time::today('Asia/Jakarta')->toDateTimeString())
            ->orderBy('waktu', 'ASC')
            ->get()->getResultArray();

        $berhasil = 0;
        foreach ($log as $l) {
            $waktu = Time::parse($l['waktu'], 'Asia/Jakarta');
            [$hasil, $jenis, $idSiswa] = $this->prosesTap($uid, $waktu, $l['mode'], $l['perangkat'], false);
            $this->db->table('tb_nfc_log')->where('id', $l['id'])->update([
                'hasil' => $hasil, 'jenis' => $jenis, 'id_siswa' => $idSiswa,
            ]);
            if ($hasil === 'berhasil') $berhasil++;
        }

        return $berhasil;
    }

    /** @return array{0:string,1:?string,2:?int} [hasil, jenis, id_siswa] */
    private function prosesTap(string $uid, Time $waktu, string $mode, string $perangkat, bool $bolehNotif): array
    {
        $siswa = $this->db->table('tb_siswa')->where('uid_kartu', $uid)->get()->getRowArray();

        if (empty($siswa)) {
            $this->db->query(
                'INSERT INTO tb_nfc_kartu_baru (uid, terakhir, perangkat, jumlah) VALUES (?, ?, ?, 1)
                 ON DUPLICATE KEY UPDATE terakhir = GREATEST(terakhir, VALUES(terakhir)),
                 perangkat = VALUES(perangkat), jumlah = jumlah + 1',
                [$uid, $waktu->toDateTimeString(), $perangkat]
            );
            return ['tidak_terdaftar', null, null];
        }

        $idSiswa = (int) $siswa['id_siswa'];
        $tanggal = $waktu->toDateString();
        $jam = $waktu->toTimeString();

        $jenis = $mode === 'otomatis' ? $this->jenisDariJadwal($jam) : $mode;
        if ($jenis === null || !isset(self::LABEL_JENIS[$jenis])) {
            return ['di_luar_jadwal', null, $idSiswa];
        }

        $presensi = $this->presensi->getPresensiByIdSiswaTanggal($idSiswa, $tanggal);

        switch ($jenis) {
            case 'masuk':
                if (!empty($presensi)) {
                    return [empty($presensi['jam_masuk']) ? 'sudah_ada_status' : 'sudah_absen', $jenis, $idSiswa];
                }
                $this->presensi->absenMasuk((string) $idSiswa, $tanggal, $jam, $siswa['id_kelas']);
                break;

            case 'pulang':
                if (empty($presensi)) return ['belum_masuk', $jenis, $idSiswa];
                if (!empty($presensi['jam_keluar'])) return ['sudah_absen', $jenis, $idSiswa];
                $this->presensi->absenKeluar((string) $presensi['id_presensi'], $jam);
                break;

            default: // dzuhur, ashar
                $kolom = 'jam_' . $jenis;
                if (empty($presensi)) return ['belum_masuk', $jenis, $idSiswa];
                if (!empty($presensi[$kolom])) return ['sudah_absen', $jenis, $idSiswa];
                $this->presensi->absenWaktu((string) $presensi['id_presensi'], $kolom, $jam);
                break;
        }

        if ($bolehNotif) {
            $this->kirimNotifikasi($siswa, $jenis, $tanggal, $jam);
        }

        return ['berhasil', $jenis, $idSiswa];
    }

    /** Sama dengan Scan::sendNotification(), pesan dengan format yang sama */
    private function kirimNotifikasi(array $siswa, string $jenis, string $tanggal, string $jam): void
    {
        if (getenv('WA_NOTIFICATION') !== 'true' || empty($siswa['no_hp'])) return;

        $token = getenv('WHATSAPP_TOKEN');
        $provider = getenv('WHATSAPP_PROVIDER');
        if (empty($token) || $provider !== 'Fonnte') return;

        // scan QR menulis "absen masuk/pulang" tapi "absen Dzuhur/Ashar"
        $label = in_array($jenis, ['dzuhur', 'ashar'], true) ? self::LABEL_JENIS[$jenis] : $jenis;
        try {
            $wa = new \App\Libraries\Whatsapp\Fonnte\Fonnte($token);
            $wa->sendMessage([
                'destination' => $siswa['no_hp'],
                'message'     => 'Siswa ' . $siswa['nama_siswa'] . ' dengan NIS ' . $siswa['nis']
                    . " sudah absen $label pada tanggal $tanggal jam $jam",
                'delay'       => 0,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Reader NFC: gagal kirim notifikasi WA: ' . $e->getMessage());
        }
    }
}
