<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Libraries\ReaderNfc;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * API untuk reader kartu NFC Si-ABDI (ESP32).
 *
 * Autentikasi lewat header X-API-KEY yang harus sama dengan READER_API_KEY
 * di .env (bukan login sesi), sehingga route api/reader/* dikecualikan dari
 * filter login global di Config\Filters. Jika READER_API_KEY kosong, API
 * nonaktif (503).
 *
 * Format sengaja teks polos agar ringan diproses ESP32:
 *   GET  api/reader/siswa  -> "#SISWA <n>\n" lalu "UID|Nama\n" per siswa
 *   POST api/reader/absen  -> body "UID,epoch_utc[,mode]\n" per tap,
 *                             balasan "OK <jumlah_baris_diproses>"
 * Header X-DEVICE berisi nama reader (mis. READER-01).
 */
class Reader extends BaseController
{
    /** Batas baris per permintaan (firmware mengirim 200 per batch) */
    private const MAKS_BARIS = 500;

    private function teks(string $isi, int $kode = 200): ResponseInterface
    {
        return $this->response
            ->setStatusCode($kode)
            ->setContentType('text/plain', 'utf-8')
            ->setBody($isi);
    }

    private function cekKunci(): ?ResponseInterface
    {
        $kunci = (string) env('READER_API_KEY', '');
        $diberikan = $this->request->getHeaderLine('X-API-KEY');

        if ($kunci === '') {
            return $this->teks('READER API NONAKTIF', 503);
        }
        if (!hash_equals($kunci, $diberikan)) {
            return $this->teks('UNAUTHORIZED', 401);
        }
        return null;
    }

    private function perangkat(): string
    {
        $nama = preg_replace('/[^A-Za-z0-9_\-]/', '', $this->request->getHeaderLine('X-DEVICE'));
        return substr($nama ?: 'READER', 0, 30);
    }

    public function siswa()
    {
        if ($tolak = $this->cekKunci()) return $tolak;

        $rows = db_connect()->table('tb_siswa')
            ->select('uid_kartu, nama_siswa')
            ->where('uid_kartu IS NOT NULL')
            ->where('uid_kartu !=', '')
            ->orderBy('nama_siswa')
            ->get()->getResultArray();

        $isi = '#SISWA ' . count($rows) . "\n";
        foreach ($rows as $r) {
            $isi .= $r['uid_kartu'] . '|' . str_replace(['|', "\r", "\n"], ' ', $r['nama_siswa']) . "\n";
        }

        return $this->teks($isi);
    }

    public function absen()
    {
        if ($tolak = $this->cekKunci()) return $tolak;

        $baris = preg_split('/\r?\n/', trim((string) $this->request->getBody()));
        if (count($baris) > self::MAKS_BARIS) {
            return $this->teks('TERLALU BANYAK DATA', 413);
        }

        $reader = new ReaderNfc($this->generalSettings);
        $perangkat = $this->perangkat();
        $diproses = 0;

        try {
            foreach ($baris as $l) {
                $kolom = explode(',', trim($l));
                $uid = ReaderNfc::normalisasiUid($kolom[0] ?? '');
                if (!ReaderNfc::uidValid($uid)) continue; // baris rusak dilewati

                $epoch = (int) ($kolom[1] ?? 0);
                $mode = strtolower(trim($kolom[2] ?? 'otomatis'));
                if (!in_array($mode, ReaderNfc::MODE, true)) $mode = 'otomatis';

                $reader->terimaTap($uid, $epoch, $mode, $perangkat);
                $diproses++;
            }
        } catch (\Throwable $e) {
            // Reader akan mengirim ulang batch ini; baris yang sudah tercatat
            // tidak dobel karena kunci unik (uid, waktu) di tb_nfc_log.
            log_message('error', 'Reader NFC: ' . $e->getMessage());
            return $this->teks('ERROR', 500);
        }

        return $this->teks("OK $diproses");
    }
}
