<?php

namespace App\Models;

use CodeIgniter\Model;

class PetugasModel extends Model
{
   protected function initialize()
   {
      $this->allowedFields = [
         'email',
         'username',
         'nama',
         'nuptk',
         'password_hash',
         'is_superadmin',
         'role',
         'id_kelas',
         'active'
      ];
   }

   protected $table = 'users';

   protected $primaryKey = 'id';

   public function getAllPetugas()
   {
      // join kelas supaya wali kelas bisa ditampilkan beserta kelas asuhannya
      return $this->select('users.*, tb_kelas.kelas AS kelas, tb_jurusan.jurusan AS jurusan')
         ->join('tb_kelas', 'tb_kelas.id_kelas = users.id_kelas', 'left')
         ->join('tb_jurusan', 'tb_jurusan.id = tb_kelas.id_jurusan', 'left')
         ->findAll();
   }

   public function getPetugasById($id)
   {
      return $this->where([$this->primaryKey => $id])->first();
   }

   public const ROLES = ['superadmin', 'guru', 'wali_kelas', 'pegawai', 'kepala_madrasah', 'siswa', 'orangtua'];

   public function savePetugas($idPetugas, $email, $username, $passwordHash, $role, $idKelas = null, $nama = null, $nuptk = null)
   {
      $role = in_array($role, self::ROLES, true) ? $role : 'guru';
      $idKelas = $role === 'wali_kelas' ? $idKelas : null;

      return $this->save([
         $this->primaryKey => $idPetugas,
         'email' => $email,
         'username' => $username,
         'nama' => $nama ?: null,
         'nuptk' => $nuptk ?: null,
         'password_hash' => $passwordHash,
         'is_superadmin' => $role === 'superadmin' ? '1' : '0',
         'role' => $role,
         'id_kelas' => $idKelas,
      ]);
   }

   public function createPetugas($email, $username, $passwordHash, $role, $idKelas = null, $nama = null, $nuptk = null)
   {
      $role = in_array($role, self::ROLES, true) ? $role : 'guru';
      $idKelas = $role === 'wali_kelas' ? $idKelas : null;

      return $this->save([
         'email' => $email,
         'username' => $username,
         'nama' => $nama ?: null,
         'nuptk' => $nuptk ?: null,
         'password_hash' => $passwordHash,
         'is_superadmin' => $role === 'superadmin' ? '1' : '0',
         'role' => $role,
         'id_kelas' => $idKelas,
         'active' => '1',
      ]);
   }

   /*
    *-------------------------------------------------------------------------------------------------
    * IMPORT PETUGAS (pola sama dengan SiswaModel::generateCSVObject/importCSVItem)
    *-------------------------------------------------------------------------------------------------
    */

   public function generateCSVObject($filePath)
   {
      $array = [];
      $fields = [];
      $txtName = uniqid() . '.txt';
      $i = 0;
      $handle = fopen($filePath, 'r');
      if ($handle) {
         while (($row = fgetcsv($handle)) !== false) {
            if (empty($fields)) {
               $fields = $row;
               continue;
            }
            foreach ($row as $k => $value) {
               $array[$i][$fields[$k]] = $value;
            }
            $i++;
         }
         if (!feof($handle)) {
            return false;
         }
         fclose($handle);
         if (!empty($array)) {
            $txtFile = fopen(FCPATH . 'uploads/tmp/' . $txtName, 'w');
            fwrite($txtFile, serialize($array));
            fclose($txtFile);
            $obj = new \stdClass();
            $obj->numberOfItems = countItems($array);
            $obj->txtFileName = $txtName;
            @unlink($filePath);
            return $obj;
         }
      }
      return false;
   }

   public function importCSVItem($txtFileName, $index)
   {
      $filePath = FCPATH . 'uploads/tmp/' . $txtFileName;
      $file = fopen($filePath, 'r');
      $content = fread($file, filesize($filePath));
      $array = @unserialize($content);

      if (empty($array)) {
         return null;
      }

      $i = 1;
      foreach ($array as $item) {
         if ($i == $index) {
            $email = getCSVInputValue($item, 'email') ?: null;
            $username = getCSVInputValue($item, 'username') ?: null;
            $nuptk = getCSVInputValue($item, 'nuptk') ?: null;
            $password = getCSVInputValue($item, 'password');

            // Myth\Auth selalu login lewat kolom username di baliknya --
            // baris CSV yang hanya mengisi NUPTK tetap diberi username
            // (disamakan dengan NUPTK) supaya bisa login (lihat juga
            // App\Filters\ResolveNuptkLogin & DataPetugas::submitRegisterPetugas)
            if (empty($username) && !empty($nuptk)) {
               $username = $nuptk;
            }
            $role = getCSVInputValue($item, 'role') ?: 'guru';
            $role = in_array($role, self::ROLES, true) ? $role : 'guru';
            $idKelas = $role === 'wali_kelas' ? (getCSVInputValue($item, 'id_kelas', 'int') ?: null) : null;

            // email opsional, tapi login hanya lewat username/NUPTK --
            // tanpa salah satunya akun ini tidak akan pernah bisa dipakai masuk
            if (empty($password) || (empty($username) && empty($nuptk))) {
               return null;
            }

            $data = [
               'email' => $email,
               'username' => $username,
               'nama' => getCSVInputValue($item, 'nama') ?: null,
               'nuptk' => $nuptk,
               'password_hash' => \Myth\Auth\Password::hash($password),
               'is_superadmin' => $role === 'superadmin' ? '1' : '0',
               'role' => $role,
               'id_kelas' => $idKelas,
               'active' => '1',
            ];

            try {
               $this->insert($data);
            } catch (\Throwable $e) {
               // email/username bentrok dengan akun yang sudah ada dsb --
               // baris ini dilewati, baris CSV berikutnya tetap diproses
               return null;
            }

            $data['username'] = $username;

            return $data;
         }
         $i++;
      }

      return null;
   }
}
