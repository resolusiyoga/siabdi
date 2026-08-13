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

   public const ROLES = ['superadmin', 'guru', 'wali_kelas', 'siswa', 'orangtua'];

   public function savePetugas($idPetugas, $email, $username, $passwordHash, $role, $idKelas = null)
   {
      $role = in_array($role, self::ROLES, true) ? $role : 'guru';
      $idKelas = $role === 'wali_kelas' ? $idKelas : null;

      return $this->save([
         $this->primaryKey => $idPetugas,
         'email' => $email,
         'username' => $username,
         'password_hash' => $passwordHash,
         'is_superadmin' => $role === 'superadmin' ? '1' : '0',
         'role' => $role,
         'id_kelas' => $idKelas,
      ]);
   }

   public function createPetugas($email, $username, $passwordHash, $role, $idKelas = null)
   {
      $role = in_array($role, self::ROLES, true) ? $role : 'guru';
      $idKelas = $role === 'wali_kelas' ? $idKelas : null;

      return $this->save([
         'email' => $email,
         'username' => $username,
         'password_hash' => $passwordHash,
         'is_superadmin' => $role === 'superadmin' ? '1' : '0',
         'role' => $role,
         'id_kelas' => $idKelas,
         'active' => '1',
      ]);
   }
}
