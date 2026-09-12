<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Arahkan template kartu bawaan ke berkas yang benar-benar ada di server.
 *
 * Alasan: sebagian hosting (Imunify/antivirus) menghapus berkas .svg hasil
 * unggahan, sehingga base template bawaan raib dan kartu tampil kosong.
 * Versi PNG dari desain yang sama dipakai sebagai gantinya. Migrasi hanya
 * menimpa path yang berkasnya memang tidak ditemukan, jadi template yang
 * sudah diganti sendiri oleh sekolah tidak terganggu.
 */
class SetKartuTemplateBawaan extends Migration
{
    public function up()
    {
        $baris = $this->db->table('tb_kartu_template')->where('id', 1)->get()->getRowArray();

        if (empty($baris)) {
            return;
        }

        $data = [];

        foreach (['svg_depan' => 'depan', 'svg_belakang' => 'belakang'] as $kolom => $sisi) {
            $sekarang = $baris[$kolom] ?? null;

            if (!empty($sekarang) && is_file(FCPATH . $sekarang)) {
                continue;
            }

            $bawaan = 'uploads/kartu/kartu-' . $sisi . '-bawaan.png';

            if (is_file(FCPATH . $bawaan)) {
                $data[$kolom] = $bawaan;
            }
        }

        if (!empty($data)) {
            $this->db->table('tb_kartu_template')->where('id', 1)->update($data);
        }
    }

    public function down()
    {
        // tidak ada yang perlu dikembalikan: hanya memperbaiki path berkas
    }
}
