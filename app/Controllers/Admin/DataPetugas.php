<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

use App\Models\PetugasModel;
use App\Models\KelasModel;
use App\Models\UploadModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Myth\Auth\Password;

class DataPetugas extends BaseController
{
   protected PetugasModel $petugasModel;

   protected KelasModel $kelasModel;

   protected $petugasValidationRules = [
      // email & username sekarang opsional -- petugas boleh hanya
      // punya NUPTK sebagai identitas login (lihat identitasLoginKosong())
      'email' => [
         'rules' => 'permit_empty|valid_email',
         'errors' => [
            'valid_email' => 'Email tidak valid.',
            'is_unique' => 'Email ini telah terdaftar.'
         ]
      ],
      'username' => [
         'rules' => 'permit_empty|min_length[6]',
         'errors' => [
            'is_unique' => 'Username ini telah terdaftar.'
         ]
      ],
      'password' => [
         'rules' => 'permit_empty|min_length[6]',
      ],
      'nama' => [
         'rules' => 'permit_empty|max_length[150]',
         'errors' => [
            'max_length' => 'Nama maksimal 150 karakter'
         ]
      ],
      'nuptk' => [
         'rules' => 'permit_empty|max_length[30]',
         'errors' => [
            'max_length' => 'NUPTK maksimal 30 karakter'
         ]
      ],
      'role' => [
         'rules' => 'required|in_list[superadmin,guru,wali_kelas,pegawai,siswa,orangtua]',
         'errors' => [
            'required' => 'Peran wajib diisi',
            'in_list' => 'Peran tidak valid'
         ]
      ]
   ];

   private function kelasKosongUntukWaliKelas(?string $role, $idKelas): bool
   {
      return $role === 'wali_kelas' && empty($idKelas);
   }

   // login hanya bisa lewat username atau NUPTK (lihat
   // App\Filters\ResolveNuptkLogin) -- email tidak dipakai untuk login,
   // jadi minimal salah satu dari keduanya wajib diisi supaya akun bisa
   // dipakai masuk sama sekali.
   private function identitasLoginKosong(?string $username, ?string $nuptk): bool
   {
      return empty($username) && empty($nuptk);
   }


   public function __construct()
   {
      $this->petugasModel = new PetugasModel();
      $this->kelasModel = new KelasModel();
   }

   public function index()
   {
      if (!isSuperadmin()) {
         return redirect()->to('admin');
      }

      $data = [
         'title' => 'Data Petugas',
         'ctx' => 'petugas'
      ];

      return view('admin/petugas/data-petugas', $data);
   }

   public function ambilDataPetugas()
   {
      if (!isSuperadmin()) {
         return redirect()->to('admin');
      }

      $petugas = $this->petugasModel->getAllPetugas();

      $data = [
         'data' => $petugas,
         'empty' => empty($petugas)
      ];

      return view('admin/petugas/list-data-petugas', $data);
   }

   public function registerPetugas()
   {
      if (!isSuperadmin()) {
         return redirect()->to('admin');
      }

      $data = [
         'title' => 'Register Petugas',
         'ctx' => 'petugas',
         'kelas' => $this->kelasModel->getDataKelas()
      ];

      return view('admin/petugas/register', $data);
   }

