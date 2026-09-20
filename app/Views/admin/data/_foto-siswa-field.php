<?php $fotoUrl = !empty($fotoUrlSiswa) ? base_url($fotoUrlSiswa) : null; ?>
<style>
   /* ---- Blok "Foto Siswa": avatar + dua tombol aksi ---- */
   .foto-siswa {
      display: flex;
      flex-wrap: wrap;
      align-items: flex-start;
      gap: 20px;
   }

   .foto-siswa__avatar {
      position: relative;
      flex: 0 0 auto;
   }

   .foto-siswa__lingkaran {
      width: 120px;
      height: 120px;
      border: 1px solid #e0e0e0;
      border-radius: 50%;
      background: #f5f5f5;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
   }

   .foto-siswa__lingkaran img {
      width: 100%;
      height: 100%;
      object-fit: cover;
   }

   .foto-siswa__ikon-kosong {
      font-size: 44px;
   }

   /* lencana hapus kecil di pojok avatar, pola umum foto profil --
      lebih ringkas daripada tombol besar yang bersaing dengan dua
      aksi utama (Upload/Kamera) */
   .foto-siswa__hapus {
      position: absolute;
      top: -2px;
      right: -2px;
      width: 30px;
      height: 30px;
      padding: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      border: 2px solid #fff;
      border-radius: 50%;
      background: #f44336;
      color: #fff;
      cursor: pointer;
      box-shadow: 0 1px 4px rgba(0, 0, 0, .35);
   }

   .foto-siswa__hapus:hover {
      background: #d32f2f;
   }

   .foto-siswa__hapus i {
      font-size: 17px;
   }

   .foto-siswa__aksi {
      flex: 1 1 220px;
      min-width: 220px;
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 10px;
   }

   /* teks bantuan sengaja di LUAR .foto-siswa__aksi (bukan flex item di
      antara tombol) -- selalu selebar penuh & tidak tergantung sumbu
      flex yang berganti arah (baris di layar lebar, kolom di mobile) */
   .foto-siswa__ket {
      margin-top: 10px;
   }

   @media (max-width: 575.98px) {
      .foto-siswa {
         flex-direction: column;
         align-items: center;
         text-align: center;
      }

      .foto-siswa__aksi {
         width: 100%;
         /* "flex: 1 1 220px" di aturan dasar mengatur LEBAR (sumbu utama
            saat arahnya baris); begitu arahnya jadi kolom di sini, sumbu
            utama berubah jadi TINGGI, dan flex-basis 220px yang sama
            malah memaksa tinggi elemen ini minimal 220px -- itulah
            sumber jarak kosong besar sebelum teks "Format JPG/PNG..."
            di layar sempit. Direset ke auto supaya tingginya kembali
            mengikuti isi (dua tombol saja). */
         flex: 0 1 auto;
         flex-direction: column;
         align-items: stretch;
      }

      .foto-siswa__aksi .btn {
         width: 100%;
      }
   }

   /* Panduan crop berbentuk lingkaran. Berkas yang disimpan tetap persegi;
      bagian luar lingkaran ditandai agar terlihat bagian mana yang tampil
      pada kartu siswa (elemen foto di kartu memakai sudut membulat). */
   .crop-bulat .cropper-view-box,
   .crop-bulat .cropper-face {
      border-radius: 50%;
   }

   .crop-bulat .cropper-view-box {
      outline-color: rgba(255, 255, 255, .85);
   }

   /* ---- Modal Potong & Edit Foto: header/footer tetap terlihat,
        hanya isi (modal-body) yang digulir. Tanpa ini, foto potret dari
        kamera HP membuat kanvas Cropper.js sangat tinggi -- karena
        Cropper.js menangkap sentuhan pada gambar untuk menggeser area
        crop, layar jadi penuh oleh kanvas itu dan tombol "Lanjut" di
        footer tidak lagi bisa dijangkau maupun digulir ke arahnya. ---- */
   #modalEditFoto .modal-dialog {
      max-height: 100%;
   }

   #modalEditFoto .modal-content {
      max-height: calc(100vh - 1rem);
      max-height: calc(100dvh - 1rem);
   }

   #modalEditFoto .modal-body {
      overflow-y: auto;
      -webkit-overflow-scrolling: touch;
   }

   /* Area gambar dibatasi tingginya & dipotong (overflow hidden) supaya
      Cropper.js tidak membuat kanvas sebesar foto aslinya -- foto potret
      dari kamera HP bisa jauh lebih tinggi daripada lebar layar. */
   .crop-image-wrap {
      height: min(48vh, 420px);
      overflow: hidden;
   }

   @media (max-width: 575.98px) {
      #modalEditFoto .modal-dialog {
         margin: 0;
         min-height: 100%;
      }

      #modalEditFoto .modal-content {
         max-height: 100vh;
         max-height: 100dvh;
         border-radius: 0;
      }

      .crop-image-wrap {
         height: 42vh;
      }
   }

   /* ---- Modal Ambil Foto dari Kamera: sama seperti modal Potong & Edit
        Foto -- header dan footer (tombol Batal/Ambil Foto) selalu
        terlihat, hanya isinya yang digulir bila perlu. Bingkai video juga
        dibatasi tingginya (lihat .kamera-bingkai di bawah) supaya pada
        video potret kamera HP, seluruh isi modal muat tanpa perlu
        menggulir sama sekali di kebanyakan layar. ---- */
   #modalKameraFoto .modal-dialog {
      max-height: 100%;
   }

   #modalKameraFoto .modal-content {
      max-height: calc(100vh - 1rem);
      max-height: calc(100dvh - 1rem);
   }

   #modalKameraFoto .modal-body {
      overflow-y: auto;
      -webkit-overflow-scrolling: touch;
   }

   @media (max-width: 575.98px) {
      #modalKameraFoto .modal-dialog {
         margin: 0;
         min-height: 100%;
      }

      #modalKameraFoto .modal-content {
         max-height: 100vh;
         max-height: 100dvh;
         border-radius: 0;
      }
   }

   /* Footer kedua modal ini bisa berisi 2-4 tombol sekaligus (Batal,
      Ulangi Crop, Lanjut/Gunakan Foto Ini). Di layar sempit, justify-
      content:flex-end bawaan tema memampatkan semuanya ke kanan dan
      tombol paling kiri (Batal) terpotong tanpa cara untuk digulir ke
      sana -- jadi ditumpuk vertikal & dilebarkan penuh, dengan Batal
      selalu di posisi paling bawah supaya aksi utama lebih mudah
      dijangkau ibu jari. */
   @media (max-width: 575.98px) {
      #modalEditFoto .modal-footer,
      #modalKameraFoto .modal-footer {
         flex-direction: column;
         align-items: stretch;
         gap: 8px;
      }

      #modalEditFoto .modal-footer .btn,
      #modalKameraFoto .modal-footer .btn {
         width: 100%;
         margin: 0;
      }

      #modalEditFoto .modal-footer [data-dismiss="modal"],
      #modalKameraFoto .modal-footer [data-dismiss="modal"] {
         order: 1;
      }
   }

   /* ---- Garis bantu pada pratinjau kamera ---- */
   .kamera-bingkai {
      position: relative;
      width: 100%;
      max-width: 400px;
      /* Tinggi dibatasi & dipotong (bukan mengikuti rasio video apa
         adanya): video potret dari kamera HP bisa jauh lebih tinggi
         daripada lebar layar, sama seperti masalah pada langkah crop.
         object-fit:cover di video hanya memotong TAMPILANnya -- jepretan
         tetap diambil dari frame video penuh (videoWidth/videoHeight),
         lihat #btnAmbilFoto. */
      height: min(46vh, 420px);
      margin: 0 auto;
      background: #000;
      overflow: hidden;
      line-height: 0;
   }

   .kamera-bingkai video {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
   }

   @media (max-width: 575.98px) {
      .kamera-bingkai {
         height: 44vh;
      }
   }

   .kamera-panduan {
      position: absolute;
      inset: 0;
      pointer-events: none;
      /* menahan bayangan peredup agar tidak meluber ke luar bingkai video */
      overflow: hidden;
   }

   .kamera-panduan__lingkaran {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      border: 2px dashed rgba(255, 255, 255, .9);
      border-radius: 50%;
      /* area di luar lingkaran diredupkan agar batasnya jelas */
      box-shadow: 0 0 0 100vmax rgba(0, 0, 0, .35);
      /* lebar & tinggi diisi via JS (lihat sesuaikanPanduanKamera): CSS
         murni tidak bisa membuat lingkaran yang selalu pas dengan sisi
         terpendek bingkai, karena persentase lebar & tinggi masing-masing
         dihitung dari sumbu yang berbeda (lebar dari lebar bingkai, tinggi
         dari tinggi bingkai) -- pada video potret (kamera HP) keduanya
         menghasilkan angka berbeda dan lingkaran jadi oval. */
   }

   .kamera-panduan__garis {
      position: absolute;
      background: rgba(255, 255, 255, .45);
   }

   .kamera-panduan__garis--v {
      top: 8%;
      bottom: 8%;
      left: 50%;
      width: 1px;
   }

   .kamera-panduan__garis--h {
      left: 12%;
      right: 12%;
      top: 45%;
      height: 1px;
   }

   /* garis bantu batas bahu di bagian bawah bingkai */
   .kamera-panduan__bahu {
      position: absolute;
      left: 18%;
      right: 18%;
      bottom: 10%;
      height: 1px;
      background: rgba(255, 255, 255, .3);
   }
