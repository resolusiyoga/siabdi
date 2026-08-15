<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use App\Models\GuruModel;
use App\Models\KelasModel;

/**
 * Halaman untuk menulis unique_code siswa/guru ke kartu NFC.
 *
 * Kartu NFC dipakai sebagai metode scan alternatif dari QR Code.
 * Penulisan kartu dilakukan lewat Web NFC API di browser (Chrome/Edge
 * Android), jadi tidak butuh alat tambahan selain kartu NTAG kosong.
 * Server hanya menyuplai data siswa/guru; proses tulis-baca NFC murni
 * terjadi di sisi klien.
 */
class NfcController extends BaseController
{
   protected SiswaModel $siswaModel;
   protected GuruModel $guruModel;
   protected KelasModel $kelasModel;

   public function __construct()
   {
      $this->siswaModel = new SiswaModel();
      $this->guruModel = new GuruModel();
      $this->kelasModel = new KelasModel();
   }

   public function index()
   {
      $data = [
         'title' => 'Tulis Kartu NFC',
         'ctx' => 'nfc',
         'kelas' => $this->kelasModel->getDataKelas(),
      ];

      return view('admin/nfc/index', $data);
   }

   public function getSiswaByKelas()
   {
      $idKelas = $this->request->getVar('idKelas');

      return $this->response->setJSON($this->siswaModel->getSiswaByKelas($idKelas));
   }

   public function getAllGuru()
   {
      return $this->response->setJSON($this->guruModel->getAllGuru());
   }
}
