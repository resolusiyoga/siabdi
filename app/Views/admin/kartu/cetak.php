<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <title><?= esc($title) ?></title>
   <link href="<?= assetUrl('assets/fonts/fonts.css'); ?>" rel="stylesheet" />
   <link href="<?= assetUrl('assets/css/kartu.css'); ?>" rel="stylesheet" />
   <style>
      /* Lembar cetak A4: 3 kartu per baris, 3 baris per halaman */
      @page {
         size: A4 portrait;
         margin: 8mm;
      }

      body {
         margin: 0;
         background: #e9e9e9;
         font-family: "Roboto", Arial, sans-serif;
      }

      .toolbar {
         position: sticky;
         top: 0;
         z-index: 10;
         display: flex;
         flex-wrap: wrap;
         gap: 8px;
         align-items: center;
         padding: 12px 16px;
         background: #1c655a;
         color: #fff;
      }

      .toolbar a,
      .toolbar button {
         font: inherit;
         padding: 6px 14px;
         border: 0;
         border-radius: 4px;
         background: #fff;
         color: #1c655a;
         cursor: pointer;
         text-decoration: none;
      }

      .lembar {
         display: flex;
         flex-wrap: wrap;
         gap: 4mm;
         justify-content: flex-start;
         padding: 8mm;
         margin: 16px auto;
         width: 210mm;
         box-sizing: border-box;
         background: #fff;
      }

      .lembar__item {
         page-break-inside: avoid;
         break-inside: avoid;
      }

      /* Garis bantu potong digambar DI DALAM kartu sebagai lapisan sendiri.
         Sebelumnya memakai outline yang tergambar di luar kotak, sehingga
         sisi kiri & atas terpotong saat dirender ke PDF. */
      .lembar--potong .kartu::after {
         content: "";
         position: absolute;
         inset: 0;
         border: 0.2mm dashed rgba(0, 0, 0, .35);
         pointer-events: none;
         -webkit-print-color-adjust: exact;
         print-color-adjust: exact;
      }

      @media print {
         .toolbar {
            display: none;
         }

         body {
            background: #fff;
         }

         .lembar {
            width: auto;
            margin: 0;
            padding: 0;
            gap: 3mm;
         }
      }
   </style>
</head>

<body>
   <div class="toolbar">
      <button onclick="window.print()">Cetak / Simpan PDF</button>
      <a href="<?= base_url('admin/kartu') ?>">Kembali</a>
      <span>
         <?= count($kartu) ?> siswa &middot; <?= count($kartu) * count($sisiCetak) ?> kartu &middot;
         ukuran <?= $lebarMm ?> x <?= $tinggiMm ?> mm
      </span>
      <span>Tip: pada dialog cetak pilih kertas A4, skala 100% (jangan "fit to page"), dan aktifkan "background graphics".</span>
   </div>

   <div class="lembar <?= $garisPotong ? 'lembar--potong' : '' ?>">
      <?php foreach ($kartu as $data) : ?>
         <?php foreach ($sisiCetak as $sisi) : ?>
            <div class="lembar__item">
               <?= view('admin/kartu/_render', [
                  'layout' => $template['layout'],
                  'sisi'   => $sisi,
                  'data'   => $data,
                  'bg'     => $template['svg_' . $sisi],
                  'elemen' => $elemen,
               ]) ?>
            </div>
         <?php endforeach; ?>
      <?php endforeach; ?>
   </div>
</body>

</html>
