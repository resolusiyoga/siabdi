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
$foto = null;   // hanya siswa yang punya foto

switch ($type) {
   case TipeUser::Siswa:
      $nama = $data['nama_siswa'];
      // assetUrl(): nama berkas foto tetap sama saat diganti, jadi perlu penanda
      // waktu modifikasi supaya kiosk tidak menampilkan foto lama dari cache
      $foto = (!empty($data['foto']) && is_file(FCPATH . $data['foto'])) ? assetUrl($data['foto']) : null;
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

<div class="person">
   <?php if ($type === TipeUser::Siswa) : ?>
      <div class="person__foto">
         <?php if ($foto) : ?>
            <img src="<?= esc($foto, 'attr'); ?>" alt="Foto <?= esc($nama, 'attr'); ?>">
         <?php else : ?>
            <i class="material-icons">person</i>
         <?php endif; ?>
      </div>
   <?php endif; ?>
   <div class="person__info">
      <p class="result__name"><?= esc($nama); ?></p>
      <p class="result__meta"><?= $meta; ?></p>
   </div>
</div>

<div class="times">
   <?php foreach ($jam as $label => $value) : ?>
      <div class="time <?= empty($value) ? 'time--empty' : 'time--filled'; ?>">
         <span class="time__label"><?= $label; ?></span>
         <span class="time__value"><?= empty($value) ? '--:--' : substr($value, 0, 5); ?></span>
      </div>
   <?php endforeach; ?>
</div>
