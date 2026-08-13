<?= $this->extend('templates/scan_page_layout'); ?>

<?php
/**
 * Warna tiap mode diambil dari palette hijau.
 * 'on' = warna teks di atas 'accent', dipilih agar kontras tetap aman dibaca.
 */
$kategoriAbsen = [
   'masuk'  => ['label' => 'Masuk',  'accent' => '#386C0B', 'on' => '#ffffff'],
   'dzuhur' => ['label' => 'Dzuhur', 'accent' => '#31D843', 'on' => '#293F14'],
   'ashar'  => ['label' => 'Ashar',  'accent' => '#3EFF8B', 'on' => '#293F14'],
   'pulang' => ['label' => 'Pulang', 'accent' => '#293F14', 'on' => '#ffffff'],
];

$aktif = strtolower($waktu);
$tema  = $kategoriAbsen[$aktif] ?? $kategoriAbsen['masuk'];
?>

<?= $this->section('pagestyle') ?>
<style>
   :root {
      --accent: <?= $tema['accent']; ?>;
      --on-accent: <?= $tema['on']; ?>;
   }
</style>
<?= $this->endSection() ?>

<?= $this->section('content'); ?>

<nav class="modes">
   <?php foreach ($kategoriAbsen as $key => $item) : ?>
      <a href="<?= base_url("scan/$key"); ?>"
         class="mode <?= $aktif === $key ? 'is-active' : ''; ?>"
         <?= $aktif === $key ? 'aria-current="page"' : ''; ?>>
         <?= $item['label']; ?>
      </a>
   <?php endforeach; ?>
</nav>

<section class="scanner" id="scanner">
   <video id="previewKamera" playsinline muted></video>

   <div class="frame">
      <div class="frame__box">
         <span></span><span></span><span></span><span></span>
         <div class="frame__line"></div>
      </div>
   </div>

   <div class="scanner__cam is-hidden" id="wrapKamera">
      <select id="pilihKamera" aria-label="Pilih kamera"></select>
   </div>

   <div class="scanner__hint" id="scanHint">Arahkan QR Code ke dalam bingkai</div>
</section>

<div id="hasilScan"></div>

<details class="guide">
   <summary>
      Panduan penggunaan
      <i class="material-icons">expand_more</i>
   </summary>
   <ul>
      <li>Izinkan akses kamera saat diminta browser.</li>
      <li>Posisikan QR Code di dalam bingkai, jangan terlalu jauh atau dekat.</li>
      <li>Pilih jenis absen (Masuk / Dzuhur / Ashar / Pulang) di bagian atas.</li>
      <li>Hasil absen otomatis muncul di bawah kamera setelah QR terbaca.</li>
   </ul>
</details>

<?= $this->endSection(); ?>

<?= $this->section('pagescript') ?>
<script src="<?= assetUrl('assets/js/plugins/zxing/zxing.min.js') ?>"></script>
<script>
   (function() {
      var COOLDOWN = 2500;
      var HINT_IDLE = 'Arahkan QR Code ke dalam bingkai';

      var codeReader = new ZXing.BrowserMultiFormatReader();
      var audio = new Audio("<?= base_url('assets/audio/beep.mp3'); ?>");
      var scanner = document.getElementById('scanner');
      var hint = document.getElementById('scanHint');
      var wrapKamera = document.getElementById('wrapKamera');
      var selectKamera = document.getElementById('pilihKamera');

      var selectedDeviceId = null;
      var busy = false;

      function setHint(text) {
         hint.textContent = text;
      }

      function setBusy(state) {
         busy = state;
         scanner.classList.toggle('is-busy', state);
      }

      // Utamakan kamera belakang di perangkat mobile
      function pilihKameraDefault(devices) {
         var belakang = devices.filter(function(d) {
            return /back|rear|environment|belakang/i.test(d.label || '');
         });
         return (belakang[0] || devices[devices.length - 1]).deviceId;
      }

      function isiDaftarKamera(devices) {
         selectKamera.innerHTML = '';
         devices.forEach(function(device, i) {
            var opt = document.createElement('option');
            opt.value = device.deviceId;
            opt.text = device.label || 'Kamera ' + (i + 1);
            opt.selected = device.deviceId === selectedDeviceId;
            selectKamera.appendChild(opt);
         });
         wrapKamera.classList.toggle('is-hidden', devices.length < 2);
      }

      function mulaiScan() {
         codeReader.decodeFromVideoDevice(selectedDeviceId, 'previewKamera', function(result) {
            if (!result || busy) return;
            prosesKode(result.text);
         });
      }

      // Gulirkan seperlunya saja supaya kamera tetap terlihat
      function tampilkanHasil() {
         var el = $('#hasilScan');
         if (!el.children().length) return;

         var atas = el.offset().top;
         var bawah = atas + el.outerHeight();
         var layar = $(window).height();
         var posisi = $(window).scrollTop();

         if (bawah <= posisi + layar) return;

         $('html, body').animate({
            scrollTop: Math.min(atas - 16, bawah - layar + 16)
         }, 350);
      }

      function prosesKode(kode) {
         setBusy(true);
         setHint('Memproses...');

         jQuery.ajax({
            url: "<?= base_url('scan/cek'); ?>",
            type: 'post',
            data: {
               'unique_code': kode,
               'waktu': '<?= strtolower($waktu); ?>'
            },
            success: function(response) {
               audio.play().catch(function() {});
               $('#hasilScan').html(response);
               tampilkanHasil();
            },
            error: function(xhr, status, thrown) {
               console.error(thrown);
               $('#hasilScan').html(
                  '<div class="result result--err">' +
                  '<div class="result__head"><i class="material-icons">close</i>Gagal terhubung ke server</div>' +
                  '</div>'
               );
            },
            complete: function() {
               setTimeout(function() {
                  setBusy(false);
                  setHint(HINT_IDLE);
               }, COOLDOWN);
            }
         });
      }

      function gagalKamera(pesan) {
         setHint(pesan);
         scanner.classList.add('is-busy');
      }

      selectKamera.addEventListener('change', function() {
         selectedDeviceId = this.value;
         codeReader.reset();
         mulaiScan();
      });

      if (!navigator.mediaDevices) {
         gagalKamera('Kamera tidak didukung browser ini');
         return;
      }

      codeReader.listVideoInputDevices()
         .then(function(devices) {
            if (!devices.length) {
               gagalKamera('Kamera tidak ditemukan');
               return;
            }
            selectedDeviceId = pilihKameraDefault(devices);
            isiDaftarKamera(devices);
            mulaiScan();
         })
         .catch(function(err) {
            console.error(err);
            gagalKamera('Tidak dapat mengakses kamera');
         });
   })();
</script>
<?= $this->endSection() ?>
