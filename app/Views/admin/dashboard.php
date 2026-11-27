<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<div class="content">
    <div class="container-fluid">
        <!-- REKAP JUMLAH DATA -->
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-6">
                <div class="card card-stats">
                    <div class="card-header card-header-primary card-header-icon">
                        <div class="card-icon">
                            <a href="<?= base_url('admin/siswa'); ?>" class="text-white">
                                <i class="material-icons">person</i>
                            </a>
                        </div>
                        <p class="card-category">Jumlah siswa</p>
                        <h3 class="card-title"><?= count($siswa); ?></h3>
                    </div>
                    <div class="card-footer">
                        <div class="stats">
                            <i class="material-icons text-primary">check</i>
                            Terdaftar
                        </div>
                    </div>
                </div>
            </div>
            <!-- <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stats">
                    <div class="card-header card-header-success card-header-icon">
                        <div class="card-icon">
                            <a href="<?= base_url('admin/guru'); ?>" class="text-white">
                                <i class="material-icons">person_4</i>
                            </a>
                        </div>
                        <p class="card-category">Jumlah guru</p>
                        <h3 class="card-title"><?= count($guru); ?></h3>
                    </div>
                    <div class="card-footer">
                        <div class="stats">
                            <i class="material-icons text-success">check</i>
                            Terdaftar
                        </div>
                    </div>
                </div>
            </div> -->
            <div class="col-lg-6 col-md-6 col-sm-6">
                <div class="card card-stats">
                    <div class="card-header card-header-info card-header-icon">
                        <div class="card-icon">
                            <a href="<?= base_url('admin/kelas'); ?>" class="text-white">
                                <i class="material-icons">school</i>
                            </a>
                        </div>
                        <p class="card-category">Jumlah kelas</p>
                        <h3 class="card-title"><?= count($kelas); ?></h3>
                    </div>
                    <div class="card-footer">
                        <div class="stats">
                            <i class="material-icons">home</i>
                            <?= $generalSettings->school_name; ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stats">
                    <div class="card-header card-header-danger card-header-icon">
                        <div class="card-icon">
                            <a href="<?= base_url('admin/petugas'); ?>" class="text-white">
                                <i class="material-icons">settings</i>
                            </a>
                        </div>
                        <p class="card-category">Jumlah petugas</p>
                        <h3 class="card-title"><?= count($petugas); ?></h3>
                    </div>
                    <div class="card-footer">
                        <div class="stats">
                            <i class="material-icons">person</i>
                            Petugas dan Administrator
                        </div>
                    </div>
                </div>
            </div> -->
        </div>
        <!-- TIMEFRAME: kontrol tanggal/rentang data yang ditampilkan -->
        <div class="row">
            <div class="col-md-12">
                <div class="card" style="border-left:4px solid #4caf50;">
                    <div class="card-body" style="display:flex !important; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:16px; padding:16px 24px;">
                        <div style="display:flex !important; align-items:center; gap:10px; flex-shrink:0;">
                            <i class="material-icons text-success">event</i>
                            <div>
                                <div class="small text-muted" style="line-height:1.1;">Menampilkan data untuk</div>
                                <b><?= esc($labelPeriode); ?></b>
                            </div>
                        </div>

                        <form id="formTimeframe" method="get" style="display:flex !important; flex-wrap:wrap; align-items:center; gap:10px; margin:0;">
                            <input type="hidden" name="mode" id="inputMode" value="<?= esc($timeframeMode); ?>">

                            <div class="btn-group" role="group" style="display:flex !important;">
                                <button type="button" class="btn btn-sm <?= $timeframeMode === 'single' ? 'btn-success' : 'btn-outline-success'; ?>" onclick="pilihModeTimeframe('single')">Tanggal</button>
                                <button type="button" class="btn btn-sm <?= $timeframeMode === 'range' ? 'btn-success' : 'btn-outline-success'; ?>" onclick="pilihModeTimeframe('range')">Rentang</button>
                            </div>

                            <div id="blokTanggalTunggal" style="display:<?= $timeframeMode === 'single' ? 'flex' : 'none'; ?> !important; align-items:center; gap:6px;">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="geserTanggal(-1)" title="Hari sebelumnya" aria-label="Hari sebelumnya">
                                    <i class="material-icons">chevron_left</i>
                                </button>
                                <input type="date" class="form-control form-control-sm" name="tanggal" id="inputTanggal"
                                    value="<?= esc($tanggalPilihan); ?>" max="<?= esc($hariIni); ?>"
                                    onchange="document.getElementById('formTimeframe').submit()">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="geserTanggal(1)" title="Hari berikutnya" aria-label="Hari berikutnya"
                                    <?= $tanggalPilihan >= $hariIni ? 'disabled' : ''; ?>>
                                    <i class="material-icons">chevron_right</i>
                                </button>
                                <?php if ($tanggalPilihan !== $hariIni) : ?>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="kembaliHariIni()">Hari ini</button>
                                <?php endif; ?>
                            </div>

                            <div id="blokRentang" style="display:<?= $timeframeMode === 'range' ? 'flex' : 'none'; ?> !important; align-items:center; gap:6px;">
                                <label class="mb-0 small text-muted">Dari</label>
                                <input type="date" class="form-control form-control-sm" name="mulai" value="<?= esc($mulaiPilihan); ?>" max="<?= esc($hariIni); ?>">
                                <label class="mb-0 small text-muted">Sampai</label>
                                <input type="date" class="form-control form-control-sm" name="akhir" value="<?= esc($akhirPilihan); ?>" max="<?= esc($hariIni); ?>">
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="material-icons" style="font-size:16px; vertical-align:text-bottom;">check</i> Terapkan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header card-header-primary">
                        <h4 class="card-title"><b>Absensi Siswa</b></h4>
                        <p class="card-category"><?= esc($labelPeriode); ?></p>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col">
                                <h4 class="text-success"><b>Hadir</b></h4>
                                <h3><?= $jumlahKehadiranSiswa['hadir']; ?></h3>
                            </div>
                            <div class="col">
                                <h4 class="text-warning"><b>Sakit</b></h4>
                                <h3><?= $jumlahKehadiranSiswa['sakit']; ?></h3>
                            </div>
                            <div class="col">
                                <h4 class="text-info"><b>Izin</b></h4>
                                <h3><?= $jumlahKehadiranSiswa['izin']; ?></h3>
                            </div>
                            <div class="col">
                                <h4 class="text-danger"><b>Alfa</b></h4>
                                <h3><?= $jumlahKehadiranSiswa['alfa']; ?></h3>
                            </div>
                            <div class="col" style="border-left:1px dashed #ddd;">
                                <h4 class="text-danger"><b>Terlambat</b></h4>
                                <h3><?= $jumlahTerlambatSiswa; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- <div class="col-md-6">
                <div class="card">
                    <div class="card-header card-header-success">
                        <h4 class="card-title"><b>Absensi Guru Hari Ini</b></h4>
                        <p class="card-category"><?= $dateNow; ?></p>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <h4 class="text-success"><b>Hadir</b></h4>
                                <h3><?= $jumlahKehadiranGuru['hadir']; ?></h3>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-warning"><b>Sakit</b></h4>
                                <h3><?= $jumlahKehadiranGuru['sakit']; ?></h3>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-info"><b>Izin</b></h4>
                                <h3><?= $jumlahKehadiranGuru['izin']; ?></h3>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-danger"><b>Alfa</b></h4>
                                <h3><?= $jumlahKehadiranGuru['alfa']; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
        </div>
        <!-- 10 SISWA DATANG TERCEPAT & TERLAMBAT -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header card-header-success">
                        <h4 class="card-title" style="display:flex !important; align-items:center; gap:8px;">
                            <i class="material-icons">bolt</i> <b>Siswa Datang Tercepat</b>
                        </h4>
                        <p class="card-category"><?= esc($labelPeriode); ?></p>
                    </div>
                    <div class="card-body p-0" style="height:360px; overflow-y:auto;">
                        <?php if (!empty($siswaTercepat)) : ?>
                            <ul class="list-group list-group-flush" style="display:block !important;">
                                <?php foreach ($siswaTercepat as $i => $s) : ?>
                                    <li class="list-group-item" style="display:flex !important; align-items:center !important; width:100% !important; gap:14px;">
                                        <span style="flex:0 0 20px; color:#9a9a9a; font-weight:600; text-align:right;"><?= $i + 1; ?></span>
                                        <?php if (!empty($s['foto'])) : ?>
                                            <img src="<?= esc($s['foto']); ?>" alt="" style="flex:0 0 44px; width:44px; height:44px; border-radius:50%; object-fit:cover;">
                                        <?php else : ?>
                                            <span style="flex:0 0 44px; width:44px; height:44px; border-radius:50%; background:#f1f1f1; display:flex !important; align-items:center; justify-content:center;">
                                                <i class="material-icons text-muted">person</i>
                                            </span>
                                        <?php endif; ?>
                                        <div style="flex:1 1 auto; min-width:0;">
                                            <div style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><b><?= esc($s['nama_siswa']); ?></b></div>
                                            <small class="text-muted">
                                                <?= labelKelas($s['kelas'] ?? null, $s['jurusan'] ?? null); ?>
                                                <?php if ($timeframeRentang && !empty($s['tanggal'])) : ?>
                                                    &middot; <?= date('d/m', strtotime($s['tanggal'])); ?>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                        <div class="text-success" style="flex:0 0 110px; display:flex !important; align-items:center; justify-content:flex-end; gap:4px; white-space:nowrap; font-weight:700;">
                                            <i class="material-icons" style="font-size:16px;">schedule</i>
                                            <?= esc($s['jam_masuk']); ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else : ?>
                            <div style="display:flex !important; align-items:center; justify-content:center; height:100%; text-align:center; padding:0 24px;">
                                <div class="text-muted">
                                    <i class="material-icons" style="font-size:40px; opacity:.5;">schedule</i>
                                    <p class="mb-0 mt-2">Belum ada siswa yang absen masuk pada periode ini</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header card-header-danger" style="display:flex !important; align-items:flex-start; justify-content:space-between; gap:12px;">
                        <div>
                            <h4 class="card-title" style="display:flex !important; align-items:center; gap:8px;">
                                <i class="material-icons">warning</i> <b>Siswa Terlambat</b>
                            </h4>
                            <p class="card-category"><?= esc($labelPeriode); ?></p>
                        </div>
                        <a href="<?= base_url('admin/absen-siswa/terlambat'); ?>" class="btn btn-danger btn-sm" style="margin-top:2px; white-space:nowrap;">
                            Lihat Semua<?= $jumlahTerlambatSiswa > 10 ? " ({$jumlahTerlambatSiswa})" : ''; ?>
                        </a>
                    </div>
                    <div class="card-body p-0" style="height:360px; overflow-y:auto;">
                        <?php if (!empty($siswaTerlambat)) : ?>
                            <ul class="list-group list-group-flush" style="display:block !important;">
                                <?php foreach ($siswaTerlambat as $i => $s) : ?>
                                    <li class="list-group-item" style="display:flex !important; align-items:center !important; width:100% !important; gap:14px;">
                                        <span style="flex:0 0 20px; color:#9a9a9a; font-weight:600; text-align:right;"><?= $i + 1; ?></span>
                                        <?php if (!empty($s['foto'])) : ?>
                                            <img src="<?= esc($s['foto']); ?>" alt="" style="flex:0 0 44px; width:44px; height:44px; border-radius:50%; object-fit:cover;">
                                        <?php else : ?>
                                            <span style="flex:0 0 44px; width:44px; height:44px; border-radius:50%; background:#f1f1f1; display:flex !important; align-items:center; justify-content:center;">
                                                <i class="material-icons text-muted">person</i>
                                            </span>
                                        <?php endif; ?>
                                        <div style="flex:1 1 auto; min-width:0;">
                                            <div style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><b><?= esc($s['nama_siswa']); ?></b></div>
                                            <small class="text-muted">
                                                <?= labelKelas($s['kelas'] ?? null, $s['jurusan'] ?? null); ?>
                                                <?php if ($timeframeRentang && !empty($s['tanggal'])) : ?>
                                                    &middot; <?= date('d/m', strtotime($s['tanggal'])); ?>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                        <div style="flex:0 0 130px; display:flex !important; flex-direction:column; align-items:flex-end; white-space:nowrap;">
                                            <div class="text-danger" style="display:flex !important; align-items:center; gap:4px; font-weight:700;">
                                                <i class="material-icons" style="font-size:16px;">schedule</i>
                                                <?= esc($s['jam_masuk']); ?>
                                            </div>
                                            <span class="badge badge-danger" style="margin-top:4px;"><?= formatDurasiTerlambat($s['jam_masuk'], $batasAbsenMasuk); ?></span>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else : ?>
                            <div style="display:flex !important; align-items:center; justify-content:center; height:100%; text-align:center; padding:0 24px;">
                                <div class="text-success">
                                    <i class="material-icons" style="font-size:40px;">check_circle</i>
                                    <p class="mb-0 mt-2 text-muted">Tidak ada siswa yang terlambat pada periode ini</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- GRAFIK CHART -->
        <div class="row">
            <div class="col-md-12">
                <div class="card card-chart">
                    <div class="card-header card-header-primary">
                        <div class="ct-chart" id="kehadiranSiswa"></div>
                    </div>
                    <div class="card-body">
                        <h4 class="card-title">Tingkat kehadiran siswa</h4>
                        <p class="card-category">Jumlah kehadiran siswa dalam 7 hari terakhir</p>
                    </div>
                    <div class="card-footer">
                        <div class="stats">
                            <i class="material-icons text-primary">checklist</i> <a class="text-primary" href="<?= base_url('admin/absen-siswa'); ?>">Lihat data</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- <div class="col-md-6">
                <div class="card card-chart">
                    <div class="card-header card-header-success">
                        <div class="ct-chart" id="kehadiranGuru"></div>
                    </div>
                    <div class="card-body">
                        <h4 class="card-title">Tingkat kehadiran guru</h4>
                        <p class="card-category">Jumlah kehadiran guru dalam 7 hari terakhir</p>
                    </div>
                    <div class="card-footer">
                        <div class="stats">
                            <i class="material-icons text-success">checklist</i> <a class="text-success" href="<?= base_url('admin/absen-guru'); ?>">Lihat data</a>
                        </div>
                    </div>
                </div>
            </div> -->
        </div>
    </div>
