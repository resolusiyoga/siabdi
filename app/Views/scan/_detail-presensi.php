<?php

use App\Libraries\enums\TipeUser;

/**
 * Partial detail hasil scan.
 *
 * @var mixed $type      TipeUser::Siswa | TipeUser::Guru
 * @var array $data      data siswa/guru
 * @var array $presensi  data presensi hari ini (opsional)
 */

$presensi = $presensi ?? [];

$nama = '-';
$meta = '';
$jam  = [];

switch ($type) {
   case TipeUser::Siswa:
      $nama = $data['nama_siswa'];
      $meta = 'NIS ' . esc($data['nis']) . ' &middot; ' . esc(labelKelas($data['kelas'], $data['jurusan']));
      $jam  = [
         'Masuk'  => $presensi['jam_masuk'] ?? null,
         'Dzuhur' => $presensi['jam_dzuhur'] ?? null,
         'Ashar'  => $presensi['jam_ashar'] ?? null,
         'Pulang' => $presensi['jam_keluar'] ?? null,
      ];
      break;

   case TipeUser::Guru:
      $nama = $data['nama_guru'];
      $meta = 'NUPTK ' . esc($data['nuptk']);
      $jam  = [
         'Masuk'  => $presensi['jam_masuk'] ?? null,
         'Pulang' => $presensi['jam_keluar'] ?? null,
      ];
      break;

   default:
      return;
}
?>

<p class="result__name"><?= esc($nama); ?></p>
<p class="result__meta"><?= $meta; ?></p>

<div class="times">
   <?php foreach ($jam as $label => $value) : ?>
      <div class="time <?= empty($value) ? 'time--empty' : 'time--filled'; ?>">
         <span class="time__label"><?= $label; ?></span>
         <span class="time__value"><?= empty($value) ? '--:--' : substr($value, 0, 5); ?></span>
      </div>
   <?php endforeach; ?>
</div>
