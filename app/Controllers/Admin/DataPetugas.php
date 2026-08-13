<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

use App\Models\PetugasModel;
use App\Models\KelasModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Myth\Auth\Password;

class DataPetugas extends BaseController
{
   protected PetugasModel $petugasModel;

   protected KelasModel $kelasModel;

   protected $petugasValidationRules = [
      'email' => [
         'rules' => 'required',
         'errors' => [
            'required' => 'Email harus diisi.',
            'is_unique' => 'Email ini telah terdaftar.'
         ]
      ],
      'username' => [
         'rules' => 'required|min_length[6]',
         'errors' => [
            'required' => 'Username harus diisi',
            'is_unique' => 'Username ini telah terdaftar.'
         ]
      ],
      'password' => [
         'rules' => 'permit_empty|min_length[6]',
      ],
      'role' => [
         'rules' => 'required|in_list[superadmin,guru,wali_kelas,siswa,orangtua]',
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
         'email' => [
            'rules' => 'required|valid_email|is_unique[users.email]',
            'errors' => [
               'required' => 'Email harus diisi.',
               'valid_email' => 'Email tidak valid.',
               'is_unique' => 'Email ini telah terdaftar.'
            ]
         ],
         'username' => [
            'rules' => 'required|min_length[6]|is_unique[users.username]',
            'errors' => [
               'required' => 'Username harus diisi',
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
         'role' => [
            'rules' => 'required|in_list[' . implode(',', PetugasModel::ROLES) . ']',
            'errors' => [
               'required' => 'Peran wajib diisi'
            ]
         ]
      ];

      $role = $this->request->getVar('role');
      $idKelas = $this->request->getVar('id_kelas') ?: null;

      $valid = $this->validate($rules);

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

      $email = $this->request->getVar('email');
      $username = $this->request->getVar('username');
      $passwordHash = Password::hash($this->request->getVar('password'));

      $result = $this->petugasModel->createPetugas($email, $username, $passwordHash, $role, $idKelas);

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

      if ($petugasLama['username'] != $this->request->getVar('username')) {
         $this->petugasValidationRules['username']['rules'] = 'required|is_unique[users.username]';
      }

      if ($petugasLama['email'] != $this->request->getVar('email')) {
         $this->petugasValidationRules['email']['rules'] = 'required|is_unique[users.email]';
      }

      $role = $this->request->getVar('role');
      $idKelas = $this->request->getVar('id_kelas') ?: null;

      // validasi
      $valid = $this->validate($this->petugasValidationRules);

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

      $email = $this->request->getVar('email');
      $username = $this->request->getVar('username');
      $passwordHash = $password ? Password::hash($password) : $petugasLama['password_hash'];

      $result = $this->petugasModel->savePetugas($idPetugas, $email, $username, $passwordHash, $role, $idKelas);

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
}
