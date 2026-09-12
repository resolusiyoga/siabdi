<?php

namespace App\Libraries;

use App\Models\KartuTemplateModel;

/**
 * Menyusun kartu siswa sebagai berkas SVG.
 *
 * Berbeda dengan versi PNG (GD), teks dan bingkai di sini tetap berupa
 * objek vektor sehingga tajam pada perbesaran berapa pun dan masih bisa
 * disunting di Illustrator/Inkscape. Latar kartu tetap gambar raster,
 * mengikuti base template yang diunggah.
 *
 * Seluruh koordinat memakai satuan milimeter, sama seperti layout yang
 * tersimpan, jadi ukuran cetaknya persis 53,98 x 85,60 mm.
 */
class KartuSvgRenderer
{
   /** Padanan ketebalan font untuk atribut font-weight */
   private const KELUARGA_FONT = "'Roboto', 'Helvetica Neue', Arial, sans-serif";

   public function render(array $layout, string $sisi, array $data, ?string $bg): string
   {
      $lebar = KartuTemplateModel::LEBAR_MM;
      $tinggi = KartuTemplateModel::TINGGI_MM;

      $isi = '';
      $klip = '';
      $nomorKlip = 0;

      // latar: base template diregangkan tepat seukuran kartu
      $latar = $this->dataUriBerkas($bg);
      if ($latar !== null) {
         $isi .= sprintf(
            '<image x="0" y="0" width="%s" height="%s" preserveAspectRatio="none" href="%s"/>',
            $lebar,
            $tinggi,
            $latar
         );
      } else {
         $isi .= sprintf('<rect x="0" y="0" width="%s" height="%s" fill="#ffffff"/>', $lebar, $tinggi);
      }

      foreach ($layout[$sisi] ?? [] as $kunci => $el) {
         if (empty($el['tampil']) || !isset(KartuTemplateModel::ELEMEN[$kunci])) {
            continue;
         }

         if (KartuTemplateModel::ELEMEN[$kunci]['tipe'] === 'gambar') {
            $src = $data[$kunci] ?? null;
            if (empty($src)) {
               continue;
            }

            $idKlip = 'klip' . (++$nomorKlip);
            $radius = (float) ($el['radius'] ?? 0);

            $klip .= sprintf(
               '<clipPath id="%s"><rect x="%s" y="%s" width="%s" height="%s" rx="%s" ry="%s"/></clipPath>',
               $idKlip,
               $el['x'],
               $el['y'],
               $el['w'],
               $el['h'],
               $radius,
               $radius
            );

            $isi .= sprintf(
               '<image x="%s" y="%s" width="%s" height="%s" preserveAspectRatio="%s" clip-path="url(#%s)" href="%s"/>',
               $el['x'],
               $el['y'],
               $el['w'],
               $el['h'],
               ($el['isi'] ?? 'cover') === 'contain' ? 'xMidYMid meet' : 'xMidYMid slice',
               $idKlip,
               $this->escAtribut($src)
            );

            if (($el['bingkai'] ?? 0) > 0) {
               $isi .= sprintf(
                  '<rect x="%s" y="%s" width="%s" height="%s" rx="%s" ry="%s" fill="none" stroke="%s" stroke-width="%s"/>',
                  $el['x'],
                  $el['y'],
                  $el['w'],
                  $el['h'],
                  $radius,
                  $radius,
                  $this->escAtribut($el['warnaBingkai']),
                  $el['bingkai']
               );
            }

            continue;
         }

         $nilai = trim((string) ($data[$kunci] ?? ''));
         if ($nilai === '') {
            continue;
         }

         $nilai = ($el['prefiks'] ?? '') . $nilai;
         if (!empty($el['kapital'])) {
            $nilai = mb_strtoupper($nilai);
         }

         // teks diletakkan di tengah kotaknya, sama seperti versi HTML & PNG
         [$posX, $anchor] = match ($el['rata'] ?? 'center') {
            'left'  => [$el['x'], 'start'],
            'right' => [$el['x'] + $el['w'], 'end'],
            default => [$el['x'] + $el['w'] / 2, 'middle'],
         };

         $isi .= sprintf(
            '<text x="%s" y="%s" font-family="%s" font-size="%s" font-weight="%d" fill="%s"'
               . ' text-anchor="%s" dominant-baseline="central">%s</text>',
            round($posX, 3),
            round($el['y'] + $el['h'] / 2, 3),
            self::KELUARGA_FONT,
            // 1 satuan viewBox = 1 mm, jadi ukuran font ditulis tanpa satuan;
            // memakai "mm" akan diartikan 1mm = 3,78 satuan dan teks membesar
            round((float) $el['ukuran'] * 25.4 / 72, 3),   // pt -> mm
            $el['tebal'],
            $this->escAtribut($el['warna']),
            $anchor,
            htmlspecialchars($nilai, ENT_XML1 | ENT_QUOTES, 'UTF-8')
         );
      }

      return '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
         . sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"'
               . ' width="%smm" height="%smm" viewBox="0 0 %s %s" version="1.1">',
            $lebar,
            $tinggi,
            $lebar,
            $tinggi
         )
         . ($klip !== '' ? '<defs>' . $klip . '</defs>' : '')
         . $isi
         . '</svg>';
   }

   private function dataUriBerkas(?string $path): ?string
   {
      if (empty($path) || !is_file(FCPATH . $path)) {
         return null;
      }

      $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
      $mime = match ($ext) {
         'png'  => 'image/png',
         'svg'  => 'image/svg+xml',
         default => 'image/jpeg',
      };

      return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents(FCPATH . $path));
   }

   private function escAtribut(string $nilai): string
   {
      return htmlspecialchars($nilai, ENT_XML1 | ENT_QUOTES, 'UTF-8');
   }
}
