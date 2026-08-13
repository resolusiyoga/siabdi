<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWaliKelasRole extends Migration
{
    public function up()
    {
        $this->forge->getConnection()->query(
            "ALTER TABLE users
            MODIFY COLUMN role ENUM('superadmin','guru','wali_kelas','siswa','orangtua') NOT NULL DEFAULT 'guru';"
        );

        $this->forge->addColumn('users', [
            'id_kelas' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'role'
            ]
        ]);

        $this->forge->getConnection()->query(
            "ALTER TABLE users
            ADD CONSTRAINT users_id_kelas_foreign FOREIGN KEY (id_kelas) REFERENCES tb_kelas (id_kelas) ON DELETE SET NULL ON UPDATE CASCADE;"
        );
    }

    public function down()
    {
        $this->forge->getConnection()->query(
            "ALTER TABLE users DROP FOREIGN KEY users_id_kelas_foreign;"
        );

        $this->forge->dropColumn('users', 'id_kelas');

        $this->forge->getConnection()->query(
            "ALTER TABLE users
            MODIFY COLUMN role ENUM('superadmin','guru','siswa','orangtua') NOT NULL DEFAULT 'guru';"
        );
    }
}
