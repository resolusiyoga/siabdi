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
      return $this->findAll();
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
