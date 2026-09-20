<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<div class="content">
   <div class="container-fluid">
      <div class="row">
         <div class="col-lg-12 col-md-12">
            <?php if (session()->getFlashdata('msg')) : ?>
               <div class="pb-2 px-3">
                  <div class="alert alert-<?= session()->getFlashdata('error') == true ? 'danger' : 'success'  ?> ">
                     <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <i class="material-icons">close</i>
                     </button>
                     <?= session()->getFlashdata('msg') ?>
                  </div>
               </div>
            <?php endif; ?>
            <?php if (isSuperadmin()) : ?>
               <a class="btn btn-primary ml-3 pl-3 py-3" href="<?= base_url('admin/siswa/create'); ?>">
                  <i class="material-icons mr-2">add</i> Tambah data siswa
               </a>
               <a class="btn btn-primary ml-3 pl-3 py-3" href="<?= base_url('admin/siswa/bulk'); ?>">
                  <i class="material-icons mr-2">add</i> Import CSV
               </a>
               <button class="btn btn-danger ml-3 pl-3 py-3 btn-table-delete" onclick="deleteSelectedSiswa('Data yang sudah dihapus tidak bisa kembalikan');"><i class="material-icons mr-2">delete_forever</i>Bulk Delete</button>
            <?php endif; ?>
            <?php if (isSuperadmin()) : ?>
               <div class="dropdown d-inline-block">
                  <button class="btn btn-info ml-3 pl-3 py-3 dropdown-toggle" type="button" id="dropdownDownloadFotoKelas" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                     <i class="material-icons mr-2">photo_library</i> Download Foto Siswa
                  </button>
                  <div class="dropdown-menu" aria-labelledby="dropdownDownloadFotoKelas">
                     <a class="dropdown-item" href="<?= base_url('admin/siswa/foto/download'); ?>">Semua</a>
                     <div class="dropdown-divider"></div>
                     <?php foreach ($kelas as $value) : ?>
                        <a class="dropdown-item" href="<?= base_url('admin/siswa/foto/download?id_kelas=' . $value['id_kelas']); ?>">
                           <?= labelKelas($value['kelas'], $value['jurusan']); ?>
                        </a>
                     <?php endforeach; ?>
                  </div>
               </div>
            <?php elseif (currentUserRole() === 'wali_kelas') : ?>
               <a class="btn btn-info ml-3 pl-3 py-3" href="<?= base_url('admin/siswa/foto/download'); ?>">
                  <i class="material-icons mr-2">photo_library</i> Download Foto Kelas Saya
               </a>
            <?php endif; ?>
            <div class="card">
               <div class="card-header card-header-tabs card-header-primary">
                  <div class="nav-tabs-navigation">
                     <div class="row">
                        <div class="col-md-2">
                           <h4 class="card-title"><b>Daftar Siswa</b></h4>
                           <p class="card-category">Angkatan <?= $generalSettings->school_year; ?></p>
                        </div>
                        <div class="col-md-4">
                           <div class="nav-tabs-wrapper">
                              <span class="nav-tabs-title">Kelas:</span>
                              <ul class="nav nav-tabs" id="tabKelas" data-tabs="tabs">
                                 <li class="nav-item">
                                    <a class="nav-link <?= empty($defaultKelas) ? 'active' : ''; ?>" data-kelas="" onclick="pilihKelas(null)" href="#" data-toggle="tab">
                                       <i class="material-icons">check</i> Semua
                                       <div class="ripple-container"></div>
                                    </a>
                                 </li>
                                 <?php
                                 $tempKelas = [];
                                 foreach ($kelas as $value) : ?>
                                    <?php if (!in_array($value['kelas'], $tempKelas)) : ?>
                                       <li class="nav-item">
                                          <a class="nav-link <?= $defaultKelas === $value['kelas'] ? 'active' : ''; ?>" data-kelas="<?= esc($value['kelas'], 'attr'); ?>" onclick="pilihKelas('<?= esc($value['kelas'], 'js'); ?>')" href="#" data-toggle="tab">
                                             <i class="material-icons">school</i> <?= $value['kelas']; ?>
                                             <div class="ripple-container"></div>
                                          </a>
                                       </li>
                                       <?php array_push($tempKelas, $value['kelas']) ?>
                                    <?php endif; ?>
                                 <?php endforeach; ?>
                              </ul>
                           </div>
                        </div>
                        <div class="col-md-6">
                           <div class="nav-tabs-wrapper">
                              <span class="nav-tabs-title">Lokal:</span>
                              <ul class="nav nav-tabs" id="tabJurusan" data-tabs="tabs">
                                 <li class="nav-item">
                                    <a class="nav-link <?= empty($defaultJurusan) ? 'active' : ''; ?>" data-jurusan="" onclick="pilihJurusan(null)" href="#" data-toggle="tab">
                                       <i class="material-icons">check</i> Semua
                                       <div class="ripple-container"></div>
                                    </a>
                                 </li>
                                 <?php foreach ($jurusan as $value) : ?>
                                    <li class="nav-item">
                                       <a class="nav-link <?= $defaultJurusan === $value['jurusan'] ? 'active' : ''; ?>" data-jurusan="<?= esc($value['jurusan'], 'attr'); ?>" onclick="pilihJurusan('<?= esc($value['jurusan'], 'js'); ?>')" href="#" data-toggle="tab">
                                          <i class="material-icons">bookmark</i> <?= $value['jurusan']; ?>
                                          <div class="ripple-container"></div>
                                       </a>
                                    </li>
                                 <?php endforeach; ?>
                              </ul>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div id="dataSiswa">
                  <p class="text-center mt-3">Daftar siswa muncul disini</p>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<script>
   // ---- Filter kelas/lokal diingat di browser (sessionStorage) ----
   // Tujuannya: Tambah/Edit/Hapus siswa tidak pernah "mereset" tampilan ke
   // Semua kelas. Hapus per baris & massal sudah murni AJAX (tidak pindah
   // halaman sama sekali); Tambah & Edit tetap di halaman terpisah karena
   // formnya besar (unggah/crop/kamera foto), tapi begitu kembali ke sini
   // -- termasuk lewat tombol back atau refresh manual -- filter terakhir
   // otomatis dipakai lagi, bukan filter bawaan dari server.
   var KUNCI_KELAS = 'siabdiSiswaFilterKelas';
   var KUNCI_JURUSAN = 'siabdiSiswaFilterJurusan';

   var kelas = <?= !empty($defaultKelas) ? json_encode($defaultKelas) : 'null'; ?>;
   var jurusan = <?= !empty($defaultJurusan) ? json_encode($defaultJurusan) : 'null'; ?>;

   // filter bawaan dari server (mis. wali kelas selalu dikunci ke kelasnya)
   // tidak pernah ditimpa; sessionStorage hanya dipakai saat server tidak
   // memaksa filter tertentu (superadmin).
   try {
      if (kelas === null && sessionStorage.getItem(KUNCI_KELAS) !== null) {
         kelas = JSON.parse(sessionStorage.getItem(KUNCI_KELAS));
      }
      if (jurusan === null && sessionStorage.getItem(KUNCI_JURUSAN) !== null) {
         jurusan = JSON.parse(sessionStorage.getItem(KUNCI_JURUSAN));
      }
   } catch (e) {
      // localStorage/sessionStorage bisa dibatasi (mode privat dsb.); abaikan
   }

   function simpanFilter() {
      try {
         kelas === null ? sessionStorage.removeItem(KUNCI_KELAS) : sessionStorage.setItem(KUNCI_KELAS, JSON.stringify(kelas));
         jurusan === null ? sessionStorage.removeItem(KUNCI_JURUSAN) : sessionStorage.setItem(KUNCI_JURUSAN, JSON.stringify(jurusan));
      } catch (e) {}
   }

   // Tandai tab yang aktif sesuai variabel kelas/jurusan saat ini -- perlu
   // dipanggil manual (bukan hanya mengandalkan klik) karena filter bisa
   // datang dari sessionStorage tanpa ada klik sama sekali.
   function tandaiTabAktif() {
      $('#tabKelas .nav-link').removeClass('active');
      $('#tabKelas .nav-link[data-kelas="' + (kelas || '') + '"]').addClass('active');
      $('#tabJurusan .nav-link').removeClass('active');
      $('#tabJurusan .nav-link[data-jurusan="' + (jurusan || '') + '"]').addClass('active');
   }

   function pilihKelas(v) {
      kelas = v;
      simpanFilter();
      tandaiTabAktif();
      trig();
   }

   function pilihJurusan(v) {
      jurusan = v;
      simpanFilter();
      tandaiTabAktif();
      trig();
   }

   tandaiTabAktif();
   getDataSiswa(kelas, jurusan);

   function trig() {
      getDataSiswa(kelas, jurusan);
   }

   function getDataSiswa(_kelas = null, _jurusan = null) {
      jQuery.ajax({
         url: "<?= base_url('/admin/siswa'); ?>",
         type: 'post',
         data: {
            'kelas': _kelas,
            'jurusan': _jurusan
         },
         success: function(response, status, xhr) {
            // console.log(status);
            $('#dataSiswa').html(response);

            $('html, body').animate({
               scrollTop: $("#dataSiswa").offset().top
            }, 500);
         },
         error: function(xhr, status, thrown) {
            console.log(thrown);
            $('#dataSiswa').html(thrown);
         }
      });
   }

   // ---- Hapus satu siswa lewat AJAX: tabel dimuat ulang di tempat,
   // filter kelas/lokal yang sedang aktif tidak hilang. ----
   function hapusSiswa(id, nama) {
      swal({
         text: 'Hapus data siswa "' + nama + '"? Data yang sudah dihapus tidak bisa dikembalikan.',
         icon: 'warning',
         buttons: [BaseConfig.textCancel, BaseConfig.textOk],
         dangerMode: true,
      }).then(function(yakin) {
         if (!yakin) return;

         $.ajax({
            type: 'POST',
            url: "<?= base_url('admin/siswa/delete/') ?>" + id,
            data: setAjaxData({ '_method': 'DELETE' }),
            success: function(res) {
               getDataSiswa(kelas, jurusan);
               if (res && res.sukses === false) {
                  swal(res.pesan || 'Gagal menghapus data', '', 'error');
               }
            },
            error: function() {
               swal('Gagal menghapus data', '', 'error');
            }
         });
      });
   }

   document.addEventListener('DOMContentLoaded', function() {
      $("#checkAll").click(function(e) {
         console.log(e);
         $('input:checkbox').not(this).prop('checked', this.checked);
      });
   });
</script>
<?= $this->endSection() ?>