   public function submitRegisterPetugas()
   {
      if (!isSuperadmin()) {
         return redirect()->to('admin');
      }

      $rules = [
         // email & username opsional -- lihat identitasLoginKosong()
         'email' => [
            'rules' => 'permit_empty|valid_email|is_unique[users.email]',
            'errors' => [
               'valid_email' => 'Email tidak valid.',
               'is_unique' => 'Email ini telah terdaftar.'
            ]
         ],
         'username' => [
            'rules' => 'permit_empty|min_length[6]|is_unique[users.username]',
            'errors' => [
               'is_unique' => 'Username ini telah terdaftar.'
            ]
         ],
         'password' => [
            'rules' => 'required|min_length[6]',
            'errors' => [
               'required' => 'Password harus diisi'
            ]
         ],
         'pass_confirm' => [
            'rules' => 'required|matches[password]',
            'errors' => [
               'matches' => 'Konfirmasi password tidak sama'
            ]
         ],
         'nama' => [
            'rules' => 'permit_empty|max_length[150]',
            'errors' => [
               'max_length' => 'Nama maksimal 150 karakter'
            ]
         ],
         'nuptk' => [
            'rules' => 'permit_empty|max_length[30]|is_unique[users.nuptk]',
            'errors' => [
               'max_length' => 'NUPTK maksimal 30 karakter',
               'is_unique' => 'NUPTK ini telah terdaftar.'
            ]
         ],
         'role' => [
            'rules' => 'required|in_list[' . implode(',', PetugasModel::ROLES) . ']',
            'errors' => [
               'required' => 'Peran wajib diisi'
            ]
         ]
      ];

      $role = $this->request->getVar('role');
      $idKelas = $this->request->getVar('id_kelas') ?: null;
      $username = $this->request->getVar('username');
      $nuptk = $this->request->getVar('nuptk');

      // Myth\Auth (lihat App\Filters\ResolveNuptkLogin) selalu login lewat
      // kolom username di baliknya -- kalau admin sengaja mengosongkan
      // username dan hanya mengisi NUPTK, username diisi otomatis dari NUPTK
      // supaya akun ini tetap bisa login. Dilakukan SEBELUM validasi supaya
      // is_unique[users.username] ikut memeriksa nilai hasil fallback ini.
      if (empty($username) && !empty($nuptk)) {
         $username = $nuptk;
         $this->request->setGlobal('post', array_merge($this->request->getPost(), ['username' => $username]));
      }

      $valid = $this->validate($rules);

      if ($valid && $this->identitasLoginKosong($username, $nuptk)) {
         $this->validator->setError('username', 'Isi username atau NUPTK supaya petugas ini bisa login');
         $valid = false;
      }

      if ($valid && $this->kelasKosongUntukWaliKelas($role, $idKelas)) {
         $this->validator->setError('id_kelas', 'Kelas wajib dipilih untuk wali kelas');
         $valid = false;
      }

      if (!$valid) {
         $data = [
            'title' => 'Register Petugas',
            'ctx' => 'petugas',
            'kelas' => $this->kelasModel->getDataKelas(),
            'validation' => $this->validator,
            'oldInput' => $this->request->getVar()
         ];
         return view('admin/petugas/register', $data);
      }

      $email = $this->request->getVar('email') ?: null;
      $username = $username ?: null;
      $nama = $this->request->getVar('nama') ?: null;
      $nuptk = $nuptk ?: null;
      $passwordHash = Password::hash($this->request->getVar('password'));

      $result = $this->petugasModel->createPetugas($email, $username, $passwordHash, $role, $idKelas, $nama, $nuptk);

      if ($result) {
         session()->setFlashdata([
            'msg' => 'Registrasi petugas berhasil',
            'error' => false
         ]);
         return redirect()->to('/admin/petugas');
      }

      session()->setFlashdata([
         'msg' => 'Gagal registrasi petugas',
         'error' => true
      ]);
      return redirect()->to('/admin/petugas/register');
   }

   public function formEditPetugas($id)
   {
      if (!isSuperadmin()) {
         return redirect()->to('admin');
      }

      $petugas = $this->petugasModel->getPetugasById($id);

      if (empty($petugas)) {
         throw new PageNotFoundException('Data petugas dengan id ' . $id . ' tidak ditemukan');
      }

      $data = [
         'data' => $petugas,
         'ctx' => 'petugas',
         'title' => 'Edit Data Petugas',
         'kelas' => $this->kelasModel->getDataKelas()
      ];

      return view('admin/petugas/edit-data-petugas', $data);
   }

