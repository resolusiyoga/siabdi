<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\ReaderNfc as ReaderNfcLib;

/**
 * Pengelolaan reader kartu NFC Si-ABDI (khusus superadmin):
 * - mendaftarkan kartu yang muncul di "Kartu baru" ke siswa
 * - melepas kartu dari siswa (kartu hilang / ganti)
 * - jadwal penentuan jenis absen untuk reader mode "otomatis"
 * - log tap terbaru & status reader
 */
class ReaderNfc extends BaseController
{
    private const KE_HALAMAN = 'admin/reader-nfc';

    private function tolakNonSuperadmin()
    {
        $this->session->setFlashdata('error', 'Halaman ini khusus untuk superadmin');
        return redirect()->to('admin/dashboard');
    }

    public function index()
    {
        if (!isSuperadmin()) return $this->tolakNonSuperadmin();

        $db = db_connect();
        $reader = new ReaderNfcLib($this->generalSettings);

        $siswaSelect = 'tb_siswa.id_siswa, tb_siswa.nama_siswa, tb_siswa.nis, tb_siswa.uid_kartu, tb_kelas.kelas, tb_jurusan.jurusan';

        $data = [
            'title'     => 'Reader NFC',
            'ctx'       => 'reader-nfc',
            'apiAktif'  => (string) env('READER_API_KEY', '') !== '',
            'apiUrl'    => rtrim(base_url('api/reader'), '/'),
            'jadwal'    => $reader->jadwal(),
            'kartuBaru' => $db->table('tb_nfc_kartu_baru')->orderBy('terakhir', 'DESC')->get()->getResultArray(),
            'siswaTanpaKartu' => $db->table('tb_siswa')->select($siswaSelect)
                ->join('tb_kelas', 'tb_kelas.id_kelas = tb_siswa.id_kelas', 'left')
                ->join('tb_jurusan', 'tb_jurusan.id = tb_kelas.id_jurusan', 'left')
                ->groupStart()->where('tb_siswa.uid_kartu IS NULL')->orWhere('tb_siswa.uid_kartu', '')->groupEnd()
                ->orderBy('tb_kelas.kelas, tb_jurusan.jurusan, tb_siswa.nama_siswa')
                ->get()->getResultArray(),
            'siswaBerkartu' => $db->table('tb_siswa')->select($siswaSelect)
                ->join('tb_kelas', 'tb_kelas.id_kelas = tb_siswa.id_kelas', 'left')
                ->join('tb_jurusan', 'tb_jurusan.id = tb_kelas.id_jurusan', 'left')
                ->where('tb_siswa.uid_kartu IS NOT NULL')->where('tb_siswa.uid_kartu !=', '')
                ->orderBy('tb_kelas.kelas, tb_jurusan.jurusan, tb_siswa.nama_siswa')
                ->get()->getResultArray(),
            'log' => $db->table('tb_nfc_log')
                ->select('tb_nfc_log.*, tb_siswa.nama_siswa')
                ->join('tb_siswa', 'tb_siswa.id_siswa = tb_nfc_log.id_siswa', 'left')
                ->orderBy('tb_nfc_log.id', 'DESC')->limit(100)
                ->get()->getResultArray(),
            'perangkat' => $db->table('tb_nfc_log')
                ->select('perangkat, MAX(diterima) AS terakhir, COUNT(*) AS jumlah')
                ->groupBy('perangkat')->orderBy('terakhir', 'DESC')
                ->get()->getResultArray(),
            'tabelReaderAda' => ReaderNfcLib::adaTabelReader(),
            'readers'        => ReaderNfcLib::daftarReader(),
            'batasOnline'    => ReaderNfcLib::BATAS_ONLINE,
        ];

        return view('admin/reader-nfc/index', $data);
    }

