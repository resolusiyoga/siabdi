<?= $this->extend('templates/laporan') ?>

<?= $this->section('content') ?>
<table class="kop">
   <tr>
      <td width="110"><img src="<?= getLogo(); ?>" width="90" height="90"></td>
      <td>
         <h2>DAFTAR HADIR GURU</h2>
         <h4><?= $generalSettings->school_name; ?></h4>
         <h4>TAHUN PELAJARAN <?= $generalSettings->school_year; ?></h4>
      </td>
      <td width="110"></td>
   </tr>
</table>

<table class="meta">
   <tr>
      <td>Bulan : <?= $bulan; ?></td>
   </tr>
</table>

<table class="absensi">
   <thead>
      <tr>
         <th class="col-no" rowspan="3">No.</th>
         <th class="col-nama" rowspan="3">Nama</th>
         <th colspan="<?= count($tanggal); ?>">Hari/Tanggal</th>
         <th colspan="4" rowspan="2">Total</th>
      </tr>
      <tr>
         <?php foreach ($tanggal as $value) : ?>
            <th class="col-hari"><?= $value->toLocalizedString('E'); ?></th>
         <?php endforeach; ?>
      </tr>
      <tr>
         <?php foreach ($tanggal as $value) : ?>
            <th class="col-hari"><?= $value->format('d'); ?></th>
         <?php endforeach; ?>
         <th class="col-total st-h">H</th>
         <th class="col-total st-s">S</th>
         <th class="col-total st-i">I</th>
         <th class="col-total st-a">A</th>
      </tr>
   </thead>
   <tbody>

   <?php $i = 0; ?>

   <?php foreach ($listGuru as $guru) : ?>
      <?php
      $jumlahHadir = count(array_filter($listAbsen, function ($a) use ($i) {
         if ($a['lewat'] || is_null($a[$i]['id_kehadiran'])) return false;
         return $a[$i]['id_kehadiran'] == 1;
      }));
      $jumlahSakit = count(array_filter($listAbsen, function ($a) use ($i) {
         if ($a['lewat'] || is_null($a[$i]['id_kehadiran'])) return false;
         return $a[$i]['id_kehadiran'] == 2;
      }));
      $jumlahIzin = count(array_filter($listAbsen, function ($a) use ($i) {
         if ($a['lewat'] || is_null($a[$i]['id_kehadiran'])) return false;
         return $a[$i]['id_kehadiran'] == 3;
      }));
      $jumlahTidakHadir = count(array_filter($listAbsen, function ($a) use ($i) {
         if ($a['lewat']) return false;
         if (is_null($a[$i]['id_kehadiran']) || $a[$i]['id_kehadiran'] == 4) return true;
         return false;
      }));
      ?>
      <tr>
         <td><?= $i + 1; ?></td>
         <td class="nama"><?= $guru['nama_guru']; ?></td>
         <?php foreach ($listAbsen as $absen) : ?>
            <?= kehadiran($absen[$i]['id_kehadiran'] ?? ($absen['lewat'] ? 5 : 4)); ?>
         <?php endforeach; ?>
         <td><?= $jumlahHadir != 0 ? $jumlahHadir : '-'; ?></td>
         <td><?= $jumlahSakit != 0 ? $jumlahSakit : '-'; ?></td>
         <td><?= $jumlahIzin != 0 ? $jumlahIzin : '-'; ?></td>
         <td><?= $jumlahTidakHadir != 0 ? $jumlahTidakHadir : '-'; ?></td>
      </tr>
   <?php
      $i++;
   endforeach; ?>
   </tbody>
</table>

<table class="ringkasan">
   <tr>
      <td>Jumlah guru</td>
      <td>: <?= count($listGuru); ?></td>
   </tr>
   <tr>
      <td>Laki-laki</td>
      <td>: <?= $jumlahGuru['laki']; ?></td>
   </tr>
   <tr>
      <td>Perempuan</td>
      <td>: <?= $jumlahGuru['perempuan']; ?></td>
   </tr>
</table>
<?php
function kehadiran($kehadiran)
{
   $text = '';
   switch ($kehadiran) {
      case 1:
         $text = "<td class='st-h'>H</td>";
         break;
      case 2:
         $text = "<td class='st-s'>S</td>";
         break;
      case 3:
         $text = "<td class='st-i'>I</td>";
         break;
      case 4:
         $text = "<td class='st-a'>A</td>";
         break;
      case 5:
      default:
         $text = "<td></td>";
         break;
   }

   return $text;
}
?>
<?= $this->endSection() ?>