   public function updatePetugas()
   {
      if (!isSuperadmin()) {
         return redirect()->to('admin');
      }

      $idPetugas = $this->request->getVar('id');

      $petugasLama = $this->petugasModel->getPetugasById($idPetugas);

      $username = $this->request->getVar('username');
      $email = $this->request->getVar('email');
      $nuptk = $this->request->getVar('nuptk');

      // lihat catatan yang sama di submitRegisterPetugas(): Myth\Auth selalu
      // login lewat kolom username di baliknya, jadi username dikosongkan
      // tapi NUPTK diisi -> username diisi otomatis dari NUPTK
      if (empty($username) && !empty($nuptk)) {
         $username = $nuptk;
         $this->request->setGlobal('post', array_merge($this->request->getPost(), ['username' => $username]));
      }

      // is_unique hanya ditambahkan kalau nilainya memang berubah dari
      // data lama -- supaya menyimpan ulang tanpa mengubah username/
      // email/nuptk sendiri tidak dianggap "bentrok dengan diri sendiri"
      if ((string) $petugasLama['username'] !== (string) $username) {
         $this->petugasValidationRules['username']['rules'] = 'permit_empty|min_length[6]|is_unique[users.username]';
      }

      if ((string) $petugasLama['email'] !== (string) $email) {
         $this->petugasValidationRules['email']['rules'] = 'permit_empty|valid_email|is_unique[users.email]';
      }

      if ((string) ($petugasLama['nuptk'] ?? '') !== (string) $nuptk) {
         $this->petugasValidationRules['nuptk']['rules'] = 'permit_empty|max_length[30]|is_unique[users.nuptk]';
      }

      $role = $this->request->getVar('role');
      $idKelas = $this->request->getVar('id_kelas') ?: null;

      // validasi
      $valid = $this->validate($this->petugasValidationRules);

      if ($valid && $this->identitasLoginKosong($username, $nuptk)) {
         $this->validator->setError('username', 'Isi username atau NUPTK supaya petugas ini bisa login');
         $valid = false;
      }

      if ($valid && $this->kelasKosongUntukWaliKelas($role, $idKelas)) {
         $this->validator->setError('id_kelas', 'Kelas wajib dipilih untuk wali kelas');
         $valid = false;
      }

      if (!$valid) {
         $data = [
            'data' => $this->petugasModel->getPetugasById($idPetugas),
            'ctx' => 'petugas',
            'title' => 'Edit Data Petugas',
            'kelas' => $this->kelasModel->getDataKelas(),
            'validation' => $this->validator,
            'oldInput' => $this->request->getVar()
         ];
         return view('admin/petugas/edit-data-petugas', $data);
      }

      $password = $this->request->getVar('password') ?? false;

      $email = $email ?: null;
      $username = $username ?: null;
      $nama = $this->request->getVar('nama') ?: null;
      $nuptk = $nuptk ?: null;
      $passwordHash = $password ? Password::hash($password) : $petugasLama['password_hash'];

      $result = $this->petugasModel->savePetugas($idPetugas, $email, $username, $passwordHash, $role, $idKelas, $nama, $nuptk);

      if ($result) {
         session()->setFlashdata([
            'msg' => 'Edit data berhasil',
            'error' => false
         ]);
         return redirect()->to('/admin/petugas');
      }

      session()->setFlashdata([
         'msg' => 'Gagal mengubah data',
         'error' => true
      ]);
      return redirect()->to('/admin/petugas/edit/' . $idPetugas);
   }

   public function delete($id)
   {
      if (!isSuperadmin()) {
         return redirect()->to('admin');
      }

      $result = $this->petugasModel->delete($id);

      if ($result) {
         session()->setFlashdata([
            'msg' => 'Data berhasil dihapus',
            'error' => false
         ]);
         return redirect()->to('/admin/petugas');
      }

      session()->setFlashdata([
         'msg' => 'Gagal menghapus data',
         'error' => true
      ]);
      return redirect()->to('/admin/petugas');
   }

   /*
    *-------------------------------------------------------------------------------------------------
    * IMPORT PETUGAS (pola sama dengan import siswa: upload CSV -> serialize
    * sementara ke uploads/tmp -> diimpor baris per baris lewat AJAX)
    *-------------------------------------------------------------------------------------------------
    */

   public function bulkPostPetugas()
   {
      if (!isSuperadmin()) {
         session()->setFlashdata(['msg' => 'Aksi ini khusus untuk superadmin', 'error' => true]);
         return redirect()->to('/admin/petugas');
      }

      $data['title'] = 'Import Petugas';
      $data['ctx'] = 'petugas';
      $data['kelas'] = $this->kelasModel->getDataKelas();

      return view('admin/petugas/import-petugas', $data);
   }

   public function generateCSVObjectPost()
   {
      if (!isSuperadmin()) {
         echo json_encode(['result' => 0]);
         exit();
      }

      $uploadModel = new UploadModel();
      // bersihkan berkas txt sementara lama
      $files = glob(FCPATH . 'uploads/tmp/*.txt');
      if (!empty($files)) {
         foreach ($files as $item) {
            @unlink($item);
         }
      }
      $file = $uploadModel->uploadCSVFile('file');
      if (!empty($file) && !empty($file['path'])) {
         $obj = $this->petugasModel->generateCSVObject($file['path']);
         if (!empty($obj)) {
            echo json_encode([
               'result' => 1,
               'numberOfItems' => $obj->numberOfItems,
               'txtFileName' => $obj->txtFileName,
            ]);
            exit();
         }
      }
      echo json_encode(['result' => 0]);
   }

   public function importCSVItemPost()
   {
      if (!isSuperadmin()) {
         echo json_encode(['result' => 0]);
         exit();
      }

      $txtFileName = inputPost('txtFileName');
      $index = inputPost('index');
      $petugas = $this->petugasModel->importCSVItem($txtFileName, $index);

      if (!empty($petugas)) {
         echo json_encode(['result' => 1, 'petugas' => $petugas, 'index' => $index]);
      } else {
         echo json_encode(['result' => 0, 'index' => $index]);
      }
   }

   public function downloadCSVFilePost()
   {
      $submit = inputPost('submit');
      $response = \Config\Services::response();
      if ($submit == 'csv_petugas_template') {
         return $response->download(FCPATH . 'assets/file/csv_petugas_template.csv', null);
      }
   }
}