</style>
<div class="form-group mt-4">
   <label>Foto Siswa</label>
   <div class="foto-siswa">
      <!-- avatar + lencana hapus kecil di pojok, pola umum untuk foto
           profil -- lebih ringkas daripada tombol "Hapus Foto" berukuran
           penuh yang bersaing dengan dua aksi utama (Upload/Kamera) -->
      <div class="foto-siswa__avatar">
         <div id="fotoPreviewWrapper" class="foto-siswa__lingkaran">
            <img id="fotoPreview" src="<?= $fotoUrl ?? ''; ?>" alt="Foto siswa" style="<?= $fotoUrl ? '' : 'display:none;'; ?>">
            <i class="material-icons text-secondary foto-siswa__ikon-kosong" id="fotoPreviewIcon" style="<?= $fotoUrl ? 'display:none;' : ''; ?>">person</i>
         </div>
         <!-- hanya berarti kalau ada foto tersimpan (edit); ditampilkan/
              disembunyikan lewat JS mengikuti isi pratinjau saat ini -->
         <button type="button" class="foto-siswa__hapus <?= $fotoUrl ? '' : 'd-none'; ?>" id="btnHapusFoto" title="Hapus foto">
            <i class="material-icons">close</i>
         </button>
      </div>

      <div class="foto-siswa__aksi">
         <button type="button" class="btn btn-primary btn-sm m-0" id="btnUploadFoto">
            <i class="material-icons mr-1">upload</i>Upload Foto
         </button>
         <button type="button" class="btn btn-outline-info btn-sm m-0" id="btnCameraFoto">
            <i class="material-icons mr-1">photo_camera</i>Ambil dari Kamera
         </button>
         <input type="file" id="fotoFileInput" accept="image/png, image/jpeg" class="d-none">
      </div>
   </div>
   <p class="text-muted small foto-siswa__ket">Format JPG/PNG. Foto dapat dipotong dan latar belakangnya diganti warna sebelum disimpan.</p>
   <input type="hidden" name="foto_data" id="fotoDataInput">
   <!-- ditandai '1' saat tombol Hapus Foto diklik; dibaca controller saat
        submit untuk menghapus berkas & mengosongkan kolom foto. Diabaikan
        server bila foto_data juga terisi (foto baru menang). -->
   <input type="hidden" name="hapus_foto" id="fotoHapusInput" value="0">
