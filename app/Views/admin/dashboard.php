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
                                <i class="material-icons">grade</i>
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
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header card-header-primary">
                        <h4 class="card-title"><b>Absensi Siswa Hari Ini</b></h4>
                        <p class="card-category"><?= $dateNow; ?></p>
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
                            <div class="col">
                                <a href="<?= base_url('admin/absen-siswa/terlambat'); ?>" class="text-decoration-none">
                                    <h4 class="text-danger"><b>Terlambat</b></h4>
                                    <h3><?= $jumlahTerlambatSiswa; ?></h3>
                                    <small class="text-muted">Lihat detail</small>
                                </a>
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
                        <h4 class="card-title"><b>10 Siswa Datang Tercepat</b></h4>
                        <p class="card-category"><?= $dateNow; ?></p>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($siswaTercepat)) : ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($siswaTercepat as $i => $s) : ?>
                                    <li class="list-group-item d-flex align-items-center">
                                        <b class="text-muted mr-3" style="width:20px;flex-shrink:0;"><?= $i + 1; ?></b>
                                        <?php if (!empty($s['foto'])) : ?>
                                            <img src="<?= esc($s['foto']); ?>" alt="" class="rounded-circle mr-3" style="width:44px;height:44px;object-fit:cover;flex-shrink:0;">
                                        <?php else : ?>
                                            <span class="rounded-circle bg-light d-flex align-items-center justify-content-center mr-3" style="width:44px;height:44px;flex-shrink:0;">
                                                <i class="material-icons text-muted">person</i>
                                            </span>
                                        <?php endif; ?>
                                        <div class="flex-grow-1" style="min-width:0;">
                                            <div class="text-truncate"><b><?= esc($s['nama_siswa']); ?></b></div>
                                            <small class="text-muted"><?= labelKelas($s['kelas'] ?? null, $s['jurusan'] ?? null); ?></small>
                                        </div>
                                        <span class="text-success d-flex align-items-center ml-2" style="flex-shrink:0;white-space:nowrap;">
                                            <i class="material-icons mr-1" style="font-size:16px;">schedule</i>
                                            <b><?= esc($s['jam_masuk']); ?></b>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else : ?>
                            <h4 class="text-center text-muted py-5">Belum ada siswa yang absen masuk hari ini</h4>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header card-header-danger">
                        <h4 class="card-title"><b>Siswa Terlambat</b></h4>
                        <p class="card-category"><?= $dateNow; ?></p>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($siswaTerlambat)) : ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($siswaTerlambat as $i => $s) : ?>
                                    <li class="list-group-item d-flex align-items-center">
                                        <b class="text-muted mr-3" style="width:20px;flex-shrink:0;"><?= $i + 1; ?></b>
                                        <?php if (!empty($s['foto'])) : ?>
                                            <img src="<?= esc($s['foto']); ?>" alt="" class="rounded-circle mr-3" style="width:44px;height:44px;object-fit:cover;flex-shrink:0;">
                                        <?php else : ?>
                                            <span class="rounded-circle bg-light d-flex align-items-center justify-content-center mr-3" style="width:44px;height:44px;flex-shrink:0;">
                                                <i class="material-icons text-muted">person</i>
                                            </span>
                                        <?php endif; ?>
                                        <div class="flex-grow-1" style="min-width:0;">
                                            <div class="text-truncate"><b><?= esc($s['nama_siswa']); ?></b></div>
                                            <small class="text-muted"><?= labelKelas($s['kelas'] ?? null, $s['jurusan'] ?? null); ?></small>
                                        </div>
                                        <span class="text-danger d-flex align-items-center ml-2" style="flex-shrink:0;white-space:nowrap;">
                                            <i class="material-icons mr-1" style="font-size:16px;">schedule</i>
                                            <b><?= esc($s['jam_masuk']); ?></b>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else : ?>
                            <h4 class="text-center text-success py-5">Tidak ada siswa yang terlambat hari ini</h4>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer text-center">
                        <a href="<?= base_url('admin/absen-siswa/terlambat'); ?>" class="btn btn-danger btn-sm">
                            Lihat Semua<?= $jumlahTerlambatSiswa > 10 ? " ({$jumlahTerlambatSiswa})" : ''; ?>
                        </a>
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