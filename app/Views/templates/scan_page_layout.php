<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
   <meta name="theme-color" content="#293F14">
   <title><?= $title ?? 'Absensi QR Code'; ?></title>

   <link href="<?= base_url('assets/fonts/fonts.css?v=1.0.0'); ?>" rel="stylesheet" />
   <link rel="apple-touch-icon" sizes="76x76" href="<?= base_url('assets/img/apple-icon.png'); ?>">
   <link rel="icon" type="image/png" href="<?= base_url('assets/img/favicon.png'); ?>">

   <style>
      *,
      *::before,
      *::after {
         box-sizing: border-box;
      }

      :root {
         /* Palette */
         --green-900: #293F14;
         --green-700: #386C0B;
         --green-500: #38A700;
         --green-400: #31D843;
         --green-300: #3EFF8B;

         /* Warna mode absen aktif (ditimpa tiap halaman) */
         --accent: var(--green-700);
         --on-accent: #ffffff;

         --bg: #f3f6ef;
         --surface: #ffffff;
         --text: var(--green-900);
         --muted: #5a6b4d;
         --border: #dfe7d6;
         --danger: #dc2626;
         --radius: 16px;
         --shadow: 0 1px 2px rgba(41, 63, 20, .05), 0 8px 24px rgba(41, 63, 20, .08);
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
         -webkit-tap-highlight-color: transparent;
         min-height: 100vh;
      }

      /* ---------- App bar ---------- */
      .app-bar {
         position: sticky;
         top: 0;
         z-index: 20;
         background: rgba(255, 255, 255, .88);
         backdrop-filter: saturate(180%) blur(12px);
         -webkit-backdrop-filter: saturate(180%) blur(12px);
         border-bottom: 1px solid var(--border);
         padding-top: env(safe-area-inset-top);
      }

      .app-bar__inner {
         max-width: 720px;
         margin: 0 auto;
         display: flex;
         align-items: center;
         gap: 12px;
         padding: 10px 16px;
      }

      .app-bar__logo {
         width: 38px;
         height: 38px;
         /* tanpa latar: contain supaya logo tampil utuh, tidak terpotong */
         object-fit: contain;
         flex: none;
      }

      .app-bar__text {
         min-width: 0;
         flex: 1;
      }

      .app-bar__title {
         margin: 0;
         font-size: 15px;
         font-weight: 700;
         line-height: 1.25;
         white-space: nowrap;
         overflow: hidden;
         text-overflow: ellipsis;
      }

      .app-bar__sub {
         margin: 0;
         font-size: 12px;
         color: var(--muted);
         white-space: nowrap;
         overflow: hidden;
         text-overflow: ellipsis;
      }

      .btn-ghost {
         display: inline-flex;
         align-items: center;
         gap: 6px;
         flex: none;
         height: 40px;
         padding: 0 14px;
         border-radius: 999px;
         border: 1px solid var(--border);
         background: var(--surface);
         color: var(--text);
         font-size: 14px;
         font-weight: 600;
         text-decoration: none;
         cursor: pointer;
      }

      .btn-ghost:active {
         background: var(--bg);
      }

      .btn-ghost .material-icons {
         font-size: 20px;
      }

      /* ---------- Page ---------- */
      .page {
         max-width: 720px;
         margin: 0 auto;
         padding: 16px 16px calc(32px + env(safe-area-inset-bottom));
      }

      /* ---------- Mode selector ---------- */
      .modes {
         display: grid;
         grid-template-columns: repeat(4, 1fr);
         gap: 8px;
         margin-bottom: 16px;
      }

      .mode {
         display: block;
         text-align: center;
         padding: 11px 4px;
         border-radius: 12px;
         border: 1px solid var(--border);
         background: var(--surface);
         color: var(--muted);
         font-size: 13px;
         font-weight: 600;
         text-decoration: none;
         transition: transform .12s ease;
      }

      .mode:active {
         transform: scale(.97);
      }

      .mode.is-active {
         background: var(--accent);
         border-color: var(--accent);
         color: var(--on-accent);
         box-shadow: 0 6px 16px -6px var(--accent);
      }

      /* ---------- Scanner ---------- */
      .scanner {
         position: relative;
         border-radius: var(--radius);
         overflow: hidden;
         background: var(--green-900);
         aspect-ratio: 3 / 4;
         box-shadow: var(--shadow);
      }

      @media (min-width: 600px) {
         .scanner {
            aspect-ratio: 4 / 3;
         }
      }

      .scanner video {
         display: block;
         width: 100%;
         height: 100%;
         object-fit: cover;
      }

      .frame {
         position: absolute;
         inset: 0;
         display: grid;
         place-items: center;
         pointer-events: none;
      }

      .frame__box {
         position: relative;
         width: 64%;
         max-width: 260px;
         aspect-ratio: 1;
      }

      .frame__box span {
         position: absolute;
         width: 26px;
         height: 26px;
         border: 3px solid rgba(255, 255, 255, .95);
      }

      .frame__box span:nth-child(1) {
         top: 0;
         left: 0;
         border-right: 0;
         border-bottom: 0;
         border-top-left-radius: 8px;
      }

      .frame__box span:nth-child(2) {
         top: 0;
         right: 0;
         border-left: 0;
         border-bottom: 0;
         border-top-right-radius: 8px;
      }

      .frame__box span:nth-child(3) {
         bottom: 0;
         left: 0;
         border-right: 0;
         border-top: 0;
         border-bottom-left-radius: 8px;
      }

      .frame__box span:nth-child(4) {
         bottom: 0;
         right: 0;
         border-left: 0;
         border-top: 0;
         border-bottom-right-radius: 8px;
      }

      .frame__line {
         position: absolute;
         left: 6%;
         right: 6%;
         height: 2px;
         border-radius: 2px;
         background: linear-gradient(90deg, transparent, var(--green-300), transparent);
         box-shadow: 0 0 14px var(--green-400);
         animation: scanline 2.4s ease-in-out infinite;
      }

      @keyframes scanline {

         0%,
         100% {
            top: 6%;
         }

         50% {
            top: 94%;
         }
      }

      .scanner__hint {
         position: absolute;
         left: 50%;
         bottom: 16px;
         transform: translateX(-50%);
         max-width: calc(100% - 32px);
         display: flex;
         align-items: center;
         gap: 8px;
         padding: 8px 14px;
         border-radius: 999px;
         background: rgba(41, 63, 20, .72);
         backdrop-filter: blur(6px);
         -webkit-backdrop-filter: blur(6px);
         color: #fff;
         font-size: 13px;
         font-weight: 500;
         text-align: center;
         white-space: nowrap;
      }

      .scanner__cam {
         position: absolute;
         top: 12px;
         right: 12px;
         max-width: calc(100% - 24px);
      }

      .scanner__cam select {
         appearance: none;
         -webkit-appearance: none;
         max-width: 100%;
         height: 36px;
         padding: 0 32px 0 14px;
         border-radius: 999px;
         border: 0;
         background: rgba(41, 63, 20, .72) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23fff'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E") no-repeat right 8px center / 18px;
         backdrop-filter: blur(6px);
         -webkit-backdrop-filter: blur(6px);
         color: #fff;
         font-family: inherit;
         font-size: 13px;
         font-weight: 500;
         text-overflow: ellipsis;
      }

      .scanner__cam select option {
         color: var(--green-900);
      }

      .scanner.is-busy .frame__line {
         display: none;
      }

      .scanner.is-busy video {
         filter: brightness(.55) saturate(.6);
      }

      /* ---------- Result ---------- */
      .result {
         margin-top: 16px;
         background: var(--surface);
         border: 1px solid var(--border);
         border-radius: var(--radius);
         box-shadow: var(--shadow);
         overflow: hidden;
         animation: pop .25s ease;
      }

      @keyframes pop {
         from {
            opacity: 0;
            transform: translateY(8px);
         }
      }

      .result__head {
         display: flex;
         align-items: center;
         gap: 10px;
         padding: 14px 16px;
         color: #fff;
         font-weight: 700;
         font-size: 15px;
      }

      .result--ok .result__head {
         background: var(--accent);
         color: var(--on-accent);
      }

      .result--err .result__head {
         background: var(--danger);
      }

      .result__head .material-icons {
         font-size: 22px;
      }

      .result__body {
         padding: 16px;
      }

      .result__name {
         margin: 0 0 2px;
         font-size: 19px;
         font-weight: 700;
         line-height: 1.3;
      }

      .result__meta {
         margin: 0 0 14px;
         color: var(--muted);
         font-size: 14px;
      }

      .times {
         display: grid;
         grid-template-columns: repeat(2, 1fr);
         gap: 8px;
      }

      @media (min-width: 480px) {
         .times {
            grid-template-columns: repeat(4, 1fr);
         }
      }

      .time {
         background: var(--bg);
         border-radius: 12px;
         padding: 10px 12px;
         text-align: center;
      }

      .time__label {
         display: block;
         font-size: 11px;
         letter-spacing: .04em;
         text-transform: uppercase;
         color: var(--muted);
         font-weight: 600;
      }

      .time__value {
         display: block;
         font-size: 16px;
         font-weight: 700;
         font-variant-numeric: tabular-nums;
         margin-top: 2px;
      }

      .time--filled .time__value {
         color: var(--green-700);
      }

      .time--empty .time__value {
         color: #b3c2a5;
      }

      /* ---------- Guide ---------- */
      .guide {
         margin-top: 16px;
         background: var(--surface);
         border: 1px solid var(--border);
         border-radius: var(--radius);
      }

      .guide summary {
         display: flex;
         align-items: center;
         justify-content: space-between;
         padding: 14px 16px;
         font-size: 14px;
         font-weight: 600;
         cursor: pointer;
         list-style: none;
      }

      .guide summary::-webkit-details-marker {
         display: none;
      }

      .guide summary .material-icons {
         font-size: 20px;
         color: var(--muted);
         transition: transform .2s ease;
      }

      .guide[open] summary .material-icons {
         transform: rotate(180deg);
      }

      .guide ul {
         margin: 0;
         padding: 0 16px 16px 34px;
         color: var(--muted);
         font-size: 14px;
      }

      .guide li+li {
         margin-top: 6px;
      }

      .is-hidden {
         display: none !important;
      }

      @media (prefers-reduced-motion: reduce) {

         .frame__line,
         .result {
            animation: none;
         }
      }
   </style>

   <?= $this->renderSection('pagestyle') ?>
</head>

<body>
   <header class="app-bar">
      <div class="app-bar__inner">
         <img class="app-bar__logo" src="<?= getLogo(); ?>" alt="Logo">
         <div class="app-bar__text">
            <p class="app-bar__title"><?= esc($generalSettings->school_name ?? 'SI-ABDI'); ?></p>
            <p class="app-bar__sub"><?= esc($generalSettings->scan_subtitle ?? 'Absensi QR Code'); ?></p>
         </div>
         <a href="<?= base_url('/admin'); ?>" class="btn-ghost">
            <i class="material-icons">dashboard</i>
         </a>
      </div>
   </header>

   <main class="page">
      <?= $this->renderSection('content') ?>
   </main>

   <script src="<?= base_url('assets/js/core/jquery-3.5.1.min.js') ?>"></script>
   <?= $this->renderSection('pagescript') ?>
</body>

</html>
