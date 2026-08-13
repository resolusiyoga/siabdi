<?php

namespace App\Controllers\Admin;

use App\Models\KelasModel;

use App\Models\SiswaModel;

use App\Models\JurusanModel;

use App\Controllers\BaseController;
use App\Models\KehadiranModel;
use App\Models\PresensiSiswaModel;
use CodeIgniter\I18n\Time;

class DataAbsenSiswa extends BaseController
{
   protected KelasModel $kelasModel;

   protected SiswaModel $siswaModel;

   protected JurusanModel $jurusanModel;

   protected KehadiranModel $kehadiranModel;

   protected PresensiSiswaModel $presensiSiswa;

   protected string $currentDate;

   public function __construct()
   {
      $this->currentDate = Time::today()->toDateString();

      $this->siswaModel = new SiswaModel();

      $this->kehadiranModel = new KehadiranModel();

      $this->kelasModel = new KelasModel();

      $this->jurusanModel = new JurusanModel();

      $this->presensiSiswa = new PresensiSiswaModel();
   }

   public function index()
   {
      $kelas = $this->kelasModel->getDataKelas();

      $kelasWali = currentUserRole() === 'wali_kelas' ? currentUserKelas() : null;

      $data = [
         'title' => 'Data Absen Siswa',
         'ctx' => 'absen-siswa',
         'kelas' => $kelas,
         'jurusan' => $this->jurusanModel->getDataJurusan(),
         'defaultKelas' => $kelasWali['kelas'] ?? null,
         'defaultJurusan' => $kelasWali['jurusan'] ?? null
      ];

      return view('admin/absen/absen-siswa', $data);
   }

   public function ambilDataSiswa()
   {
      // ambil variabel POST
      $kelas = $this->request->getVar('kelas') ?? null;
      $jurusan = $this->request->getVar('jurusan') ?? null;
      $tanggal = $this->request->getVar('tanggal');

      $lewat = Time::parse($tanggal)->isAfter(Time::today());

      $result = $this->presensiSiswa->getPresensiByKelasJurusanTanggal($kelas, $jurusan, $tanggal);

      $data = [
         'data' => $result,
         'listKehadiran' => $this->kehadiranModel->getAllKehadiran(),
         'lewat' => $lewat
      ];

      return view('admin/absen/list-absen-siswa', $data);
   }

   public function ambilKehadiran()
   {
      $idPresensi = $this->request->getVar('id_presensi');
      $idSiswa = $this->request->getVar('id_siswa');

      $data = [
         'presensi' => $this->presensiSiswa->getPresensiById($idPresensi),
         'listKehadiran' => $this->kehadiranModel->getAllKehadiran(),
         'data' => $this->siswaModel->getSiswaById($idSiswa)
      ];

      return view('admin/absen/ubah-kehadiran-modal', $data);
   }

   public function ubahKehadiran()
   {
      // ambil variabel POST
      $idKehadiran = $this->request->getVar('id_kehadiran');
      $idSiswa = $this->request->getVar('id_siswa');
      $idKelas = $this->request->getVar('id_kelas');
      $tanggal = $this->request->getVar('tanggal');
      $jamMasuk = $this->request->getVar('jam_masuk');
      $jamKeluar = $this->request->getVar('jam_keluar');
      $jamDzuhur = $this->request->getVar('jam_dzuhur');
      $jamAshar = $this->request->getVar('jam_ashar');
      $keterangan = $this->request->getVar('keterangan');

      $cek = $this->presensiSiswa->cekAbsen($idSiswa, $tanggal);

      $result = $this->presensiSiswa->updatePresensi(
         $cek == false ? NULL : $cek,
         $idSiswa,
         $idKelas,
         $tanggal,
         $idKehadiran,
         $jamMasuk ?? NULL,
         $jamKeluar ?? NULL,
         $keterangan,
         $jamDzuhur ?? NULL,
         $jamAshar ?? NULL
      );

      $response['nama_siswa'] = $this->siswaModel->getSiswaById($idSiswa)['nama_siswa'];

      if ($result) {
         $response['status'] = TRUE;
      } else {
         $response['status'] = FALSE;
      }

      return $this->response->setJSON($response);
   }
}
