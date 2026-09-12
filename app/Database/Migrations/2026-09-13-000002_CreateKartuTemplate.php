<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Template kartu siswa: base template SVG (sisi depan & belakang) beserta
 * posisi tiap elemen data (foto, nama, NIS/NISN, QR code) dalam satuan mm.
 */
class CreateKartuTemplate extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'default'    => 'Template Kartu Siswa',
            ],
            'svg_depan' => [
                'type'       => 'VARCHAR',
                'constraint' => 225,
                'null'       => true,
            ],
            'svg_belakang' => [
                'type'       => 'VARCHAR',
                'constraint' => 225,
                'null'       => true,
            ],
            // JSON posisi & gaya tiap elemen per sisi kartu
            'layout' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('tb_kartu_template');

        $this->db->table('tb_kartu_template')->insert([
            'id'           => 1,
            'nama'         => 'Template Kartu Siswa',
            // 2.svg adalah desain kartu polos; 1.svg hanya panduan tata letak
            'svg_depan'    => $this->salinContoh('2.svg', 'depan'),
            'svg_belakang' => $this->salinContoh('2.svg', 'belakang'),
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Salin contoh base template bawaan (bila tersedia) ke folder template.
     */
    private function salinContoh(string $berkas, string $sisi): ?string
    {
        $sumber = FCPATH . 'uploads/tmp/' . $berkas;

        if (!is_file($sumber)) {
            return null;
        }

        $direktori = 'uploads/kartu/';
        if (!is_dir(FCPATH . $direktori)) {
            mkdir(FCPATH . $direktori, 0777, true);
        }

        $tujuan = $direktori . 'kartu-' . $sisi . '-bawaan.svg';

        return copy($sumber, FCPATH . $tujuan) ? $tujuan : null;
    }

    public function down()
    {
        $this->forge->dropTable('tb_kartu_template');
    }
}
