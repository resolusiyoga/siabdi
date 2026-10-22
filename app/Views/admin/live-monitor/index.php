<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta name="theme-color" content="#ffffff">
   <title><?= esc($title); ?></title>

   <link href="<?= assetUrl('assets/fonts/fonts.css'); ?>" rel="stylesheet" />
   <link rel="apple-touch-icon" sizes="76x76" href="<?= base_url('assets/img/apple-icon.png'); ?>">
   <link rel="icon" type="image/png" href="<?= base_url('assets/img/favicon.png'); ?>">
   <script src="<?= assetUrl('assets/js/core/jquery-3.5.1.min.js') ?>"></script>

   <style>
      /*
       * Halaman berdiri sendiri (bukan memakai sidebar admin): dimaksudkan
       * dipasang di layar besar (TV lobi dsb.) dan dibiarkan terbuka,
       * menyegarkan diri sendiri lewat polling AJAX -- bukan dioperasikan
       * dengan klik berulang seperti halaman admin lain.
       */
      *,
      *::before,
      *::after {
         box-sizing: border-box;
      }

      /*
       * Palet TERANG (bukan gelap): layar ini dimaksudkan tampil lewat
       * proyektor di luar ruangan, siang hari. Proyektor kehilangan
       * banyak kecerahan & kontras terhadap cahaya matahari -- latar
       * gelap jadi abu-abu pudar dan nyaris tidak terbaca dari jarak.
       * Latar putih + warna pekat + teks nyaris hitam memberi kontras
       * maksimum yang masih tersisa setelah "tercuci" sinar luar.
       */
      :root {
         --hijau-900: #07321f;
         --hijau-700: #0a6b3f;
         --hijau-500: #15a34a;
         --bg: #ffffff;
         /* sedikit berbeda dari --bg (bukan putih murni juga) supaya kartu
            & chip tetap terlihat batasnya sendiri meski garis tipis mudah
            hilang lewat proyektor */
         --surface: #f3faf5;
         --surface-2: #e0f3e6;
         --text: #07241a;
         --muted: #3c5d4e;
         --border: #bfe6cc;
         --merah: #dc2626;
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
         min-height: 100vh;
      }

      .papan {
         max-width: 1400px;
         margin: 0 auto;
         padding: 20px 28px 40px;
      }

      /* ---------- Kepala papan ---------- */
      .kepala {
         display: flex;
         flex-wrap: wrap;
         align-items: center;
         gap: 16px;
         margin-bottom: 20px;
      }

      .kepala__judul {
         display: flex;
         align-items: center;
         gap: 14px;
      }

      .kepala__logo {
         width: 48px;
         height: 48px;
         border-radius: 50%;
         object-fit: cover;
         background: #fff;
      }

      .kepala__sekolah {
         margin: 0;
         font-size: 20px;
         font-weight: 700;
      }

      .kepala__sub {
         margin: 2px 0 0;
         color: var(--muted);
         font-size: 13px;
      }

      .kepala__waktu {
         text-align: right;
      }

      /* tombol toggle suara: didorong ke kanan (bersama jam), judul tetap
         di kiri -- dipisah dari .kepala__waktu sendiri supaya keduanya
         rapi berdampingan, bukan jam yang mendorong sendirian */
      .tombol-suara {
         margin-left: auto;
         display: flex;
         align-items: center;
         gap: 6px;
         background: var(--surface);
         border: 2px solid var(--border);
         border-radius: 999px;
         padding: 8px 16px;
         font-size: 13px;
         font-weight: 700;
         color: var(--text);
         cursor: pointer;
      }

      .tombol-suara .material-icons {
         font-size: 20px;
         color: var(--hijau-700);
      }

      .tombol-suara--mati {
         color: var(--muted);
      }

      .tombol-suara--mati .material-icons {
         color: var(--muted);
      }

      /* tombol pengaturan suara (gear) + panel pilihan bahasa/dubber --
         dibuat relative supaya panelnya bisa ditempel tepat di bawahnya */
      .pengaturan-suara {
         position: relative;
      }

      .tombol-pengaturan {
         display: flex;
         align-items: center;
         justify-content: center;
         width: 38px;
         height: 38px;
         background: var(--surface);
         border: 2px solid var(--border);
         border-radius: 50%;
         color: var(--hijau-700);
         cursor: pointer;
      }

      .panel-pengaturan {
         display: none;
         position: absolute;
         top: calc(100% + 8px);
         right: 0;
         z-index: 20;
         width: 280px;
         background: var(--bg);
         border: 2px solid var(--border);
         border-radius: 16px;
         padding: 16px;
         box-shadow: 0 8px 24px rgba(7, 50, 31, .18);
         text-align: left;
      }

      .panel-pengaturan--terbuka {
         display: block;
      }

      .panel-pengaturan label {
         display: block;
         font-size: 12px;
         font-weight: 700;
         color: var(--muted);
         margin-bottom: 4px;
      }

      .panel-pengaturan select {
         width: 100%;
         background: var(--surface);
         color: var(--text);
         border: 2px solid var(--border);
         border-radius: 10px;
         padding: 8px 10px;
         font-size: 13px;
         margin-bottom: 12px;
      }

      .panel-pengaturan select:last-of-type {
         margin-bottom: 8px;
      }

      .panel-pengaturan__coba {
         width: 100%;
         background: var(--hijau-500);
         color: #04241d;
         border: none;
         border-radius: 10px;
         padding: 8px 10px;
         font-size: 13px;
         font-weight: 700;
         cursor: pointer;
      }

      .kepala__jam {
         font-size: 30px;
         font-weight: 700;
         font-variant-numeric: tabular-nums;
         line-height: 1.1;
      }

      .kepala__tanggal {
         color: var(--muted);
         font-size: 13px;
      }

      .live {
         display: inline-flex;
         align-items: center;
         gap: 6px;
         color: var(--merah);
         font-weight: 700;
         font-size: 12px;
         letter-spacing: .08em;
      }

      .live__titik {
         width: 8px;
         height: 8px;
         border-radius: 50%;
         background: var(--merah);
         animation: kedip 1.4s ease-in-out infinite;
      }

      @keyframes kedip {

         0%,
         100% {
            opacity: 1;
         }

         50% {
            opacity: .25;
         }
      }

      /* ---------- Ringkasan hitungan + filter kelas ---------- */
      .ringkas {
         display: flex;
         flex-wrap: wrap;
         gap: 10px;
         margin-bottom: 20px;
         align-items: center;
      }

      .chip {
         display: flex;
         align-items: center;
         gap: 8px;
         background: var(--surface);
         border: 2px solid var(--border);
         border-radius: 999px;
         padding: 8px 16px;
         font-size: 14px;
      }

      .chip b {
         font-size: 17px;
      }

      .ringkas select {
         margin-left: auto;
         background: var(--surface);
         color: var(--text);
         border: 2px solid var(--border);
         border-radius: 999px;
         padding: 8px 16px;
         font-size: 14px;
      }

      /* ---------- Satu kartu besar: siswa yang BARU SAJA absen ---------- */
      .panggung {
         min-height: 58vh;
         display: flex;
         align-items: center;
         justify-content: center;
      }

      .kartu-absen {
         width: 100%;
         max-width: 460px;
         background: var(--surface);
         /* garis 3px, bukan 1px: tipis gampang hilang kena silau matahari
            saat ditampilkan lewat proyektor luar ruangan */
         border: 3px solid var(--hijau-500);
         border-radius: 24px;
         padding: 40px 32px;
         text-align: center;
         animation: muncul .35s ease;
      }

      @keyframes muncul {
         from {
            opacity: 0;
            transform: scale(.96) translateY(10px);
         }
      }

      .kartu-absen__foto {
         width: 180px;
         height: 180px;
         margin: 0 auto 20px;
         border-radius: 50%;
         overflow: hidden;
         background: var(--surface-2);
         border: 5px solid var(--hijau-500);
         display: flex;
         align-items: center;
         justify-content: center;
         color: var(--muted);
      }

      .kartu-absen--telat {
         border-color: var(--merah);
      }

      .kartu-absen--telat .kartu-absen__foto {
         border-color: var(--merah);
      }

      .kartu-absen__foto img {
         width: 100%;
         height: 100%;
         object-fit: cover;
         display: block;
      }

      .kartu-absen__foto .material-icons {
         font-size: 90px;
      }

      .kartu-absen__nama {
         margin: 0;
         font-weight: 700;
         font-size: 28px;
         line-height: 1.25;
      }

      .kartu-absen__meta {
         margin: 6px 0 0;
         color: var(--muted);
         font-size: 15px;
      }

      .badge-jenis {
         display: inline-block;
         margin-top: 20px;
         padding: 6px 18px;
         border-radius: 999px;
         font-size: 13px;
         font-weight: 700;
         letter-spacing: .04em;
         text-transform: uppercase;
         background: var(--hijau-500);
         color: #04241d;
      }

      .badge-jenis--telat {
         background: var(--merah);
         color: #fff;
      }

      .kartu-absen__jam {
         margin: 10px 0 0;
         font-size: 40px;
         font-weight: 700;
         font-variant-numeric: tabular-nums;
      }

      .kartu-absen--telat .kartu-absen__jam {
         color: var(--merah);
      }

      .kosong {
         text-align: center;
         color: var(--muted);
      }

      .kosong .material-icons {
         font-size: 64px;
         display: block;
         margin-bottom: 10px;
         opacity: .6;
      }

      @media (max-width: 480px) {
         body {
            overflow-x: hidden;
         }

         .papan {
            padding: 16px 14px 32px;
         }

         /* ditumpuk vertikal: baris judul+jam berdampingan gampang meluber
            ke samping di layar sempit karena jam & judul sama-sama tidak
            mau menyusut (flex item default tidak menyusut di bawah lebar
            kontennya sendiri) */
         .kepala {
            flex-direction: column;
            align-items: flex-start;
         }

         .kepala__waktu {
            margin-left: 0;
            text-align: left;
         }

         .tombol-suara {
            margin-left: 0;
         }

         .panel-pengaturan {
            right: auto;
            left: 0;
            width: 240px;
         }

         .kepala__jam {
            font-size: 24px;
         }

         .ringkas {
            gap: 8px;
         }

         .chip {
            padding: 6px 12px;
            font-size: 13px;
         }

         .ringkas select {
            margin-left: 0;
            width: 100%;
         }

         .kartu-absen {
            padding: 28px 20px;
         }

         .kartu-absen__foto {
            width: 140px;
            height: 140px;
         }

         .kartu-absen__nama {
            font-size: 22px;
         }

         .kartu-absen__jam {
            font-size: 32px;
         }
      }
   </style>
