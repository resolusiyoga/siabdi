<?php

use App\Libraries\enums\TipeUser;

$type  = $type ?? null;
$valid = in_array($type, [TipeUser::Siswa, TipeUser::Guru], true);
?>

<div class="result result--err">
   <div class="result__head">
      <i class="material-icons">close</i>
      <?= esc($msg); ?>
   </div>

   <?php if ($valid) : ?>
      <div class="result__body">
         <?= $this->include('scan/_detail-presensi'); ?>
      </div>
   <?php endif; ?>
</div>
