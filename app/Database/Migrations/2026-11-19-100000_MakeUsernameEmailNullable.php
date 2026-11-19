<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * username sudah nullable sejak tabel users dibuat (migrasi Myth\Auth),
 * tapi email masih NOT NULL -- diubah jadi nullable supaya petugas bisa
 * dibuat hanya dengan NUPTK (tanpa email/username sama sekali). Kunci unik
 * di MySQL membolehkan banyak baris NULL, jadi beberapa petugas boleh
 * sama-sama tidak punya email tanpa bentrok "is_unique".
 *
 * nuptk juga diberi kunci unik supaya login via NUPTK (lihat
 * App\Filters\ResolveNuptkLogin) selalu mengarah ke SATU akun, bukan
 * baris pertama yang kebetulan cocok.
 */
class MakeUsernameEmailNullable extends Migration
{
    public function up()
    {
        $this->forge->getConnection()->query(
            "ALTER TABLE users MODIFY COLUMN email VARCHAR(255) NULL;"
        );

        $this->forge->getConnection()->query(
            "ALTER TABLE users ADD UNIQUE KEY users_nuptk_unique (nuptk);"
        );
    }

    public function down()
    {
        $this->forge->getConnection()->query(
            "ALTER TABLE users DROP INDEX users_nuptk_unique;"
        );

        $this->forge->getConnection()->query(
            "ALTER TABLE users MODIFY COLUMN email VARCHAR(255) NOT NULL;"
        );
    }
}
