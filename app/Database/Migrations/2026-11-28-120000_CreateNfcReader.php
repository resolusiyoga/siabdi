<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Status tiap reader NFC Si-ABDI. Reader mengirim laporan (heartbeat) ke
 * api/reader/status tiap ±60 detik; kolom "terakhir" juga diperbarui setiap
 * reader memanggil API lain. Reader dianggap online bila "terakhir" masih
 * baru (lihat ReaderNfc::BATAS_ONLINE).
 */
class CreateNfcReader extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'perangkat' => ['type' => 'VARCHAR', 'constraint' => 30],
            'versi'     => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => ''],
            'mode'      => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => ''],
            'ip'        => ['type' => 'VARCHAR', 'constraint' => 45, 'default' => ''],
            'rssi'      => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'antrian'   => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'flash'     => ['type' => 'TINYINT', 'constraint' => 3, 'default' => 0],
            'siswa'     => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'pn532'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'jam_valid' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'uptime'    => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'laporan'   => ['type' => 'DATETIME', 'null' => true],
            'terakhir'  => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('perangkat', true);
        $this->forge->createTable('tb_nfc_reader', true);
    }

    public function down()
    {
        $this->forge->dropTable('tb_nfc_reader', true);
    }
}
