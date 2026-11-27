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
   .nfc-usb { border: 1px dashed #c8d3cc; border-radius: 8px; padding: 10px 12px; }
   .nfc-usb .material-icons { font-size: 18px; vertical-align: middle; }
   #uidDaftar.nfc-baru { animation: nfcKedip 1s ease-out; }
   @keyframes nfcKedip { from { background: #fff3c4; } to { background: transparent; } }
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
                  <div class="nfc-usb mb-3">
                     <button type="button" class="btn btn-sm btn-primary" id="btnUsb">
                        <i class="material-icons">usb</i> <span id="btnUsbTeks">Hubungkan reader USB</span>
                     </button>
                     <span id="usbStatus" class="ml-2 text-muted">Daftar kartu langsung: colokkan reader ke laptop ini lewat USB, lalu tap kartu.</span>
                     <div id="usbInfo" class="mt-2"></div>
                  </div>

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
   /*
    * Daftar kartu lewat reader USB (Web Serial, Chrome/Edge desktop).
    * Firmware reader V1.4+: perintah "daftar" membuat tap tidak dicatat
    * sebagai absen dan UID dikirim sebagai baris "UID:DE AD BE EF".
    * Izin port diingat browser, jadi setelah form dikirim (halaman
    * dimuat ulang) reader tersambung kembali otomatis.
    */
   const kartuTerdaftar = <?= json_encode(array_column($siswaBerkartu, 'nama_siswa', 'uid_kartu'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
   const usb = { port: null, reader: null, writer: null, pipa: null, aktif: false };
   const escHtml = (t) => $('<div>').text(t).html();

   function usbStatus(teks, warna) {
      $('#usbStatus').attr('class', 'ml-2 ' + (warna ? 'text-' + warna : 'text-muted')).text(teks);
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
      sessionStorage.setItem('nfcUsb', '1');
      $('#btnUsbTeks').text('Putuskan reader USB');
      usbStatus('Terhubung, menyiapkan mode daftar...', 'info');
      setTimeout(() => usbKirim('daftar'), 1500);
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
         if (usb.aktif) usbStatus('Koneksi USB terputus. Cabut-colok kabel lalu hubungkan lagi.', 'danger');
      }
   }

   function usbBaris(baris) {
      if (baris.startsWith('STATUS:SIAP')) {
         usbKirim('daftar'); // reader baru menyala / restart
      } else if (baris.startsWith('MODE:DAFTAR')) {
         usbStatus('Siap: tempelkan kartu ke reader', 'success');
      } else if (baris.startsWith('UID:')) {
         usbUid(baris.slice(4).trim().toUpperCase());
      }
   }

   function usbUid(uid) {
      if (!/^[0-9A-F]{2}( [0-9A-F]{2}){3,9}$/.test(uid)) return;
      $('#uidDaftar').val(uid).removeClass('nfc-baru');
      void document.getElementById('uidDaftar').offsetWidth;
      $('#uidDaftar').addClass('nfc-baru');

      if (kartuTerdaftar[uid]) {
         $('#usbInfo').html('<div class="alert alert-warning mb-0">Kartu <b class="nfc-uid">' + uid +
            '</b> sudah terdaftar ke <b>' + escHtml(kartuTerdaftar[uid]) + '</b>. Lepas dulu kartunya jika ingin dipindahkan.</div>');
      } else {
         $('#usbInfo').html('<div class="alert alert-success mb-0">Kartu <b class="nfc-uid">' + uid +
            '</b> terbaca. Cari & pilih siswa, lalu klik <b>Daftarkan kartu</b>.</div>');
         $('#cariSiswa').focus().select();
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
      usbStatus('Reader USB diputus. Reader kembali ke mode absen biasa.');
   }

   $(function() {
      if (!('serial' in navigator)) {
         $('#btnUsb').prop('disabled', true);
         usbStatus('Browser ini tidak mendukung reader USB. Gunakan Chrome atau Edge di laptop/PC.', 'warning');
      } else {
         $('#btnUsb').on('click', async function() {
            if (usb.aktif) return usbPutuskan();
            try {
               await usbHubungkan(await navigator.serial.requestPort());
            } catch (e) {
               if (e.name !== 'NotFoundError') usbStatus('Gagal membuka port: ' + e.message + ' (tutup Serial Monitor Arduino jika sedang terbuka)', 'danger');
            }
         });
         // sambung ulang otomatis setelah halaman dimuat ulang (mis. sesudah Daftarkan)
         if (sessionStorage.getItem('nfcUsb') === '1') {
            navigator.serial.getPorts().then((ports) => {
               if (ports.length) usbHubungkan(ports[0]).catch(() => sessionStorage.removeItem('nfcUsb'));
            });
         }
      }

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
