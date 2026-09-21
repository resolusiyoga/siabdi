<?php helper('datatable'); ?>
<style>
   /* ---- Modal Potong Foto Siswa: pola yang sama dipakai modal Potong &
        Edit Foto di form tambah/edit siswa (_foto-siswa-field.php) --
        panduan crop lingkaran, area gambar dibatasi tingginya, header/
        footer tetap terlihat & tombol footer ditumpuk penuh di mobile. */
   .crop-bulat .cropper-view-box,
   .crop-bulat .cropper-face {
      border-radius: 50%;
   }

   .crop-bulat .cropper-view-box {
      outline-color: rgba(255, 255, 255, .85);
   }

   .crop-image-wrap {
      height: min(48vh, 420px);
      overflow: hidden;
   }

   #modalPotongFotoSiswa .modal-dialog {
      max-height: 100%;
   }

   #modalPotongFotoSiswa .modal-content {
      max-height: calc(100vh - 1rem);
      max-height: calc(100dvh - 1rem);
   }

   #modalPotongFotoSiswa .modal-body {
      overflow-y: auto;
      -webkit-overflow-scrolling: touch;
   }

   @media (max-width: 575.98px) {
      #modalPotongFotoSiswa .modal-dialog {
         margin: 0;
         min-height: 100%;
      }

      #modalPotongFotoSiswa .modal-content {
         max-height: 100vh;
         max-height: 100dvh;
         border-radius: 0;
      }

      .crop-image-wrap {
         height: 42vh;
      }

      #modalPotongFotoSiswa .modal-footer {
         flex-direction: column;
         align-items: stretch;
         gap: 8px;
      }

      #modalPotongFotoSiswa .modal-footer .btn {
         width: 100%;
         margin: 0;
      }

      #modalPotongFotoSiswa .modal-footer [data-dismiss="modal"] {
         order: 1;
      }
   }
</style>
<div class="card-body table-responsive">
   <?php if (!$empty) : ?>
      <table class="table table-hover" id="tableDataSiswa">
         <thead class="text-primary">
            <?php if (isSuperadmin()) : ?>
               <th width="20"><input type="checkbox" class="checkbox-table" id="checkAll"></th>
            <?php endif; ?>
            <th><b>No</b></th>
            <th><b>NIS</b></th>
            <th><b>Nama Siswa</b></th>
            <th><b>Jenis Kelamin</b></th>
            <th><b>Kelas</b></th>
            <th><b>No HP</b></th>
            <th width="1%"><b>Aksi</b></th>
         </thead>
         <tbody>
            <?php $i = 1;
            foreach ($data as $value) : ?>
               <tr>
                  <?php if (isSuperadmin()) : ?>
                     <td><input type="checkbox" name="checkbox-table" class="checkbox-table" value="<?= $value['id_siswa']; ?>"></td>
                  <?php endif; ?>
                  <td><?= $i; ?></td>
                  <td><?= $value['nis']; ?></td>
                  <td><b><?= $value['nama_siswa']; ?></b></td>
                  <td><?= $value['jenis_kelamin']; ?></td>
                  <td><?= labelKelas($value['kelas'] ?? null, $value['jurusan'] ?? null); ?></td>
                  <td><?= $value['no_hp'] !== null && $value['no_hp'] !== '' ? esc($value['no_hp']) : '-'; ?></td>
                  <td>
                     <div class="d-flex justify-content-center">
                        <button title="Lihat Foto" type="button" class="btn btn-info p-2 btn-lihat-foto-siswa" data-id="<?= $value['id_siswa']; ?>" data-foto="<?= !empty($value['foto']) ? base_url($value['foto']) : ''; ?>" data-nama="<?= esc($value['nama_siswa']); ?>" data-edit-url="<?= isSuperadmin() ? base_url('admin/siswa/edit/' . $value['id_siswa']) : ''; ?>">
                           <i class="material-icons">photo</i>
                        </button>
                        <button title="Lihat Kartu Siswa" type="button" class="btn btn-warning p-2 btn-lihat-kartu-siswa" data-id="<?= $value['id_siswa']; ?>" data-nama="<?= esc($value['nama_siswa']); ?>">
                           <i class="material-icons">badge</i>
                        </button>
                        <?php if (isSuperadmin()) : ?>
                           <a title="Edit" href="<?= base_url('admin/siswa/edit/' . $value['id_siswa']); ?>" class="btn btn-primary p-2" id="<?= $value['nis']; ?>">
                              <i class="material-icons">edit</i>
                           </a>
                           <!-- hapus lewat AJAX (lihat hapusSiswa() di data-siswa.php) supaya tabel
                                dimuat ulang tanpa reload halaman & filter kelas/lokal tidak hilang -->
                           <button title="Delete" type="button" class="btn btn-danger p-2"
                              onclick="hapusSiswa('<?= $value['id_siswa']; ?>', '<?= esc($value['nama_siswa'], 'js'); ?>')">
                              <i class="material-icons">delete_forever</i>
                           </button>
                        <?php endif; ?>
                        <a title="Download QR Code" href="<?= base_url('admin/qr/siswa/' . $value['id_siswa'] . '/download'); ?>" class="btn btn-success p-2">
                           <i class="material-icons">qr_code</i>
                        </a>
                     </div>
                  </td>
               </tr>
            <?php $i++;
            endforeach; ?>
         </tbody>
      </table>
   <?php else : ?>
      <div class="row">
         <div class="col">
            <h4 class="text-center text-danger">Data tidak ditemukan</h4>
         </div>
      </div>
   <?php endif; ?>
