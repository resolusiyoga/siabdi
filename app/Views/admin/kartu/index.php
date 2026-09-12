<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<link href="<?= assetUrl('assets/css/kartu.css'); ?>" rel="stylesheet" />
<style>
   /* ---- Editor tata letak kartu ---- */
   .editor {
      display: flex;
      flex-wrap: wrap;
      gap: 24px;
   }

   .editor__kanvas-area {
      flex: 0 1 auto;
      min-width: 0;
      max-width: 100%;
   }

   .editor__panel {
      flex: 1 1 320px;
      min-width: 300px;
   }

   /* kanvas diperbesar lewat scale agar koordinat tetap dalam mm */
   .kanvas-bingkai {
      background: #f3f3f3;
      padding: 16px;
      border-radius: 6px;
      display: flex;
      justify-content: center;
      max-width: 100%;
      overflow: auto;
   }

   /* Ruang penampung seukuran kartu setelah diperbesar, supaya kartu yang
      di-scale tetap berada di dalam kotak dan tidak menimpa elemen lain. */
   .kanvas-ruang {
      position: relative;
      flex: 0 0 auto;
   }

   .kanvas-skala {
      position: absolute;
      top: 0;
      left: 0;
      transform-origin: top left;
   }

   .kanvas {
      box-shadow: 0 2px 12px rgba(0, 0, 0, .25);
   }

   .kanvas .kartu__el {
      cursor: move;
   }

   .kanvas .kartu__el:hover {
      outline: 0.3mm solid rgba(28, 101, 90, .6);
   }

   .kanvas .kartu__el.terpilih {
      outline: 0.4mm solid #1c655a;
   }

   .kartu__pegangan {
      position: absolute;
      right: 0;
      bottom: 0;
      width: 2.5mm;
      height: 2.5mm;
      background: #1c655a;
      cursor: nwse-resize;
   }

   .daftar-elemen .list-group-item {
      padding: .5rem .75rem;
      cursor: pointer;
   }

   .daftar-elemen .list-group-item.aktif {
      background: #e8f3f0;
      font-weight: 600;
   }

   .prop-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 8px 12px;
   }

   .prop-grid label {
      font-size: 12px;
      margin-bottom: 2px;
      color: #555;
   }

   .prop-grid .form-control,
   .prop-grid .custom-select {
      margin-top: 0;
   }

   .thumb-template {
      max-width: 90px;
      border: 1px solid #ddd;
      background: #fff;
   }
</style>

