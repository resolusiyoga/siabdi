<?php $fotoUrl = !empty($fotoUrlSiswa) ? base_url($fotoUrlSiswa) : null; ?>
<div class="form-group mt-4">
   <label>Foto Siswa</label>
   <div class="row align-items-center">
      <div class="col-auto">
         <div id="fotoPreviewWrapper" style="width:130px;height:130px;border:1px solid #ddd;border-radius:6px;background:#f5f5f5;display:flex;align-items:center;justify-content:center;overflow:hidden;">
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
            <video id="videoKameraFoto" autoplay playsinline style="width:100%;max-width:400px;background:#000;"></video>
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
               <p class="text-muted small">Geser dan sesuaikan area foto, lalu klik <b>Lanjut: Edit Background</b>.</p>
               <div style="max-height:420px;">
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

      function inisialisasiCropper() {
         if (cropper) {
            cropper.destroy();
         }
         cropper = new Cropper($cropFotoImage, {
            aspectRatio: 1,
            viewMode: 1,
            autoCropArea: 1,
            background: false
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
         var constraints = deviceId ? { video: { deviceId: { exact: deviceId } } } : { video: true };
         return navigator.mediaDevices.getUserMedia(constraints).then(function(stream) {
            hentikanStreamKamera();
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
         mulaiStreamKamera(this.value);
      });

      $('#modalKameraFoto').on('hidden.bs.modal', hentikanStreamKamera);

      $('#btnAmbilFoto').on('click', function() {
         var canvas = document.createElement('canvas');
         canvas.width = $videoKamera.videoWidth;
         canvas.height = $videoKamera.videoHeight;
         canvas.getContext('2d').drawImage($videoKamera, 0, 0);
         $('#modalKameraFoto').modal('hide');
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
         var canvas = cropper.getCroppedCanvas({ width: 480, height: 480 });
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