</div>

<!-- Modal: viewer foto siswa -->
<div class="modal fade" id="modalLihatFotoSiswa" tabindex="-1" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="modalLihatFotoSiswaNama">Foto Siswa</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">&times;</span>
            </button>
         </div>
         <div class="modal-body text-center">
            <img id="modalLihatFotoSiswaImg" src="" alt="Foto siswa" style="max-width:100%;max-height:400px;display:none;">
            <p id="modalLihatFotoSiswaKosong" class="text-muted mb-0">Siswa ini belum memiliki foto.</p>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            <!-- hanya berarti kalau ada foto & pengguna boleh mengedit (editUrl
                 terisi); dipasang/dilepas lewat JS saat modal dibuka -->
            <button type="button" id="modalLihatFotoSiswaPotong" class="btn btn-warning d-none">
               <i class="material-icons mr-2">crop</i>Potong Foto
            </button>
            <a href="#" id="modalLihatFotoSiswaGanti" class="btn btn-primary">
               <i class="material-icons mr-2">photo_camera</i>Ganti Foto
            </a>
         </div>
      </div>
   </div>
</div>

<!-- Modal: potong (crop) ulang foto siswa yang sudah tersimpan, tanpa
     lewat form edit penuh -->
<div class="modal fade" id="modalPotongFotoSiswa" tabindex="-1" aria-hidden="true" data-backdrop="static" data-keyboard="false">
   <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title">Potong Foto Siswa</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">&times;</span>
            </button>
         </div>
         <div class="modal-body">
            <div class="row align-items-end mb-2">
               <div class="col-sm-5">
                  <label class="small mb-1" for="potongRasio">Rasio foto</label>
                  <select id="potongRasio" class="custom-select custom-select-sm">
                     <option value="bulat" selected>Lingkaran 1 : 1 (disarankan)</option>
                     <option value="0.75">Pas foto 3 : 4</option>
                     <option value="1">Kotak 1 : 1</option>
                     <option value="0.6667">Potret 2 : 3</option>
                     <option value="bebas">Bebas (rasio tidak dikunci)</option>
                  </select>
               </div>
               <div class="col-sm-7 mt-2 mt-sm-0">
                  <div class="btn-group btn-group-sm" role="group">
                     <button type="button" class="btn btn-outline-secondary" id="potongZoomIn" title="Perbesar">
                        <i class="material-icons">zoom_in</i>
                     </button>
                     <button type="button" class="btn btn-outline-secondary" id="potongZoomOut" title="Perkecil">
                        <i class="material-icons">zoom_out</i>
                     </button>
                     <button type="button" class="btn btn-outline-secondary" id="potongPutar" title="Putar 90 derajat">
                        <i class="material-icons">rotate_right</i>
                     </button>
                     <button type="button" class="btn btn-outline-secondary" id="potongReset" title="Kembalikan">
                        <i class="material-icons">restart_alt</i>
                     </button>
                  </div>
               </div>
            </div>
            <div class="crop-image-wrap">
               <img id="potongFotoImage" style="max-width:100%;display:block;">
            </div>
            <p id="potongStatus" class="text-danger small mt-2 mb-0"></p>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            <button type="button" class="btn btn-primary" id="potongSimpan">
               <i class="material-icons mr-2">save</i>Simpan
            </button>
         </div>
      </div>
   </div>