</div>

<!-- Modal: ambil foto dari kamera -->
<div class="modal fade" id="modalKameraFoto" tabindex="-1" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title">Ambil Foto dari Kamera</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">&times;</span>
            </button>
         </div>
         <div class="modal-body text-center">
            <div class="form-group text-left d-none" id="wrapPilihKameraFoto">
               <label class="small mb-1" for="pilihKameraFoto">Pilih Kamera</label>
               <select id="pilihKameraFoto" class="custom-select"></select>
            </div>
            <div class="kamera-bingkai">
               <video id="videoKameraFoto" autoplay playsinline></video>
               <!-- garis bantu: lingkaran = area yang tersimpan saat crop 1:1,
                    garis tengah membantu meluruskan posisi wajah -->
               <div class="kamera-panduan" aria-hidden="true">
                  <div class="kamera-panduan__lingkaran"></div>
                  <div class="kamera-panduan__garis kamera-panduan__garis--v"></div>
                  <div class="kamera-panduan__garis kamera-panduan__garis--h"></div>
                  <div class="kamera-panduan__bahu"></div>
               </div>
            </div>
            <p class="text-muted small mt-2 mb-0">
               Posisikan wajah di dalam lingkaran, mata kira-kira sejajar garis mendatar,
               dan sisakan ruang di atas kepala.
            </p>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            <button type="button" class="btn btn-primary" id="btnAmbilFoto">Ambil Foto</button>
         </div>
      </div>
   </div>
