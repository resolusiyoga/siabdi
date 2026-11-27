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

   /**
    * Semua baris presensi hari ini yang sudah punya minimal satu jam
    * terisi (masuk/dzuhur/ashar/pulang), lengkap dengan data siswa --
    * dipakai Live Monitor untuk disusun jadi satu daftar kejadian
    * (tiap jam yang terisi = satu baris "siapa absen apa jam berapa").
    */
   public function getPresensiHariIni(string $tanggal, $idKelas = null)
   {
      $builder = $this->select(
         'tb_presensi_siswa.id_presensi, tb_presensi_siswa.jam_masuk, tb_presensi_siswa.jam_dzuhur, '
            . 'tb_presensi_siswa.jam_ashar, tb_presensi_siswa.jam_keluar, '
            . 'tb_siswa.id_siswa, tb_siswa.nama_siswa, tb_siswa.nis, tb_siswa.nisn, tb_siswa.foto, '
            . 'tb_kelas.id_kelas, tb_kelas.kelas, tb_jurusan.jurusan'
      )
         ->join('tb_siswa', 'tb_siswa.id_siswa = tb_presensi_siswa.id_siswa')
         ->join('tb_kelas', 'tb_kelas.id_kelas = tb_siswa.id_kelas', 'left')
         ->join('tb_jurusan', 'tb_jurusan.id = tb_kelas.id_jurusan', 'left')
         ->where('tb_presensi_siswa.tanggal', $tanggal)
         ->groupStart()
         ->where('tb_presensi_siswa.jam_masuk IS NOT NULL', null, false)
         ->orWhere('tb_presensi_siswa.jam_dzuhur IS NOT NULL', null, false)
         ->orWhere('tb_presensi_siswa.jam_ashar IS NOT NULL', null, false)
         ->orWhere('tb_presensi_siswa.jam_keluar IS NOT NULL', null, false)
         ->groupEnd();

      if (!empty($idKelas)) {
         $builder = $builder->where('tb_kelas.id_kelas', $idKelas);
      }

      return $builder->findAll();
   }

   /**
    * Siswa yang absen masuk melewati jam batas (lihat General Settings ->
    * batas_absen_masuk), lengkap dengan data kelas -- dipakai kartu
    * "Terlambat" di dashboard & halaman detailnya.
    */
   public function getTerlambatByTanggal(string $tanggal, string $batasAbsenMasuk, $idKelas = null, ?string $akhir = null)
   {
      $builder = $this->select(
         'tb_presensi_siswa.id_presensi, tb_presensi_siswa.jam_masuk, tb_presensi_siswa.tanggal, '
            . 'tb_siswa.id_siswa, tb_siswa.nama_siswa, tb_siswa.nis, tb_siswa.nisn, tb_siswa.foto, '
            . 'tb_kelas.id_kelas, tb_kelas.kelas, tb_jurusan.jurusan'
      )
         ->join('tb_siswa', 'tb_siswa.id_siswa = tb_presensi_siswa.id_siswa')
         ->join('tb_kelas', 'tb_kelas.id_kelas = tb_siswa.id_kelas', 'left')
         ->join('tb_jurusan', 'tb_jurusan.id = tb_kelas.id_jurusan', 'left')
         ->where('tb_presensi_siswa.jam_masuk IS NOT NULL', null, false)
         ->where('tb_presensi_siswa.jam_masuk >', $batasAbsenMasuk);

      if (!empty($akhir) && $akhir !== $tanggal) {
         $builder = $builder->where('tb_presensi_siswa.tanggal >=', $tanggal)
            ->where('tb_presensi_siswa.tanggal <=', $akhir);
      } else {
         $builder = $builder->where('tb_presensi_siswa.tanggal', $tanggal);
      }

      if (!empty($idKelas)) {
         $builder = $builder->where('tb_kelas.id_kelas', $idKelas);
      }

      // paling terlambat (jam_masuk terbesar) tampil duluan
      return $builder->orderBy('tb_presensi_siswa.jam_masuk', 'desc')->findAll();
   }

   /**
    * N siswa dengan jam_masuk paling awal pada tanggal tertentu (atau
    * rentang tanggal, kalau $akhir diisi) -- dipakai kartu "Datang
    * Tercepat" di dashboard.
    */
   public function getTercepatByTanggal(string $tanggal, int $limit = 10, $idKelas = null, ?string $akhir = null)
   {
      $builder = $this->select(
         'tb_presensi_siswa.jam_masuk, tb_presensi_siswa.tanggal, '
            . 'tb_siswa.id_siswa, tb_siswa.nama_siswa, tb_siswa.nis, tb_siswa.foto, '
            . 'tb_kelas.id_kelas, tb_kelas.kelas, tb_jurusan.jurusan'
      )
         ->join('tb_siswa', 'tb_siswa.id_siswa = tb_presensi_siswa.id_siswa')
         ->join('tb_kelas', 'tb_kelas.id_kelas = tb_siswa.id_kelas', 'left')
         ->join('tb_jurusan', 'tb_jurusan.id = tb_kelas.id_jurusan', 'left')
         ->where('tb_presensi_siswa.jam_masuk IS NOT NULL', null, false);

      if (!empty($akhir) && $akhir !== $tanggal) {
         $builder = $builder->where('tb_presensi_siswa.tanggal >=', $tanggal)
            ->where('tb_presensi_siswa.tanggal <=', $akhir);
      } else {
         $builder = $builder->where('tb_presensi_siswa.tanggal', $tanggal);
      }

      if (!empty($idKelas)) {
         $builder = $builder->where('tb_kelas.id_kelas', $idKelas);
      }

      return $builder->orderBy('tb_presensi_siswa.jam_masuk', 'asc')
         ->limit($limit)
         ->findAll();
   }

   public function getPresensiByKehadiran(string $idKehadiran, $tanggal, ?string $akhir = null)
   {
      // rentang tanggal (dashboard): join biasa + where tanggal BETWEEN,
      // siswa yang sama sekali tidak absen pada rentang itu tidak ikut
      // terhitung (beda dari perilaku satu-tanggal di bawah).
      if (!empty($akhir) && $akhir !== $tanggal) {
         $builder = $this->join('tb_siswa', 'tb_presensi_siswa.id_siswa = tb_siswa.id_siswa')
            ->where('tb_presensi_siswa.tanggal >=', $tanggal)
            ->where('tb_presensi_siswa.tanggal <=', $akhir);

         if ($idKehadiran == '4') {
            $builder = $builder->where('tb_presensi_siswa.id_kehadiran NOT IN (1,2,3)', null, false);
         } else {
            $builder = $builder->where('tb_presensi_siswa.id_kehadiran', $idKehadiran);
         }

         return $builder->findAll();
      }

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
