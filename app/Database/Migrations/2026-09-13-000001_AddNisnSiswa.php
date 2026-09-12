<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNisnSiswa extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tb_siswa', [
            'nisn' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'nis',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tb_siswa', 'nisn');
    }
}
