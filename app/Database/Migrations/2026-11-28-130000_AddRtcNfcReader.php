<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Informasi RTC DS1307 pada status reader NFC (firmware V1.15+):
 * - rtc         : ok / belum_set / tidak_ada / nonaktif ('' = tidak dilaporkan)
 * - sumber_jam  : ntp / rtc / manual / belum
 * - rtc_waktu   : jam RTC saat laporan terakhir (WIB)
 * - rtc_selisih : jam RTC dikurangi jam server saat laporan diterima (detik)
 */
class AddRtcNfcReader extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tb_nfc_reader', [
            'rtc'         => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => '', 'after' => 'jam_valid'],
            'sumber_jam'  => ['type' => 'VARCHAR', 'constraint' => 6, 'default' => '', 'after' => 'rtc'],
            'rtc_waktu'   => ['type' => 'DATETIME', 'null' => true, 'after' => 'sumber_jam'],
            'rtc_selisih' => ['type' => 'INT', 'constraint' => 11, 'null' => true, 'after' => 'rtc_waktu'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tb_nfc_reader', ['rtc', 'sumber_jam', 'rtc_waktu', 'rtc_selisih']);
    }
}
