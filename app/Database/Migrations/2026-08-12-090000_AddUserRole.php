<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserRole extends Migration
{
    public function up()
    {
        $this->forge->getConnection()->query(
            "ALTER TABLE users
            ADD COLUMN role ENUM('superadmin','guru','siswa','orangtua') NOT NULL DEFAULT 'guru' AFTER is_superadmin;"
        );

        $this->forge->getConnection()->query(
            "UPDATE users SET role = 'superadmin' WHERE is_superadmin = 1;"
        );
    }

    public function down()
    {
        $this->forge->getConnection()->query(
            "ALTER TABLE users DROP COLUMN role;"
        );
    }
}