<div class="content">
   <div class="container-fluid">
      <?php if (session()->getFlashdata('msg')) : ?>
         <div class="alert alert-<?= session()->getFlashdata('error') == true ? 'danger' : 'success' ?>">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
               <i class="material-icons">close</i>
            </button>
            <?= session()->getFlashdata('msg') ?>
         </div>
      <?php endif; ?>

      <div class="card">
         <!-- Tab memakai markup standar tema (card-header-tabs + nav-tabs),
              perpindahannya ditangani JavaScript halaman ini. -->
         <div class="card-header card-header-primary card-header-tabs">
            <div class="nav-tabs-navigation">
               <div class="row align-items-center">
                  <div class="col-lg-5">
                     <h4 class="card-title"><b>Kartu Siswa</b></h4>
                     <p class="card-category">
                        Ukuran kartu <?= $tinggiMm ?> x <?= $lebarMm ?> mm (T x L)
                     </p>
                  </div>
                  <div class="col-lg-7 ml-lg-auto">
                     <div class="nav-tabs-wrapper">
                        <ul class="nav nav-tabs" role="tablist">
                           <li class="nav-item">
                              <a class="nav-link active" href="#bagianDesain" data-seksi="bagianDesain">
                                 <i class="material-icons">design_services</i> Desain Kartu
                              </a>
                           </li>
                           <li class="nav-item">
                              <a class="nav-link" href="#bagianTemplate" data-seksi="bagianTemplate">
                                 <i class="material-icons">image</i> Base Template
                              </a>
                           </li>
                           <li class="nav-item">
                              <a class="nav-link" href="#bagianCetak" data-seksi="bagianCetak">
                                 <i class="material-icons">print</i> Cetak
                              </a>
                           </li>
                           <li class="nav-item">
                              <a class="nav-link" href="#bagianDownload" data-seksi="bagianDownload">
                                 <i class="material-icons">cloud_download</i> Download
                              </a>
                           </li>
                        </ul>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="card-body">

               <!-- ============ 1. DESAIN ============ -->
               <div class="seksi-kartu" id="bagianDesain">
                  <div class="editor">
                     <div class="editor__kanvas-area">
                        <div class="btn-group btn-group-sm mb-2" role="group">
                           <button type="button" class="btn btn-primary" id="btnSisiDepan">Sisi Depan</button>
                           <button type="button" class="btn btn-outline-primary" id="btnSisiBelakang">Sisi Belakang</button>
                        </div>
                        <!-- tanpa .form-group: tema memakai floating label yang
                             menumpuk di atas isian -->
                        <div class="mb-3">
                           <label class="d-block small mb-1" for="cariSiswaKartu">Pratinjau data siswa</label>
                           <input type="text" id="cariSiswaKartu" class="form-control form-control-sm"
                              list="daftarSiswaKartu" autocomplete="off"
                              placeholder="Ketik nama atau NIS...">
                           <datalist id="daftarSiswaKartu">
                              <?php foreach ($daftarSiswa as $s) : ?>
                                 <option value="<?= esc($s['nama'] . ' - ' . $s['nis'], 'attr') ?>"><?= esc($s['kelas']) ?></option>
                              <?php endforeach; ?>
                           </datalist>
                           <small class="d-block mt-1 text-muted" id="statusCariSiswa"></small>
                        </div>
                        <div class="form-inline mb-2" style="gap:8px;">
                           <label class="mr-2 mb-0" for="skala">Perbesaran</label>
                           <select id="skala" class="custom-select custom-select-sm">
                              <option value="1.2">120%</option>
                              <option value="1.6" selected>160%</option>
                              <option value="2">200%</option>
                              <option value="2.6">260%</option>
                           </select>
                        </div>
                        <div class="kanvas-bingkai">
                           <div class="kanvas-ruang" id="kanvasRuang">
                              <div class="kanvas-skala" id="kanvasSkala">
                                 <div class="kartu kanvas" id="kanvas"></div>
                              </div>
                           </div>
                        </div>
                        <p class="text-muted mt-2" style="max-width:320px;font-size:12px;">
                           Seret elemen untuk memindahkan, tarik kotak hijau di pojok kanan bawah untuk mengubah ukuran.
                           Gunakan tombol panah pada keyboard untuk geser halus 0,5 mm (tahan Shift = 0,1 mm).
                        </p>
                     </div>

                     <div class="editor__panel">
                        <h5 class="mb-2"><b>Elemen data</b></h5>
                        <ul class="list-group daftar-elemen mb-4" id="daftarElemen"></ul>

                        <h5 class="mb-2"><b>Properti elemen</b></h5>
                        <div id="panelProperti" class="mb-3">
                           <p class="text-muted">Pilih salah satu elemen terlebih dahulu.</p>
                        </div>

                        <?php if ($bolehUbah) : ?>
                           <button type="button" class="btn btn-primary" id="btnSimpanLayout">
                              <i class="material-icons">save</i> Simpan Tata Letak
                           </button>
                           <a href="<?= base_url('admin/kartu/layout/reset') ?>" class="btn btn-outline-secondary"
                              onclick="return confirm('Kembalikan tata letak ke bawaan?')">Reset</a>
                           <span id="statusSimpan" class="ml-2 text-success"></span>
                        <?php else : ?>
                           <p class="text-muted">Perubahan tata letak hanya dapat disimpan oleh superadmin.</p>
                        <?php endif; ?>
                     </div>
                  </div>
               </div>

               <!-- ============ 2. BASE TEMPLATE ============ -->
               <?php if ($bolehUbah) : ?>
                  <div class="seksi-kartu" id="bagianTemplate" hidden>
                     <form action="<?= base_url('admin/kartu/template') ?>" method="post" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <div class="form-group">
                           <label for="namaTemplate">Nama Template</label>
                           <input type="text" class="form-control" id="namaTemplate" name="nama"
                              value="<?= esc($template['nama']) ?>">
                        </div>

                        <div class="row">
                           <?php foreach (['depan', 'belakang'] as $sisi) : ?>
                              <div class="col-md-6">
                                 <h5 class="text-capitalize"><b>Sisi <?= $sisi ?></b></h5>
                                 <?php if (!empty($template['svg_' . $sisi])) : ?>
                                    <p>
                                       <img class="thumb-template" src="<?= base_url($template['svg_' . $sisi]) ?>" alt="template <?= $sisi ?>">
                                    </p>
                                    <p class="text-muted" style="font-size:12px;">
                                       <?= esc(basename($template['svg_' . $sisi])) ?>
                                       &middot;
                                       <a class="text-danger" href="<?= base_url('admin/kartu/template/hapus/' . $sisi) ?>"
                                          onclick="return confirm('Hapus base template sisi <?= $sisi ?>?')">hapus</a>
                                    </p>
                                 <?php else : ?>
                                    <p class="text-muted">Belum ada base template.</p>
                                 <?php endif; ?>
                                 <!-- input file sengaja TIDAK dibungkus .form-group:
                                      tema menyembunyikan input file di dalamnya (opacity 0) -->
                                 <div class="berkas-template">
                                    <button type="button" class="btn btn-primary btn-sm"
                                       onclick="$('#berkas<?= ucfirst($sisi) ?>').trigger('click');">
                                       <i class="material-icons">folder_open</i> Pilih berkas
                                    </button>
                                    <input type="file" id="berkas<?= ucfirst($sisi) ?>" name="svg_<?= $sisi ?>"
                                       accept=".svg,image/svg+xml,image/png,image/jpeg"
                                       onchange="$('#namaBerkas<?= ucfirst($sisi) ?>').text(this.value.replace(/.*[\\/]/, ''));">
                                    <span class="text-info" id="namaBerkas<?= ucfirst($sisi) ?>"></span>
                                 </div>
                              </div>
                           <?php endforeach; ?>
                        </div>

                        <p class="text-muted" style="font-size:13px;">
                           Gunakan rasio kartu <?= $lebarMm ?> : <?= $tinggiMm ?> (potret); template diregangkan
                           tepat seukuran kartu. <b>Disarankan PNG</b> (mis. 638 x 1011 piksel, setara 300 dpi)
                           karena antivirus hosting kerap menghapus berkas SVG yang diunggah. SVG dan JPG tetap diterima.
                        </p>

                        <button type="submit" class="btn btn-primary">
                           <i class="material-icons">upload</i> Simpan Base Template
                        </button>
                     </form>
                  </div>
               <?php else : ?>
                  <div class="seksi-kartu" id="bagianTemplate" hidden>
                     <p class="text-muted">Penggantian base template hanya dapat dilakukan oleh superadmin.</p>
                  </div>
               <?php endif; ?>

               <!-- ============ 3. CETAK ============ -->
               <div class="seksi-kartu" id="bagianCetak" hidden>
                  <form action="<?= base_url('admin/kartu/cetak') ?>" method="get" target="_blank">
                     <div class="row">
                        <div class="col-md-4">
                           <label for="cetakKelas">Kelas</label>
                           <select name="id_kelas" id="cetakKelas" class="custom-select">
                              <?php if (empty($kelasWali)) : ?>
                                 <option value="">-- Semua kelas --</option>
                              <?php endif; ?>
                              <?php foreach ($kelas as $value) : ?>
                                 <?php if (!empty($kelasWali) && $kelasWali['id_kelas'] != $value['id_kelas']) continue; ?>
                                 <option value="<?= $value['id_kelas']; ?>">
                                    <?= labelKelas($value['kelas'], $value['jurusan']); ?>
                                 </option>
                              <?php endforeach; ?>
                           </select>
                        </div>
                        <div class="col-md-4">
                           <label for="cetakSiswa">Siswa (opsional)</label>
                           <select name="id_siswa" id="cetakSiswa" class="custom-select">
                              <option value="">-- Semua siswa pada kelas --</option>
                           </select>
                        </div>
                        <div class="col-md-4">
                           <label for="cetakSisi">Sisi kartu</label>
                           <select name="sisi" id="cetakSisi" class="custom-select">
                              <option value="keduanya">Depan &amp; belakang</option>
                              <option value="depan">Depan saja</option>
                              <option value="belakang">Belakang saja</option>
                           </select>
                        </div>
                     </div>
                     <div class="form-check mt-3">
                        <label class="form-check-label">
                           <input class="form-check-input" type="checkbox" name="garis_potong" value="1" checked>
                           Tampilkan garis bantu potong
                           <span class="form-check-sign"><span class="check"></span></span>
                        </label>
                     </div>
                     <button type="submit" class="btn btn-primary mt-3">
                        <i class="material-icons">print</i> Buka Halaman Cetak
                     </button>
                     <p class="text-muted mt-2" style="font-size:13px;">
                        Halaman cetak terbuka di tab baru. Gunakan <b>Cetak / Simpan PDF</b> di sana, pilih kertas A4
                        dengan skala 100% agar ukuran kartu tepat <?= $tinggiMm ?> x <?= $lebarMm ?> mm.
                     </p>
                  </form>
               </div>

               <!-- ============ 4. DOWNLOAD ============ -->
               <div class="seksi-kartu" id="bagianDownload" hidden>
                  <div class="row">
                     <div class="col-md-5">
                        <label for="unduhKelas">Kelas</label>
                        <select id="unduhKelas" class="custom-select">
                           <?php if (empty($kelasWali)) : ?>
                              <option value="">-- Semua kelas --</option>
                           <?php endif; ?>
                           <?php foreach ($kelas as $value) : ?>
                              <?php if (!empty($kelasWali) && $kelasWali['id_kelas'] != $value['id_kelas']) continue; ?>
                              <option value="<?= $value['id_kelas']; ?>">
                                 <?= labelKelas($value['kelas'], $value['jurusan']); ?>
                              </option>
                           <?php endforeach; ?>
                        </select>
                     </div>
                     <div class="col-md-4">
                        <label for="unduhSisi">Sisi kartu</label>
                        <select id="unduhSisi" class="custom-select">
                           <option value="keduanya">Depan &amp; belakang</option>
                           <option value="depan">Depan saja</option>
                           <option value="belakang">Belakang saja</option>
                        </select>
                     </div>
                     <div class="col-md-3 d-flex align-items-end">
                        <a id="unduhSemua" class="btn btn-primary w-100" href="#">
                           <i class="material-icons">folder_zip</i> Unduh semua (ZIP)
                        </a>
                     </div>
                  </div>

                  <p class="text-muted mt-2" style="font-size:13px;">
                     Berkas PNG 300 dpi (<?= $lebarMm ?> x <?= $tinggiMm ?> mm) dirender di server memakai
                     tata letak yang tersimpan. Satu siswa dengan dua sisi diunduh sebagai ZIP.
                  </p>

                  <div class="table-responsive mt-3">
                     <table class="table table-hover">
                        <thead class="text-primary">
                           <tr>
                              <th style="width:40px;">#</th>
                              <th>Nama Siswa</th>
                              <th>NIS</th>
                              <th class="text-right">Unduh</th>
                           </tr>
                        </thead>
                        <tbody id="daftarUnduh">
                           <tr>
                              <td colspan="4" class="text-muted">Memuat data siswa...</td>
                           </tr>
                        </tbody>
                     </table>
                  </div>
               </div>


         </div>
      </div>
   </div>