</div>

<!-- Modal: potong & edit latar belakang foto -->
<div class="modal fade" id="modalEditFoto" tabindex="-1" aria-hidden="true" data-backdrop="static" data-keyboard="false">
   <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title">Potong &amp; Edit Foto</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">&times;</span>
            </button>
         </div>
         <div class="modal-body">
            <div id="langkahCrop">
               <p class="text-muted small">
                  Geser dan tarik sudut area untuk mengubah ukurannya, lalu klik
                  <b>Lanjut: Edit Background</b>. Ukurannya mengikuti rasio yang dipilih, jadi foto
                  tidak akan gepeng. Panduan lingkaran menunjukkan bagian yang tampil pada kartu
                  siswa; berkas yang disimpan tetap persegi.
               </p>
               <div class="row align-items-end mb-2">
                  <div class="col-sm-5">
                     <label class="small mb-1" for="rasioCropFoto">Rasio foto</label>
                     <select id="rasioCropFoto" class="custom-select custom-select-sm">
                        <option value="bulat" selected>Lingkaran 1 : 1 (disarankan)</option>
                        <option value="0.75">Pas foto 3 : 4</option>
                        <option value="1">Kotak 1 : 1</option>
                        <option value="0.6667">Potret 2 : 3</option>
                        <option value="bebas">Bebas (rasio tidak dikunci)</option>
                     </select>
                  </div>
                  <div class="col-sm-7 mt-2 mt-sm-0">
                     <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary" id="btnZoomInFoto" title="Perbesar">
                           <i class="material-icons">zoom_in</i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="btnZoomOutFoto" title="Perkecil">
                           <i class="material-icons">zoom_out</i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="btnPutarFoto" title="Putar 90 derajat">
                           <i class="material-icons">rotate_right</i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="btnResetCropFoto" title="Kembalikan">
                           <i class="material-icons">restart_alt</i>
                        </button>
                     </div>
                  </div>
               </div>
               <div class="crop-image-wrap">
                  <img id="cropFotoImage" style="max-width:100%;display:block;">
               </div>
            </div>
            <div id="langkahBackground" class="d-none">
               <div class="text-center mb-3">
                  <canvas id="bgFotoCanvas" style="max-width:100%;max-height:420px;border:1px solid #ddd;cursor:crosshair;"></canvas>
               </div>
               <div class="row">
                  <div class="col-md-5">
                     <label class="small mb-1" for="warnaPenggantiFoto">Warna pengganti background</label>
                     <input type="color" id="warnaPenggantiFoto" class="form-control form-control-sm" value="#ffffff">
                  </div>
                  <div class="col-md-5">
                     <label class="small mb-1" for="toleransiWarnaFoto">Toleransi warna (<span id="toleransiWarnaValue">40</span>)</label>
                     <input type="range" id="toleransiWarnaFoto" class="form-control-range" min="5" max="120" value="40">
                  </div>
                  <div class="col-md-2 d-flex align-items-end">
                     <button type="button" class="btn btn-outline-secondary btn-sm btn-block" id="btnResetWarnaFoto">Reset</button>
                  </div>
               </div>
               <p class="text-muted small mt-2 mb-0" id="statusBackgroundFoto">Klik pada bagian latar belakang foto untuk memilih warna yang akan diganti.</p>
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            <button type="button" class="btn btn-outline-primary d-none" id="btnUlangiCropFoto">Ulangi Crop</button>
            <button type="button" class="btn btn-primary" id="btnLanjutCropFoto">Lanjut: Edit Background</button>
            <button type="button" class="btn btn-success d-none" id="btnGunakanFoto">Gunakan Foto Ini</button>
         </div>
      </div>
   </div>