</div>
<!-- Chartist JS -->
<script src="<?= base_url('assets/js/plugins/chartist.min.js') ?>"></script>
<script>
    $(document).ready(function() {
        initDashboardPageCharts();
    });

    function pilihModeTimeframe(mode) {
        document.getElementById('inputMode').value = mode;
        document.getElementById('blokTanggalTunggal').style.display = mode === 'single' ? 'flex' : 'none';
        document.getElementById('blokRentang').style.display = mode === 'range' ? 'flex' : 'none';

        if (mode === 'single') {
            document.getElementById('formTimeframe').submit();
        }
    }

    function geserTanggal(delta) {
        var input = document.getElementById('inputTanggal');
        var bagian = input.value.split('-');
        // tanggal dibentuk & diformat ulang di waktu lokal supaya tidak
        // mundur satu hari akibat konversi ke UTC (toISOString)
        var tanggal = new Date(parseInt(bagian[0], 10), parseInt(bagian[1], 10) - 1, parseInt(bagian[2], 10));
        tanggal.setDate(tanggal.getDate() + delta);

        var tahun = tanggal.getFullYear();
        var bulan = String(tanggal.getMonth() + 1).padStart(2, '0');
        var hari = String(tanggal.getDate()).padStart(2, '0');

        input.value = tahun + '-' + bulan + '-' + hari;
        document.getElementById('formTimeframe').submit();
    }

    function kembaliHariIni() {
        document.getElementById('inputTanggal').value = '<?= esc($hariIni); ?>';
        document.getElementById('formTimeframe').submit();
    }

    function initDashboardPageCharts() {

        if ($('#kehadiranSiswa').length != 0) {
            /* ----------==========     Chart tingkat kehadiran siswa    ==========---------- */
            const dataKehadiranSiswa = [<?php foreach ($grafikKehadiranSiswa as $value) echo "$value,"; ?>];

            const chartKehadiranSiswa = {
                labels: [
                    <?php
                    foreach ($dateRange as  $value) {
                        echo "'$value',";
                    }
                    ?>
                ],
                series: [dataKehadiranSiswa]
            };

            var highestData = 0;

            dataKehadiranSiswa.forEach(e => {
                if (e >= highestData) {
                    highestData = e;
                }
            })

            const optionsChart = {
                lineSmooth: Chartist.Interpolation.cardinal({
                    tension: 0
                }),
                low: 0,
                high: highestData + (highestData / 4),
                chartPadding: {
                    top: 0,
                    right: 0,
                    bottom: 0,
                    left: 0
                }
            }

            var kehadiranSiswaChart = new Chartist.Line('#kehadiranSiswa', chartKehadiranSiswa, optionsChart);

            md.startAnimationForLineChart(kehadiranSiswaChart);
        }

        if ($('#kehadiranGuru').length != 0) {
            /* ----------==========     Chart tingkat kehadiran guru    ==========---------- */
            const dataKehadiranGuru = [<?php foreach ($grafikkKehadiranGuru as $value) echo "$value,"; ?>];

            const chartKehadiranGuru = {
                labels: [
                    <?php
                    foreach ($dateRange as  $value) {
                        echo "'$value',";
                    }
                    ?>
                ],
                series: [dataKehadiranGuru]
            };

            var highestData = 0;

            dataKehadiranGuru.forEach(e => {
                if (e >= highestData) {
                    highestData = e;
                }
            })

            const optionsChart = {
                lineSmooth: Chartist.Interpolation.cardinal({
                    tension: 0
                }),
                low: 0,
                high: highestData + (highestData / 4),
                chartPadding: {
                    top: 0,
                    right: 0,
                    bottom: 0,
                    left: 0
                }
            }

            var kehadiranGuruChart = new Chartist.Line('#kehadiranGuru', chartKehadiranGuru, optionsChart);

            md.startAnimationForLineChart(kehadiranGuruChart);
        }
    }
</script>
<?= $this->endSection() ?>