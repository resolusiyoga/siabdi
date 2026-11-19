<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKepalaMadrasahRole extends Migration
{
    public function up()
    {
        $this->forge->getConnection()->query(
            "ALTER TABLE users
            MODIFY COLUMN role ENUM('superadmin','guru','wali_kelas','pegawai','kepala_madrasah','siswa','orangtua') NOT NULL DEFAULT 'guru';"
        );
    }

    public function down()
    {
        $this->forge->getConnection()->query(
            "ALTER TABLE users
            MODIFY COLUMN role ENUM('superadmin','guru','wali_kelas','pegawai','siswa','orangtua') NOT NULL DEFAULT 'guru';"
        );
    }
}
