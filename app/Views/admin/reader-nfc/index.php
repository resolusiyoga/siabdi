<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<?php
$labelHasil = [
   'berhasil'         => ['Berhasil', 'success'],
   'sudah_absen'      => ['Sudah absen', 'info'],
   'sudah_ada_status' => ['Sudah ada status (izin/sakit/dll)', 'info'],
   'belum_masuk'      => ['Belum absen masuk', 'warning'],
   'di_luar_jadwal'   => ['Di luar jadwal', 'warning'],
   'tidak_terdaftar'  => ['Kartu belum terdaftar', 'danger'],
   'diproses'         => ['Diproses', 'secondary'],
];
$labelJenis = ['masuk' => 'Masuk', 'dzuhur' => 'Dzuhur', 'ashar' => 'Ashar', 'pulang' => 'Pulang'];
$namaKelas = fn($r) => trim(($r['kelas'] ?? '') . ' ' . ($r['jurusan'] ?? '')) ?: '-';
$jam = fn($t) => substr($t, 0, 5);
?>
<style>
   .nfc-uid { font-family: monospace; font-size: .95em; }
   .nfc-table td, .nfc-table th { vertical-align: middle; }
   .nfc-scroll { max-height: 460px; overflow-y: auto; }
</style>
<div class="content">
   <div class="container-fluid">
      <?= view('admin/_messages'); ?>

      <?php if (!$apiAktif) : ?>
         <div class="alert alert-warning">
            <b>API reader belum aktif.</b> Tambahkan <code>READER_API_KEY = &lt;kunci acak&gt;</code> di file <code>.env</code> server,
            lalu isi kunci yang sama di menu Pengaturan web reader.
         </div>
      <?php endif; ?>

      <div class="row">
         <!-- Kartu baru + form daftar -->
         <div class="col-12 col-xl-7">
            <div class="card">
               <div class="card-header card-header-danger">
                  <h4 class="card-title"><b>Kartu NFC baru</b></h4>
                  <p class="card-category">Kartu yang di-tap di reader tapi belum terdaftar ke siswa</p>
               </div>
               <div class="card-body">
                  <form action="<?= base_url('admin/reader-nfc/daftar'); ?>" method="post" id="formDaftar" class="mb-3">
                     <?= csrf_field() ?>
                     <div class="row">
                        <div class="col-md-4">
                           <div class="form-group">
                              <label>UID kartu</label>
                              <input type="text" name="uid" id="uidDaftar" class="form-control nfc-uid" placeholder="Klik kartu di bawah" required>
                           </div>
                        </div>
                        <div class="col-md-8">
                           <div class="form-group">
                              <label>Siswa (belum punya kartu: <?= count($siswaTanpaKartu); ?>)</label>
                              <input type="text" id="cariSiswa" class="form-control mb-1" placeholder="Cari nama / NIS / kelas...">
                              <select name="id_siswa" id="pilihSiswa" class="form-control" size="6" required>
                                 <?php $grup = null; ?>
                                 <?php foreach ($siswaTanpaKartu as $s) : ?>
                                    <?php $k = $namaKelas($s); ?>
                                    <?php if ($k !== $grup) : ?>
                                       <?php if ($grup !== null) echo '</optgroup>'; ?>
                                       <optgroup label="<?= esc($k); ?>">
                                       <?php $grup = $k; ?>
                                    <?php endif; ?>
                                    <option value="<?= $s['id_siswa']; ?>" data-cari="<?= esc(strtolower($s['nama_siswa'] . ' ' . $s['nis'] . ' ' . $k)); ?>">
                                       <?= esc($s['nama_siswa']); ?> (<?= esc($s['nis']); ?>)
                                    </option>
                                 <?php endforeach; ?>
                                 <?php if ($grup !== null) echo '</optgroup>'; ?>
                              </select>
                           </div>
                        </div>
                     </div>
                     <button type="submit" class="btn btn-danger">Daftarkan kartu</button>
                  </form>

                  <div class="nfc-scroll">
                     <table class="table table-hover nfc-table">
                        <thead class="text-danger">
                           <tr><th>UID</th><th>Tap terakhir</th><th>Reader</th><th>Tap</th><th></th></tr>
                        </thead>
                        <tbody>
                           <?php if (empty($kartuBaru)) : ?>
                              <tr><td colspan="5" class="text-center text-muted">Tidak ada kartu baru. Tap kartu baru di reader, setelah reader online kartu muncul di sini.</td></tr>
                           <?php endif; ?>
                           <?php foreach ($kartuBaru as $k) : ?>
                              <tr>
                                 <td><a href="javascript:void(0)" class="nfc-uid pilih-uid" data-uid="<?= esc($k['uid']); ?>"><?= esc($k['uid']); ?></a></td>
                                 <td><?= esc($k['terakhir']); ?></td>
                                 <td><?= esc($k['perangkat']); ?></td>
                                 <td><?= (int) $k['jumlah']; ?></td>
                                 <td class="text-right">
                                    <button type="button" class="btn btn-sm btn-danger pilih-uid" data-uid="<?= esc($k['uid']); ?>">Pilih</button>
                                    <form action="<?= base_url('admin/reader-nfc/abaikan'); ?>" method="post" class="d-inline">
                                       <?= csrf_field() ?>
                                       <input type="hidden" name="uid" value="<?= esc($k['uid']); ?>">
                                       <button type="submit" class="btn btn-sm btn-secondary">Abaikan</button>
                                    </form>
                                 </td>
                              </tr>
                           <?php endforeach; ?>
                        </tbody>
                     </table>
                  </div>
               </div>
            </div>
         </div>

         <!-- Status + jadwal -->
         <div class="col-12 col-xl-5">
            <div class="card">
               <div class="card-header card-header-primary">
                  <h4 class="card-title"><b>Status reader</b></h4>
               </div>
               <div class="card-body">
                  <p class="mb-1">API: <?= $apiAktif ? '<span class="text-success"><b>Aktif</b></span>' : '<span class="text-danger"><b>Nonaktif</b></span>'; ?></p>
                  <p>Alamat API untuk reader: <code><?= esc($apiUrl); ?></code></p>
                  <table class="table table-sm nfc-table">
                     <thead><tr><th>Reader</th><th>Terakhir kirim data</th><th>Total tap</th></tr></thead>
                     <tbody>
                        <?php if (empty($perangkat)) : ?>
                           <tr><td colspan="3" class="text-muted">Belum ada reader yang mengirim data.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($perangkat as $p) : ?>
                           <tr><td><?= esc($p['perangkat']); ?></td><td><?= esc($p['terakhir']); ?></td><td><?= (int) $p['jumlah']; ?></td></tr>
                        <?php endforeach; ?>
                     </tbody>
                  </table>
               </div>
            </div>

            <div class="card">
               <div class="card-header card-header-primary">
                  <h4 class="card-title"><b>Jadwal absen otomatis</b></h4>
                  <p class="card-category">Untuk reader dengan mode "Otomatis". Diperiksa berurutan: masuk → dzuhur → ashar → pulang.</p>
               </div>
               <div class="card-body">
                  <form action="<?= base_url('admin/reader-nfc/jadwal'); ?>" method="post">
                     <?= csrf_field() ?>
                     <div class="row">
                        <div class="col-12"><div class="form-group"><label>Masuk: sampai jam</label>
                           <input type="time" name="nfc_akhir_masuk" class="form-control" value="<?= $jam($jadwal['akhir_masuk']); ?>" required></div></div>
                        <div class="col-6"><div class="form-group"><label>Dzuhur: mulai</label>
                           <input type="time" name="nfc_mulai_dzuhur" class="form-control" value="<?= $jam($jadwal['mulai_dzuhur']); ?>" required></div></div>
                        <div class="col-6"><div class="form-group"><label>sampai</label>
                           <input type="time" name="nfc_akhir_dzuhur" class="form-control" value="<?= $jam($jadwal['akhir_dzuhur']); ?>" required></div></div>
                        <div class="col-6"><div class="form-group"><label>Ashar: mulai</label>
                           <input type="time" name="nfc_mulai_ashar" class="form-control" value="<?= $jam($jadwal['mulai_ashar']); ?>" required></div></div>
                        <div class="col-6"><div class="form-group"><label>sampai</label>
                           <input type="time" name="nfc_akhir_ashar" class="form-control" value="<?= $jam($jadwal['akhir_ashar']); ?>" required></div></div>
                        <div class="col-12"><div class="form-group"><label>Pulang: mulai jam</label>
                           <input type="time" name="nfc_mulai_pulang" class="form-control" value="<?= $jam($jadwal['mulai_pulang']); ?>" required></div></div>
                     </div>
                     <small class="text-muted d-block mb-2">Tap di luar semua rentang dicatat di log sebagai "Di luar jadwal" dan tidak mengubah absensi.
                        Batas terlambat tetap memakai "Batas Waktu Absen Masuk" di menu Pengaturan.</small>
                     <button type="submit" class="btn btn-primary btn-block">Simpan jadwal</button>
                  </form>
               </div>
            </div>
         </div>
      </div>

      <div class="row">
         <!-- Kartu terdaftar -->
         <div class="col-12 col-xl-6">
            <div class="card">
               <div class="card-header card-header-primary">
                  <h4 class="card-title"><b>Kartu terdaftar (<?= count($siswaBerkartu); ?>)</b></h4>
               </div>
               <div class="card-body">
                  <input type="text" id="cariBerkartu" class="form-control mb-2" placeholder="Cari nama / kelas / UID...">
                  <div class="nfc-scroll">
                     <table class="table table-hover nfc-table" id="tabelBerkartu">
                        <thead class="text-primary"><tr><th>Nama</th><th>Kelas</th><th>UID</th><th></th></tr></thead>
                        <tbody>
                           <?php foreach ($siswaBerkartu as $s) : ?>
                              <tr data-cari="<?= esc(strtolower($s['nama_siswa'] . ' ' . $namaKelas($s) . ' ' . $s['uid_kartu'])); ?>">
                                 <td><?= esc($s['nama_siswa']); ?></td>
                                 <td><?= esc($namaKelas($s)); ?></td>
                                 <td class="nfc-uid"><?= esc($s['uid_kartu']); ?></td>
                                 <td class="text-right">
                                    <form action="<?= base_url('admin/reader-nfc/lepas'); ?>" method="post" onsubmit="return confirm(this.dataset.konfirmasi)" data-konfirmasi="<?= esc('Lepas kartu dari ' . $s['nama_siswa'] . '?'); ?>">
                                       <?= csrf_field() ?>
                                       <input type="hidden" name="id_siswa" value="<?= $s['id_siswa']; ?>">
                                       <button type="submit" class="btn btn-sm btn-secondary">Lepas</button>
                                    </form>
                                 </td>
                              </tr>
                           <?php endforeach; ?>
                        </tbody>
                     </table>
                  </div>
               </div>
            </div>
         </div>

         <!-- Log tap -->
         <div class="col-12 col-xl-6">
            <div class="card">
               <div class="card-header card-header-primary">
                  <h4 class="card-title"><b>Log tap terbaru</b></h4>
                  <p class="card-category">100 tap terakhir yang diterima dari reader</p>
               </div>
               <div class="card-body nfc-scroll">
                  <table class="table table-sm nfc-table">
                     <thead class="text-primary"><tr><th>Waktu tap</th><th>Siswa / UID</th><th>Jenis</th><th>Hasil</th><th>Reader</th></tr></thead>
                     <tbody>
                        <?php if (empty($log)) : ?>
                           <tr><td colspan="5" class="text-muted">Belum ada data.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($log as $l) : ?>
                           <?php [$teks, $warna] = $labelHasil[$l['hasil']] ?? [$l['hasil'], 'secondary']; ?>
                           <tr>
                              <td><?= esc(substr($l['waktu'], 0, 16)); ?></td>
                              <td><?= $l['nama_siswa'] ? esc($l['nama_siswa']) : '<span class="nfc-uid">' . esc($l['uid']) . '</span>'; ?></td>
                              <td><?= esc($labelJenis[$l['jenis']] ?? '-'); ?><?= $l['mode'] !== 'otomatis' ? ' <small class="text-muted">(mode ' . esc($l['mode']) . ')</small>' : ''; ?></td>
                              <td><span class="badge badge-<?= $warna; ?>"><?= esc($teks); ?></span></td>
                              <td><?= esc($l['perangkat']); ?></td>
                           </tr>
                        <?php endforeach; ?>
                     </tbody>
                  </table>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

<script>
   $(function() {
      $('.pilih-uid').on('click', function() {
         $('#uidDaftar').val($(this).data('uid'));
         $('#cariSiswa').focus();
         $('html, body').animate({ scrollTop: $('#formDaftar').offset().top - 90 }, 200);
      });

      $('#cariSiswa').on('input', function() {
         const q = this.value.toLowerCase().trim();
         $('#pilihSiswa option').each(function() {
            this.hidden = q !== '' && !String($(this).data('cari')).includes(q);
         });
         const pertama = $('#pilihSiswa option').filter(function() { return !this.hidden; }).first();
         if (pertama.length) $('#pilihSiswa').val(pertama.val());
      });

      $('#cariBerkartu').on('input', function() {
         const q = this.value.toLowerCase().trim();
         $('#tabelBerkartu tbody tr').each(function() {
            this.hidden = q !== '' && !String($(this).data('cari')).includes(q);
         });
      });
   });
</script>
<?= $this->endSection() ?>
