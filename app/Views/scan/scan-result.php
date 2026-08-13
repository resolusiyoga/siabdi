<?php

use App\Libraries\enums\TipeUser;

$valid = in_array($type, [TipeUser::Siswa, TipeUser::Guru], true);
?>

<?php if (!$valid) : ?>
   <div class="result result--err">
      <div class="result__head">
         <i class="material-icons">close</i>
         Tipe pengguna tidak valid
      </div>
   </div>
<?php else : ?>
   <div class="result result--ok">
      <div class="result__head">
         <i class="material-icons">check</i>
         Absen <?= $waktu; ?> berhasil
      </div>
      <div class="result__body">
         <?= $this->include('scan/_detail-presensi'); ?>
      </div>
   </div>
<?php endif; ?>
