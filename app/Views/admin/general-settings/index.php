<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <?= view('admin/_messages'); ?>
                <div class="card">
                    <div class="card-header card-header-primary">
                        <h4 class="card-title"><b>Pengaturan</b></h4>
                    </div>
                    <div class="card-body mx-5 my-3">

                        <?php $readonly = !isSuperadmin(); ?>
                        <?php if ($readonly) : ?>
                            <div class="alert alert-info">Pengaturan ini hanya dapat diubah oleh superadmin.</div>
                        <?php endif; ?>
                        <form action="<?= base_url('admin/general-settings/update'); ?>" method="post" enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <div class="form-group mt-4">
                                <label for="logo">Logo</label>
                                <div style="margin-bottom: 10px; border: 1px solid #eee; padding: 10px; width: fit-content;">
                                    <img id="logo" src="<?= getLogo(); ?>" alt="logo" style="max-width: 160px; max-height: 160px; display: block;">
                                </div>
                                <?php if (!$readonly) : ?>
                                    <div class="display-block">
                                        <button type="button" onclick="$('#logo-upload').trigger('click');" class="btn btn-primary btn-sm btn-file-upload">
                                            Ganti
                                        </button>
                                        <input type="file" id="logo-upload" name="logo" size="40" accept="image/jpg,image/jpeg,image/png,image/gif,image/svg+xml" onchange="$('#upload-file-info1').html($(this).val().replace(/.*[\/\\]/, ''));">
                                        <span class="text-sm text-secondary">(.png, .jpg, .jpeg, .gif, .svg)</span>
                                    </div>
                                    <span class='label label-info' id="upload-file-info1"></span>
                                <?php endif; ?>
                            </div>

                            <div class="form-group mt-4">
                                <label for="school_name">Nama Sekolah</label>
                                <input type="text" id="school_name" class="form-control <?= invalidFeedback('school_name') ? 'is-invalid' : ''; ?>" name="school_name" placeholder="SMK 1 Indonesia" value="<?= $generalSettings->school_name; ?>" <?= $readonly ? 'disabled' : ''; ?> required>
                                <div class="invalid-feedback">
                                    <?= invalidFeedback('school_name'); ?>
                                </div>
                            </div>

                            <div class="form-group mt-4">
                                <label for="scan_subtitle">Subjudul Aplikasi</label>
                                <input type="text" id="scan_subtitle" class="form-control <?= invalidFeedback('scan_subtitle') ? 'is-invalid' : ''; ?>" name="scan_subtitle" placeholder="Absensi QR Code" value="<?= $generalSettings->scan_subtitle ?? 'Absensi QR Code'; ?>" <?= $readonly ? 'disabled' : ''; ?> required>
                                <small class="text-muted">Tampil di bawah nama sekolah pada halaman scan QR, dan sebagai judul kartu di halaman login.</small>
                                <div class="invalid-feedback">
                                    <?= invalidFeedback('scan_subtitle'); ?>
                                </div>
                            </div>

                            <div class="form-group mt-4">
                                <label for="school_year">Tahun Ajaran</label>
                                <input type="text" id="school_year" class="form-control <?= invalidFeedback('school_year') ? 'is-invalid' : ''; ?>" name="school_year" placeholder="2024/2025" value="<?= $generalSettings->school_year; ?>" <?= $readonly ? 'disabled' : ''; ?> required>
                                <div class="invalid-feedback">
                                    <?= invalidFeedback('school_year'); ?>
                                </div>
                            </div>

                            <div class="form-group mt-4">
                                <label for="batas_absen_masuk">Batas Waktu Absen Masuk</label>
                                <input type="time" id="batas_absen_masuk" class="form-control <?= invalidFeedback('batas_absen_masuk') ? 'is-invalid' : ''; ?>" name="batas_absen_masuk" value="<?= substr($generalSettings->batas_absen_masuk ?? '07:20:00', 0, 5); ?>" <?= $readonly ? 'disabled' : ''; ?> required>
                                <small class="text-muted">Siswa yang absen masuk melewati jam ini akan ditandai terlambat.</small>
                                <div class="invalid-feedback">
                                    <?= invalidFeedback('batas_absen_masuk'); ?>
                                </div>
                            </div>

                            <div class="form-group mt-4">
                                <label for="copyright">Copyright</label>
                                <input type="text" id="copyright" class="form-control <?= invalidFeedback('copyright') ? 'is-invalid' : ''; ?>" name="copyright" placeholder="@ 2024 All" value="<?= $generalSettings->copyright; ?>" <?= $readonly ? 'disabled' : ''; ?> required>
                                <div class="invalid-feedback">
                                    <?= invalidFeedback('copyright'); ?>
                                </div>
                            </div>

                            <?php if (!$readonly) : ?>
                                <button type="submit" class="btn btn-primary btn-block">Simpan</button>
                            <?php endif; ?>
                        </form>

                        <hr>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>