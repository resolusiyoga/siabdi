<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Template kartu siswa.
 *
 * Hanya ada satu template aktif (baris id = 1). Ukuran kartu tetap
 * 53,98 mm (lebar) x 85,60 mm (tinggi) -- standar ID-1 posisi potret --
 * dan seluruh koordinat elemen disimpan dalam milimeter supaya hasil
 * cetak persis sama di layar maupun di kertas.
 */
class KartuTemplateModel extends Model
{
   protected $table = 'tb_kartu_template';
   protected $primaryKey = 'id';
   protected $allowedFields = ['nama', 'svg_depan', 'svg_belakang', 'layout'];
   protected $useTimestamps = true;

   /** Lebar kartu dalam mm */
   public const LEBAR_MM = 53.98;
   /** Tinggi kartu dalam mm */
   public const TINGGI_MM = 85.60;

   public const ID_AKTIF = 1;

   /** Sisi kartu yang tersedia */
   public const SISI = ['depan', 'belakang'];

   /**
    * Daftar elemen data yang bisa ditata pada kartu.
    * Kunci array dipakai sebagai kunci di JSON layout.
    */
   public const ELEMEN = [
      'foto'      => ['label' => 'Foto Siswa', 'tipe' => 'gambar'],
      'qrcode'    => ['label' => 'QR Code', 'tipe' => 'gambar'],
      'nama'      => ['label' => 'Nama Siswa', 'tipe' => 'teks'],
      'nis'       => ['label' => 'NIS', 'tipe' => 'teks'],
      'nisn'      => ['label' => 'NISN', 'tipe' => 'teks'],
      'kelas'     => ['label' => 'Kelas', 'tipe' => 'teks'],
      'sekolah'   => ['label' => 'Nama Sekolah', 'tipe' => 'teks'],
      'tahun'     => ['label' => 'Tahun Ajaran', 'tipe' => 'teks'],
   ];

   /**
    * Template aktif lengkap dengan layout yang sudah dinormalisasi.
    */
   public function getTemplateAktif(): array
   {
      $row = $this->find(self::ID_AKTIF);

      if (empty($row)) {
         $row = [
            'id'           => self::ID_AKTIF,
            'nama'         => 'Template Kartu Siswa',
            'svg_depan'    => null,
            'svg_belakang' => null,
            'layout'       => null,
         ];
      }

      $row['layout'] = $this->normalisasiLayout(
         json_decode((string) ($row['layout'] ?? ''), true)
      );

      return $row;
   }

   public function simpanLayout(array $layout): bool
   {
      return $this->simpan(['layout' => json_encode($this->normalisasiLayout($layout))]);
   }

   public function simpan(array $data): bool
   {
      $data['id'] = self::ID_AKTIF;

      // baris id=1 dibuat lewat migrasi, tapi tetap dijaga kalau terhapus
      if (!$this->find(self::ID_AKTIF)) {
         return (bool) $this->insert($data, false);
      }

      return (bool) $this->update(self::ID_AKTIF, $data);
   }

