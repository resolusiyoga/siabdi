<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Interval laporan status reader NFC yang bisa diatur (firmware V1.16+):
 * - general_settings.nfc_interval_status : interval yang diminta admin (detik)
 * - tb_nfc_reader.interval_status        : interval yang sedang dipakai reader
 *                                          (0 = firmware lama, lapor tiap 60 detik)
 */
class AddIntervalStatusReader extends Migration
{
    public function up()
    {
        $this->forge->addColumn('general_settings', [
            'nfc_interval_status' => ['type' => 'SMALLINT', 'constraint' => 5, 'null' => false, 'default' => 10],
        ]);
        $this->forge->addColumn('tb_nfc_reader', [
            'interval_status' => ['type' => 'SMALLINT', 'constraint' => 5, 'null' => false, 'default' => 0, 'after' => 'uptime'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('general_settings', 'nfc_interval_status');
        $this->forge->dropColumn('tb_nfc_reader', 'interval_status');
    }
}
