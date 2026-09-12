<?php

namespace App\Database\Migrations;

use App\Libraries\KodeUnik;
use CodeIgniter\Database\Migration;

/**
 * Persingkat kode unik siswa & guru menjadi 9 karakter.
 *
 * Kode baru dicek duplikatnya lintas tabel (tb_siswa dan tb_guru) sebelum
 * disimpan, termasuk terhadap kode yang baru dibuat di batch yang sama.
 *
 * PERHATIAN: QR yang sudah dicetak dengan kode lama tidak berlaku lagi.
 * Riwayat presensi memakai id siswa/guru sehingga tidak terpengaruh.
 */
class ShortenUniqueCode extends Migration
{
    public function up()
    {
        $dipakai = [];

        foreach (['tb_siswa' => 'id_siswa', 'tb_guru' => 'id_guru'] as $tabel => $kunci) {
            if (!$this->db->tableExists($tabel)) {
                continue;
            }

            $baris = $this->db->table($tabel)->select($kunci . ', unique_code')->get()->getResultArray();

            foreach ($baris as $item) {
                // kode yang sudah berformat baru dibiarkan apa adanya
                if (strlen((string) $item['unique_code']) === KodeUnik::PANJANG) {
                    $dipakai[] = $item['unique_code'];
                    continue;
                }

                $kode = KodeUnik::buat($dipakai, $this->db);
                $dipakai[] = $kode;

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
