<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
   <meta name="theme-color" content="#ddedca">
   <?= csrf_meta(); ?>
   <title><?= $title ?? 'Masuk'; ?> &middot; <?= esc(generalSetting('school_name', 'SI-ABDI')); ?></title>

   <link href="<?= assetUrl('assets/fonts/fonts.css'); ?>" rel="stylesheet" />
   <link rel="apple-touch-icon" sizes="76x76" href="<?= base_url('assets/img/apple-icon.png'); ?>">
   <link rel="icon" type="image/png" href="<?= base_url('assets/img/favicon.png'); ?>">

   <style>
      *,
      *::before,
      *::after {
         box-sizing: border-box;
      }

      :root {
         --green-900: #293F14;
         --green-700: #386C0B;
         --green-500: #38A700;
         --green-300: #3EFF8B;
         --bg: #ddedca;
         --surface: #ffffff;
         --text: #293F14;
         --muted: #5a6b4d;
         --border: #dfe7d6;
         --field: #eef2f7;
         --danger: #dc2626;
      }

      html,
      body {
         margin: 0;
         padding: 0;
      }

      body {
         font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif;
         background: var(--bg);
         color: var(--text);
         line-height: 1.5;
         -webkit-font-smoothing: antialiased;
      }

      /* ---------- Kerangka ---------- */
      .auth {
         min-height: 100vh;
         max-width: 1180px;
         margin: 0 auto;
         padding: 32px 20px calc(32px + env(safe-area-inset-bottom));
         display: grid;
         grid-template-columns: 1fr;
         align-content: center;
         justify-items: center;
         gap: 28px;
      }

      @media (min-width: 992px) {
         .auth {
            grid-template-columns: 1fr 1fr;
            align-items: center;
            gap: 64px;
         }
      }

      /* Di layar kecil ilustrasi tidak ditampilkan sebagai gambar, melainkan
         menjadi latar belakang samar supaya form tetap jadi fokus utama. */
      @media (max-width: 991.98px) {
         .brand {
            display: none;
         }

         .auth::before {
            content: "";
            position: fixed;
            inset: 0;
            background: url('<?= assetUrl('assets/img/login.png'); ?>') center / cover no-repeat;
            opacity: .15;
            pointer-events: none;
            z-index: 0;
         }

         .panel {
            position: relative;
            z-index: 1;
         }
      }

      /* ---------- Kolom kiri: logo + ilustrasi ---------- */
      .brand {
         width: 100%;
         text-align: center;
      }

      .brand__illustration {
         width: 100%;
         max-width: 460px;
         height: auto;
         display: block;
         margin: 0 auto;
      }

      /* ---------- Kolom kanan ---------- */
      .panel {
         width: 100%;
         max-width: 440px;
      }

      .panel__logo {
         width: 84px;
         height: 84px;
         object-fit: contain;
         margin: 0 auto 8px;
         display: block;
      }

      .panel__school {
         margin: 0 0 18px;
         text-align: center;
         font-size: 18px;
         font-weight: 700;
         color: var(--green-700);
      }

      .card {
         background: var(--surface);
         border: 1px solid var(--green-700);
         border-radius: 14px;
         overflow: hidden;
      }

      .card__head {
         padding: 15px 10px;
         text-align: center;
         font-size: 16px;
         font-weight: 700;
         background: var(--green-700);
         color: #fff;
      }

      .card__body {
         padding: 22px 22px 26px;
      }

      /* ---------- Form ---------- */
      .field+.field {
         margin-top: 16px;
      }

      .field__label {
         display: block;
         margin-bottom: 6px;
         font-size: 14px;
         color: var(--text);
      }

      .control {
         display: flex;
         align-items: center;
         gap: 10px;
         padding: 0 12px;
         background: var(--field);
         border: 1px solid var(--border);
         border-radius: 8px;
      }

      .control:focus-within {
         border-color: var(--green-500);
      }

      .control .material-icons {
         font-size: 20px;
         color: var(--muted);
         flex: none;
      }

      .control input {
         flex: 1;
         min-width: 0;
         border: 0;
         outline: none;
         background: transparent;
         padding: 13px 0;
         font-family: inherit;
         font-size: 15px;
         color: var(--text);
      }

      .control--error {
         border-color: var(--danger);
      }

      .toggle-pass {
         border: 0;
         background: transparent;
         padding: 4px;
         cursor: pointer;
         color: var(--muted);
         display: flex;
      }

      .field__error {
         margin: 6px 0 0;
         font-size: 13px;
         color: var(--danger);
      }

      .options {
         display: flex;
         align-items: center;
         justify-content: space-between;
         gap: 12px;
         margin: 16px 0;
         font-size: 14px;
      }

      .remember {
         display: flex;
         align-items: center;
         gap: 8px;
         cursor: pointer;
      }

      .remember input {
         width: 18px;
         height: 18px;
         accent-color: var(--green-700);
      }

      .link {
         color: var(--green-700);
         font-weight: 600;
         text-decoration: none;
      }

      .link:hover {
         text-decoration: underline;
      }

      .btn-submit {
         width: 100%;
         padding: 13px 16px;
         border: 0;
         border-radius: 999px;
         background: var(--green-700);
         color: #fff;
         font-family: inherit;
         font-size: 15px;
         font-weight: 700;
         cursor: pointer;
      }

      .btn-submit:hover {
         background: var(--green-900);
      }

      /* ---------- Notifikasi ---------- */
      .alert {
         padding: 11px 14px;
         border-radius: 8px;
         font-size: 14px;
         margin-bottom: 16px;
      }

      .alert--ok {
         background: #d9efd6;
         color: #1e5620;
      }

      .alert--err {
         background: #f8dad6;
         color: #8c1d13;
      }

      .alert p {
         margin: 0;
      }

      .alert p+p {
         margin-top: 4px;
      }
   </style>
</head>

<body>
   <main class="auth">
      <section class="brand">
         <img class="brand__illustration" src="<?= assetUrl('assets/img/login.png'); ?>" alt="">
      </section>

      <section class="panel">
         <img class="panel__logo" src="<?= getLogo(); ?>" alt="Logo sekolah">
         <p class="panel__school"><?= esc(generalSetting('school_name', 'SI-ABDI')); ?></p>
         <?= $this->renderSection('content') ?>
      </section>
   </main>
</body>

</html>
