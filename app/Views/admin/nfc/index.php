<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<div class="content">
   <div class="container-fluid">
      <div class="row">
         <div class="col-lg-12 col-md-12">
            <div class="card">
               <div class="card-header card-header-primary">
                  <h4 class="card-title"><b>Tulis Kartu NFC</b></h4>
                  <p class="card-category">Daftarkan kartu/tag NFC sebagai metode absen alternatif dari QR Code</p>
               </div>
               <div class="card-body mx-4 my-3">

                  <div class="alert alert-info" id="nfcUnsupported" style="display:none;">
                     Perangkat/browser ini tidak mendukung Web NFC. Buka halaman ini di <b>Chrome atau Edge Android</b> untuk menulis kartu.
                  </div>

                  <div id="nfcArea">
                     <div class="row">
                        <div class="col-md-4">
                           <div class="form-group">
                              <label>Jenis Data</label>
                              <select id="tipeData" class="custom-select">
                                 <option value="siswa">Siswa</option>
                                 <option value="guru">Guru</option>
                              </select>
                           </div>
                        </div>
                        <div class="col-md-4" id="wrapKelas">
                           <div class="form-group">
                              <label>Kelas</label>
                              <select id="kelasSelect" class="custom-select">
                                 <option value="">--Pilih kelas--</option>
                                 <?php foreach ($kelas as $value) : ?>
                                    <option value="<?= $value['id_kelas']; ?>">
                                       <?= labelKelas($value['kelas'], $value['jurusan']); ?>
                                    </option>
                                 <?php endforeach; ?>
                              </select>
                           </div>
                        </div>
                        <div class="col-md-4">
                           <div class="form-group">
                              <label>Nama</label>
                              <select id="orangSelect" class="custom-select" disabled>
                                 <option value="">--Pilih kelas dahulu--</option>
                              </select>
                           </div>
                        </div>
                     </div>

                     <div class="card-body px-0" style="border: 1px dashed #ccc; border-radius: 8px; text-align: center; padding: 32px 16px;">
                        <i class="material-icons" style="font-size: 56px; color: #386C0B;">contactless</i>
                        <h5 class="mt-2 mb-1" id="nfcTargetName">Pilih siswa/guru terlebih dahulu</h5>
                        <p class="text-muted mb-3" id="nfcTargetCode"></p>
                        <button type="button" id="nfcWriteBtn" class="btn btn-primary" disabled>
                           Tempelkan &amp; Tulis Kartu
                        </button>
                        <p class="mt-3 mb-0" id="nfcWriteStatus"></p>
                     </div>

                     <div class="alert alert-secondary mt-4">
                        <b>Cara pakai:</b>
                        <ol class="mb-0 pl-3">
                           <li>Pilih siswa atau guru yang kartunya akan didaftarkan.</li>
                           <li>Tekan tombol "Tempelkan &amp; Tulis Kartu".</li>
                           <li>Izinkan akses NFC saat diminta browser.</li>
                           <li>Tempelkan kartu NTAG kosong ke belakang HP hingga muncul konfirmasi berhasil.</li>
                           <li>Satu kartu hanya bisa dipakai untuk satu siswa/guru. Menulis ulang akan menimpa data lama di kartu.</li>
                        </ol>
                     </div>
                  </div>

               </div>
            </div>
         </div>
      </div>
   </div>
</div>

