<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Ganti seluruh kode unik siswa & guru menjadi 11 karakter.
 *
 * Kode lama (uniqid/sha1, 32-64 karakter) membuat pola QR sangat rapat
 * sehingga sulit terbaca saat dicetak kecil pada kartu siswa.
 *
 * PERHATIAN: QR lama otomatis tidak berlaku setelah migrasi ini, jadi
 * QR/kartu yang sudah dicetak harus dibuat ulang. Tabel presensi memakai
 * id siswa/guru, bukan kode unik, sehingga riwayat absensi tidak terganggu.
 */
class RegenerateUniqueCode extends Migration
{
    public function up()
    {
        foreach (['tb_siswa' => 'id_siswa', 'tb_guru' => 'id_guru'] as $tabel => $kunci) {
            if (!$this->db->tableExists($tabel)) {
                continue;
            }

            $terpakai = array_column(
                $this->db->table($tabel)->select('unique_code')->get()->getResultArray(),
                'unique_code'
            );
            $terpakai = array_flip($terpakai);

            $baris = $this->db->table($tabel)
                ->select($kunci . ', unique_code')
                ->get()
                ->getResultArray();

            foreach ($baris as $item) {
                // kode yang sudah berformat baru dibiarkan
                if (strlen((string) $item['unique_code']) === 11) {
                    continue;
                }

                do {
                    $kode = generateUniqueCode();
                } while (isset($terpakai[$kode]));

                $terpakai[$kode] = true;

                $this->db->table($tabel)
                    ->where($kunci, $item[$kunci])
                    ->update(['unique_code' => $kode]);
            }
        }
    }

    public function down()
    {
        // kode acak tidak bisa dikembalikan ke nilai semula
    }
}
