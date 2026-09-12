<?php

namespace App\Libraries;

use App\Models\KartuTemplateModel;

/**
 * Menggambar kartu siswa menjadi berkas PNG memakai GD.
 *
 * Halaman cetak memakai HTML/CSS, tetapi untuk unduhan per siswa kartu
 * dirender di server supaya hasilnya satu berkas gambar siap pakai.
 * Koordinat layout disimpan dalam milimeter, jadi seluruh ukuran dikalikan
 * DPI/25.4 agar hasilnya sama persis dengan versi cetak.
 */
class KartuRenderer
{
   /** Resolusi keluaran (dot per inch) */
   public const DPI = 300;

   private float $pxPerMm;
   private int $lebarPx;
   private int $tinggiPx;

   /** Berkas font per ketebalan */
   private const FONT = [
      300 => 'Roboto-Light.ttf',
      400 => 'Roboto-Regular.ttf',
      500 => 'Roboto-Medium.ttf',
      600 => 'Roboto-Medium.ttf',
      700 => 'Roboto-Bold.ttf',
      800 => 'Roboto-Bold.ttf',
   ];

   public function __construct()
   {
      $this->pxPerMm = self::DPI / 25.4;
      $this->lebarPx = (int) round(KartuTemplateModel::LEBAR_MM * $this->pxPerMm);
      $this->tinggiPx = (int) round(KartuTemplateModel::TINGGI_MM * $this->pxPerMm);
   }

   /**
    * Render satu sisi kartu dan kembalikan berkas PNG sebagai string biner.
    *
    * @param array       $layout layout[$sisi] hasil normalisasi model
    * @param array       $data   nilai elemen satu siswa (foto & qrcode berupa data URI)
    * @param string|null $bg     path base template relatif terhadap FCPATH
    */
   public function render(array $layout, string $sisi, array $data, ?string $bg): string
   {
      $kartu = imagecreatetruecolor($this->lebarPx, $this->tinggiPx);
      imagealphablending($kartu, true);
      imagefilledrectangle($kartu, 0, 0, $this->lebarPx, $this->tinggiPx, imagecolorallocate($kartu, 255, 255, 255));

      $this->gambarLatar($kartu, $bg);

      foreach ($layout[$sisi] ?? [] as $kunci => $el) {
         if (empty($el['tampil']) || !isset(KartuTemplateModel::ELEMEN[$kunci])) {
            continue;
         }

         if (KartuTemplateModel::ELEMEN[$kunci]['tipe'] === 'gambar') {
            $this->gambarElemenGambar($kartu, $el, $data[$kunci] ?? null);
         } else {
            $this->gambarElemenTeks($kartu, $el, (string) ($data[$kunci] ?? ''));
         }
      }

      ob_start();
      imagepng($kartu, null, 6);
      $png = (string) ob_get_clean();
      imagedestroy($kartu);

      return $png;
   }

   private function gambarLatar($kartu, ?string $bg): void
   {
      if (empty($bg) || !is_file(FCPATH . $bg)) {
         return;
      }

      $sumber = $this->bukaGambar(file_get_contents(FCPATH . $bg));
      if ($sumber === null) {
         return;
      }

      // base template diregangkan tepat seukuran kartu, sama seperti versi cetak
      imagecopyresampled(
         $kartu,
         $sumber,
         0,
         0,
         0,
         0,
         $this->lebarPx,
         $this->tinggiPx,
         imagesx($sumber),
         imagesy($sumber)
      );
      imagedestroy($sumber);
   }

   private function gambarElemenGambar($kartu, array $el, ?string $dataUri): void
   {
      if (empty($dataUri) || !preg_match('/^data:image\/[a-z.+-]+;base64,(.+)$/i', $dataUri, $m)) {
         return;
      }

      $sumber = $this->bukaGambar((string) base64_decode($m[1]));
      if ($sumber === null) {
         return;
      }

      [$x, $y, $w, $h] = $this->kotak($el);

      $kotak = imagecreatetruecolor($w, $h);
      imagealphablending($kotak, false);
      imagesavealpha($kotak, true);
      imagefilledrectangle($kotak, 0, 0, $w, $h, imagecolorallocatealpha($kotak, 0, 0, 0, 127));
      imagealphablending($kotak, true);

      $sw = imagesx($sumber);
      $sh = imagesy($sumber);

      if (($el['isi'] ?? 'cover') === 'cover') {
         // potong bagian tengah sumber agar mengisi penuh kotak
         $skala = max($w / $sw, $h / $sh);
         $potongW = (int) round($w / $skala);
         $potongH = (int) round($h / $skala);
         imagecopyresampled(
            $kotak,
            $sumber,
            0,
            0,
            (int) round(($sw - $potongW) / 2),
            (int) round(($sh - $potongH) / 2),
            $w,
            $h,
            $potongW,
            $potongH
         );
      } else {
         $skala = min($w / $sw, $h / $sh);
         $isiW = (int) round($sw * $skala);
         $isiH = (int) round($sh * $skala);
         imagecopyresampled(
            $kotak,
            $sumber,
            (int) round(($w - $isiW) / 2),
            (int) round(($h - $isiH) / 2),
            0,
            0,
            $isiW,
            $isiH,
            $sw,
            $sh
         );
      }

      imagedestroy($sumber);

      $radius = (int) round(($el['radius'] ?? 0) * $this->pxPerMm);
      if ($radius > 0) {
         $this->bulatkanSudut($kotak, $radius);
      }

      $bingkai = (int) round(($el['bingkai'] ?? 0) * $this->pxPerMm);
      if ($bingkai > 0) {
         $this->gambarBingkai($kotak, $bingkai, $el['warnaBingkai'] ?? '#ffffff', $radius);
      }

      imagecopy($kartu, $kotak, $x, $y, 0, 0, $w, $h);
      imagedestroy($kotak);
   }