<script>
   (function() {
      var tipeData = document.getElementById('tipeData');
      var wrapKelas = document.getElementById('wrapKelas');
      var kelasSelect = document.getElementById('kelasSelect');
      var orangSelect = document.getElementById('orangSelect');
      var targetName = document.getElementById('nfcTargetName');
      var targetCode = document.getElementById('nfcTargetCode');
      var writeBtn = document.getElementById('nfcWriteBtn');
      var writeStatus = document.getElementById('nfcWriteStatus');

      var kodeTerpilih = null;
      var namaTerpilih = null;

      if (!('NDEFReader' in window)) {
         document.getElementById('nfcUnsupported').style.display = 'block';
         writeBtn.disabled = true;
      }

      function resetPilihanOrang(placeholder) {
         orangSelect.innerHTML = '<option value="">' + placeholder + '</option>';
         orangSelect.disabled = true;
         pilihTarget(null, null);
      }

      function pilihTarget(kode, nama) {
         kodeTerpilih = kode;
         namaTerpilih = nama;
         writeStatus.textContent = '';

         if (!kode) {
            targetName.textContent = 'Pilih siswa/guru terlebih dahulu';
            targetCode.textContent = '';
            writeBtn.disabled = true;
            return;
         }

         targetName.textContent = nama;
         targetCode.textContent = 'Kode: ' + kode;
         writeBtn.disabled = !('NDEFReader' in window);
      }

      function muatSiswa(idKelas) {
         resetPilihanOrang('Memuat...');
         jQuery.ajax({
            url: "<?= base_url('admin/nfc/siswa-by-kelas'); ?>",
            type: 'post',
            data: setAjaxData({
               idKelas: idKelas
            }),
            success: function(res) {
               var siswa = typeof res === 'string' ? JSON.parse(res) : res;
               orangSelect.innerHTML = '<option value="">--Pilih siswa--</option>';
               siswa.forEach(function(s) {
                  var opt = document.createElement('option');
                  opt.value = s.unique_code;
                  opt.text = s.nis + ' - ' + s.nama_siswa;
                  orangSelect.appendChild(opt);
               });
               orangSelect.disabled = siswa.length === 0;
            },
            error: function() {
               resetPilihanOrang('Gagal memuat data');
            }
         });
      }

      function muatGuru() {
         resetPilihanOrang('Memuat...');
         jQuery.ajax({
            url: "<?= base_url('admin/nfc/guru'); ?>",
            type: 'post',
            data: setAjaxData({}),
            success: function(res) {
               var guru = typeof res === 'string' ? JSON.parse(res) : res;
               orangSelect.innerHTML = '<option value="">--Pilih guru--</option>';
               guru.forEach(function(g) {
                  var opt = document.createElement('option');
                  opt.value = g.unique_code;
                  opt.text = g.nama_guru;
                  orangSelect.appendChild(opt);
               });
               orangSelect.disabled = guru.length === 0;
            },
            error: function() {
               resetPilihanOrang('Gagal memuat data');
            }
         });
      }

      tipeData.addEventListener('change', function() {
         if (this.value === 'guru') {
            wrapKelas.style.display = 'none';
            muatGuru();
         } else {
            wrapKelas.style.display = '';
            resetPilihanOrang('--Pilih kelas dahulu--');
         }
      });

      kelasSelect.addEventListener('change', function() {
         if (this.value) {
            muatSiswa(this.value);
         } else {
            resetPilihanOrang('--Pilih kelas dahulu--');
         }
      });

      orangSelect.addEventListener('change', function() {
         var opt = this.options[this.selectedIndex];
         pilihTarget(this.value || null, this.value ? opt.text : null);
      });

      writeBtn.addEventListener('click', function() {
         if (!kodeTerpilih) return;

         if (!('NDEFReader' in window)) {
            writeStatus.textContent = 'Perangkat ini tidak mendukung Web NFC.';
            return;
         }

         var writer = new NDEFReader();
         writeStatus.textContent = 'Tempelkan kartu ke belakang perangkat...';
         writeBtn.disabled = true;

         writer.write({
               records: [{
                  recordType: 'text',
                  data: kodeTerpilih
               }]
            })
            .then(function() {
               writeStatus.textContent = '✓ Kartu berhasil ditulis untuk ' + namaTerpilih;
            })
            .catch(function(err) {
               console.error(err);
               writeStatus.textContent = 'Gagal menulis kartu: ' + (err.message || err.name);
            })
            .finally(function() {
               writeBtn.disabled = false;
            });
      });
   })();
</script>

<?= $this->endSection() ?>
