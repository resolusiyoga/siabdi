<?php helper('datatable'); ?>
<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<div class="content">
   <div class="container-fluid">
      <div class="row">
         <div class="col-lg-12 col-md-12">
            <div class="card">
               <div class="card-header card-header-danger">
                  <h4 class="card-title"><b>Siswa Terlambat</b></h4>
                  <p class="card-category">
                     Absen masuk melewati jam <?= esc(substr($batasAbsenMasuk, 0, 5)); ?>
                  </p>
               </div>
               <div class="card-body">
                  <form action="<?= base_url('admin/absen-siswa/terlambat'); ?>" method="get" class="row align-items-end">
                     <div class="col-md-3">
                        <label for="tanggal"><b>Tanggal</b></label>
                        <input class="form-control" type="date" name="tanggal" id="tanggal" value="<?= esc($tanggal); ?>">
                     </div>
                     <?php if (empty($kelasWali)) : ?>
                        <div class="col-md-3">
                           <label for="id_kelas"><b>Kelas</b></label>
                           <select class="form-control" name="id_kelas" id="id_kelas">
                              <option value="">Semua kelas</option>
                              <?php foreach ($kelas as $value) : ?>
                                 <option value="<?= $value['id_kelas']; ?>" <?= (string) $idKelas === (string) $value['id_kelas'] ? 'selected' : ''; ?>>
                                    <?= labelKelas($value['kelas'], $value['jurusan']); ?>
                                 </option>
                              <?php endforeach; ?>
                           </select>
                        </div>
                     <?php endif; ?>
                     <div class="col-md-3">
                        <button type="submit" class="btn btn-danger">
                           <i class="material-icons mr-2">search</i> Tampilkan
                        </button>
                     </div>
                  </form>
               </div>
            </div>
         </div>
      </div>

      <div class="row">
         <div class="col-lg-12 col-md-12">
            <div class="card">
               <div class="card-body table-responsive">
                  <?php if (!empty($data)) : ?>
                     <table class="table table-hover" id="tableTerlambat">
                        <thead class="text-primary">
                           <th><b>No.</b></th>
                           <th><b>NIS</b></th>
                           <th><b>Nama Siswa</b></th>
                           <th><b>Kelas</b></th>
                           <th><b>Jam Masuk</b></th>
                           <th><b>Terlambat</b></th>
                        </thead>
                        <tbody>
                           <?php $no = 1; ?>
                           <?php foreach ($data as $value) : ?>
                              <?php
                              $selisih = strtotime($value['jam_masuk']) - strtotime($batasAbsenMasuk);
                              $menit = $selisih > 0 ? intdiv($selisih, 60) : 0;
                              ?>
                              <tr>
                                 <td><?= $no; ?></td>
                                 <td><?= esc($value['nis']); ?></td>
                                 <td><b><?= esc($value['nama_siswa']); ?></b></td>
                                 <td><?= labelKelas($value['kelas'] ?? null, $value['jurusan'] ?? null); ?></td>
                                 <td><b class="text-danger"><?= esc($value['jam_masuk']); ?></b></td>
                                 <td><span class="badge badge-danger"><?= $menit; ?> menit</span></td>
                              </tr>
                           <?php $no++;
                           endforeach; ?>
                        </tbody>
                     </table>
                  <?php else : ?>
                     <h4 class="text-center text-success py-5">
                        Tidak ada siswa yang terlambat pada tanggal ini
                     </h4>
                  <?php endif; ?>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php if (!empty($data)) : ?>
   <script>
      $('#tableTerlambat').DataTable({
         columnDefs: [{
            orderable: false,
            targets: [0]
         }],
         language: <?= json_encode(datatable_lang_id()) ?>
      });
   </script>
<?php endif; ?>
<?= $this->endSection() ?>
