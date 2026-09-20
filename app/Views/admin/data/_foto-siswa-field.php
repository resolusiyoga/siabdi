<?php $fotoUrl = !empty($fotoUrlSiswa) ? base_url($fotoUrlSiswa) : null; ?>
<style>
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

   /* ---- Garis bantu pada pratinjau kamera ---- */
   .kamera-bingkai {
      position: relative;
      width: 100%;
      max-width: 400px;
      margin: 0 auto;
      background: #000;
      line-height: 0;
   }

   .kamera-bingkai video {
      width: 100%;
      display: block;
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
      /* Ukuran mengikuti sisi terpendek bingkai: pada layar ponsel video
         berorientasi potret, sehingga lingkaran setinggi 86% akan lebih
         lebar daripada bingkainya dan terpotong di kiri-kanan. */
      height: 86%;
      max-width: 86%;
      aspect-ratio: 1;
      transform: translate(-50%, -50%);
      border: 2px dashed rgba(255, 255, 255, .9);
      border-radius: 50%;
      /* area di luar lingkaran diredupkan agar batasnya jelas */
      box-shadow: 0 0 0 100vmax rgba(0, 0, 0, .35);
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
   <div class="row align-items-center">
      <div class="col-auto">
         <div id="fotoPreviewWrapper" style="width:130px;height:130px;border:1px solid #ddd;border-radius:50%;background:#f5f5f5;display:flex;align-items:center;justify-content:center;overflow:hidden;">
            <img id="fotoPreview" src="<?= $fotoUrl ?? ''; ?>" alt="Foto siswa" style="width:100%;height:100%;object-fit:cover;<?= $fotoUrl ? '' : 'display:none;'; ?>">
            <i class="material-icons text-secondary" id="fotoPreviewIcon" style="font-size:48px;<?= $fotoUrl ? 'display:none;' : ''; ?>">person</i>
         </div>
      </div>
      <div class="col">
         <button type="button" class="btn btn-primary" id="btnUploadFoto">
            <i class="material-icons mr-2">upload</i>Upload Foto
         </button>
         <button type="button" class="btn btn-info" id="btnCameraFoto">
            <i class="material-icons mr-2">photo_camera</i>Ambil dari Kamera
         </button>
         <input type="file" id="fotoFileInput" accept="image/png, image/jpeg" class="d-none">
         <p class="text-muted small mt-2 mb-0">Format JPG/PNG. Foto dapat dipotong dan latar belakangnya diganti warna sebelum disimpan.</p>
      </div>
   </div>
   <input type="hidden" name="foto_data" id="fotoDataInput">
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

      // --- Ambil dari kamera ---
      function hentikanStreamKamera() {
         if (kameraStream) {
            kameraStream.getTracks().forEach(function(t) { t.stop(); });
            kameraStream = null;
         }
      }

      // Mulai stream kamera. deviceId null = kamera default (dipakai saat
      // pertama kali membuka modal, sekaligus utk memicu izin & label device).
      function mulaiStreamKamera(deviceId) {
         hentikanStreamKamera();
         var constraints = deviceId ? { video: { deviceId: { exact: deviceId } } } : { video: true };
         return navigator.mediaDevices.getUserMedia(constraints).then(function(stream) {
            kameraStream = stream;
            $videoKamera.srcObject = stream;
         });
      }

      function isiDaftarKamera(devices) {
         $selectKamera.innerHTML = '';
         devices.forEach(function(d, i) {
            var opt = document.createElement('option');
            opt.value = d.deviceId;
            opt.text = d.label || ('Kamera ' + (i + 1));
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
            .then(function() {
               return navigator.mediaDevices.enumerateDevices();
            })
            .then(function(devices) {
               isiDaftarKamera(devices.filter(function(d) { return d.kind === 'videoinput'; }));
               $('#modalKameraFoto').modal('show');
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
         document.getElementById('fotoPreview').src = hasilAkhir;
         document.getElementById('fotoPreview').style.display = '';
         document.getElementById('fotoPreviewIcon').style.display = 'none';
         $('#modalEditFoto').modal('hide');
      });
   })();
</script>
