<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

use App\Models\GuruModel;
use App\Models\SiswaModel;
use App\Models\KelasModel;
use App\Models\PetugasModel;
use App\Models\PresensiGuruModel;
use App\Models\PresensiSiswaModel;
use CodeIgniter\I18n\Time;
use Config\AbsensiSekolah as ConfigAbsensiSekolah;

class Dashboard extends BaseController
{
   protected SiswaModel $siswaModel;
   protected GuruModel $guruModel;

   protected KelasModel $KelasModel;

   protected PresensiSiswaModel $presensiSiswaModel;
   protected PresensiGuruModel $presensiGuruModel;

   protected PetugasModel $petugasModel;

   public function __construct()
   {
      $this->siswaModel = new SiswaModel();
      $this->guruModel = new GuruModel();
      $this->KelasModel = new KelasModel();
      $this->presensiSiswaModel = new PresensiSiswaModel();
      $this->presensiGuruModel = new PresensiGuruModel();
      $this->petugasModel = new PetugasModel();
   }

   public function index()
   {
      $now = Time::now();

      $dateRange = [];
      $siswaKehadiranArray = [];
      $guruKehadiranArray = [];

      for ($i = 6; $i >= 0; $i--) {
         $date = $now->subDays($i)->toDateString();
         if ($i == 0) {
            $formattedDate = "Hari ini";
         } else {
            $t = $now->subDays($i);
            $formattedDate = "{$t->getDay()} " . substr($t->toFormattedDateString(), 0, 3);
         }
         array_push($dateRange, $formattedDate);
         array_push(
            $siswaKehadiranArray,
            count($this->presensiSiswaModel
               ->join('tb_siswa', 'tb_presensi_siswa.id_siswa = tb_siswa.id_siswa', 'left')
               ->where(['tb_presensi_siswa.tanggal' => "$date", 'tb_presensi_siswa.id_kehadiran' => '1'])->findAll())
         );
         array_push(
            $guruKehadiranArray,
            count($this->presensiGuruModel
               ->join('tb_guru', 'tb_presensi_guru.id_guru = tb_guru.id_guru', 'left')
               ->where(['tb_presensi_guru.tanggal' => "$date", 'tb_presensi_guru.id_kehadiran' => '1'])->findAll())
         );
      }

      $today = $now->toDateString();

      // --- timeframe pilihan user (tanggal tunggal atau rentang) ---
      $mode = $this->request->getVar('mode') === 'range' ? 'range' : 'single';
      $tanggalPilihan = $this->validTanggal($this->request->getVar('tanggal')) ?? $today;
      $mulaiPilihan = $this->validTanggal($this->request->getVar('mulai')) ?? $tanggalPilihan;
      $akhirPilihan = $this->validTanggal($this->request->getVar('akhir')) ?? $tanggalPilihan;

      if ($mode === 'range') {
         $tanggalAwal = min($mulaiPilihan, $akhirPilihan);
         $tanggalAkhir = max($mulaiPilihan, $akhirPilihan);
      } else {
         $tanggalAwal = $tanggalAkhir = $tanggalPilihan;
      }

      $batasAbsenMasuk = $this->generalSettings->batas_absen_masuk ?? '07:20:00';
      $siswaTerlambatSemua = $this->presensiSiswaModel->getTerlambatByTanggal($tanggalAwal, $batasAbsenMasuk, null, $tanggalAkhir);

      $data = [
         'title' => 'Dashboard',
         'ctx' => 'dashboard',

         'siswa' => $this->siswaModel->getAllSiswaWithKelas(),
         'guru' => $this->guruModel->getAllGuru(),

         'kelas' => $this->KelasModel->getDataKelas(),

         'dateRange' => $dateRange,
         'dateNow' => $now->toLocalizedString('d MMMM Y'),

         'grafikKehadiranSiswa' => $siswaKehadiranArray,
         'grafikkKehadiranGuru' => $guruKehadiranArray,

         // --- kontrol timeframe ---
         'hariIni' => $today,
         'timeframeMode' => $mode,
         'tanggalPilihan' => $tanggalPilihan,
         'mulaiPilihan' => $mulaiPilihan,
         'akhirPilihan' => $akhirPilihan,
         'timeframeRentang' => $mode === 'range',
         'labelPeriode' => $this->labelPeriode($mode, $tanggalAwal, $tanggalAkhir, $today),

         'jumlahKehadiranSiswa' => [
            'hadir' => count($this->presensiSiswaModel->getPresensiByKehadiran('1', $tanggalAwal, $tanggalAkhir)),
            'sakit' => count($this->presensiSiswaModel->getPresensiByKehadiran('2', $tanggalAwal, $tanggalAkhir)),
            'izin' => count($this->presensiSiswaModel->getPresensiByKehadiran('3', $tanggalAwal, $tanggalAkhir)),
            'alfa' => count($this->presensiSiswaModel->getPresensiByKehadiran('4', $tanggalAwal, $tanggalAkhir))
         ],

         'jumlahTerlambatSiswa' => count($siswaTerlambatSemua),
         'batasAbsenMasuk' => $batasAbsenMasuk,

         'siswaTercepat' => $this->petakanFoto($this->presensiSiswaModel->getTercepatByTanggal($tanggalAwal, 10, null, $tanggalAkhir)),
         'siswaTerlambat' => $this->petakanFoto(array_slice($siswaTerlambatSemua, 0, 10)),

         'jumlahKehadiranGuru' => [
            'hadir' => count($this->presensiGuruModel->getPresensiByKehadiran('1', $tanggalAwal, $tanggalAkhir)),
            'sakit' => count($this->presensiGuruModel->getPresensiByKehadiran('2', $tanggalAwal, $tanggalAkhir)),
            'izin' => count($this->presensiGuruModel->getPresensiByKehadiran('3', $tanggalAwal, $tanggalAkhir)),
            'alfa' => count($this->presensiGuruModel->getPresensiByKehadiran('4', $tanggalAwal, $tanggalAkhir))
         ],

         'petugas' => $this->petugasModel->getAllPetugas(),
      ];

      return view('admin/dashboard', $data);
   }

   /**
    * Validasi string tanggal dari GET (dipakai mentah di query SQL pada
    * beberapa model lama) -- hanya format Y-m-d yang benar-benar tanggal
    * kalender yang diloloskan, selain itu dianggap tidak diisi.
    */
   private function validTanggal(?string $value): ?string
   {
      if (empty($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
         return null;
      }

      [$tahun, $bulan, $hari] = array_map('intval', explode('-', $value));

      return checkdate($bulan, $hari, $tahun) ? $value : null;
   }

   /**
    * Label tanggal/rentang yang ditampilkan di toolbar & kartu dashboard.
    */
   private function labelPeriode(string $mode, string $awal, string $akhir, string $today): string
   {
      if ($mode === 'single') {
         return $awal === $today ? 'Hari ini' : Time::parse($awal)->toLocalizedString('d MMMM Y');
      }

      return Time::parse($awal)->toLocalizedString('d MMM Y') . ' – ' . Time::parse($akhir)->toLocalizedString('d MMM Y');
   }

   /**
    * Ganti path foto siswa jadi URL asset kalau berkasnya ada, selain itu
    * null supaya tampilan pakai ikon placeholder.
    */
   private function petakanFoto(array $daftarSiswa): array
   {
      return array_map(function ($s) {
         $s['foto'] = (!empty($s['foto']) && is_file(FCPATH . $s['foto'])) ? assetUrl($s['foto']) : null;
         return $s;
      }, $daftarSiswa);
   }
}
