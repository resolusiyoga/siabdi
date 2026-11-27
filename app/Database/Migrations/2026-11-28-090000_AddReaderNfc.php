<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Dukungan reader kartu NFC Si-ABDI (ESP32 + PN532):
 * - tb_siswa.uid_kartu       : UID kartu NFC milik siswa (unik, boleh kosong)
 * - general_settings.nfc_*   : jadwal penentuan jenis absen otomatis
 *                              (reader dengan mode "otomatis")
 * - tb_nfc_kartu_baru        : kartu yang di-tap tapi belum terdaftar,
 *                              untuk didaftarkan admin
 * - tb_nfc_log               : semua tap dari reader + hasilnya. Kunci unik
 *                              (uid, waktu) mencegah tap dobel saat reader
 *                              mengirim ulang antrian offline.
 */
class AddReaderNfc extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tb_siswa', [
            'uid_kartu' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
                'default'    => null,
                'after'      => 'unique_code',
            ],
        ]);
        $this->db->query('ALTER TABLE tb_siswa ADD UNIQUE KEY uniq_uid_kartu (uid_kartu)');

        $this->forge->addColumn('general_settings', [
            'nfc_akhir_masuk'  => ['type' => 'TIME', 'null' => false, 'default' => '10:00:00'],
            'nfc_mulai_dzuhur' => ['type' => 'TIME', 'null' => false, 'default' => '12:00:00'],
            'nfc_akhir_dzuhur' => ['type' => 'TIME', 'null' => false, 'default' => '13:30:00'],
            'nfc_mulai_ashar'  => ['type' => 'TIME', 'null' => false, 'default' => '15:15:00'],
            'nfc_akhir_ashar'  => ['type' => 'TIME', 'null' => false, 'default' => '16:00:00'],
            'nfc_mulai_pulang' => ['type' => 'TIME', 'null' => false, 'default' => '13:30:00'],
        ]);

        $this->forge->addField([
            'uid'       => ['type' => 'VARCHAR', 'constraint' => 30],
            'terakhir'  => ['type' => 'DATETIME'],
            'perangkat' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => ''],
            'jumlah'    => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
        ]);
        $this->forge->addKey('uid', true);
        $this->forge->createTable('tb_nfc_kartu_baru', true);

        $this->forge->addField([
            'id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'uid'       => ['type' => 'VARCHAR', 'constraint' => 30],
            'waktu'     => ['type' => 'DATETIME'],
            'mode'      => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'otomatis'],
            'jenis'     => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'hasil'     => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => ''],
            'id_siswa'  => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'perangkat' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => ''],
            'diterima'  => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['uid', 'waktu'], 'uniq_tap');
        $this->forge->addKey('waktu');
        $this->forge->createTable('tb_nfc_log', true);
    }

    public function down()
    {
        $this->forge->dropTable('tb_nfc_log', true);
        $this->forge->dropTable('tb_nfc_kartu_baru', true);
        $this->forge->dropColumn('general_settings', [
            'nfc_akhir_masuk', 'nfc_mulai_dzuhur', 'nfc_akhir_dzuhur',
            'nfc_mulai_ashar', 'nfc_akhir_ashar', 'nfc_mulai_pulang',
        ]);
        $this->db->query('ALTER TABLE tb_siswa DROP INDEX uniq_uid_kartu');
        $this->forge->dropColumn('tb_siswa', 'uid_kartu');
    }
}
