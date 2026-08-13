<?php

if (!function_exists('currentUserRole')) {
   function currentUserRole(): ?string
   {
      $currentUser = user();

      if (empty($currentUser)) {
         return null;
      }

      return $currentUser->toArray()['role'] ?? null;
   }
}

if (!function_exists('isSuperadmin')) {
   function isSuperadmin(): bool
   {
      return currentUserRole() === 'superadmin';
   }
}

if (!function_exists('currentUserKelas')) {
   function currentUserKelas(): ?array
   {
      $currentUser = user();

      if (empty($currentUser)) {
         return null;
      }

      $data = $currentUser->toArray();

      if (empty($data['id_kelas'])) {
         return null;
      }

      $db = \Config\Database::connect();

      $row = $db->table('tb_kelas')
         ->select('tb_kelas.id_kelas, tb_kelas.kelas, tb_jurusan.jurusan')
         ->join('tb_jurusan', 'tb_jurusan.id = tb_kelas.id_jurusan')
         ->where('tb_kelas.id_kelas', $data['id_kelas'])
         ->get()
         ->getRowArray();

      return $row ?: null;
   }
}