   /**
    * Layout bawaan: tata letak yang masuk akal untuk kartu potret 53,98 x 85,60 mm.
    */
   public function layoutDefault(): array
   {
      $teks = fn(array $o) => array_merge([
         'tampil'    => true,
         'x'         => 4.0,
         'y'         => 0.0,
         'w'         => 46.0,
         'h'         => 5.0,
         'ukuran'    => 8.0,
         'tebal'     => 400,
         'warna'     => '#1b1b1b',
         'rata'      => 'center',
         'kapital'   => false,
         'prefiks'   => '',
      ], $o);

      $gambar = fn(array $o) => array_merge([
         'tampil'    => true,
         'x'         => 0.0,
         'y'         => 0.0,
         'w'         => 20.0,
         'h'         => 20.0,
         'radius'    => 0.0,
         'isi'       => 'cover',  // cover | contain
         'bingkai'   => 0.0,
         'warnaBingkai' => '#ffffff',
      ], $o);

      return [
         'depan' => [
            'foto'    => $gambar(['x' => 16.99, 'y' => 26.0, 'w' => 20.0, 'h' => 26.0, 'radius' => 1.5]),
            'nama'    => $teks(['y' => 54.0, 'ukuran' => 9.0, 'tebal' => 700, 'kapital' => true]),
            'nis'     => $teks(['y' => 60.0, 'ukuran' => 7.0, 'prefiks' => 'NIS ']),
            'nisn'    => $teks(['y' => 64.5, 'ukuran' => 7.0, 'prefiks' => 'NISN ']),
            'kelas'   => $teks(['y' => 69.0, 'ukuran' => 7.5, 'tebal' => 600]),
            'qrcode'  => $gambar(['tampil' => false, 'x' => 36.0, 'y' => 70.0, 'w' => 14.0, 'h' => 14.0]),
            'sekolah' => $teks(['tampil' => false, 'y' => 10.0, 'ukuran' => 8.0, 'tebal' => 700]),
            'tahun'   => $teks(['tampil' => false, 'y' => 76.0, 'ukuran' => 6.5]),
         ],
         'belakang' => [
            'qrcode'  => $gambar(['x' => 14.99, 'y' => 24.0, 'w' => 24.0, 'h' => 24.0]),
            'nama'    => $teks(['y' => 50.0, 'ukuran' => 8.0, 'tebal' => 700, 'kapital' => true]),
            'nis'     => $teks(['y' => 55.0, 'ukuran' => 7.0, 'prefiks' => 'NIS ']),
            'nisn'    => $teks(['tampil' => false, 'y' => 59.5, 'ukuran' => 7.0, 'prefiks' => 'NISN ']),
            'kelas'   => $teks(['tampil' => false, 'y' => 64.0, 'ukuran' => 7.0]),
            'foto'    => $gambar(['tampil' => false, 'x' => 4.0, 'y' => 4.0, 'w' => 15.0, 'h' => 20.0]),
            'sekolah' => $teks(['tampil' => false, 'y' => 8.0, 'ukuran' => 7.5, 'tebal' => 700]),
            'tahun'   => $teks(['tampil' => false, 'y' => 76.0, 'ukuran' => 6.5]),
         ],
      ];
   }

   /**
    * Pastikan layout yang tersimpan selalu punya seluruh sisi & elemen
    * dengan tipe data yang benar, walau JSON di database sudah usang.
    */
   public function normalisasiLayout(?array $layout): array
   {
      $default = $this->layoutDefault();
      $hasil = [];

      foreach (self::SISI as $sisi) {
         foreach (self::ELEMEN as $kunci => $meta) {
            $bawaan = $default[$sisi][$kunci];
            $data = $layout[$sisi][$kunci] ?? [];
            $data = is_array($data) ? $data : [];

            $item = ['tampil' => (bool) ($data['tampil'] ?? $bawaan['tampil'])];

            foreach (['x', 'y', 'w', 'h'] as $angka) {
               $item[$angka] = $this->batas(
                  (float) ($data[$angka] ?? $bawaan[$angka]),
                  -50,
                  200
               );
            }

            if ($meta['tipe'] === 'teks') {
               $item['ukuran']  = $this->batas((float) ($data['ukuran'] ?? $bawaan['ukuran']), 3, 40);
               $item['tebal']   = in_array((int) ($data['tebal'] ?? $bawaan['tebal']), [300, 400, 500, 600, 700, 800], true)
                  ? (int) ($data['tebal'] ?? $bawaan['tebal'])
                  : 400;
               $item['warna']   = $this->warna($data['warna'] ?? $bawaan['warna']);
               $item['rata']    = in_array($data['rata'] ?? '', ['left', 'center', 'right'], true)
                  ? $data['rata']
                  : $bawaan['rata'];
               $item['kapital'] = (bool) ($data['kapital'] ?? $bawaan['kapital']);
               $item['prefiks'] = mb_substr((string) ($data['prefiks'] ?? $bawaan['prefiks']), 0, 30);
            } else {
               $item['radius']       = $this->batas((float) ($data['radius'] ?? $bawaan['radius']), 0, 50);
               $item['isi']          = ($data['isi'] ?? '') === 'contain' ? 'contain' : 'cover';
               $item['bingkai']      = $this->batas((float) ($data['bingkai'] ?? $bawaan['bingkai']), 0, 5);
               $item['warnaBingkai'] = $this->warna($data['warnaBingkai'] ?? $bawaan['warnaBingkai']);
            }

            $hasil[$sisi][$kunci] = $item;
         }
      }

      return $hasil;
   }

   private function batas(float $nilai, float $min, float $max): float
   {
      return round(max($min, min($max, $nilai)), 2);
   }

   private function warna($nilai): string
   {
      $nilai = (string) $nilai;

      return preg_match('/^#[0-9a-fA-F]{6}$/', $nilai) ? strtolower($nilai) : '#000000';
   }
}
