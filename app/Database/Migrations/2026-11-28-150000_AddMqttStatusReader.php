<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Mode status reader NFC lewat MQTT (firmware V1.17+), dipilih dari menu
 * Reader NFC. Data absensi tetap dikirim lewat HTTPS pada kedua mode.
 * - general_settings.nfc_metode_status  : http / mqtt
 * - general_settings.nfc_mqtt_keepalive : keep-alive reader ke broker (detik)
 * - tb_nfc_reader.metode                : mode yang sedang dipakai reader
 * - tb_nfc_reader.mqtt_ok               : 1 = reader tersambung ke broker
 */
class AddMqttStatusReader extends Migration
{
    public function up()
    {
        $this->forge->addColumn('general_settings', [
            'nfc_metode_status'  => ['type' => 'VARCHAR', 'constraint' => 4, 'null' => false, 'default' => 'http'],
            'nfc_mqtt_keepalive' => ['type' => 'SMALLINT', 'constraint' => 5, 'null' => false, 'default' => 5],
        ]);
        $this->forge->addColumn('tb_nfc_reader', [
            'metode'  => ['type' => 'VARCHAR', 'constraint' => 4, 'null' => false, 'default' => '', 'after' => 'interval_status'],
            'mqtt_ok' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 0, 'after' => 'metode'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('general_settings', ['nfc_metode_status', 'nfc_mqtt_keepalive']);
        $this->forge->dropColumn('tb_nfc_reader', ['metode', 'mqtt_ok']);
    }
}
