<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFotoSiswa extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tb_siswa', [
            'foto' => [
                'type'       => 'VARCHAR',
                'constraint' => 225,
                'null'       => true,
                'after'      => 'unique_code',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tb_siswa', 'foto');
    }
}
