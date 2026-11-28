<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<?php
$labelHasil = [
   'berhasil'         => ['Berhasil', 'ok'],
   'sudah_absen'      => ['Sudah absen', 'info'],
   'sudah_ada_status' => ['Sudah ada status', 'info'],
   'belum_masuk'      => ['Belum absen masuk', 'warn'],
   'di_luar_jadwal'   => ['Di luar jadwal', 'warn'],
   'tidak_terdaftar'  => ['Belum terdaftar', 'bad'],
   'diproses'         => ['Diproses', 'info'],
];
$labelJenis = ['masuk' => 'Masuk', 'dzuhur' => 'Dzuhur', 'ashar' => 'Ashar', 'pulang' => 'Pulang'];
$namaKelas = fn($r) => trim(($r['kelas'] ?? '') . ' ' . ($r['jurusan'] ?? '')) ?: '-';
$jam = fn($t) => substr($t, 0, 5);
$tglJam = fn($t) => $t ? date('d/m H:i', strtotime($t)) : '-';
?>
<style>
   .nfc { --hijau: #386C0B; --hijau-tua: #293F14; --hijau-muda: #eef6e7; --garis: #e3e8df; --abu: #6b7468; }
   .nfc .card { margin-top: 30px; }
   .nfc .card-header h4 { margin: 0; }
   .nfc .card-header .card-category { margin: 4px 0 0; }
   .nfc-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; letter-spacing: .5px; }
   .nfc-label { font-size: 12px; font-weight: 500; text-transform: uppercase; letter-spacing: .6px; color: var(--abu); margin-bottom: 8px; display: block; }
   .nfc-langkah { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 50%;
      background: var(--hijau); color: #fff; font-size: 12px; font-weight: 500; margin-right: 6px; }
   .nfc-ikon { font-size: 18px; vertical-align: middle; }

   /* Kotak UID */
   .nfc-uid-box { border: 2px dashed #c9d6bf; border-radius: 12px; padding: 18px 12px; text-align: center; background: #fafcf8; transition: all .2s; }
   .nfc-uid-box.ada { border-style: solid; border-color: var(--hijau); background: var(--hijau-muda); }
   .nfc-uid-box.dipakai { border-style: solid; border-color: #e0a800; background: #fff8e1; }
   .nfc-uid-box .material-icons { font-size: 40px; color: #b7c4ad; }
   .nfc-uid-box.ada .material-icons { color: var(--hijau); }
   .nfc-uid-box.dipakai .material-icons { color: #e0a800; }
   .nfc-uid-nilai { font-size: 22px; font-weight: 500; color: var(--hijau-tua); margin: 4px 0; word-break: break-all; }
   .nfc-uid-ket { font-size: 13px; color: var(--abu); }
   .nfc-uid-ket.peringatan { color: #8a6100; }
   .nfc-uid-manual { font-size: 12px; margin-top: 8px; }
   .nfc-uid-manual input { width: 100%; border: 1px solid var(--garis); border-radius: 6px; padding: 6px 8px; font-size: 13px; outline: none; }
   .nfc-uid-manual input:focus { border-color: var(--hijau); }

   /* USB */
   .nfc-usb { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 12px; }
   .nfc-usb .btn { margin: 0; }
   .nfc-titik { width: 10px; height: 10px; border-radius: 50%; background: #c4c4c4; display: inline-block; flex: none; }
   .nfc-titik.siap { background: #38A700; box-shadow: 0 0 0 4px rgba(56, 167, 0, .18); }
   .nfc-titik.proses { background: #f0ad4e; }
   .nfc-titik.error { background: #e53935; }
   .nfc-usb-status { font-size: 13px; color: var(--abu); }
   .nfc-catatan { margin-top: 10px; font-size: 13px; }

   /* Daftar siswa */
   .nfc-cari { position: relative; }
   .nfc-cari .material-icons { position: absolute; left: 10px; top: 9px; color: #9aa596; font-size: 20px; }
   .nfc-cari input { width: 100%; border: 1px solid var(--garis); border-radius: 8px; padding: 9px 10px 9px 38px; font-size: 14px; outline: none; }
   .nfc-cari input:focus { border-color: var(--hijau); box-shadow: 0 0 0 3px rgba(56, 108, 11, .12); }
   .nfc-daftar { border: 1px solid var(--garis); border-radius: 8px; margin-top: 8px; height: 300px; overflow-y: auto; }
   .nfc-grup { position: sticky; top: 0; background: #f4f7f1; font-size: 12px; font-weight: 500; color: var(--hijau-tua); padding: 5px 12px; border-bottom: 1px solid var(--garis); z-index: 1; }
   .nfc-siswa { display: flex; justify-content: space-between; align-items: center; gap: 8px; padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f1f3ef; font-size: 14px; }
   .nfc-siswa:hover { background: #f6faf2; }
   .nfc-siswa.dipilih { background: var(--hijau); color: #fff; }
   .nfc-siswa.dipilih small { color: #e3f0d6; }
   .nfc-siswa small { color: var(--abu); flex: none; }
   .nfc-kosong { padding: 18px; text-align: center; color: var(--abu); font-size: 13px; }

   .nfc-ringkas { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; justify-content: space-between;
      background: #f6f8f4; border-radius: 10px; padding: 10px 12px; margin-top: 16px; }
   .nfc-ringkas-teks { font-size: 14px; color: var(--abu); }
   .nfc-ringkas-teks b { color: var(--hijau-tua); }
   .nfc-ringkas .btn { margin: 0; }

   /* Tabel */
   .nfc-tabel { width: 100%; font-size: 14px; border-collapse: collapse; }
   .nfc-tabel th { font-size: 12px; font-weight: 500; text-transform: uppercase; letter-spacing: .5px; color: var(--abu);
      border-bottom: 1px solid var(--garis); padding: 8px; position: sticky; top: 0; background: #fff; z-index: 1; }
   .nfc-tabel td { padding: 9px 8px; border-bottom: 1px solid #f1f3ef; vertical-align: middle; }
   .nfc-tabel tr:last-child td { border-bottom: 0; }
   .nfc-gulir { max-height: 380px; overflow-y: auto; }
   .nfc-badge { display: inline-block; font-size: 12px; padding: 2px 8px; border-radius: 10px; font-weight: 500; white-space: nowrap; }
   .nfc-badge.ok { background: #e3f4d8; color: #2c5a08; }
   .nfc-badge.info { background: #e8eef7; color: #2f4b73; }
   .nfc-badge.warn { background: #fff1d6; color: #7a5200; }
   .nfc-badge.bad { background: #fde4e4; color: #9b1c1c; }
   .nfc-aksi { white-space: nowrap; }
   .nfc-aksi .btn { margin: 0 0 0 4px; padding: 5px 10px; font-size: 12px; }
   .nfc-filter { width: 100%; border: 1px solid var(--garis); border-radius: 8px; padding: 8px 10px; font-size: 14px; margin-bottom: 8px; outline: none; }
   .nfc-filter:focus { border-color: var(--hijau); }

   /* Status & jadwal */
   .nfc-stat { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; }
   .nfc-api { display: flex; gap: 6px; align-items: center; background: #f6f8f4; border-radius: 8px; padding: 8px 10px; font-size: 13px; word-break: break-all; }
   .nfc-api .btn { margin: 0; padding: 4px 8px; flex: none; }
   .nfc-jadwal { width: 100%; }
   .nfc-jadwal td { padding: 6px 3px; vertical-align: middle; }
   .nfc-jadwal td:first-child { font-weight: 500; color: var(--hijau-tua); width: 70px; }
   .nfc-jadwal .sd { color: var(--abu); font-size: 12px; text-align: center; width: 40px; }
   .nfc-jadwal input[type=time] { border: 1px solid var(--garis); border-radius: 6px; padding: 5px 6px; width: 100%; font-size: 14px; outline: none; }
   .nfc-jadwal input[type=time]:focus { border-color: var(--hijau); }
   .nfc-kecil { font-size: 12px; color: var(--abu); line-height: 1.5; }
   .nfc-reader { border: 1px solid var(--garis); border-radius: 10px; padding: 10px 12px; margin-bottom: 8px; }
   .nfc-reader.offline { background: #fafafa; }
   .nfc-reader-atas { display: flex; align-items: center; gap: 8px; margin-bottom: 2px; }
   .nfc-reader-atas .lalu { margin-left: auto; }
   .nfc-reader-peringatan { margin-top: 6px; display: flex; gap: 4px; flex-wrap: wrap; }
   .nfc-atur label { display: block; font-size: 13px; color: var(--abu); margin: 0 0 4px; }
   .nfc-broker { display: flex; align-items: flex-start; gap: 8px; margin-bottom: 8px; }
   .nfc-broker .nfc-titik { margin-top: 4px; }
</style>

<div class="content nfc">
   <div class="container-fluid">
      <?= view('admin/_messages'); ?>

      <?php if (!$apiAktif) : ?>
         <div class="alert alert-warning">
            <b>API reader belum aktif.</b> Tambahkan <code>READER_API_KEY = &lt;kunci acak&gt;</code> di file <code>.env</code> server,
            lalu isi kunci yang sama di Pengaturan web reader.
         </div>
      <?php endif; ?>

      <div class="row">
         <!-- ================= Daftarkan kartu ================= -->
         <div class="col-12 col-xl-8">
            <div class="card">
               <div class="card-header card-header-primary">
                  <h4 class="card-title"><b>Daftarkan kartu NFC</b></h4>
                  <p class="card-category">Hubungkan reader lewat USB, tap kartu, pilih siswa, lalu simpan.</p>
               </div>
               <div class="card-body">
                  <form action="<?= base_url('admin/reader-nfc/daftar'); ?>" method="post" id="formDaftar">
                     <?= csrf_field() ?>
                     <input type="hidden" name="uid" id="uidDaftar">
                     <input type="hidden" name="id_siswa" id="idSiswa">

                     <div class="row">
                        <!-- Langkah 1: kartu -->
                        <div class="col-md-5">
                           <span class="nfc-label"><span class="nfc-langkah">1</span>Kartu</span>

                           <div class="nfc-usb">
                              <button type="button" class="btn btn-primary btn-sm" id="btnUsb">
                                 <i class="material-icons nfc-ikon">usb</i> <span id="btnUsbTeks">Hubungkan reader USB</span>
                              </button>
                              <span class="nfc-titik" id="usbTitik"></span>
                              <span class="nfc-usb-status" id="usbStatus">Belum terhubung</span>
                           </div>

                           <div class="nfc-uid-box" id="uidBox">
                              <i class="material-icons">contactless</i>
                              <div class="nfc-uid-nilai nfc-mono" id="uidTampil">— — — —</div>
                              <div class="nfc-uid-ket" id="uidKet">Tempelkan kartu ke reader, atau pilih dari "Kartu baru" di bawah.</div>
                           </div>
                           <div class="nfc-uid-manual">
                              <a href="javascript:void(0)" id="lnkManual">Ketik UID manual</a>
                              <div id="boxManual" style="display:none" class="mt-1">
                                 <input type="text" id="uidManual" class="nfc-mono" placeholder="mis. DE AD BE EF" autocomplete="off">
                              </div>
                           </div>
                           <div id="usbInfo"></div>
                        </div>

                        <!-- Langkah 2: siswa -->
                        <div class="col-md-7 mt-4 mt-md-0">
                           <span class="nfc-label"><span class="nfc-langkah">2</span>Siswa
                              <span style="text-transform:none;letter-spacing:0">(<?= count($siswaTanpaKartu); ?> belum punya kartu)</span></span>
                           <div class="nfc-cari">
                              <i class="material-icons">search</i>
                              <input type="text" id="cariSiswa" placeholder="Cari nama, NIS, atau kelas..." autocomplete="off">
                           </div>
                           <div class="nfc-daftar" id="daftarSiswa">
                              <?php if (empty($siswaTanpaKartu)) : ?>
                                 <div class="nfc-kosong">Semua siswa sudah punya kartu.</div>
                              <?php endif; ?>
                              <?php $grup = null; ?>
                              <?php foreach ($siswaTanpaKartu as $s) : ?>
                                 <?php $k = $namaKelas($s); ?>
                                 <?php if ($k !== $grup) : $grup = $k; ?>
                                    <div class="nfc-grup" data-grup="<?= esc($k); ?>"><?= esc($k); ?></div>
                                 <?php endif; ?>
                                 <div class="nfc-siswa" data-id="<?= $s['id_siswa']; ?>" data-nama="<?= esc($s['nama_siswa']); ?>"
                                    data-kelas="<?= esc($k); ?>" data-cari="<?= esc(strtolower($s['nama_siswa'] . ' ' . $s['nis'] . ' ' . $k)); ?>">
                                    <span><?= esc($s['nama_siswa']); ?></span>
                                    <small><?= esc($s['nis']); ?></small>
                                 </div>
                              <?php endforeach; ?>
                              <div class="nfc-kosong" id="siswaTidakAda" style="display:none">Tidak ada siswa yang cocok.</div>
                           </div>
                        </div>
                     </div>

                     <div class="nfc-ringkas">
                        <span class="nfc-ringkas-teks" id="ringkasan">Tap kartu dan pilih siswa untuk mendaftarkan.</span>
                        <button type="submit" class="btn btn-primary" id="btnDaftar" disabled>
                           <i class="material-icons nfc-ikon">how_to_reg</i> Daftarkan kartu
                        </button>
                     </div>
                  </form>
               </div>
            </div>

            <!-- ================= Kartu baru ================= -->
            <div class="card">
               <div class="card-header card-header-primary">
                  <h4 class="card-title"><b>Kartu baru</b> <small>(<?= count($kartuBaru); ?>)</small></h4>
                  <p class="card-category">Kartu yang di-tap di reader absensi tapi belum terdaftar. Klik "Pilih" untuk mendaftarkannya.</p>
               </div>
               <div class="card-body">
                  <?php if (empty($kartuBaru)) : ?>
                     <div class="nfc-kosong">Belum ada kartu baru.</div>
                  <?php else : ?>
                     <div class="nfc-gulir">
                        <table class="nfc-tabel">
                           <thead><tr><th>UID</th><th>Tap terakhir</th><th>Reader</th><th>Tap</th><th></th></tr></thead>
                           <tbody>
                              <?php foreach ($kartuBaru as $k) : ?>
                                 <tr>
                                    <td class="nfc-mono"><?= esc($k['uid']); ?></td>
                                    <td><?= esc($tglJam($k['terakhir'])); ?></td>
                                    <td><?= esc($k['perangkat']); ?></td>
                                    <td><?= (int) $k['jumlah']; ?>×</td>
                                    <td class="text-right nfc-aksi">
                                       <button type="button" class="btn btn-primary pilih-uid" data-uid="<?= esc($k['uid']); ?>">Pilih</button>
                                       <form action="<?= base_url('admin/reader-nfc/abaikan'); ?>" method="post" class="d-inline">
                                          <?= csrf_field() ?>
                                          <input type="hidden" name="uid" value="<?= esc($k['uid']); ?>">
                                          <button type="submit" class="btn btn-default" title="Hapus dari daftar kartu baru">Abaikan</button>
                                       </form>
                                    </td>
                                 </tr>
                              <?php endforeach; ?>
                           </tbody>
                        </table>
                     </div>
                  <?php endif; ?>
               </div>
            </div>
         </div>

         <!-- ================= Kolom kanan ================= -->
         <div class="col-12 col-xl-4">
            <div class="card">
               <div class="card-header card-header-primary">
                  <h4 class="card-title"><b>Status reader</b></h4>
               </div>
               <div class="card-body">
                  <div class="nfc-stat">
                     <span class="nfc-titik <?= $apiAktif ? 'siap' : 'error'; ?>"></span>
                     <span>API <?= $apiAktif ? '<b>aktif</b>' : '<b class="text-danger">nonaktif</b>'; ?></span>
                  </div>
                  <span class="nfc-label">Alamat API (isi di web reader)</span>
                  <div class="nfc-api">
                     <span class="nfc-mono flex-grow-1" id="apiUrl"><?= esc($apiUrl); ?></span>
                     <button type="button" class="btn btn-sm btn-default" id="btnSalin" title="Salin alamat"><i class="material-icons" style="font-size:16px">content_copy</i></button>
                  </div>

                  <span class="nfc-label mt-4">Reader <span id="ringkasReader" style="text-transform:none;letter-spacing:0"></span></span>
                  <?php if ($mqttWeb) : ?>
                     <div class="nfc-broker nfc-kecil"><span class="nfc-titik proses" id="brokerTitik"></span><span id="brokerStatus">Menghubungi broker MQTT...</span></div>
                  <?php endif; ?>
                  <?php if ($tabelReaderAda) : ?>
                     <div id="daftarReader"><div class="nfc-kecil">Memuat...</div></div>
                  <?php else : ?>
                     <div class="nfc-kecil mb-2">Status online/offline tampil setelah migrasi <code>CreateNfcReader</code> dijalankan.</div>
                  <?php endif; ?>
                  <?php if ($tabelReaderAda) : ?>
                  <?php elseif (empty($perangkat)) : ?>
                     <div class="nfc-kecil">Belum ada reader yang mengirim data.</div>
                  <?php else : ?>
                     <table class="nfc-tabel">
                        <tbody>
                           <?php foreach ($perangkat as $p) : ?>
                              <tr>
                                 <td><b><?= esc($p['perangkat']); ?></b><br><span class="nfc-kecil"><?= (int) $p['jumlah']; ?> tap</span></td>
                                 <td class="text-right nfc-kecil">terakhir<br><?= esc($tglJam($p['terakhir'])); ?></td>
                              </tr>
                           <?php endforeach; ?>
                        </tbody>
                     </table>
                  <?php endif; ?>

                  <span class="nfc-label mt-4">Kecepatan deteksi offline</span>
                  <?php if ($adaPengaturanStatus) : ?>
                     <?php $lib = \App\Libraries\ReaderNfc::class; ?>
                     <form action="<?= base_url('admin/reader-nfc/pengaturan-status'); ?>" method="post" class="nfc-atur">
                        <?= csrf_field() ?>
                        <label for="aturMetode">Metode status reader</label>
                        <select name="metode" id="aturMetode" class="nfc-filter">
                           <option value="http" <?= $metodeStatus === 'http' ? 'selected' : ''; ?>>HTTP (langsung ke server MAD1NAH)</option>
                           <option value="mqtt" <?= $metodeStatus === 'mqtt' ? 'selected' : ''; ?> <?= $mqttSiap ? '' : 'disabled'; ?>>
                              MQTT (lewat broker, lebih cepat)<?= $mqttSiap ? '' : ' - isi MQTT_* di .env'; ?></option>
                        </select>
                        <label for="aturInterval" id="labelInterval">Interval HTTP</label>
                        <select name="interval" id="aturInterval" class="nfc-filter">
                           <?php foreach ($lib::PILIHAN_INTERVAL as $i) : ?>
                              <option value="<?= $i; ?>" <?= $i === $intervalStatus ? 'selected' : ''; ?>>
                                 Lapor tiap <?= $i; ?> dtk, offline setelah <?= $lib::batasOnline($i); ?> dtk</option>
                           <?php endforeach; ?>
                        </select>
                        <div id="barisKeepalive">
                           <label for="aturKeepalive">Keep-alive MQTT</label>
                           <select name="keepalive" id="aturKeepalive" class="nfc-filter">
                              <?php foreach ($lib::PILIHAN_KEEPALIVE as $k) : ?>
                                 <option value="<?= $k; ?>" <?= $k === $keepalive ? 'selected' : ''; ?>>
                                    <?= $k; ?> dtk, offline diketahui sekitar <?= (int) ceil($k * 1.5); ?> dtk</option>
                              <?php endforeach; ?>
                           </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block btn-sm">Simpan</button>
                     </form>
                     <div class="nfc-kecil mt-2">Diterapkan reader pada laporan berikutnya, tanpa menyentuh perangkat.
                        HTTP butuh firmware V1.16+, MQTT butuh V1.17+ dengan data broker terisi di web reader.
                        Firmware lama tetap lapor tiap 60 detik. Data absensi selalu dikirim lewat HTTPS.</div>
                  <?php else : ?>
                     <div class="nfc-kecil">Pengaturan tampil setelah migrasi <code>AddMqttStatusReader</code> dijalankan.</div>
                  <?php endif; ?>
               </div>
            </div>

            <div class="card">
               <div class="card-header card-header-primary">
                  <h4 class="card-title"><b>Jadwal mode otomatis</b></h4>
                  <p class="card-category">Menentukan jenis absen dari jam tap.</p>
               </div>
               <div class="card-body">
                  <form action="<?= base_url('admin/reader-nfc/jadwal'); ?>" method="post">
                     <?= csrf_field() ?>
                     <table class="nfc-jadwal">
                        <tr>
                           <td>Masuk</td>
                           <td class="sd">s/d</td>
                           <td colspan="3"><input type="time" name="nfc_akhir_masuk" value="<?= $jam($jadwal['akhir_masuk']); ?>" required></td>
                        </tr>
                        <tr>
                           <td>Dzuhur</td>
                           <td colspan="2"><input type="time" name="nfc_mulai_dzuhur" value="<?= $jam($jadwal['mulai_dzuhur']); ?>" required></td>
                           <td class="sd">s/d</td>
                           <td><input type="time" name="nfc_akhir_dzuhur" value="<?= $jam($jadwal['akhir_dzuhur']); ?>" required></td>
                        </tr>
                        <tr>
                           <td>Ashar</td>
                           <td colspan="2"><input type="time" name="nfc_mulai_ashar" value="<?= $jam($jadwal['mulai_ashar']); ?>" required></td>
                           <td class="sd">s/d</td>
                           <td><input type="time" name="nfc_akhir_ashar" value="<?= $jam($jadwal['akhir_ashar']); ?>" required></td>
                        </tr>
                        <tr>
                           <td>Pulang</td>
                           <td class="sd">mulai</td>
                           <td colspan="3"><input type="time" name="nfc_mulai_pulang" value="<?= $jam($jadwal['mulai_pulang']); ?>" required></td>
                        </tr>
                     </table>
                     <p class="nfc-kecil mt-3 mb-3">Diperiksa berurutan: masuk → dzuhur → ashar → pulang. Tap di luar semua rentang dicatat
                        "Di luar jadwal" tanpa mengubah absensi. Batas terlambat tetap mengikuti menu Pengaturan.</p>
                     <button type="submit" class="btn btn-primary btn-block">Simpan jadwal</button>
                  </form>
               </div>
            </div>
         </div>
      </div>

      <div class="row">
         <!-- ================= Kartu terdaftar ================= -->
         <div class="col-12 col-xl-6">
            <div class="card">
               <div class="card-header card-header-primary">
                  <h4 class="card-title"><b>Kartu terdaftar</b> <small>(<?= count($siswaBerkartu); ?>)</small></h4>
               </div>
               <div class="card-body">
                  <input type="text" id="cariBerkartu" class="nfc-filter" placeholder="Cari nama, kelas, atau UID..." autocomplete="off">
                  <div class="nfc-gulir">
                     <table class="nfc-tabel" id="tabelBerkartu">
                        <thead><tr><th>Nama</th><th>Kelas</th><th>UID</th><th></th></tr></thead>
                        <tbody>
                           <?php if (empty($siswaBerkartu)) : ?>
                              <tr><td colspan="4" class="nfc-kosong">Belum ada kartu yang terdaftar.</td></tr>
                           <?php endif; ?>
                           <?php foreach ($siswaBerkartu as $s) : ?>
                              <tr data-cari="<?= esc(strtolower($s['nama_siswa'] . ' ' . $namaKelas($s) . ' ' . $s['uid_kartu'])); ?>">
                                 <td><?= esc($s['nama_siswa']); ?></td>
                                 <td><?= esc($namaKelas($s)); ?></td>
                                 <td class="nfc-mono" style="font-size:13px"><?= esc($s['uid_kartu']); ?></td>
                                 <td class="text-right nfc-aksi">
                                    <form action="<?= base_url('admin/reader-nfc/lepas'); ?>" method="post" onsubmit="return confirm(this.dataset.konfirmasi)"
                                       data-konfirmasi="<?= esc('Lepas kartu dari ' . $s['nama_siswa'] . '? Kartu ini tidak bisa dipakai absen lagi.'); ?>">
                                       <?= csrf_field() ?>
                                       <input type="hidden" name="id_siswa" value="<?= $s['id_siswa']; ?>">
                                       <button type="submit" class="btn btn-default">Lepas</button>
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

         <!-- ================= Log tap ================= -->
         <div class="col-12 col-xl-6">
            <div class="card">
               <div class="card-header card-header-primary">
                  <h4 class="card-title"><b>Log tap terbaru</b></h4>
                  <p class="card-category">100 tap terakhir dari reader</p>
               </div>
               <div class="card-body">
                  <div class="nfc-gulir">
                     <table class="nfc-tabel">
                        <thead><tr><th>Waktu</th><th>Siswa / UID</th><th>Jenis</th><th>Hasil</th></tr></thead>
                        <tbody>
                           <?php if (empty($log)) : ?>
                              <tr><td colspan="4" class="nfc-kosong">Belum ada data.</td></tr>
                           <?php endif; ?>
                           <?php foreach ($log as $l) : ?>
                              <?php [$teks, $warna] = $labelHasil[$l['hasil']] ?? [$l['hasil'], 'info']; ?>
                              <tr>
                                 <td style="white-space:nowrap"><?= esc($tglJam($l['waktu'])); ?></td>
                                 <td>
                                    <?= $l['nama_siswa'] ? esc($l['nama_siswa']) : '<span class="nfc-mono" style="font-size:13px">' . esc($l['uid']) . '</span>'; ?>
                                    <div class="nfc-kecil"><?= esc($l['perangkat']); ?><?= $l['mode'] !== 'otomatis' ? ' · mode ' . esc($l['mode']) : ''; ?></div>
                                 </td>
                                 <td><?= esc($labelJenis[$l['jenis']] ?? '-'); ?></td>
                                 <td><span class="nfc-badge <?= $warna; ?>"><?= esc($teks); ?></span></td>
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
</div>

<script>
   const kartuTerdaftar = <?= json_encode(array_column($siswaBerkartu, 'nama_siswa', 'uid_kartu'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
   const escHtml = (t) => $('<div>').text(t).html();
   const POLA_UID = /^[0-9A-F]{2}( [0-9A-F]{2}){3,9}$/;

   /* ---------- Form daftar: kartu + siswa ---------- */
   const pilihan = { uid: '', id: '', nama: '', kelas: '' };

   function normalisasiUid(teks) {
      const hex = String(teks).toUpperCase().replace(/[^0-9A-F]/g, '');
      return (hex.match(/.{1,2}/g) || []).join(' ');
   }

   function setUid(uid) {
      uid = normalisasiUid(uid);
      const valid = POLA_UID.test(uid);
      pilihan.uid = valid ? uid : '';
      $('#uidDaftar').val(pilihan.uid);
      const box = $('#uidBox').removeClass('ada dipakai');
      if (!valid) {
         $('#uidTampil').text('— — — —');
         $('#uidKet').removeClass('peringatan').text('Tempelkan kartu ke reader, atau pilih dari "Kartu baru" di bawah.');
      } else if (kartuTerdaftar[uid]) {
         box.addClass('dipakai');
         $('#uidTampil').text(uid);
         $('#uidKet').addClass('peringatan').html('Sudah terdaftar ke <b>' + escHtml(kartuTerdaftar[uid]) + '</b>. Lepas dulu jika ingin dipindahkan.');
      } else {
         box.addClass('ada');
         $('#uidTampil').text(uid);
         $('#uidKet').removeClass('peringatan').text('Kartu siap didaftarkan.');
         if (!pilihan.id) $('#cariSiswa').focus();
      }
      perbaruiRingkasan();
   }

   function pilihSiswa(el) {
      $('.nfc-siswa').removeClass('dipilih');
      const $el = $(el).addClass('dipilih');
      pilihan.id = String($el.data('id'));
      pilihan.nama = $el.data('nama');
      pilihan.kelas = $el.data('kelas');
      $('#idSiswa').val(pilihan.id);
      perbaruiRingkasan();
   }

   function perbaruiRingkasan() {
      const siap = pilihan.uid && pilihan.id && !kartuTerdaftar[pilihan.uid];
      $('#btnDaftar').prop('disabled', !siap);
      let teks;
      if (siap) teks = 'Kartu <b class="nfc-mono">' + pilihan.uid + '</b> → <b>' + escHtml(pilihan.nama) + '</b> (' + escHtml(pilihan.kelas) + ')';
      else if (!pilihan.uid && !pilihan.id) teks = 'Tap kartu dan pilih siswa untuk mendaftarkan.';
      else if (!pilihan.uid) teks = 'Siswa: <b>' + escHtml(pilihan.nama) + '</b>. Sekarang tap kartunya.';
      else if (kartuTerdaftar[pilihan.uid]) teks = 'Kartu ini sudah dipakai siswa lain. Tap kartu lain.';
      else teks = 'Kartu <b class="nfc-mono">' + pilihan.uid + '</b> terbaca. Sekarang pilih siswanya.';
      $('#ringkasan').html(teks);
   }

   /* ---------- Reader USB (Web Serial, firmware V1.4+) ----------
    * Perintah "daftar" membuat tap tidak dicatat sebagai absen dan UID dikirim
    * sebagai baris "UID:DE AD BE EF". Izin port diingat browser, jadi setelah
    * form dikirim (halaman dimuat ulang) reader tersambung kembali otomatis.
    */
   const usb = { port: null, reader: null, writer: null, pipa: null, aktif: false, siap: false, terakhir: [] };

   function usbStatus(teks, keadaan) {
      $('#usbStatus').text(teks);
      $('#usbTitik').attr('class', 'nfc-titik' + (keadaan ? ' ' + keadaan : ''));
   }

   function usbCatatan(html, jenis) {
      $('#usbInfo').html(html ? '<div class="alert alert-' + jenis + ' nfc-catatan mb-0">' + html + '</div>' : '');
   }

   async function usbKirim(perintah) {
      if (usb.writer) await usb.writer.write(new TextEncoder().encode(perintah + '\n'));
   }

   async function usbHubungkan(port) {
      usb.port = port;
      await port.open({ baudRate: 115200 });
      // lepas DTR/RTS supaya ESP32 tidak tertahan di mode reset
      try { await port.setSignals({ dataTerminalReady: false, requestToSend: false }); } catch (e) {}
      usb.writer = port.writable.getWriter();
      usb.aktif = true;
      usb.siap = false;
      usb.terakhir = [];
      sessionStorage.setItem('nfcUsb', '1');
      $('#btnUsbTeks').text('Putuskan');
      usbStatus('Menyiapkan reader...', 'proses');
      setTimeout(() => usbKirim('daftar'), 1500);
      setTimeout(usbCekBalasan, 6000);
      usbBaca();
   }

   async function usbBaca() {
      const dekoder = new TextDecoderStream();
      usb.pipa = usb.port.readable.pipeTo(dekoder.writable).catch(() => {});
      usb.reader = dekoder.readable.getReader();
      let sisa = '';
      try {
         while (usb.aktif) {
            const { value, done } = await usb.reader.read();
            if (done) break;
            sisa += value;
            let i;
            while ((i = sisa.indexOf('\n')) >= 0) {
               usbBaris(sisa.slice(0, i).trim());
               sisa = sisa.slice(i + 1);
            }
         }
      } catch (e) {
         if (usb.aktif) usbStatus('Koneksi terputus, cabut-colok kabel lalu hubungkan lagi', 'error');
      }
   }

   // Reader tidak membalas "MODE:DAFTAR": tampilkan data terakhir supaya penyebabnya terlihat
   function usbCekBalasan() {
      if (!usb.aktif || usb.siap) return;
      usbKirim('daftar');
      const diterima = usb.terakhir.length
         ? '<pre class="mb-1 mt-1" style="white-space:pre-wrap;font-size:11px">' + escHtml(usb.terakhir.join('\n')) + '</pre>'
         : ' Tidak ada data dari port ini. ';
      usbCatatan('<b>Reader belum merespons.</b>' + diterima +
         'Pastikan firmware reader minimal V1.4, port yang dipilih adalah ESP32 (usbserial / CP210x / CH340), dan Serial Monitor Arduino tertutup.', 'warning');
      usbStatus('Belum ada balasan, mencoba lagi...', 'proses');
      setTimeout(usbCekBalasan, 6000);
   }

   function usbBaris(baris) {
      if (baris) {
         usb.terakhir.push(baris);
         if (usb.terakhir.length > 8) usb.terakhir.shift();
      }
      if (baris.startsWith('STATUS:PN532_ERROR')) {
         usb.siap = true; // reader menjawab, masalahnya di modul PN532
         usbCatatan('<b>Modul PN532 tidak terdeteksi.</b> Periksa kabel & saklar DIP PN532, lalu cabut-colok daya. Detail ada di Serial Monitor.', 'danger');
         usbStatus('PN532 bermasalah', 'error');
      } else if (baris.startsWith('STATUS:SIAP')) {
         usbCatatan('');
         usbKirim('daftar'); // reader baru menyala / restart
      } else if (baris.startsWith('MODE:DAFTAR')) {
         usb.siap = true;
         usbCatatan('');
         usbStatus('Siap, tempelkan kartu', 'siap');
      } else if (baris.startsWith('UID:')) {
         setUid(baris.slice(4).trim());
      }
   }

   async function usbPutuskan() {
      usb.aktif = false;
      sessionStorage.removeItem('nfcUsb');
      try { await usbKirim('selesai'); } catch (e) {}
      try { await usb.reader.cancel(); } catch (e) {}
      try { await usb.pipa; } catch (e) {}
      try { usb.writer.releaseLock(); } catch (e) {}
      try { await usb.port.close(); } catch (e) {}
      usb.port = usb.reader = usb.writer = usb.pipa = null;
      $('#btnUsbTeks').text('Hubungkan reader USB');
      usbStatus('Belum terhubung');
      usbCatatan('');
   }

   $(function() {
      /* pilih siswa */
      $('#daftarSiswa').on('click', '.nfc-siswa', function() { pilihSiswa(this); });

      $('#cariSiswa').on('input', function() {
         const q = this.value.toLowerCase().trim();
         const terlihatPerKelas = {};
         let ada = 0;
         $('.nfc-siswa').each(function() {
            const cocok = q === '' || String($(this).data('cari')).includes(q);
            this.style.display = cocok ? '' : 'none';
            if (cocok) {
               ada++;
               terlihatPerKelas[$(this).data('kelas')] = true;
            }
         });
         $('.nfc-grup').each(function() {
            this.style.display = terlihatPerKelas[$(this).data('grup')] ? '' : 'none';
         });
         $('#siswaTidakAda').toggle(ada === 0 && $('.nfc-siswa').length > 0);
      }).on('keydown', function(e) {
         if (e.key === 'Enter') { // Enter = pilih hasil pertama
            e.preventDefault();
            const pertama = $('.nfc-siswa').filter(function() { return this.style.display !== 'none'; }).first();
            if (pertama.length) pilihSiswa(pertama[0]);
         }
      });

      /* kartu dari tabel "Kartu baru" */
      $('.pilih-uid').on('click', function() {
         setUid($(this).data('uid'));
         $('html, body').animate({ scrollTop: $('#formDaftar').offset().top - 140 }, 200);
      });

      /* UID manual */
      $('#lnkManual').on('click', function() { $('#boxManual').toggle(); $('#uidManual').focus(); });
      $('#uidManual').on('input', function() { setUid(this.value); });

      /* salin alamat API */
      $('#btnSalin').on('click', function() {
         if (navigator.clipboard) navigator.clipboard.writeText($('#apiUrl').text().trim());
         const ikon = $(this).find('i').text('check');
         setTimeout(() => ikon.text('content_copy'), 1500);
      });

      /* filter kartu terdaftar */
      $('#cariBerkartu').on('input', function() {
         const q = this.value.toLowerCase().trim();
         $('#tabelBerkartu tbody tr[data-cari]').each(function() {
            this.style.display = q === '' || String($(this).data('cari')).includes(q) ? '' : 'none';
         });
      });

      /* reader USB */
      if (!('serial' in navigator)) {
         $('#btnUsb').prop('disabled', true);
         usbStatus('Reader USB butuh Chrome/Edge di laptop/PC', 'error');
      } else {
         $('#btnUsb').on('click', async function() {
            if (usb.aktif) return usbPutuskan();
            try {
               await usbHubungkan(await navigator.serial.requestPort());
            } catch (e) {
               if (e.name !== 'NotFoundError') {
                  usbStatus('Gagal membuka port', 'error');
                  usbCatatan('Gagal membuka port: ' + escHtml(e.message) + '. Tutup Serial Monitor Arduino jika sedang terbuka.', 'danger');
               }
            }
         });
         // sambung ulang otomatis setelah halaman dimuat ulang (mis. sesudah Daftarkan)
         if (sessionStorage.getItem('nfcUsb') === '1') {
            navigator.serial.getPorts().then((ports) => {
               if (ports.length) usbHubungkan(ports[0]).catch(() => sessionStorage.removeItem('nfcUsb'));
            });
         }
      }

      perbaruiRingkasan();

      /* pilihan keep-alive hanya relevan untuk MQTT */
      $('#aturMetode').on('change', function() {
         const mqttDipilih = this.value === 'mqtt';
         $('#barisKeepalive').toggle(mqttDipilih);
         $('#labelInterval').text(mqttDipilih ? 'Interval HTTP cadangan (bila broker putus)' : 'Interval HTTP');
      }).trigger('change');

      /* pustaka MQTT hanya dimuat bila mode MQTT dipakai */
      if (MQTT_CFG) {
         const skrip = document.createElement('script');
         skrip.src = '<?= base_url('assets/js/plugins/mqtt/mqtt.min.js'); ?>';
         skrip.onload = sambungBroker;
         skrip.onerror = () => statusBroker('Pustaka MQTT gagal dimuat, status memakai server', 'error');
         document.body.appendChild(skrip);
      }

      /* daftar reader + status online, diperbarui tiap 5 detik */
      if (document.getElementById('daftarReader')) {
         tampilkanReader(<?= json_encode($readers, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>);
         setInterval(muatReader, 5000);
         setInterval(() => tampilkanReader(dataReader.list), 1000); // perbarui "x detik lalu"
      }
   });

   /* ---------- Status reader ---------- */
   const MODE_LABEL = { otomatis: 'Otomatis', masuk: 'Masuk', pulang: 'Pulang', dzuhur: 'Dzuhur', ashar: 'Ashar', daftar: 'Daftar kartu' };
   const dataReader = { list: [], dimuat: Date.now() };

   function teksLalu(detik) {
      if (detik < 60) return detik + ' detik lalu';
      if (detik < 3600) return Math.floor(detik / 60) + ' menit lalu';
      if (detik < 86400) return Math.floor(detik / 3600) + ' jam lalu';
      return Math.floor(detik / 86400) + ' hari lalu';
   }

   function teksSinyal(rssi) {
      if (rssi === null || rssi === undefined || rssi === '' || +rssi === 0) return '';
      rssi = +rssi;
      return 'sinyal ' + (rssi >= -60 ? 'kuat' : rssi >= -72 ? 'sedang' : 'lemah') + ' (' + rssi + ' dBm)';
   }

   const RTC_LABEL = { ok: 'Normal', belum_set: 'Belum diset', tidak_ada: 'Tidak terdeteksi', nonaktif: 'Nonaktif' };
   const SUMBER_JAM = { ntp: 'Internet (NTP)', rtc: 'RTC', manual: 'Diset manual', belum: 'Belum diset' };
   const BATAS_SELISIH_RTC = 60; // detik

   function teksDurasi(detik) {
      if (detik < 60) return detik + ' detik';
      if (detik < 3600) return Math.floor(detik / 60) + ' menit';
      if (detik < 86400) return Math.floor(detik / 3600) + ' jam';
      return Math.floor(detik / 86400) + ' hari';
   }

   // "2026-11-28 06:45:12" -> "28/11/2026 06:45:12"
   function teksWaktu(w) {
      const m = String(w).match(/^(\d{4})-(\d{2})-(\d{2}) (.+)$/);
      return m ? m[3] + '/' + m[2] + '/' + m[1] + ' ' + m[4] : w;
   }

   function barisRtc(r) {
      if (!r.laporan) return '';
      if (r.rtc === undefined) return 'RTC: tampil setelah migrasi AddRtcNfcReader dijalankan';
      if (!r.rtc) return 'RTC: firmware belum mengirim info (perbarui ke V1.15+)';
      const bagian = ['RTC: <b>' + escHtml(RTC_LABEL[r.rtc] || r.rtc) + '</b>'];
      if (r.rtc_waktu) bagian.push(escHtml(teksWaktu(r.rtc_waktu)));
      if (r.rtc_selisih !== null) {
         const s = +r.rtc_selisih;
         bagian.push(Math.abs(s) <= 2 ? 'cocok dengan server'
            : (s > 0 ? 'lebih cepat ' : 'terlambat ') + teksDurasi(Math.abs(s)));
      }
      return bagian.join(' · ') + (r.sumber_jam ? '<br>Sumber jam: ' + escHtml(SUMBER_JAM[r.sumber_jam] || r.sumber_jam) : '');
   }

   /* ---------- MQTT: status dari broker, server tetap jadi cadangan ---------- */
   const MQTT_CFG = <?= json_encode($mqttWeb, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
   const MQTT_SEGAR = 90; // detik; reader mempublikasikan status tiap 30 detik
   const KUNCI_MQTT = ['versi', 'mode', 'ip', 'rssi', 'antrian', 'flash', 'siswa', 'pn532', 'jam_valid', 'rtc'];
   const mqttData = {}; // nama reader -> { online, isi, waktu }
   let mqttTersambung = false;

   // "kunci=nilai" per baris -> objek
   function uraiPesan(teks) {
      const isi = {};
      String(teks).split('\n').forEach((baris) => {
         const i = baris.indexOf('=');
         if (i > 0) isi[baris.slice(0, i)] = baris.slice(i + 1).trim();
      });
      return isi;
   }

   function terimaPesan(topik, teks, waktu) {
      const nama = String(topik).split('/').slice(-2)[0];
      if (!teks) delete mqttData[nama]; // dikosongkan reader saat beralih ke HTTP
      else {
         const isi = uraiPesan(teks);
         mqttData[nama] = { online: isi.online === '1', isi: isi, waktu: waktu };
      }
   }

   // Gabungkan data server (r) dengan pesan broker untuk reader tersebut
   function statusGabungan(r, lalu) {
      const server = { r: r, lalu: lalu, online: lalu <= (+r.batas), sumber: 'server' }; // batas mengikuti interval reader
      const m = mqttTersambung ? mqttData[r.perangkat] : null;
      if (!m || r.metode !== 'mqtt') return server;
      // reader sendiri melapor putus dari broker tetapi HTTP-nya jalan
      if (+r.mqtt_ok === 0 && server.online) return server;
      if (!m.online) return { r: r, lalu: lalu, online: false, sumber: 'broker' };
      const umur = Math.max(0, Math.floor((Date.now() - m.waktu) / 1000));
      if (umur > MQTT_SEGAR) return server;
      const gabung = Object.assign({}, r);
      KUNCI_MQTT.forEach((k) => { if (m.isi[k] !== undefined) gabung[k] = m.isi[k]; });
      return { r: gabung, lalu: umur, online: true, sumber: 'mqtt' };
   }

   function statusBroker(teks, keadaan) {
      $('#brokerStatus').text(teks);
      $('#brokerTitik').attr('class', 'nfc-titik ' + keadaan);
   }

   function sambungBroker() {
      const klien = mqtt.connect(MQTT_CFG.url, {
         username: MQTT_CFG.username, password: MQTT_CFG.password,
         clientId: 'madinah-web-' + Math.random().toString(16).slice(2, 10),
         keepalive: 30, reconnectPeriod: 5000, clean: true,
      });
      klien.on('connect', () => {
         klien.subscribe(MQTT_CFG.topik, { qos: 1 }, (err, hasil) => {
            if (err || (hasil && hasil.some((h) => h.qos === 128))) {
               statusBroker('Broker menolak subscribe, periksa hak akses user web. Status memakai server.', 'error');
               return;
            }
            mqttTersambung = true;
            statusBroker('Broker MQTT tersambung', 'siap');
         });
      });
      klien.on('close', () => {
         mqttTersambung = false;
         statusBroker('Broker MQTT terputus, status memakai server. Mencoba lagi...', 'error');
      });
      klien.on('error', (e) => statusBroker('Broker MQTT: ' + e.message, 'error'));
      klien.on('message', (topik, isi) => {
         terimaPesan(topik, isi.toString(), Date.now());
         tampilkanReader(dataReader.list);
      });
   }

   function tampilkanReader(list) {
      if (list !== dataReader.list) { dataReader.list = list; dataReader.dimuat = Date.now(); }
      const tambahan = Math.floor((Date.now() - dataReader.dimuat) / 1000);
      const el = $('#daftarReader');
      if (!list.length) {
         el.html('<div class="nfc-kecil">Belum ada reader yang terhubung ke server.</div>');
         $('#ringkasReader').text('');
         return;
      }
      let online = 0;
      el.html(list.map((baris) => {
         const s = statusGabungan(baris, (+baris.lalu) + tambahan);
         const r = s.r, on = s.online;
         if (on) online++;
         const baris1 = [r.versi ? 'V' + escHtml(r.versi) : '', r.mode ? 'mode ' + escHtml(MODE_LABEL[r.mode] || r.mode) : '',
            r.ip ? escHtml(r.ip) : '', teksSinyal(r.rssi), +r.interval_status > 0 ? 'lapor ' + (+r.interval_status) + ' dtk' : ''].filter(Boolean).join(' · ');
         const baris2 = r.laporan
            ? ['antrian ' + (+r.antrian), 'flash ' + (+r.flash) + '%', (+r.siswa) + ' siswa'].join(' · ')
            : 'Firmware belum mengirim laporan status (perbarui ke V1.14+)';
         const catatan = [];
         if (r.laporan && r.metode === 'mqtt' && +r.mqtt_ok === 0 && s.sumber === 'server') catatan.push('<span class="nfc-badge warn">MQTT belum tersambung, memakai HTTP</span>');
         if (r.laporan && +r.pn532 === 0) catatan.push('<span class="nfc-badge bad">PN532 tidak terdeteksi</span>');
         if (r.laporan && +r.jam_valid === 0) catatan.push('<span class="nfc-badge warn">Jam belum diset</span>');
         if (r.laporan && r.rtc === 'tidak_ada') catatan.push('<span class="nfc-badge bad">RTC tidak terdeteksi</span>');
         if (r.laporan && r.rtc === 'belum_set') catatan.push('<span class="nfc-badge warn">RTC belum diset, cek baterai</span>');
         if (r.laporan && r.rtc === 'ok' && Math.abs(+r.rtc_selisih) > BATAS_SELISIH_RTC) catatan.push('<span class="nfc-badge warn">Jam RTC meleset</span>');
         if (r.laporan && +r.antrian > 0) catatan.push('<span class="nfc-badge warn">' + (+r.antrian) + ' data belum terkirim</span>');
         if (r.laporan && +r.flash >= 80) catatan.push('<span class="nfc-badge bad">Flash ' + (+r.flash) + '%</span>');
         return '<div class="nfc-reader' + (on ? '' : ' offline') + '">' +
            '<div class="nfc-reader-atas"><span class="nfc-titik ' + (on ? 'siap' : 'error') + '"></span><b>' + escHtml(r.perangkat) + '</b>' +
            '<span class="nfc-badge ' + (on ? 'ok' : 'bad') + '">' + (on ? 'Online' : 'Offline') + '</span>' +
            '<span class="nfc-kecil lalu">' + (s.sumber === 'broker' ? 'terputus dari broker' : (s.sumber === 'mqtt' ? 'MQTT · ' : '') + teksLalu(s.lalu)) + '</span></div>' +
            (baris1 ? '<div class="nfc-kecil">' + baris1 + '</div>' : '') +
            '<div class="nfc-kecil">' + baris2 + '</div>' +
            (r.laporan ? '<div class="nfc-kecil">' + barisRtc(r) + '</div>' : '') +
            (catatan.length ? '<div class="nfc-reader-peringatan">' + catatan.join('') + '</div>' : '') +
            '</div>';
      }).join(''));
      $('#ringkasReader').text('(' + online + ' dari ' + list.length + ' online)');
   }

   function muatReader() {
      fetch('<?= base_url('admin/reader-nfc/status'); ?>', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
         .then((r) => r.ok ? r.json() : Promise.reject())
         .then((d) => tampilkanReader(d.readers || []))
         .catch(() => {});
   }
</script>
<?= $this->endSection() ?>
