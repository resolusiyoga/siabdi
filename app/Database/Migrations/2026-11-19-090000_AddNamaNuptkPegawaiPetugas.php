<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNamaNuptkPegawaiPetugas extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'after'      => 'username',
            ],
            'nuptk' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
                'after'      => 'nama',
            ],
        ]);

        $this->forge->getConnection()->query(
            "ALTER TABLE users
            MODIFY COLUMN role ENUM('superadmin','guru','wali_kelas','pegawai','siswa','orangtua') NOT NULL DEFAULT 'guru';"
        );
    }

    public function down()
    {
        $this->forge->getConnection()->query(
            "ALTER TABLE users
            MODIFY COLUMN role ENUM('superadmin','guru','wali_kelas','siswa','orangtua') NOT NULL DEFAULT 'guru';"
        );

        $this->forge->dropColumn('users', 'nuptk');
        $this->forge->dropColumn('users', 'nama');
    }
}