</div>

<script>
   (function() {
      var cropper = null;
      var sourceDataUrl = null; // foto asli sebelum di-crop (utk "Ulangi Crop")
      var croppedDataUrl = null; // hasil crop bersih, sebelum warna background diganti (utk "Reset")
      var targetColor = null; // warna background yang dipilih (RGB) utk diganti
      var kameraStream = null;
      var sedangBukaKamera = false;

      var $fotoFileInput = document.getElementById('fotoFileInput');
      var $cropFotoImage = document.getElementById('cropFotoImage');
      var $bgCanvas = document.getElementById('bgFotoCanvas');
      var bgCtx = $bgCanvas.getContext('2d');
      var $videoKamera = document.getElementById('videoKameraFoto');
      var $selectKamera = document.getElementById('pilihKameraFoto');

      // Tombol tutup/batal pada modal kamera & edit foto diikat langsung ke
      // modal('hide') supaya tidak bergantung sepenuhnya pada auto-binding
      // data-dismiss bawaan Bootstrap.
      $('#modalKameraFoto, #modalEditFoto').on('click', '[data-dismiss="modal"]', function() {
         $(this).closest('.modal').modal('hide');
      });

      function hexToRgb(hex) {
         var v = parseInt(hex.replace('#', ''), 16);
         return { r: (v >> 16) & 255, g: (v >> 8) & 255, b: v & 255 };
      }

      function bukaModalCrop(dataUrl) {
         sourceDataUrl = dataUrl;
         croppedDataUrl = null;
         targetColor = null;
         $('#langkahCrop').removeClass('d-none');
         $('#langkahBackground').addClass('d-none');
         $('#btnLanjutCropFoto').removeClass('d-none');
         $('#btnUlangiCropFoto, #btnGunakanFoto').addClass('d-none');
         $cropFotoImage.src = sourceDataUrl;
         $('#modalEditFoto').modal('show');
      }

      function rasioTerpilih() {
         var nilai = document.getElementById('rasioCropFoto').value;
         // NaN = rasio bebas (Cropper membiarkan kotak diubah sesuka hati)
         if (nilai === 'bebas') return NaN;
         if (nilai === 'bulat') return 1;
         return parseFloat(nilai);
      }

      // tampilkan panduan crop sebagai lingkaran bila rasio bulat dipilih
      function terapkanBentukCrop() {
         var bulat = document.getElementById('rasioCropFoto').value === 'bulat';
         $('#langkahCrop').toggleClass('crop-bulat', bulat);
      }

      function inisialisasiCropper() {
         if (cropper) {
            cropper.destroy();
         }
         terapkanBentukCrop();

         cropper = new Cropper($cropFotoImage, {
            aspectRatio: rasioTerpilih(),
            viewMode: 1,
            autoCropArea: 1,
            background: false,
            // kotak crop bisa digeser & diubah ukurannya; dengan aspectRatio
            // terkunci, tinggi otomatis menyesuaikan lebar
            movable: true,
            zoomable: true,
            cropBoxResizable: true
         });
      }

      function gambarUlangDariCroppedDataUrl(callback) {
         var img = new Image();
         img.onload = function() {
            bgCtx.clearRect(0, 0, $bgCanvas.width, $bgCanvas.height);
            bgCtx.drawImage(img, 0, 0, $bgCanvas.width, $bgCanvas.height);
            if (typeof callback === 'function') callback();
         };
         img.src = croppedDataUrl;
      }

      function terapkanGantiWarna() {
         if (!targetColor) return;
         gambarUlangDariCroppedDataUrl(function() {
            var imgData = bgCtx.getImageData(0, 0, $bgCanvas.width, $bgCanvas.height);
            var data = imgData.data;
            var toleransi = parseInt(document.getElementById('toleransiWarnaFoto').value, 10);
            var penggantiWarna = hexToRgb(document.getElementById('warnaPenggantiFoto').value);

            for (var i = 0; i < data.length; i += 4) {
               var dr = data[i] - targetColor.r;
               var dg = data[i + 1] - targetColor.g;
               var db = data[i + 2] - targetColor.b;
               var jarak = Math.sqrt(dr * dr + dg * dg + db * db);
               if (jarak <= toleransi) {
                  data[i] = penggantiWarna.r;
                  data[i + 1] = penggantiWarna.g;
                  data[i + 2] = penggantiWarna.b;
               }
            }
            bgCtx.putImageData(imgData, 0, 0);
            document.getElementById('statusBackgroundFoto').textContent =
               'Latar belakang diganti. Klik area lain untuk memilih ulang warna, atau ubah warna/toleransi di atas.';
         });
      }

      // --- Upload dari file ---
      $('#btnUploadFoto').on('click', function() {
         $fotoFileInput.click();
      });

      $fotoFileInput.addEventListener('change', function(e) {
         var file = e.target.files[0];
         if (!file) return;
         var reader = new FileReader();
         reader.onload = function(ev) {
            bukaModalCrop(ev.target.result);
         };
         reader.readAsDataURL(file);
         $fotoFileInput.value = '';
      });

      // --- Panduan bingkai kamera: lingkaran selalu pas dengan sisi
      // terpendek bingkai (video kamera HP biasanya potret, jadi lebih
      // tinggi daripada lebar), dihitung ulang tiap kali ukurannya berubah
      // (rotasi layar, video baru mulai, dst).
      var $kameraBingkai = document.querySelector('.kamera-bingkai');
      var $lingkaranPanduan = document.querySelector('.kamera-panduan__lingkaran');

      function sesuaikanPanduanKamera() {
         var sisi = Math.min($kameraBingkai.clientWidth, $kameraBingkai.clientHeight) * 0.86;
         $lingkaranPanduan.style.width = sisi + 'px';
         $lingkaranPanduan.style.height = sisi + 'px';
      }

      if (window.ResizeObserver) {
         new ResizeObserver(sesuaikanPanduanKamera).observe($kameraBingkai);
      } else {
         window.addEventListener('resize', sesuaikanPanduanKamera);
      }
      $videoKamera.addEventListener('loadedmetadata', sesuaikanPanduanKamera);

      // --- Ambil dari kamera ---
      function hentikanStreamKamera() {
         if (kameraStream) {
            kameraStream.getTracks().forEach(function(t) { t.stop(); });
            kameraStream = null;
         }
      }

      // Mulai stream kamera.
      // deviceId diisi -> kamera spesifik itu (dipilih dari dropdown).
      // deviceId kosong -> kamera belakang diutamakan (facingMode: ideal
      // 'environment'); browser tetap boleh memberi kamera lain jika
      // perangkat tidak punya kamera belakang (mis. laptop).
      function mulaiStreamKamera(deviceId) {
         hentikanStreamKamera();
         var constraints = deviceId
            ? { video: { deviceId: { exact: deviceId } } }
            : { video: { facingMode: { ideal: 'environment' } } };
         return navigator.mediaDevices.getUserMedia(constraints).then(function(stream) {
            kameraStream = stream;
            $videoKamera.srcObject = stream;
            return stream;
         });
      }

      // Tandai kamera yang sedang aktif di dropdown, supaya pilihannya
      // konsisten dengan stream yang benar-benar tampil (bukan selalu
      // opsi pertama), termasuk saat dibuka otomatis ke kamera belakang.
      function isiDaftarKamera(devices, deviceIdAktif) {
         $selectKamera.innerHTML = '';
         devices.forEach(function(d, i) {
            var opt = document.createElement('option');
            opt.value = d.deviceId;
            opt.text = d.label || ('Kamera ' + (i + 1));
            opt.selected = d.deviceId === deviceIdAktif;
            $selectKamera.appendChild(opt);
         });
         $('#wrapPilihKameraFoto').toggleClass('d-none', devices.length < 2);
      }

      $('#btnCameraFoto').on('click', function() {
         if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert('Kamera tidak didukung oleh browser ini.');
            return;
         }
         if (sedangBukaKamera) return;
         sedangBukaKamera = true;

         mulaiStreamKamera(null)
            .then(function(stream) {
               var deviceIdAktif = stream.getVideoTracks()[0].getSettings().deviceId;
               return navigator.mediaDevices.enumerateDevices().then(function(devices) {
                  return { devices: devices, deviceIdAktif: deviceIdAktif };
               });
            })
            .then(function(hasil) {
               isiDaftarKamera(
                  hasil.devices.filter(function(d) { return d.kind === 'videoinput'; }),
                  hasil.deviceIdAktif
               );
               $('#modalKameraFoto').modal('show');
               sesuaikanPanduanKamera();
            })
            .catch(function() {
               alert('Tidak dapat mengakses kamera. Pastikan izin kamera sudah diberikan.');
            })
            .then(function() {
               sedangBukaKamera = false;
            });
      });

      $selectKamera.addEventListener('change', function() {
         mulaiStreamKamera(this.value).catch(function() {
            alert('Tidak dapat beralih ke kamera yang dipilih.');
         });
      });

      $('#modalKameraFoto').on('hidden.bs.modal', hentikanStreamKamera);

      $('#btnAmbilFoto').on('click', function() {
         var canvas = document.createElement('canvas');
         canvas.width = $videoKamera.videoWidth;
         canvas.height = $videoKamera.videoHeight;
         canvas.getContext('2d').drawImage($videoKamera, 0, 0);
         $('#modalKameraFoto').modal('hide');
         // hasil kamera dipakai untuk kartu, jadi mulai dari lingkaran 1:1
         document.getElementById('rasioCropFoto').value = 'bulat';
         bukaModalCrop(canvas.toDataURL('image/jpeg', 0.92));
      });

      // --- Crop ---
      $('#modalEditFoto').on('shown.bs.modal', function() {
         if ($('#langkahCrop').hasClass('d-none')) return; // sudah di langkah background (mis. saat "Ulangi Crop")
         inisialisasiCropper();
      });

      $('#modalEditFoto').on('hidden.bs.modal', function() {
         if (cropper) {
            cropper.destroy();
            cropper = null;
         }
      });

      $('#btnLanjutCropFoto').on('click', function() {
         // Sisi terpanjang dibatasi 720 px dan sisi lain mengikuti rasio
         // kotak crop, sehingga foto tidak pernah teregang.
         var canvas = cropper.getCroppedCanvas({
            maxWidth: 720,
            maxHeight: 720,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
         });
         $bgCanvas.width = canvas.width;
         $bgCanvas.height = canvas.height;
         bgCtx.drawImage(canvas, 0, 0);
         croppedDataUrl = canvas.toDataURL('image/jpeg', 0.92);
         targetColor = null;

         cropper.destroy();
         cropper = null;

         $('#langkahCrop').addClass('d-none');
         $('#langkahBackground').removeClass('d-none');
         $('#btnLanjutCropFoto').addClass('d-none');
         $('#btnUlangiCropFoto, #btnGunakanFoto').removeClass('d-none');
         document.getElementById('statusBackgroundFoto').textContent =
            'Klik pada bagian latar belakang foto untuk memilih warna yang akan diganti.';
      });

      document.getElementById('rasioCropFoto').addEventListener('change', function() {
         terapkanBentukCrop();
         if (cropper) cropper.setAspectRatio(rasioTerpilih());
      });

      $('#btnZoomInFoto').on('click', function() { if (cropper) cropper.zoom(0.1); });
      $('#btnZoomOutFoto').on('click', function() { if (cropper) cropper.zoom(-0.1); });
      $('#btnPutarFoto').on('click', function() { if (cropper) cropper.rotate(90); });
      $('#btnResetCropFoto').on('click', function() { if (cropper) cropper.reset(); });

      $('#btnUlangiCropFoto').on('click', function() {
         $('#langkahBackground').addClass('d-none');
         $('#langkahCrop').removeClass('d-none');
         $('#btnLanjutCropFoto').removeClass('d-none');
         $('#btnUlangiCropFoto, #btnGunakanFoto').addClass('d-none');
         inisialisasiCropper();
      });

      // --- Ganti warna background (chroma-key sederhana) ---
      $bgCanvas.addEventListener('click', function(e) {
         var rect = $bgCanvas.getBoundingClientRect();
         var scaleX = $bgCanvas.width / rect.width;
         var scaleY = $bgCanvas.height / rect.height;
         var x = Math.floor((e.clientX - rect.left) * scaleX);
         var y = Math.floor((e.clientY - rect.top) * scaleY);
         var pixel = bgCtx.getImageData(x, y, 1, 1).data;
         targetColor = { r: pixel[0], g: pixel[1], b: pixel[2] };
         terapkanGantiWarna();
      });

      $('#warnaPenggantiFoto').on('input', function() {
         terapkanGantiWarna();
      });

      $('#toleransiWarnaFoto').on('input', function() {
         document.getElementById('toleransiWarnaValue').textContent = this.value;
         terapkanGantiWarna();
      });

      $('#btnResetWarnaFoto').on('click', function() {
         targetColor = null;
         gambarUlangDariCroppedDataUrl();
         document.getElementById('statusBackgroundFoto').textContent =
            'Klik pada bagian latar belakang foto untuk memilih warna yang akan diganti.';
      });

      // --- Konfirmasi akhir ---
      $('#btnGunakanFoto').on('click', function() {
         var hasilAkhir = $bgCanvas.toDataURL('image/jpeg', 0.9);
         document.getElementById('fotoDataInput').value = hasilAkhir;
         document.getElementById('fotoHapusInput').value = '0'; // foto baru membatalkan niat hapus sebelumnya
         document.getElementById('fotoPreview').src = hasilAkhir;
         document.getElementById('fotoPreview').style.display = '';
         document.getElementById('fotoPreviewIcon').style.display = 'none';
         $('#btnHapusFoto').removeClass('d-none');
         $('#modalEditFoto').modal('hide');
      });

      // --- Hapus foto ---
      $('#btnHapusFoto').on('click', function() {
         if (!confirm('Hapus foto siswa ini? Foto baru dapat diunggah lagi sebelum menyimpan perubahan.')) {
            return;
         }
         document.getElementById('fotoDataInput').value = '';
         document.getElementById('fotoHapusInput').value = '1';
         document.getElementById('fotoPreview').src = '';
         document.getElementById('fotoPreview').style.display = 'none';
         document.getElementById('fotoPreviewIcon').style.display = '';
         $(this).addClass('d-none');
      });
   })();
</script>
