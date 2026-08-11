<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBatasAbsenMasuk extends Migration
{
    public function up()
    {
        $this->forge->addColumn('general_settings', [
            'batas_absen_masuk' => [
                'type'       => 'TIME',
                'null'       => false,
                'default'    => '07:20:00',
                'after'      => 'school_year'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('general_settings', 'batas_absen_masuk');
    }
}
