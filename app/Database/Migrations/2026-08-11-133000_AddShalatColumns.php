<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddShalatColumns extends Migration
{
    public function up()
    {
        $this->forge->getConnection()->query(
            "ALTER TABLE tb_presensi_siswa
            ADD COLUMN jam_dzuhur TIME NULL DEFAULT NULL AFTER jam_masuk,
            ADD COLUMN jam_ashar TIME NULL DEFAULT NULL AFTER jam_dzuhur;"
        );
    }

    public function down()
    {
        $this->forge->getConnection()->query(
            "ALTER TABLE tb_presensi_siswa
            DROP COLUMN jam_dzuhur,
            DROP COLUMN jam_ashar;"
        );
    }
}
