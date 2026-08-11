<?php

namespace App\Models;

use App\Models\PresensiInterface;
use CodeIgniter\I18n\Time;
use CodeIgniter\Model;
use App\Libraries\enums\Kehadiran;

class PresensiSiswaModel extends Model implements PresensiInterface
{
   protected $primaryKey = 'id_presensi';

   protected $allowedFields = [
      'id_siswa',
      'id_kelas',
      'tanggal',
      'jam_masuk',
      'jam_keluar',
      'jam_dzuhur',
      'jam_ashar',
      'id_kehadiran',
      'keterangan'
   ];

   protected $table = 'tb_presensi_siswa';

   public function cekAbsen(string|int $id, string|Time $date)
   {
      $result = $this->where(['id_siswa' => $id, 'tanggal' => $date])->first();

      if (empty($result)) return false;

      return $result[$this->primaryKey];
   }

   public function absenMasuk(string $id,  $date, $time, $idKelas = '')
   {
      $this->save([
         'id_siswa' => $id,
         'id_kelas' => $idKelas,
         'tanggal' => $date,
         'jam_masuk' => $time,
         // 'jam_keluar' => '',
         'id_kehadiran' => Kehadiran::Hadir->value,
         'keterangan' => ''
      ]);
   }

   public function absenKeluar(string $id, $time)
   {
      $this->update($id, [
         'jam_keluar' => $time,
         'keterangan' => ''
      ]);
   }

   public function absenWaktu(string $idPresensi, string $field, string $time)
   {
      $this->update($idPresensi, [
         $field => $time
      ]);
   }

   public function getPresensiByIdSiswaTanggal($idSiswa, $date)
   {
      return $this->where(['id_siswa' => $idSiswa, 'tanggal' => $date])->first();
   }

   public function getPresensiById(string $idPresensi)
   {
      return $this->where([$this->primaryKey => $idPresensi])->first();
   }

   public function getPresensiByKelasTanggal($idKelas, $tanggal)
   {
      return $this->setTable('tb_siswa')
         ->select('*')
         ->join(
            "(SELECT id_presensi, id_siswa AS id_siswa_presensi, tanggal, jam_masuk, jam_keluar, jam_dzuhur, jam_ashar, id_kehadiran, keterangan FROM tb_presensi_siswa)tb_presensi_siswa",
            "{$this->table}.id_siswa = tb_presensi_siswa.id_siswa_presensi AND tb_presensi_siswa.tanggal = '$tanggal'",
            'left'
         )
         ->join(
            'tb_kehadiran',
            'tb_presensi_siswa.id_kehadiran = tb_kehadiran.id_kehadiran',
            'left'
         )
         ->where("{$this->table}.id_kelas = $idKelas")
         ->orderBy("nama_siswa")
         ->findAll();
   }

   public function getPresensiByKelasJurusanTanggal($kelas, $jurusan, $tanggal)
   {
      $builder = $this->setTable('tb_siswa')
         ->select('*')
         ->select('tb_kelas.kelas AS kelas, tb_jurusan.jurusan AS jurusan')
         ->join(
            'tb_kelas',
            "tb_kelas.id_kelas = {$this->table}.id_kelas",
            'left'
         )
         ->join(
            'tb_jurusan',
            'tb_jurusan.id = tb_kelas.id_jurusan',
            'left'
         )
         ->join(
            "(SELECT id_presensi, id_siswa AS id_siswa_presensi, tanggal, jam_masuk, jam_keluar, jam_dzuhur, jam_ashar, id_kehadiran, keterangan FROM tb_presensi_siswa)tb_presensi_siswa",
            "{$this->table}.id_siswa = tb_presensi_siswa.id_siswa_presensi AND tb_presensi_siswa.tanggal = '$tanggal'",
            'left'
         )
         ->join(
            'tb_kehadiran',
            'tb_presensi_siswa.id_kehadiran = tb_kehadiran.id_kehadiran',
            'left'
         );

      if (!empty($kelas)) {
         $builder = $builder->where('tb_kelas.kelas', $kelas);
      }

      if (!empty($jurusan)) {
         $builder = $builder->where('tb_jurusan.jurusan', $jurusan);
      }

      return $builder->orderBy("tb_kelas.kelas, tb_jurusan.jurusan, nama_siswa")
         ->findAll();
   }

   public function getPresensiByKehadiran(string $idKehadiran, $tanggal)
   {
      $this->join(
         'tb_siswa',
         "tb_presensi_siswa.id_siswa = tb_siswa.id_siswa AND tb_presensi_siswa.tanggal = '$tanggal'",
         'right'
      );

      if ($idKehadiran == '4') {
         $result = $this->findAll();

         $filteredResult = [];

         foreach ($result as $value) {
            if ($value['id_kehadiran'] != ('1' || '2' || '3')) {
               array_push($filteredResult, $value);
            }
         }

         return $filteredResult;
      } else {
         $this->where(['tb_presensi_siswa.id_kehadiran' => $idKehadiran]);
         return $this->findAll();
      }
   }

   public function updatePresensi(
      $idPresensi,
      $idSiswa,
      $idKelas,
      $tanggal,
      $idKehadiran,
      $jamMasuk,
      $jamKeluar,
      $keterangan,
      $jamDzuhur = null,
      $jamAshar = null
   ) {
      $presensi = $this->getPresensiByIdSiswaTanggal($idSiswa, $tanggal);

      $data = [
         'id_siswa' => $idSiswa,
         'id_kelas' => $idKelas,
         'tanggal' => $tanggal,
         'id_kehadiran' => $idKehadiran,
         'keterangan' => $keterangan ?? $presensi['keterangan'] ?? ''
      ];

      if ($idPresensi != null) {
         $data[$this->primaryKey] = $idPresensi;
      }

      if ($jamMasuk != null) {
         $data['jam_masuk'] = $jamMasuk;
      }

      if ($jamKeluar != null) {
         $data['jam_keluar'] = $jamKeluar;
      }

      if ($jamDzuhur != null) {
         $data['jam_dzuhur'] = $jamDzuhur;
      }

      if ($jamAshar != null) {
         $data['jam_ashar'] = $jamAshar;
      }

      return $this->save($data);
   }
}
