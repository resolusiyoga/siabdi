<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use RuntimeException;

/**
 * Pembuat kode unik QR untuk siswa & guru.
 *
 * Pengecekan duplikat dilakukan lintas tabel: halaman scan mencari kode
 * di tb_siswa lebih dulu, baru tb_guru, sehingga kode yang sama pada dua
 * tabel akan selalu terbaca sebagai siswa. Karena itu kode harus unik
 * untuk keduanya, bukan hanya di dalam satu tabel.
 */
class KodeUnik
{
   /** Panjang kode unik */
   public const PANJANG = 9;

   /** Batas percobaan sebelum menyerah (praktis tidak pernah tercapai) */
   private const MAKS_PERCOBAAN = 25;

   /** Tabel dan kolom kode unik yang harus diperiksa */
   private const TABEL = ['tb_siswa', 'tb_guru'];

   /**
    * Kode unik baru yang sudah dipastikan belum dipakai siapa pun.
    *
    * @param string[] $hindari kode tambahan yang dianggap terpakai
    *                          (mis. kode yang baru dibuat dalam satu batch
    *                          tetapi belum tersimpan ke database)
    */
   public static function buat(array $hindari = [], ?BaseConnection $db = null): string
   {
      $db ??= Database::connect();
      $hindari = array_flip($hindari);

      for ($i = 0; $i < self::MAKS_PERCOBAAN; $i++) {
         $kode = generateUniqueCode(self::PANJANG);

         if (!isset($hindari[$kode]) && !self::dipakai($kode, $db)) {
            return $kode;
         }
      }

      throw new RuntimeException('Gagal membuat kode unik yang belum terpakai');
   }

   /** Apakah kode sudah dipakai siswa atau guru? */
   public static function dipakai(string $kode, ?BaseConnection $db = null): bool
   {
      $db ??= Database::connect();

      foreach (self::TABEL as $tabel) {
         if (!$db->tableExists($tabel)) {
            continue;
         }

         if ($db->table($tabel)->where('unique_code', $kode)->countAllResults() > 0) {
            return true;
         }
      }

      return false;
   }
}
