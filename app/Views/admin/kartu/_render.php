<?php

/**
 * Render satu kartu siswa (satu sisi) dari layout template.
 *
 * Dipakai halaman cetak. Editor membangun DOM yang sama lewat JavaScript.
 *
 * @var array       $layout   layout[$sisi] -> daftar elemen
 * @var string      $sisi     'depan' | 'belakang'
 * @var array       $data     nilai elemen untuk satu siswa
 * @var string|null $bg       path base template (relatif FCPATH)
 * @var array       $elemen   metadata elemen (label & tipe)
 */

$elemenSisi = $layout[$sisi] ?? [];
?>
<div class="kartu">
   <?php if (!empty($bg)) : ?>
      <img class="kartu__bg" src="<?= base_url($bg) ?>" alt="">
   <?php else : ?>
      <div class="kartu__kosong">Base template sisi <?= esc($sisi) ?> belum diunggah</div>
   <?php endif; ?>

   <?php foreach ($elemenSisi as $kunci => $el) : ?>
      <?php
      if (empty($el['tampil']) || !isset($elemen[$kunci])) {
         continue;
      }

      $posisi = sprintf(
         'left:%smm;top:%smm;width:%smm;height:%smm;',
         $el['x'],
         $el['y'],
         $el['w'],
         $el['h']
      );

      if ($elemen[$kunci]['tipe'] === 'teks') {
         $nilai = trim((string) ($data[$kunci] ?? ''));
         if ($nilai === '') {
            continue;
         }
         $nilai = ($el['prefiks'] ?? '') . $nilai;
         $gaya = $posisi . sprintf(
            'font-size:%spt;font-weight:%d;color:%s;%s',
            $el['ukuran'],
            $el['tebal'],
            $el['warna'],
            !empty($el['kapital']) ? 'text-transform:uppercase;' : ''
         );
      ?>
         <div class="kartu__el kartu__teks kartu__teks--<?= esc($el['rata']) ?>" style="<?= esc($gaya, 'attr') ?>">
            <span><?= esc($nilai) ?></span>
         </div>
      <?php
      } else {
         // QR code memakai sumber teks yang dipilih pada sisi ini
         $src = $kunci === 'qrcode'
            ? ($data['qr'][$el['sumber'] ?? 'unique_code'] ?? $data['qrcode'] ?? null)
            : ($data[$kunci] ?? null);
         $gaya = $posisi . sprintf(
            'border-radius:%smm;%s',
            $el['radius'],
            ($el['bingkai'] ?? 0) > 0
               ? sprintf('border:%smm solid %s;', $el['bingkai'], $el['warnaBingkai'])
               : ''
         );
      ?>
         <div class="kartu__el" style="<?= esc($gaya, 'attr') ?>">
            <?php if (!empty($src)) : ?>
               <img class="kartu__gambar" src="<?= $src ?>" style="object-fit:<?= esc($el['isi']) ?>;border-radius:inherit;" alt="">
            <?php else : ?>
               <div class="kartu__gambar--kosong" style="border-radius:inherit;"><?= esc($elemen[$kunci]['label']) ?></div>
            <?php endif; ?>
         </div>
   <?php
      }
   endforeach; ?>
</div>