</div>

<!-- Modal: pratinjau kartu siswa (depan & belakang), mengikuti desain
     yang diatur di menu Kartu Siswa -->
<div class="modal fade" id="modalLihatKartuSiswa" tabindex="-1" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="modalLihatKartuSiswaNama">Kartu Siswa</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">&times;</span>
            </button>
         </div>
         <div class="modal-body text-center">
            <div id="modalLihatKartuSiswaIsi" class="modal-lihat-kartu__isi">
               <div id="modalLihatKartuSiswaMuat" class="spinner"></div>
            </div>
         </div>
         <div class="modal-footer">
            <a href="#" id="modalLihatKartuSiswaCetak" target="_blank" class="btn btn-primary">
               <i class="material-icons mr-2">print</i>Cetak / Unduh
            </a>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
         </div>
      </div>
   </div>
</div>

<?php if (!$empty) : ?>
   <script>
      $('#tableDataSiswa').DataTable({
         destroy: true,
         columnDefs: [{
            orderable: false,
            targets: <?= isSuperadmin() ? '[0, 1, 7]' : '[0, 6]' ?>
         }],
         language: <?= json_encode(datatable_lang_id()) ?>
      });
   </script>
<?php endif; ?>
<script>
   // Tutup/x diikat langsung ke modal('hide'), tidak mengandalkan
   // auto-binding data-dismiss bawaan Bootstrap sepenuhnya (pola yang sama
   // dipakai modal kamera/edit foto di _foto-siswa-field.php) -- tanpa ini
   // tombol Tutup & x tidak merespons klik.
   $('#modalLihatFotoSiswa, #modalLihatKartuSiswa, #modalPotongFotoSiswa').on('click', '[data-dismiss="modal"]', function() {
      $(this).closest('.modal').modal('hide');
   });

   // Delegasi lewat document (bukan $('.btn-lihat-foto-siswa').on('click', ...)
   // langsung): DataTables membangun ulang <tr>/<td> dari cache internalnya
   // setiap kali menggambar halaman baru (pindah ke page 2 dst, mengurutkan,
   // atau mencari), sehingga tombol pada halaman selain yang pertama adalah
   // elemen DOM baru yang tidak pernah kena ikatan event langsung -- hanya
   // baris di halaman yang sedang tampil saat skrip ini berjalan yang
   // kebetulan berhasil terikat. Delegasi tetap berfungsi karena listener-nya
   // menempel di document, bukan di tombol yang bisa diganti-ganti itu.
   // off() dulu: partial ini dimuat ulang tiap kali filter kelas/lokal
   // berganti atau tabel disegarkan (lihat getDataSiswa() di
   // data-siswa.php) -- tanpa off(), listener di document akan menumpuk
   // satu per satu setiap reload dan modal terpicu berkali-kali.
   $(document).off('click.lihatFotoSiswa', '.btn-lihat-foto-siswa')
      .on('click.lihatFotoSiswa', '.btn-lihat-foto-siswa', function() {
         var idSiswa = $(this).data('id');
         var foto = $(this).data('foto');
         var nama = $(this).data('nama');
         var editUrl = $(this).data('edit-url');

         $('#modalLihatFotoSiswaNama').text(nama || 'Foto Siswa');
         $('#modalLihatFotoSiswaGanti').attr('href', editUrl).toggle(!!editUrl);
         // disimpan di elemen modal supaya tombol "Potong Foto" tahu siswa
         // mana yang sedang dilihat tanpa perlu membaca ulang tombol pemicu
         $('#modalLihatFotoSiswa').data('id-siswa', idSiswa).data('nama-siswa', nama);

         if (foto) {
            $('#modalLihatFotoSiswaImg').attr('src', foto).show();
            $('#modalLihatFotoSiswaKosong').hide();
         } else {
            $('#modalLihatFotoSiswaImg').hide();
            $('#modalLihatFotoSiswaKosong').show();
         }
         // "Potong Foto" hanya berarti kalau ada foto & pengguna boleh mengedit
         $('#modalLihatFotoSiswaPotong').toggleClass('d-none', !(foto && editUrl));

         $('#modalLihatFotoSiswa').modal('show');
      });

   // ---- Potong (crop) ulang foto yang sudah tersimpan ----
   var potongCropper = null;

   function potongRasioTerpilih() {
      var v = $('#potongRasio').val();
      if (v === 'bebas') return NaN;   // NaN = Cropper membiarkan rasio bebas
      if (v === 'bulat') return 1;
      return parseFloat(v);
   }

   function potongTerapkanBentuk() {
      $('.crop-image-wrap').toggleClass('crop-bulat', $('#potongRasio').val() === 'bulat');
   }

   $('#modalPotongFotoSiswa').on('shown.bs.modal', function() {
      potongTerapkanBentuk();
      if (potongCropper) potongCropper.destroy();
      potongCropper = new Cropper(document.getElementById('potongFotoImage'), {
         aspectRatio: potongRasioTerpilih(),
         viewMode: 1,
         autoCropArea: 1,
         background: false,
         movable: true,
         zoomable: true,
         cropBoxResizable: true
      });
   });

   $('#modalPotongFotoSiswa').on('hidden.bs.modal', function() {
      if (potongCropper) {
         potongCropper.destroy();
         potongCropper = null;
      }
      $('#potongStatus').text('');
   });

   $('#potongRasio').on('change', function() {
      potongTerapkanBentuk();
      if (potongCropper) potongCropper.setAspectRatio(potongRasioTerpilih());
   });
   $('#potongZoomIn').on('click', function() { if (potongCropper) potongCropper.zoom(0.1); });
   $('#potongZoomOut').on('click', function() { if (potongCropper) potongCropper.zoom(-0.1); });
   $('#potongPutar').on('click', function() { if (potongCropper) potongCropper.rotate(90); });
   $('#potongReset').on('click', function() { if (potongCropper) potongCropper.reset(); });

   // Tombol di modal Lihat Foto membuka modal Potong Foto memakai foto yang
   // sedang tampil; kedua modal Bootstrap tidak boleh terbuka bersamaan
   // (backdrop-nya tumpang tindih), jadi Lihat Foto ditutup dulu.
   $('#modalLihatFotoSiswaPotong').on('click', function() {
      var $lihat = $('#modalLihatFotoSiswa');
      document.getElementById('potongFotoImage').src = $('#modalLihatFotoSiswaImg').attr('src');
      $lihat.one('hidden.bs.modal', function() {
         $('#modalPotongFotoSiswa').modal('show');
      });
      $lihat.modal('hide');
   });

   $('#potongSimpan').on('click', function() {
      if (!potongCropper) return;

      var idSiswa = $('#modalLihatFotoSiswa').data('id-siswa');
      if (!idSiswa) {
         $('#potongStatus').text('Siswa tidak diketahui, coba buka ulang foto ini.');
         return;
      }

      var $tombol = $(this);
      $tombol.prop('disabled', true);
      $('#potongStatus').text('');

      // sisi terpanjang dibatasi 720px, sama seperti langkah crop pada
      // form tambah/edit siswa
      var canvas = potongCropper.getCroppedCanvas({
         maxWidth: 720,
         maxHeight: 720,
         imageSmoothingEnabled: true,
         imageSmoothingQuality: 'high'
      });
      var hasil = canvas.toDataURL('image/jpeg', 0.9);

      $.ajax({
         type: 'POST',
         url: "<?= base_url('admin/siswa/potong-foto/') ?>" + idSiswa,
         data: setAjaxData({ foto_data: hasil }),
         success: function(res) {
            if (!res || !res.sukses) {
               $('#potongStatus').text((res && res.pesan) || 'Gagal menyimpan foto');
               return;
            }

            // sisipkan penanda waktu supaya browser tidak memakai foto lama
            // dari cache walau nama berkasnya sama persis
            var fotoBaru = res.foto + (res.foto.indexOf('?') === -1 ? '?' : '&') + 't=' + Date.now();

            $('#modalLihatFotoSiswaImg').attr('src', fotoBaru);
            $('.btn-lihat-foto-siswa[data-id="' + idSiswa + '"]').data('foto', fotoBaru).attr('data-foto', fotoBaru);

            $('#modalPotongFotoSiswa').one('hidden.bs.modal', function() {
               $('#modalLihatFotoSiswa').modal('show');
            }).modal('hide');
         },
         error: function(xhr) {
            var res = xhr.responseJSON;
            $('#potongStatus').text((res && res.pesan) || 'Gagal menyimpan foto');
         },
         complete: function() {
            $tombol.prop('disabled', false);
         }
      });
   });
</script>