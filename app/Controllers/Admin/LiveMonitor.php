<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\KelasModel;
use App\Models\PresensiSiswaModel;
use CodeIgniter\I18n\Time;

/**
 * Live Monitor: layar pemantau siapa saja yang sudah absen hari ini
 * (masuk, dzuhur, ashar, pulang), ditampilkan sebagai umpan kejadian
 * yang menyegarkan diri sendiri -- dimaksudkan untuk ditampilkan di
 * layar besar (TV lobi dsb.), bukan dioperasikan lewat klik berulang.
 */
class LiveMonitor extends BaseController
{
   protected PresensiSiswaModel $presensiSiswaModel;
   protected KelasModel $kelasModel;

   /** Label & urutan tampil tiap jenis absen */
   private const JENIS = [
      'jam_masuk'  => 'Masuk',
      'jam_dzuhur' => 'Dzuhur',
      'jam_ashar'  => 'Ashar',
      'jam_keluar' => 'Pulang',
   ];

   public function __construct()
   {
      $this->presensiSiswaModel = new PresensiSiswaModel();
      $this->kelasModel = new KelasModel();
   }

   public function index()
   {
      $kelasWali = currentUserRole() === 'wali_kelas' ? currentUserKelas() : null;

      $data = [
         'title'       => 'Live Monitor Absensi',
         'ctx'         => 'live-monitor',
         'kelas'       => $this->kelasModel->getDataKelas(),
         'idKelasWali' => $kelasWali['id_kelas'] ?? null,
      ];

      return view('admin/live-monitor/index', $data);
   }

   /**
    * Umpan kejadian absen hari ini (JSON), untuk dipanggil berkala lewat
    * AJAX dari halaman index(). Satu baris presensi bisa menghasilkan
    * sampai 4 kejadian (masuk/dzuhur/ashar/pulang masing-masing jadi
    * barisnya sendiri), diurutkan dari yang paling baru.
    */
   public function data()
   {
      $idKelas = $this->request->getVar('id_kelas') ?: null;

      // wali kelas selalu dikunci ke kelasnya sendiri, apa pun yang dikirim klien
      if (currentUserRole() === 'wali_kelas') {
         $kelasWali = currentUserKelas();
         $idKelas = $kelasWali['id_kelas'] ?? -1;
      }

      $tanggal = Time::today()->toDateString();
      $batasAbsenMasuk = $this->generalSettings->batas_absen_masuk ?? '07:20:00';

      $baris = $this->presensiSiswaModel->getPresensiHariIni($tanggal, $idKelas);

      $kejadian = [];
      $hitung = array_fill_keys(array_keys(self::JENIS), 0);

      foreach ($baris as $r) {
         foreach (self::JENIS as $kolom => $label) {
            if (empty($r[$kolom])) {
               continue;
            }

            $hitung[$kolom]++;

            $kejadian[] = [
               'id'      => $r['id_presensi'] . '-' . $kolom,
               'jenis'   => $label,
               'waktu'   => substr($r[$kolom], 0, 5),
               'urutan'  => $tanggal . ' ' . $r[$kolom],
               'telat'   => $kolom === 'jam_masuk' && $r[$kolom] > $batasAbsenMasuk,
               'nama'    => $r['nama_siswa'],
               'nis'     => $r['nis'],
               'nisn'    => $r['nisn'],
               'kelas'   => labelKelas($r['kelas'] ?? null, $r['jurusan'] ?? null, ''),
               'foto'    => (!empty($r['foto']) && is_file(FCPATH . $r['foto'])) ? assetUrl($r['foto']) : null,
            ];
         }
      }

      // terbaru di atas; batasi 150 baris -- cukup untuk layar yang
      // menggulir sendiri, tanpa membebani sekolah dengan siswa banyak
      usort($kejadian, fn($a, $b) => strcmp($b['urutan'], $a['urutan']));
      $kejadian = array_slice($kejadian, 0, 150);

      return $this->response->setJSON([
         'sukses'  => true,
         'waktu'   => Time::now()->toDateTimeString(),
         'hitung'  => [
            'masuk'  => $hitung['jam_masuk'],
            'dzuhur' => $hitung['jam_dzuhur'],
            'ashar'  => $hitung['jam_ashar'],
            'pulang' => $hitung['jam_keluar'],
         ],
         'kejadian' => $kejadian,
      ]);
   }
}