</head>

<body>
   <div class="papan">
      <div class="kepala">
         <div class="kepala__judul">
            <img class="kepala__logo" src="<?= getLogo(); ?>" alt="Logo sekolah">
            <div>
               <p class="kepala__sekolah"><?= esc($generalSettings->school_name ?? 'SI-ABDI'); ?></p>
               <p class="kepala__sub"><span class="live"><span class="live__titik"></span>LIVE</span> &middot; Monitor Absensi Siswa</p>
            </div>
         </div>
         <button type="button" class="tombol-suara" id="tombolSuara" title="Aktifkan/nonaktifkan suara pengumuman">
            <i class="material-icons">volume_up</i>
            <span id="labelSuara">Suara aktif</span>
         </button>

         <div class="pengaturan-suara">
            <button type="button" class="tombol-pengaturan" id="tombolPengaturan" title="Atur bahasa & suara dubber">
               <i class="material-icons">settings</i>
            </button>
            <div class="panel-pengaturan" id="panelPengaturan">
               <label for="pilihBahasa">Bahasa pengumuman</label>
               <select id="pilihBahasa"></select>

               <label for="pilihSuara">Suara dubber</label>
               <select id="pilihSuara"></select>

               <button type="button" class="panel-pengaturan__coba" id="tombolCobaSuara">Coba suara</button>
            </div>
         </div>

         <div class="kepala__waktu">
            <div class="kepala__jam" id="jamSekarang">--:--:--</div>
            <div class="kepala__tanggal" id="tanggalSekarang"></div>
         </div>
      </div>

      <div class="ringkas">
         <div class="chip"><i class="material-icons" style="font-size:18px;">login</i> Masuk <b id="hitungMasuk">0</b></div>
         <div class="chip">🕐 Dzuhur <b id="hitungDzuhur">0</b></div>
         <div class="chip">🕒 Ashar <b id="hitungAshar">0</b></div>
         <div class="chip"><i class="material-icons" style="font-size:18px;">logout</i> Pulang <b id="hitungPulang">0</b></div>

         <?php if (empty($idKelasWali)) : ?>
            <select id="filterKelas">
               <option value="">Semua kelas</option>
               <?php foreach ($kelas as $value) : ?>
                  <option value="<?= $value['id_kelas']; ?>"><?= labelKelas($value['kelas'], $value['jurusan']); ?></option>
               <?php endforeach; ?>
            </select>
         <?php endif; ?>
      </div>

      <div class="panggung" id="panggungAbsen">
         <div class="kosong">
            <i class="material-icons">hourglass_empty</i>
            Memuat data absen hari ini&hellip;
         </div>
      </div>
   </div>

   <script>
      var idKelasWali = <?= json_encode($idKelasWali ?: null); ?>;

      function jamTeksLengkap() {
         var d = new Date();
         var dua = function(n) { return (n < 10 ? '0' : '') + n; };
         return dua(d.getHours()) + ':' + dua(d.getMinutes()) + ':' + dua(d.getSeconds());
      }

      function perbaruiJam() {
         document.getElementById('jamSekarang').textContent = jamTeksLengkap();
         document.getElementById('tanggalSekarang').textContent = new Date().toLocaleDateString('id-ID', {
            weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
         });
      }
      perbaruiJam();
      setInterval(perbaruiJam, 1000);

      function escTeks(teks) {
         return String(teks).replace(/[&<>"]/g, function(c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
         });
      }

      function renderKartu(k) {
         var badgeClass = 'badge-jenis' + (k.telat ? ' badge-jenis--telat' : '');
         var kartuClass = 'kartu-absen' + (k.telat ? ' kartu-absen--telat' : '');
         var foto = k.foto
            ? '<img src="' + escTeks(k.foto) + '" alt="">'
            : '<i class="material-icons">person</i>';
         var meta = (k.nisn ? 'NISN ' + escTeks(k.nisn) : 'NIS ' + escTeks(k.nis)) + (k.kelas ? ' &middot; ' + escTeks(k.kelas) : '');

         return '' +
            '<div class="' + kartuClass + '">' +
            '  <div class="kartu-absen__foto">' + foto + '</div>' +
            '  <p class="kartu-absen__nama">' + escTeks(k.nama) + '</p>' +
            '  <p class="kartu-absen__meta">' + meta + '</p>' +
            '  <span class="' + badgeClass + '">' + escTeks(k.jenis) + (k.telat ? ' &middot; Terlambat' : '') + '</span>' +
            '  <p class="kartu-absen__jam">' + escTeks(k.waktu) + '</p>' +
            '</div>';
      }

      // hanya digambar ulang kalau kejadian TERBARU benar-benar berganti --
      // supaya kartu tidak berkedip/mengulang animasi setiap polling kalau
      // belum ada siswa baru yang absen
      var idKejadianTerakhir = null;

      // frasa yang diucapkan untuk tiap jenis absen
      var frasaJenis = {
         'Masuk': 'masuk',
         'Dzuhur': 'shalat dhuhur',
         'Ashar': 'shalat ashar',
         'Pulang': 'pulang'
      };

      var KUNCI_SUARA = 'liveMonitorSuara';
      var suaraAktif = true;
      try {
         var simpanan = localStorage.getItem(KUNCI_SUARA);
         if (simpanan !== null) suaraAktif = simpanan === '1';
      } catch (e) {
         // localStorage bisa diblokir (mis. mode privat) -- biarkan default aktif
      }

      // kejadian yang sudah ada saat halaman pertama dibuka tidak perlu
      // diucapkan -- hanya kejadian baru setelah itu yang diumumkan
      var pertamaKali = true;

      var $tombolSuara = $('#tombolSuara');
      var $labelSuara = $('#labelSuara');

      function terapkanTampilanSuara() {
         $tombolSuara.toggleClass('tombol-suara--mati', !suaraAktif);
         $tombolSuara.find('.material-icons').text(suaraAktif ? 'volume_up' : 'volume_off');
         $labelSuara.text(suaraAktif ? 'Suara aktif' : 'Suara nonaktif');
      }
      terapkanTampilanSuara();

      $tombolSuara.on('click', function() {
         suaraAktif = !suaraAktif;
         try {
            localStorage.setItem(KUNCI_SUARA, suaraAktif ? '1' : '0');
         } catch (e) {
            // abaikan kalau localStorage tidak tersedia
         }
         terapkanTampilanSuara();

         // klik tombol juga berfungsi sebagai gesture pengguna yang
         // dibutuhkan sebagian browser sebelum izin memutar suara
         if (suaraAktif && 'speechSynthesis' in window) {
            bicarakan('Suara pengumuman diaktifkan');
         }
      });

      // ---------- Pengaturan bahasa & suara dubber ----------
      // Daftar suara (speechSynthesis.getVoices()) baru siap setelah event
      // "voiceschanged" di banyak browser -- tidak selalu langsung tersedia
      // begitu halaman dimuat.
      var KUNCI_BAHASA = 'liveMonitorBahasa';
      var KUNCI_DUBBER = 'liveMonitorDubber';
      var bahasaDipilih = 'id-ID'; // default: Bahasa Indonesia
      var namaSuaraDipilih = ''; // '' = biarkan browser pilih otomatis
      try {
         var simpananBahasa = localStorage.getItem(KUNCI_BAHASA);
         if (simpananBahasa) bahasaDipilih = simpananBahasa;
         namaSuaraDipilih = localStorage.getItem(KUNCI_DUBBER) || '';
      } catch (e) {
         // abaikan kalau localStorage tidak tersedia
      }

      var $pilihBahasa = $('#pilihBahasa');
      var $pilihSuara = $('#pilihSuara');
      var daftarSuara = [];

      function muatDaftarSuara() {
         if (!('speechSynthesis' in window)) return;
         daftarSuara = window.speechSynthesis.getVoices();
         if (!daftarSuara.length) return;

         var bahasaTersedia = [];
         daftarSuara.forEach(function(v) {
            if (bahasaTersedia.indexOf(v.lang) === -1) bahasaTersedia.push(v.lang);
         });
         // bahasa yang tersimpan/dipakai sebelumnya tapi sudah tidak ada di
         // daftar suara browser ini tetap ditampilkan, supaya pilihan tidak
         // "hilang" tiba-tiba kalau sebelumnya dipilih dari perangkat lain
         if (bahasaTersedia.indexOf(bahasaDipilih) === -1) bahasaTersedia.unshift(bahasaDipilih);
         bahasaTersedia.sort();

         $pilihBahasa.empty();
         bahasaTersedia.forEach(function(lang) {
            $pilihBahasa.append($('<option>').val(lang).text(lang));
         });
         $pilihBahasa.val(bahasaDipilih);

         isiDaftarSuaraUntukBahasa();
      }

      function isiDaftarSuaraUntukBahasa() {
         var suaraCocok = daftarSuara.filter(function(v) {
            return v.lang === bahasaDipilih;
         });

         $pilihSuara.empty();
         $pilihSuara.append($('<option>').val('').text('Otomatis (bawaan browser)'));
         suaraCocok.forEach(function(v) {
            $pilihSuara.append($('<option>').val(v.name).text(v.name + (v.localService ? '' : ' (online)')));
         });

         // kalau suara yang tersimpan tidak termasuk suara untuk bahasa ini,
         // jatuh ke "Otomatis" daripada diam-diam memakai suara bahasa lain
         var cocok = suaraCocok.some(function(v) {
            return v.name === namaSuaraDipilih;
         });
         $pilihSuara.val(cocok ? namaSuaraDipilih : '');
      }

      if ('speechSynthesis' in window) {
         muatDaftarSuara();
         window.speechSynthesis.onvoiceschanged = muatDaftarSuara;
      }

      $pilihBahasa.on('change', function() {
         bahasaDipilih = $(this).val();
         namaSuaraDipilih = '';
         try {
            localStorage.setItem(KUNCI_BAHASA, bahasaDipilih);
            localStorage.removeItem(KUNCI_DUBBER);
         } catch (e) {}
         isiDaftarSuaraUntukBahasa();
      });

      $pilihSuara.on('change', function() {
         namaSuaraDipilih = $(this).val();
         try {
            localStorage.setItem(KUNCI_DUBBER, namaSuaraDipilih);
         } catch (e) {}
      });

      var $tombolPengaturan = $('#tombolPengaturan');
      var $panelPengaturan = $('#panelPengaturan');

      $tombolPengaturan.on('click', function(e) {
         e.stopPropagation();
         $panelPengaturan.toggleClass('panel-pengaturan--terbuka');
      });

      $(document).on('click', function(e) {
         if (!$(e.target).closest('.pengaturan-suara').length) {
            $panelPengaturan.removeClass('panel-pengaturan--terbuka');
         }
      });

      $('#tombolCobaSuara').on('click', function() {
         bicarakan('Contoh suara pengumuman Live Monitor');
      });

      function bicarakan(teks) {
         if (!('speechSynthesis' in window)) return;
         window.speechSynthesis.cancel();
         var ucapan = new SpeechSynthesisUtterance(teks);
         ucapan.lang = bahasaDipilih;

         if (namaSuaraDipilih) {
            var suaraTerpilih = daftarSuara.find(function(v) {
               return v.name === namaSuaraDipilih;
            });
            if (suaraTerpilih) ucapan.voice = suaraTerpilih;
         }

         window.speechSynthesis.speak(ucapan);
      }

      function muatData() {
         var idKelas = idKelasWali || (document.getElementById('filterKelas') ? document.getElementById('filterKelas').value : '');

         return $.ajax({
            url: "<?= base_url('admin/live-monitor/data') ?>",
            type: 'GET',
            data: { id_kelas: idKelas },
            dataType: 'json',
            complete: function() {
               requestBerjalan = null;
            },
            success: function(res) {
               if (!res || !res.sukses) return;

               document.getElementById('hitungMasuk').textContent = res.hitung.masuk;
               document.getElementById('hitungDzuhur').textContent = res.hitung.dzuhur;
               document.getElementById('hitungAshar').textContent = res.hitung.ashar;
               document.getElementById('hitungPulang').textContent = res.hitung.pulang;

               var terbaru = res.kejadian[0] || null;
               var idTerbaru = terbaru ? terbaru.id : null;

               if (idTerbaru === idKejadianTerakhir) return; // belum ada yang baru
               idKejadianTerakhir = idTerbaru;

               var $panggung = $('#panggungAbsen');

               if (!terbaru) {
                  $panggung.html('<div class="kosong"><i class="material-icons">hourglass_empty</i>Belum ada siswa yang absen hari ini</div>');
                  pertamaKali = false;
                  return;
               }

               $panggung.html(renderKartu(terbaru));

               if (suaraAktif && !pertamaKali) {
                  bicarakan(terbaru.nama + ' berhasil absen ' + (frasaJenis[terbaru.jenis] || terbaru.jenis));
               }
               pertamaKali = false;
            }
         });
      }

      muatData();
      // 1 detik: sekecil mungkin tanpa membuka permintaan baru sebelum
      // yang sebelumnya selesai (query-nya ringan -- 1 tabel presensi hari
      // ini + join id, aman dipanggil sesering ini untuk satu layar).
      // Request lama dibatalkan dulu bila sempat menumpuk (mis. koneksi
      // proyektor/wifi outdoor sempat lambat), supaya tidak makin tertinggal.
      var requestBerjalan = null;
      setInterval(function() {
         if (requestBerjalan) requestBerjalan.abort();
         requestBerjalan = muatData();
      }, 1000);

      var $filterKelas = $('#filterKelas');
      if ($filterKelas.length) {
         $filterKelas.on('change', muatData);
      }
   </script>
</body>

</html>
