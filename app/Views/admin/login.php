<?= $this->extend('templates/auth_page_layout'); ?>

<?= $this->section('content'); ?>
<?php
// Myth\Auth menerima email maupun username, jadi label mengikuti konfigurasi
$emailSaja       = $config->validFields === ['email'];
$labelLogin      = $emailSaja ? 'Email' : 'Email atau Username';
$placeholderLogin = $emailSaja ? 'Masukkan email' : 'Masukkan email atau username';
?>
<div class="card">
   <div class="card__head"><?= esc(generalSetting('scan_subtitle', 'Absensi QR Code')); ?></div>

   <div class="card__body">
      <?php if (session()->has('message')) : ?>
         <div class="alert alert--ok">
            <p><?= session('message') ?></p>
         </div>
      <?php endif; ?>

      <?php if (session()->has('error')) : ?>
         <div class="alert alert--err">
            <p><?= session('error') ?></p>
         </div>
      <?php endif; ?>

      <?php if (session()->has('errors')) : ?>
         <div class="alert alert--err">
            <?php foreach (session('errors') as $error) : ?>
               <p><?= $error ?></p>
            <?php endforeach; ?>
         </div>
      <?php endif; ?>

      <form action="<?= url_to('login'); ?>" method="post">
         <?= csrf_field(); ?>

         <div class="field">
            <label class="field__label" for="login"><?= $labelLogin; ?></label>
            <div class="control <?= session('errors.login') ? 'control--error' : ''; ?>">
               <i class="material-icons">mail</i>
               <input type="text" id="login" name="login" value="<?= old('login'); ?>"
                  placeholder="<?= $placeholderLogin; ?>"
                  autocomplete="username" autofocus required>
            </div>
            <?php if (session('errors.login')) : ?>
               <p class="field__error"><?= session('errors.login'); ?></p>
            <?php endif; ?>
         </div>

         <div class="field">
            <label class="field__label" for="password">Kata Sandi</label>
            <div class="control <?= session('errors.password') ? 'control--error' : ''; ?>">
               <i class="material-icons">lock</i>
               <input type="password" id="password" name="password"
                  placeholder="Masukkan kata sandi"
                  autocomplete="current-password" required>
               <button type="button" class="toggle-pass" id="togglePass"
                  aria-label="Tampilkan kata sandi">
                  <i class="material-icons" id="togglePassIcon">visibility</i>
               </button>
            </div>
            <?php if (session('errors.password')) : ?>
               <p class="field__error"><?= session('errors.password'); ?></p>
            <?php endif; ?>
         </div>

         <div class="options">
            <?php if ($config->allowRemembering) : ?>
               <label class="remember">
                  <input type="checkbox" name="remember" <?= old('remember') ? 'checked' : ''; ?>>
                  Ingat saya
               </label>
            <?php else : ?>
               <span></span>
            <?php endif; ?>

            <?php if ($config->activeResetter) : ?>
               <a class="link" href="<?= url_to('forgot'); ?>">Lupa Kata Sandi?</a>
            <?php endif; ?>
         </div>

         <button type="submit" class="btn-submit">Masuk</button>
      </form>
   </div>
</div>

<script>
   (function() {
      var input = document.getElementById('password');
      var btn = document.getElementById('togglePass');
      var icon = document.getElementById('togglePassIcon');

      btn.addEventListener('click', function() {
         var hidden = input.type === 'password';
         input.type = hidden ? 'text' : 'password';
         icon.textContent = hidden ? 'visibility_off' : 'visibility';
         btn.setAttribute('aria-label', hidden ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
      });
   })();
</script>
<?= $this->endSection(); ?>
