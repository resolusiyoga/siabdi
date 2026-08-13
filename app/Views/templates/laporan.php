<html>

<head>
   <title>Rekap absen <?= $grup ?></title>
   <style>
      /* Dipakai untuk cetak PDF (window.print) dan ekspor .doc (dibaca Word),
         jadi CSS sengaja dibatasi pada properti yang didukung keduanya. */

      @page {
         size: A4 landscape;
         margin: 10mm;
      }

      body {
         font-family: Arial, Helvetica, sans-serif;
         font-size: 12px;
         color: #1a1a1a;
      }

      table {
         border-collapse: collapse;
      }

      /* ---------- Kop laporan ---------- */
      .kop {
         width: 100%;
         margin-bottom: 4px;
      }

      .kop td {
         vertical-align: middle;
      }

      .kop h2,
      .kop h4 {
         margin: 0;
         text-align: center;
      }

      .kop h2 {
         font-size: 18px;
         letter-spacing: .5px;
      }

      .kop h4 {
         font-size: 13px;
         font-weight: normal;
         margin-top: 3px;
      }

      .meta {
         width: 100%;
         margin-bottom: 6px;
         font-size: 12px;
      }

      .meta .kanan {
         text-align: right;
      }

      /* ---------- Tabel absensi ---------- */
      .absensi {
         width: 100%;
         font-size: 11px;
      }

      .absensi th,
      .absensi td {
         border: 1px solid #9e9e9e;
         padding: 5px 6px;
      }

      .absensi thead th {
         background-color: #eef2e9;
         font-weight: bold;
         text-align: center;
         white-space: nowrap;
      }

      .absensi .col-no {
         width: 28px;
         text-align: center;
      }

      .absensi .col-nama {
         text-align: left;
         white-space: nowrap;
      }

      /* Kolom hari & tanggal: lebar seragam + padding kiri-kanan */
      .absensi .col-hari {
         width: 18px;
         padding-left: 6px;
         padding-right: 6px;
         text-align: center;
      }

      .absensi .col-total {
         width: 22px;
         padding-left: 6px;
         padding-right: 6px;
         text-align: center;
      }

      .absensi tbody td {
         text-align: center;
      }

      .absensi tbody td.nama {
         text-align: left;
         white-space: nowrap;
      }

      /* ---------- Penanda kehadiran ---------- */
      /* Warna dibuat lembut supaya tetap terbaca saat dicetak, dan tiap
         status punya rona berbeda (sebelumnya Sakit & Izin sama-sama kuning). */
      .st-h {
         background-color: #d9efd6;
         color: #1e5620;
         font-weight: bold;
      }

      .st-s {
         background-color: #fdeecd;
         color: #7a5200;
         font-weight: bold;
      }

      .st-i {
         background-color: #dbe6f6;
         color: #1c3f6e;
         font-weight: bold;
      }

      .st-a {
         background-color: #f8dad6;
         color: #8c1d13;
         font-weight: bold;
      }

      /* ---------- Ringkasan ---------- */
      .ringkasan {
         margin-top: 14px;
         font-size: 12px;
      }

      .ringkasan td {
         padding: 2px 4px;
      }
   </style>
</head>


<body>

   <?= $this->renderSection('content') ?>

</body>

</html>