</div>

<script>
   (function () {
      // ---- data dari server ----
      const ELEMEN = <?= json_encode($elemen) ?>;
      const CONTOH = <?= json_encode($contoh) ?>;
      const LEBAR_MM = <?= $lebarMm ?>;
      const TINGGI_MM = <?= $tinggiMm ?>;
      const BG = {
         depan: <?= json_encode($template['svg_depan'] ? base_url($template['svg_depan']) : null) ?>,
         belakang: <?= json_encode($template['svg_belakang'] ? base_url($template['svg_belakang']) : null) ?>
      };
      const BOLEH_UBAH = <?= $bolehUbah ? 'true' : 'false' ?>;

      const DAFTAR_SISWA = <?= json_encode($daftarSiswa) ?>;

      let layout = <?= json_encode($template['layout']) ?>;
      let CONTOH_AKTIF = CONTOH;
      let sisi = 'depan';
      let terpilih = null;
      let skala = 1.6;

      const kanvas = document.getElementById('kanvas');
      const kanvasSkala = document.getElementById('kanvasSkala');
      const kanvasRuang = document.getElementById('kanvasRuang');
      const daftarElemen = document.getElementById('daftarElemen');
      const panelProperti = document.getElementById('panelProperti');

      const MM_PER_PX = 25.4 / 96; // 1 px CSS = 0,264583 mm

      function bulat(n) {
         return Math.round(n * 100) / 100;
      }

      // ---- gambar ulang kanvas ----
      function render() {
         kanvas.innerHTML = '';

         if (BG[sisi]) {
            const img = document.createElement('img');
            img.className = 'kartu__bg';
            img.src = BG[sisi];
            kanvas.appendChild(img);
         } else {
            const kosong = document.createElement('div');
            kosong.className = 'kartu__kosong';
            kosong.textContent = 'Base template sisi ' + sisi + ' belum diunggah';
            kanvas.appendChild(kosong);
         }

         Object.keys(ELEMEN).forEach(function (kunci) {
            const el = layout[sisi][kunci];
            if (!el.tampil) return;
            kanvas.appendChild(buatElemen(kunci, el));
         });
      }

      function buatElemen(kunci, el) {
         const kotak = document.createElement('div');
         kotak.className = 'kartu__el';
         kotak.dataset.kunci = kunci;
         kotak.style.left = el.x + 'mm';
         kotak.style.top = el.y + 'mm';
         kotak.style.width = el.w + 'mm';
         kotak.style.height = el.h + 'mm';

         if (ELEMEN[kunci].tipe === 'teks') {
            kotak.classList.add('kartu__teks', 'kartu__teks--' + el.rata);
            kotak.style.fontSize = el.ukuran + 'pt';
            kotak.style.fontWeight = el.tebal;
            kotak.style.color = el.warna;
            kotak.style.textTransform = el.kapital ? 'uppercase' : 'none';
            const span = document.createElement('span');
            span.textContent = (el.prefiks || '') + (CONTOH_AKTIF[kunci] || ELEMEN[kunci].label);
            kotak.appendChild(span);
         } else {
            kotak.style.borderRadius = el.radius + 'mm';
            if (el.bingkai > 0) {
               kotak.style.border = el.bingkai + 'mm solid ' + el.warnaBingkai;
            }
            if (CONTOH_AKTIF[kunci]) {
               const img = document.createElement('img');
               img.className = 'kartu__gambar';
               img.src = CONTOH_AKTIF[kunci];
               img.style.objectFit = el.isi;
               img.style.borderRadius = 'inherit';
               kotak.appendChild(img);
            } else {
               const ph = document.createElement('div');
               ph.className = 'kartu__gambar--kosong';
               ph.style.borderRadius = 'inherit';
               ph.textContent = ELEMEN[kunci].label;
               kotak.appendChild(ph);
            }
         }

         if (kunci === terpilih) {
            kotak.classList.add('terpilih');
            const pegangan = document.createElement('div');
            pegangan.className = 'kartu__pegangan';
            pegangan.addEventListener('pointerdown', function (e) {
               mulaiGeser(e, kunci, 'ukuran');
            });
            kotak.appendChild(pegangan);
         }

         kotak.addEventListener('pointerdown', function (e) {
            if (e.target.classList.contains('kartu__pegangan')) return;
            pilih(kunci);
            mulaiGeser(e, kunci, 'posisi');
         });

         return kotak;
      }

      // ---- geser & ubah ukuran ----
      function mulaiGeser(e, kunci, mode) {
         if (!BOLEH_UBAH) return;
         e.preventDefault();

         const el = layout[sisi][kunci];
         const awal = { x: e.clientX, y: e.clientY, ex: el.x, ey: el.y, ew: el.w, eh: el.h };
         const target = e.currentTarget;
         target.setPointerCapture(e.pointerId);

         function pindah(ev) {
            const dx = (ev.clientX - awal.x) * MM_PER_PX / skala;
            const dy = (ev.clientY - awal.y) * MM_PER_PX / skala;

            if (mode === 'posisi') {
               el.x = bulat(awal.ex + dx);
               el.y = bulat(awal.ey + dy);
            } else {
               el.w = bulat(Math.max(2, awal.ew + dx));
               el.h = bulat(Math.max(2, awal.eh + dy));
            }
            render();
            isiProperti();
         }

         function selesai(ev) {
            target.releasePointerCapture(ev.pointerId);
            window.removeEventListener('pointermove', pindah);
            window.removeEventListener('pointerup', selesai);
         }

         window.addEventListener('pointermove', pindah);
         window.addEventListener('pointerup', selesai);
      }

      // ---- daftar elemen ----
      function renderDaftar() {
         daftarElemen.innerHTML = '';

         Object.keys(ELEMEN).forEach(function (kunci) {
            const el = layout[sisi][kunci];
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex align-items-center justify-content-between' +
               (kunci === terpilih ? ' aktif' : '');

            const nama = document.createElement('span');
            nama.textContent = ELEMEN[kunci].label;
            nama.addEventListener('click', function () {
               pilih(kunci);
            });

            const toggle = document.createElement('input');
            toggle.type = 'checkbox';
            toggle.checked = !!el.tampil;
            toggle.disabled = !BOLEH_UBAH;
            toggle.title = 'Tampilkan elemen pada kartu';
            toggle.addEventListener('change', function () {
               el.tampil = toggle.checked;
               if (el.tampil) terpilih = kunci;
               gambarUlang();
            });

            li.appendChild(nama);
            li.appendChild(toggle);
            daftarElemen.appendChild(li);
         });
      }

      // ---- panel properti ----
      function isiProperti() {
         if (!terpilih || !layout[sisi][terpilih]) {
            panelProperti.innerHTML = '<p class="text-muted">Pilih salah satu elemen terlebih dahulu.</p>';
            return;
         }

         const el = layout[sisi][terpilih];
         const teks = ELEMEN[terpilih].tipe === 'teks';
         const nonaktif = BOLEH_UBAH ? '' : 'disabled';

         let html = '<h6 class="mb-2"><b>' + ELEMEN[terpilih].label + '</b> (sisi ' + sisi + ')</h6>';
         html += '<div class="prop-grid">';
         html += kolomAngka('x', 'Kiri (mm)', el.x, nonaktif);
         html += kolomAngka('y', 'Atas (mm)', el.y, nonaktif);
         html += kolomAngka('w', 'Lebar (mm)', el.w, nonaktif);
         html += kolomAngka('h', 'Tinggi (mm)', el.h, nonaktif);

         if (teks) {
            html += kolomAngka('ukuran', 'Ukuran font (pt)', el.ukuran, nonaktif, 0.5);
            html += '<div><label>Ketebalan</label><select class="custom-select custom-select-sm" data-prop="tebal" ' + nonaktif + '>' +
               [300, 400, 500, 600, 700, 800].map(function (b) {
                  return '<option value="' + b + '"' + (el.tebal == b ? ' selected' : '') + '>' + b + '</option>';
               }).join('') + '</select></div>';
            html += '<div><label>Perataan</label><select class="custom-select custom-select-sm" data-prop="rata" ' + nonaktif + '>' +
               [['left', 'Kiri'], ['center', 'Tengah'], ['right', 'Kanan']].map(function (r) {
                  return '<option value="' + r[0] + '"' + (el.rata === r[0] ? ' selected' : '') + '>' + r[1] + '</option>';
               }).join('') + '</select></div>';
            html += '<div><label>Warna teks</label><input type="color" class="form-control form-control-sm" data-prop="warna" value="' + el.warna + '" ' + nonaktif + '></div>';
            html += '<div><label>Awalan teks</label><input type="text" class="form-control form-control-sm" data-prop="prefiks" value="' + (el.prefiks || '').replace(/"/g, '&quot;') + '" ' + nonaktif + '></div>';
            html += '<div><label>Huruf kapital</label><input type="checkbox" data-prop="kapital"' + (el.kapital ? ' checked' : '') + ' ' + nonaktif + '></div>';
         } else {
            html += kolomAngka('radius', 'Sudut membulat (mm)', el.radius, nonaktif, 0.5);
            html += kolomAngka('bingkai', 'Tebal bingkai (mm)', el.bingkai, nonaktif, 0.1);
            html += '<div><label>Warna bingkai</label><input type="color" class="form-control form-control-sm" data-prop="warnaBingkai" value="' + el.warnaBingkai + '" ' + nonaktif + '></div>';
            html += '<div><label>Penyesuaian gambar</label><select class="custom-select custom-select-sm" data-prop="isi" ' + nonaktif + '>' +
               [['cover', 'Penuhi kotak (crop)'], ['contain', 'Muat seluruhnya']].map(function (r) {
                  return '<option value="' + r[0] + '"' + (el.isi === r[0] ? ' selected' : '') + '>' + r[1] + '</option>';
               }).join('') + '</select></div>';
         }

         html += '</div>';
         panelProperti.innerHTML = html;

         panelProperti.querySelectorAll('[data-prop]').forEach(function (input) {
            input.addEventListener('input', function () {
               const prop = input.dataset.prop;
               if (input.type === 'checkbox') {
                  el[prop] = input.checked;
               } else if (input.type === 'number') {
                  el[prop] = parseFloat(input.value || 0);
               } else if (prop === 'tebal') {
                  el[prop] = parseInt(input.value, 10);
               } else {
                  el[prop] = input.value;
               }
               render();
            });
         });
      }

      function kolomAngka(prop, label, nilai, nonaktif, step) {
         return '<div><label>' + label + '</label>' +
            '<input type="number" step="' + (step || 0.1) + '" class="form-control form-control-sm" data-prop="' + prop + '" value="' + nilai + '" ' + nonaktif + '></div>';
      }

      function pilih(kunci) {
         terpilih = kunci;
         gambarUlang();
      }

      function gambarUlang() {
         render();
         renderDaftar();
         isiProperti();
      }

      // ---- kontrol ----
      document.getElementById('btnSisiDepan').addEventListener('click', function () {
         gantiSisi('depan', this, document.getElementById('btnSisiBelakang'));
      });
      document.getElementById('btnSisiBelakang').addEventListener('click', function () {
         gantiSisi('belakang', this, document.getElementById('btnSisiDepan'));
      });

      function gantiSisi(baru, tombolAktif, tombolLain) {
         sisi = baru;
         terpilih = null;
         tombolAktif.className = 'btn btn-primary';
         tombolLain.className = 'btn btn-outline-primary';
         gambarUlang();
      }

      document.getElementById('skala').addEventListener('change', function () {
         skala = parseFloat(this.value);
         terapkanSkala();
      });

      function terapkanSkala() {
         kanvasSkala.style.transform = 'scale(' + skala + ')';

         // Kartu yang di-scale tidak lagi menempati ruang aslinya, jadi
         // penampungnya diberi ukuran px sebesar kartu setelah diperbesar.
         const PX_PER_MM = 96 / 25.4;
         kanvasRuang.style.width = Math.ceil(LEBAR_MM * PX_PER_MM * skala) + 'px';
         kanvasRuang.style.height = Math.ceil(TINGGI_MM * PX_PER_MM * skala) + 'px';
      }

      // geser halus dengan tombol panah
      document.addEventListener('keydown', function (e) {
         if (!BOLEH_UBAH || !terpilih) return;
         if (document.activeElement && /INPUT|SELECT|TEXTAREA/.test(document.activeElement.tagName)) return;

         const langkah = e.shiftKey ? 0.1 : 0.5;
         const el = layout[sisi][terpilih];
         const peta = { ArrowLeft: ['x', -1], ArrowRight: ['x', 1], ArrowUp: ['y', -1], ArrowDown: ['y', 1] };

         if (!peta[e.key]) return;
         e.preventDefault();
         el[peta[e.key][0]] = bulat(el[peta[e.key][0]] + peta[e.key][1] * langkah);
         render();
         isiProperti();
      });

      const btnSimpan = document.getElementById('btnSimpanLayout');
      if (btnSimpan) {
         btnSimpan.addEventListener('click', function () {
            const status = document.getElementById('statusSimpan');
            status.className = 'ml-2 text-muted';
            status.textContent = 'Menyimpan...';

            $.ajax({
               type: 'POST',
               url: '<?= base_url('admin/kartu/layout') ?>',
               data: setAjaxData({ layout: JSON.stringify(layout) }),
               success: function (res) {
                  if (res && res.sukses) {
                     layout = res.layout;
                     status.className = 'ml-2 text-success';
                     status.textContent = 'Tata letak tersimpan';
                     gambarUlang();
                  } else {
                     status.className = 'ml-2 text-danger';
                     status.textContent = (res && res.pesan) || 'Gagal menyimpan';
                  }
               },
               error: function (xhr) {
                  status.className = 'ml-2 text-danger';
                  status.textContent = (xhr.responseJSON && xhr.responseJSON.pesan) || 'Gagal menyimpan';
               }
            });
         });
      }

      // ---- dropdown siswa pada tab cetak ----
      const cetakKelas = document.getElementById('cetakKelas');
      const cetakSiswa = document.getElementById('cetakSiswa');

      function muatSiswa() {
         $.ajax({
            type: 'POST',
            url: '<?= base_url('admin/kartu/siswa-by-kelas') ?>',
            data: setAjaxData({ id_kelas: cetakKelas.value }),
            success: function (res) {
               cetakSiswa.innerHTML = '<option value="">-- Semua siswa pada kelas --</option>';
               (res || []).forEach(function (s) {
                  const opt = document.createElement('option');
                  opt.value = s.id_siswa;
                  opt.textContent = s.nama_siswa + ' (' + s.nis + ')';
                  cetakSiswa.appendChild(opt);
               });
            }
         });
      }

      cetakKelas.addEventListener('change', muatSiswa);
      muatSiswa();

      // ---- daftar unduhan per siswa ----
      const unduhKelas = document.getElementById('unduhKelas');
      const unduhSisi = document.getElementById('unduhSisi');
      const daftarUnduh = document.getElementById('daftarUnduh');
      const unduhSemua = document.getElementById('unduhSemua');
      const URL_UNDUH = '<?= base_url('admin/kartu/download') ?>';

      function tautanUnduh(params) {
         const q = new URLSearchParams(params);
         q.set('sisi', unduhSisi.value);
         return URL_UNDUH + '?' + q.toString();
      }

      function perbaruiUnduhSemua() {
         unduhSemua.href = tautanUnduh(unduhKelas.value ? { id_kelas: unduhKelas.value } : {});
      }

      function muatDaftarUnduh() {
         perbaruiUnduhSemua();
         daftarUnduh.innerHTML = '<tr><td colspan="4" class="text-muted">Memuat data siswa...</td></tr>';

         $.ajax({
            type: 'POST',
            url: '<?= base_url('admin/kartu/siswa-by-kelas') ?>',
            data: setAjaxData({ id_kelas: unduhKelas.value }),
            success: function (res) {
               daftarUnduh.innerHTML = '';

               if (!res || !res.length) {
                  daftarUnduh.innerHTML = '<tr><td colspan="4" class="text-muted">Tidak ada siswa pada kelas ini.</td></tr>';
                  return;
               }

               res.forEach(function (s, i) {
                  const tr = document.createElement('tr');

                  const no = document.createElement('td');
                  no.textContent = i + 1;

                  const nama = document.createElement('td');
                  nama.textContent = s.nama_siswa;

                  const nis = document.createElement('td');
                  nis.textContent = s.nis;

                  const aksi = document.createElement('td');
                  aksi.className = 'text-right';
                  const tombol = document.createElement('a');
                  tombol.className = 'btn btn-sm btn-primary';
                  tombol.href = tautanUnduh({ id_siswa: s.id_siswa });
                  tombol.innerHTML = '<i class="material-icons">cloud_download</i> Unduh';
                  aksi.appendChild(tombol);

                  tr.append(no, nama, nis, aksi);
                  daftarUnduh.appendChild(tr);
               });
            },
            error: function () {
               daftarUnduh.innerHTML = '<tr><td colspan="4" class="text-danger">Gagal memuat data siswa.</td></tr>';
            }
         });
      }

      unduhKelas.addEventListener('change', muatDaftarUnduh);
      // pilihan sisi cukup memperbarui tautan yang sudah tampil
      unduhSisi.addEventListener('change', function () {
         perbaruiUnduhSemua();
         daftarUnduh.querySelectorAll('a[href]').forEach(function (a) {
            const url = new URL(a.href);
            url.searchParams.set('sisi', unduhSisi.value);
            a.href = url.toString();
         });
      });
      muatDaftarUnduh();

      // ---- pencarian siswa untuk pratinjau ----
      const cariSiswa = document.getElementById('cariSiswaKartu');
      const statusCari = document.getElementById('statusCariSiswa');

      function cocokkanSiswa(teks) {
         const bersih = teks.trim().toLowerCase();
         if (!bersih) return null;

         // cocok persis dengan format "Nama - NIS" (hasil pilih dari datalist)
         let siswa = DAFTAR_SISWA.find(function (s) {
            return (s.nama + ' - ' + s.nis).toLowerCase() === bersih;
         });

         // kalau diketik manual, terima NIS atau nama yang cocok penuh
         if (!siswa) {
            siswa = DAFTAR_SISWA.find(function (s) {
               return String(s.nis).toLowerCase() === bersih || s.nama.toLowerCase() === bersih;
            });
         }

         return siswa || null;
      }

      function muatPratinjauSiswa(idSiswa) {
         statusCari.className = 'text-muted';
         statusCari.textContent = 'Memuat data siswa...';

         $.ajax({
            type: 'POST',
            url: '<?= base_url('admin/kartu/pratinjau') ?>',
            data: setAjaxData({ id_siswa: idSiswa }),
            success: function (res) {
               if (!res || !res.sukses) {
                  statusCari.className = 'text-danger';
                  statusCari.textContent = (res && res.pesan) || 'Data siswa tidak ditemukan';
                  return;
               }
               CONTOH_AKTIF = res.data;
               statusCari.className = 'text-success';
               statusCari.textContent = 'Pratinjau memakai data ' + (res.data.nama || '');
               render();
            },
            error: function () {
               statusCari.className = 'text-danger';
               statusCari.textContent = 'Gagal memuat data siswa';
            }
         });
      }

      cariSiswa.addEventListener('change', function () {
         const siswa = cocokkanSiswa(this.value);

         if (!this.value.trim()) {
            CONTOH_AKTIF = CONTOH;
            statusCari.textContent = '';
            render();
            return;
         }

         if (!siswa) {
            statusCari.className = 'text-danger';
            statusCari.textContent = 'Siswa tidak ditemukan, pilih dari daftar saran';
            return;
         }

         muatPratinjauSiswa(siswa.id);
      });

      // ---- perpindahan tab ----
      const tombolTab = document.querySelectorAll('.nav-tabs .nav-link[data-seksi]');

      tombolTab.forEach(function (tombol) {
         tombol.addEventListener('click', function (e) {
            e.preventDefault();
            tombolTab.forEach(function (t) {
               const seksi = document.getElementById(t.dataset.seksi);
               const aktif = t === tombol;
               t.classList.toggle('active', aktif);
               if (seksi) seksi.hidden = !aktif;
            });
            // kanvas perlu diukur ulang bila tadinya tersembunyi
            if (tombol.dataset.seksi === 'bagianDesain') terapkanSkala();
         });
      });

      terapkanSkala();
      gambarUlang();
   })();
</script>
<?= $this->endSection() ?>
