<?php helper('datatable'); ?>
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
                        <button title="Lihat Foto" type="button" class="btn btn-info p-2 btn-lihat-foto-siswa" data-foto="<?= !empty($value['foto']) ? base_url($value['foto']) : ''; ?>" data-nama="<?= esc($value['nama_siswa']); ?>" data-edit-url="<?= isSuperadmin() ? base_url('admin/siswa/edit/' . $value['id_siswa']) : ''; ?>">
                           <i class="material-icons">photo</i>
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
            <a href="#" id="modalLihatFotoSiswaGanti" class="btn btn-primary">
               <i class="material-icons mr-2">photo_camera</i>Ganti Foto
            </a>
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
   $('#modalLihatFotoSiswa').on('click', '[data-dismiss="modal"]', function() {
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
         var foto = $(this).data('foto');
         var nama = $(this).data('nama');
         var editUrl = $(this).data('edit-url');

         $('#modalLihatFotoSiswaNama').text(nama || 'Foto Siswa');
         $('#modalLihatFotoSiswaGanti').attr('href', editUrl).toggle(!!editUrl);

         if (foto) {
            $('#modalLihatFotoSiswaImg').attr('src', foto).show();
            $('#modalLihatFotoSiswaKosong').hide();
         } else {
            $('#modalLihatFotoSiswaImg').hide();
            $('#modalLihatFotoSiswaKosong').show();
         }
         $('#modalLihatFotoSiswa').modal('show');
      });
</script>