    /** Status reader (JSON) untuk penyegaran berkala di halaman */
    public function statusReader()
    {
        if (!isSuperadmin()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'khusus superadmin']);
        }
        return $this->response->setJSON(['readers' => ReaderNfcLib::daftarReader()]);
    }

    public function daftar()
    {
        if (!isSuperadmin()) return $this->tolakNonSuperadmin();

        $uid = ReaderNfcLib::normalisasiUid((string) $this->request->getPost('uid'));
        $idSiswa = (int) $this->request->getPost('id_siswa');

        if (!ReaderNfcLib::uidValid($uid)) {
            $this->session->setFlashdata('error', 'UID kartu tidak valid');
            return redirect()->to(self::KE_HALAMAN);
        }

        $db = db_connect();
        $siswa = $db->table('tb_siswa')->where('id_siswa', $idSiswa)->get()->getRowArray();
        if (empty($siswa)) {
            $this->session->setFlashdata('error', 'Pilih siswa terlebih dahulu');
            return redirect()->to(self::KE_HALAMAN);
        }

        $pemilik = $db->table('tb_siswa')->select('nama_siswa')->where('uid_kartu', $uid)->get()->getRowArray();
        if (!empty($pemilik)) {
            $this->session->setFlashdata('error', "Kartu $uid sudah dipakai oleh " . esc($pemilik['nama_siswa']));
            return redirect()->to(self::KE_HALAMAN);
        }

        $db->table('tb_siswa')->where('id_siswa', $idSiswa)->update(['uid_kartu' => $uid]);
        $db->table('tb_nfc_kartu_baru')->where('uid', $uid)->delete();

        // tap hari ini sebelum kartu didaftarkan ikut dihitung sebagai absen
        $tercatat = (new ReaderNfcLib($this->generalSettings))->prosesUlangTidakTerdaftar($uid);

        $pesan = 'Kartu ' . $uid . ' didaftarkan ke ' . esc($siswa['nama_siswa']) . '.';
        if (!empty($siswa['uid_kartu'])) {
            $pesan .= ' Kartu lama (' . esc($siswa['uid_kartu']) . ') tidak berlaku lagi.';
        }
        if ($tercatat > 0) {
            $pesan .= " $tercatat tap hari ini ikut dicatat sebagai absen.";
        }
        $pesan .= ' Reader memperbarui daftar siswa dalam ±10 menit.';

        $this->session->setFlashdata('success', $pesan);
        return redirect()->to(self::KE_HALAMAN);
    }

    public function lepas()
    {
        if (!isSuperadmin()) return $this->tolakNonSuperadmin();

        $idSiswa = (int) $this->request->getPost('id_siswa');
        db_connect()->table('tb_siswa')->where('id_siswa', $idSiswa)->update(['uid_kartu' => null]);

        $this->session->setFlashdata('success', 'Kartu dilepas dari siswa. Kartu tersebut tidak bisa dipakai absen lagi.');
        return redirect()->to(self::KE_HALAMAN);
    }

    public function abaikan()
    {
        if (!isSuperadmin()) return $this->tolakNonSuperadmin();

        $uid = ReaderNfcLib::normalisasiUid((string) $this->request->getPost('uid'));
        db_connect()->table('tb_nfc_kartu_baru')->where('uid', $uid)->delete();

        return redirect()->to(self::KE_HALAMAN);
    }

    public function jadwal()
    {
        if (!isSuperadmin()) return $this->tolakNonSuperadmin();

        $kolom = ['nfc_akhir_masuk', 'nfc_mulai_dzuhur', 'nfc_akhir_dzuhur', 'nfc_mulai_ashar', 'nfc_akhir_ashar', 'nfc_mulai_pulang'];
        $data = [];
        foreach ($kolom as $k) {
            $nilai = (string) $this->request->getPost($k);
            if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $nilai)) {
                $this->session->setFlashdata('error', 'Format jam tidak valid');
                return redirect()->to(self::KE_HALAMAN);
            }
            $data[$k] = $nilai . ':00';
        }

        if ($data['nfc_mulai_dzuhur'] > $data['nfc_akhir_dzuhur'] || $data['nfc_mulai_ashar'] > $data['nfc_akhir_ashar']) {
            $this->session->setFlashdata('error', 'Jam mulai tidak boleh melewati jam akhir');
            return redirect()->to(self::KE_HALAMAN);
        }

        db_connect()->table('general_settings')->where('id', 1)->update($data);

        $this->session->setFlashdata('success', 'Jadwal absen otomatis disimpan');
        return redirect()->to(self::KE_HALAMAN);
    }
}
