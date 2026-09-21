<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Menyisipkan hash CSRF terkini ke header respons setiap request.
 *
 * Kenapa perlu: proteksi CSRF di app ini diatur regenerate=true (hash baru
 * dibuat otomatis setiap kali sebuah POST/PUT/DELETE/PATCH berhasil
 * diverifikasi -- lihat Config\Security). Cookie-nya sendiri otomatis
 * diperbarui browser lewat header Set-Cookie, tapi nilai yang tercetak di
 * <meta name="X-CSRF-TOKEN"> hanya terisi SEKALI saat halaman pertama
 * dimuat. Begitu satu permintaan AJAX berhasil, meta tag itu jadi basi
 * dan SEMUA permintaan AJAX berikutnya di halaman yang sama ditolak (403)
 * -- inilah sebabnya tombol Simpan pada modal potong foto (dan aksi AJAX
 * lain) gagal tersimpan pada percobaan kedua dst tanpa pesan yang jelas.
 *
 * Header ini membiarkan JavaScript (lihat public/assets/js/custom.js,
 * $(document).ajaxComplete) menyegarkan meta tag setelah SETIAP panggilan
 * AJAX, tanpa perlu setiap controller menyertakannya manual di body
 * respons.
 */
class CsrfHashHeader implements FilterInterface
{
   public function before(RequestInterface $request, $arguments = null)
   {
   }

   public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
   {
      $hash = service('security')->getHash();

      if ($hash !== null) {
         $response->setHeader('X-CSRF-Refresh', $hash);
      }
   }
}
