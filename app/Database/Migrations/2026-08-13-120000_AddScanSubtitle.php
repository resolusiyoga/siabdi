<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddScanSubtitle extends Migration
{
    public function up()
    {
        $this->forge->addColumn('general_settings', [
            'scan_subtitle' => [
                'type'       => 'VARCHAR',
                'constraint' => 225,
                'null'       => true,
                'default'    => 'Absensi QR Code',
                'after'      => 'school_name'
            ]
        ]);

        $this->db->table('general_settings')
            ->where('scan_subtitle IS NULL', null, false)
            ->update(['scan_subtitle' => 'Absensi QR Code']);
    }

    public function down()
    {
        $this->forge->dropColumn('general_settings', 'scan_subtitle');
    }
}
