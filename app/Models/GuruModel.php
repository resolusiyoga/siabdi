<?php

namespace App\Models;

use CodeIgniter\Model;

class GuruModel extends Model
{
   protected $allowedFields = [
      'nuptk',
      'nama_guru',
      'jenis_kelamin',
      'alamat',
      'no_hp',
      'unique_code'
   ];

   protected $table = 'tb_guru';

   protected $primaryKey = 'id_guru';

   public function cekGuru(string $unique_code)
   {
      return $this->where(['unique_code' => $unique_code])->first();
   }

   public function getAllGuru()
   {
      return $this->orderBy('nama_guru')->findAll();
   }

   public function getGuruById($id)
   {
      return $this->where([$this->primaryKey => $id])->first();
   }

   /**
    * Kode unik baru yang dipastikan belum dipakai guru lain.
    */
   public function kodeUnikBaru(): string
   {
      do {
         $kode = generateUniqueCode();
      } while ($this->db->table($this->table)->where('unique_code', $kode)->countAllResults() > 0);

      return $kode;
   }

   public function createGuru($nuptk, $nama, $jenisKelamin, $alamat, $noHp)
   {
      return $this->save([
         'nuptk' => $nuptk,
         'nama_guru' => $nama,
         'jenis_kelamin' => $jenisKelamin,
         'alamat' => $alamat,
         'no_hp' => $noHp,
         'unique_code' => $this->kodeUnikBaru()
      ]);
   }

   public function updateGuru($id, $nuptk, $nama, $jenisKelamin, $alamat, $noHp)
   {
      return $this->save([
         $this->primaryKey => $id,
         'nuptk' => $nuptk,
         'nama_guru' => $nama,
         'jenis_kelamin' => $jenisKelamin,
         'alamat' => $alamat,
         'no_hp' => $noHp,
      ]);
   }
}