   private function gambarElemenTeks($kartu, array $el, string $nilai): void
   {
      $nilai = trim($nilai);
      if ($nilai === '') {
         return;
      }

      $nilai = ($el['prefiks'] ?? '') . $nilai;
      if (!empty($el['kapital'])) {
         $nilai = mb_strtoupper($nilai);
      }

      [$x, $y, $w, $h] = $this->kotak($el);

      $font = FCPATH . 'assets/fonts/' . (self::FONT[(int) ($el['tebal'] ?? 400)] ?? self::FONT[400]);
      if (!is_file($font)) {
         return;
      }

      // Ukuran font pt -> em piksel pada DPI keluaran. GD menafsirkan
      // parameter ukuran sebagai point pada 96 dpi (dikalikan 96/72 di
      // dalam libgd), jadi pembaginya 96 dan bukan 72 -- tanpa ini teks
      // tampil sepertiga lebih besar daripada versi cetak HTML.
      $ukuran = (float) $el['ukuran'] * self::DPI / 96;

      $kotakTeks = imagettfbbox($ukuran, 0, $font, $nilai);
      $lebarTeks = $kotakTeks[2] - $kotakTeks[0];
      $atas = $kotakTeks[7];
      $bawah = $kotakTeks[1];

      $posX = match ($el['rata'] ?? 'center') {
         'left'  => $x,
         'right' => $x + $w - $lebarTeks,
         default => $x + (int) round(($w - $lebarTeks) / 2),
      };

      // baseline diletakkan agar teks berada di tengah kotak secara vertikal
      $posY = $y + (int) round(($h - ($bawah - $atas)) / 2) - $atas;

      imagettftext($kartu, $ukuran, 0, $posX, $posY, $this->warna($kartu, $el['warna'] ?? '#000000'), $font, $nilai);
   }

   /** Kotak elemen dalam piksel: [x, y, lebar, tinggi] */
   private function kotak(array $el): array
   {
      return [
         (int) round($el['x'] * $this->pxPerMm),
         (int) round($el['y'] * $this->pxPerMm),
         max(1, (int) round($el['w'] * $this->pxPerMm)),
         max(1, (int) round($el['h'] * $this->pxPerMm)),
      ];
   }

   /** Buat sudut gambar membulat dengan menghapus piksel di luar radius. */
   private function bulatkanSudut($gambar, int $radius): void
   {
      $w = imagesx($gambar);
      $h = imagesy($gambar);
      $radius = min($radius, (int) floor(min($w, $h) / 2));
      $transparan = imagecolorallocatealpha($gambar, 0, 0, 0, 127);

      imagealphablending($gambar, false);
      imagesavealpha($gambar, true);

      $sudut = [
         [$radius, $radius, 0, 0],
         [$w - $radius - 1, $radius, $w - $radius, 0],
         [$radius, $h - $radius - 1, 0, $h - $radius],
         [$w - $radius - 1, $h - $radius - 1, $w - $radius, $h - $radius],
      ];

      foreach ($sudut as [$pusatX, $pusatY, $mulaiX, $mulaiY]) {
         for ($x = $mulaiX; $x < $mulaiX + $radius; $x++) {
            for ($y = $mulaiY; $y < $mulaiY + $radius; $y++) {
               if ((($x - $pusatX) ** 2 + ($y - $pusatY) ** 2) > $radius ** 2) {
                  imagesetpixel($gambar, $x, $y, $transparan);
               }
            }
         }
      }

      imagealphablending($gambar, true);
   }

   private function gambarBingkai($gambar, int $tebal, string $warna, int $radius): void
   {
      $w = imagesx($gambar);
      $h = imagesy($gambar);
      $warnaGd = $this->warna($gambar, $warna);

      imagesetthickness($gambar, $tebal);

      if ($radius > 0) {
         imagerectangle($gambar, (int) ($tebal / 2), (int) ($tebal / 2), $w - 1 - (int) ($tebal / 2), $h - 1 - (int) ($tebal / 2), $warnaGd);
         $this->bulatkanSudut($gambar, $radius);
      } else {
         imagerectangle($gambar, (int) ($tebal / 2), (int) ($tebal / 2), $w - 1 - (int) ($tebal / 2), $h - 1 - (int) ($tebal / 2), $warnaGd);
      }

      imagesetthickness($gambar, 1);
   }

   private function warna($gambar, string $hex)
   {
      $hex = ltrim($hex, '#');
      if (strlen($hex) !== 6) {
         $hex = '000000';
      }

      return imagecolorallocate(
         $gambar,
         (int) hexdec(substr($hex, 0, 2)),
         (int) hexdec(substr($hex, 2, 2)),
         (int) hexdec(substr($hex, 4, 2))
      );
   }

   private function bukaGambar(string $biner)
   {
      $gambar = @imagecreatefromstring($biner);
      if ($gambar === false) {
         return null;   // mis. berkas SVG yang tidak didukung GD
      }

      imagealphablending($gambar, true);

      return $gambar;
   }
}
