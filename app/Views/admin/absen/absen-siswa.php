<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<div class="content">
   <div class="container-fluid">
      <div class="row">
         <div class="col-lg-12 col-md-12">
            <div class="card">
               <div class="card-header card-header-tabs card-header-primary">
                  <div class="nav-tabs-navigation">
                     <div class="row">
                        <div class="col-md-2">
                           <h4 class="card-title"><b>Absen Siswa</b></h4>
                           <p class="card-category">Silakan pilih kelas</p>
                        </div>
                        <div class="col-md-4">
                           <div class="nav-tabs-wrapper">
                              <span class="nav-tabs-title">Kelas:</span>
                              <ul class="nav nav-tabs" data-tabs="tabs">
                                 <li class="nav-item">
                                    <a class="nav-link <?= empty($defaultKelas) ? 'active' : ''; ?>" onclick="kelas = null; trig()" href="#" data-toggle="tab">
                                       <i class="material-icons">check</i> Semua
                                       <div class="ripple-container"></div>
                                    </a>
                                 </li>
                                 <?php
                                 $tempKelas = [];
                                 foreach ($kelas as $value) : ?>
                                    <?php if (!in_array($value['kelas'], $tempKelas)) : ?>
                                       <li class="nav-item">
                                          <a class="nav-link <?= $defaultKelas === $value['kelas'] ? 'active' : ''; ?>" onclick="kelas = '<?= $value['kelas']; ?>'; trig()" href="#" data-toggle="tab">
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
                              <ul class="nav nav-tabs" data-tabs="tabs">
                                 <li class="nav-item">
                                    <a class="nav-link <?= empty($defaultJurusan) ? 'active' : ''; ?>" onclick="jurusan = null; trig()" href="#" data-toggle="tab">
                                       <i class="material-icons">check</i> Semua
                                       <div class="ripple-container"></div>
                                    </a>
                                 </li>
                                 <?php foreach ($jurusan as $value) : ?>
                                    <li class="nav-item">
                                       <a class="nav-link <?= $defaultJurusan === $value['jurusan'] ? 'active' : ''; ?>" onclick="jurusan = '<?= $value['jurusan']; ?>'; trig();" href="#" data-toggle="tab">
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
               <div class="card-body">
                  <div class="row">
                     <div class="col-md-3">
                        <div class="pl-3">
                           <h4><b>Tanggal</b></h4>
                           <input class="form-control" type="date" name="tangal" id="tanggal" value="<?= date('Y-m-d'); ?>" onchange="onDateChange()">
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <div class="card" id="dataSiswa">
         <div class="card-body">
            <div class="row justify-content-between">
               <div class="col-auto me-auto">
                  <div class="pt-3 pl-3">
                     <h4><b>Absen Siswa</b></h4>
                     <p>Daftar siswa muncul disini</p>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>

   <!-- Modal ubah kehadiran -->
   <div class="modal fade" id="ubahModal" tabindex="-1" aria-labelledby="modalUbahKehadiran" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="modalUbahKehadiran">Ubah kehadiran</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
               </button>
            </div>
            <div id="modalFormUbahSiswa"></div>
         </div>
      </div>
   </div>
</div>
<script>
   var kelas = <?= !empty($defaultKelas) ? json_encode($defaultKelas) : 'null'; ?>;
   var jurusan = <?= !empty($defaultJurusan) ? json_encode($defaultJurusan) : 'null'; ?>;

   getSiswa();

   function trig() {
      getSiswa();
   }

   function onDateChange() {
      getSiswa();
   }

   function getSiswa() {
      var tanggal = $('#tanggal').val();

      jQuery.ajax({
         url: "<?= base_url('/admin/absen-siswa'); ?>",
         type: 'post',
         data: {
            'kelas': kelas,
            'jurusan': jurusan,
            'tanggal': tanggal
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

   function getDataKehadiran(idPresensi, idSiswa) {
      jQuery.ajax({
         url: "<?= base_url('/admin/absen-siswa/kehadiran'); ?>",
         type: 'post',
         data: {
            'id_presensi': idPresensi,
            'id_siswa': idSiswa
         },
         success: function(response, status, xhr) {
            // console.log(status);
            $('#modalFormUbahSiswa').html(response);
         },
         error: function(xhr, status, thrown) {
            console.log(thrown);
            $('#modalFormUbahSiswa').html(thrown);
         }
      });
   }

   function ubahKehadiran() {
      var tanggal = $('#tanggal').val();

      var form = $('#formUbah').serializeArray();

      form.push({
         name: 'tanggal',
         value: tanggal
      });

      console.log(form);

      jQuery.ajax({
         url: "<?= base_url('/admin/absen-siswa/edit'); ?>",
         type: 'post',
         data: form,
         success: function(response, status, xhr) {
            // console.log(status);

            if (response['status']) {
               getSiswa();
               alert('Berhasil ubah kehadiran : ' + response['nama_siswa']);
            } else {
               alert('Gagal ubah kehadiran : ' + response['nama_siswa']);
            }
         },
         error: function(xhr, status, thrown) {
            console.log(thrown);
            alert('Gagal ubah kehadiran\n' + thrown);
         }
      });
   }
</script>
<?= $this->endSection() ?>