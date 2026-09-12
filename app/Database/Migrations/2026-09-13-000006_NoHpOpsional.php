<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Jadikan nomor HP opsional: banyak siswa (terutama kelas bawah) tidak
 * punya nomor sendiri, sehingga kolom ini tidak lagi wajib diisi.
 */
class NoHpOpsional extends Migration
{
    public function up()
    {
        foreach (['tb_siswa', 'tb_guru'] as $tabel) {
            if (!$this->db->tableExists($tabel)) {
                continue;
            }

            $this->forge->modifyColumn($tabel, [
                'no_hp' => [
                    'name'       => 'no_hp',
                    'type'       => 'VARCHAR',
                    'constraint' => 32,
                    'null'       => true,
                ],
            ]);
        }
    }

    public function down()
    {
        foreach (['tb_siswa', 'tb_guru'] as $tabel) {
            if (!$this->db->tableExists($tabel)) {
                continue;
            }

            $this->db->table($tabel)->where('no_hp IS NULL', null, false)->update(['no_hp' => '']);

            $this->forge->modifyColumn($tabel, [
                'no_hp' => [
                    'name'       => 'no_hp',
                    'type'       => 'VARCHAR',
                    'constraint' => 32,
                    'null'       => false,
                ],
            ]);
        }
    }
}
