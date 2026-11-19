<?php

namespace App\Filters;

use App\Models\PetugasModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * AuthController::attemptLogin() bawaan Myth\Auth hanya mengenali kredensial
 * email atau username (lihat $config->validFields). Supaya petugas yang
 * tidak punya email/username (login via NUPTK) tetap bisa masuk tanpa
 * menambal kode vendor, filter ini berjalan SEBELUM attemptLogin: kalau
 * nilai yang diketik di kolom "login" bukan email dan cocok dengan NUPTK
 * seorang petugas, nilai itu diganti dengan username petugas tsb sebelum
 * diteruskan ke validasi bawaan -- alur selanjutnya (deteksi email/username,
 * pencocokan password) berjalan seperti biasa tanpa perubahan lain.
 */
class ResolveNuptkLogin implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!method_exists($request, 'getPost')) {
            return;
        }

        $login = $request->getPost('login');

        if (empty($login) || filter_var($login, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $petugas = (new PetugasModel())->where('nuptk', $login)->first();

        if (empty($petugas) || empty($petugas['username'])) {
            return;
        }

        $post = $request->getPost();
        $post['login'] = $petugas['username'];
        $request->setGlobal('post', $post);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
