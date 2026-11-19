<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <?= view('admin/_messages'); ?>
                <div class="row">
                    <div class="col-12 col-xl-8">
                        <div class="card">
                            <div class="card-header card-header-tabs card-header-info">
                                <div class="nav-tabs-navigation">
                                    <div class="row">
                                        <div class="col-md-4 col-lg-5">
                                            <h4 class="card-title"><b>Bulk Post Upload Petugas</b></h4>
                                            <p class="card-category">Import akun petugas lewat CSV</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <div class="dm-uploader-container">
                                        <div id="drag-and-drop-zone" class="dm-uploader p-2">
                                            <p class="dm-upload-icon">
                                                <i class="material-icons">cloud_upload</i>
                                            </p>
                                            <h3 class="text-muted">Drag &amp; drop files here</h3>
                                            <div class="btn btn-primary mb-5">
                                                <span>Open the file Browser</span>
                                                <input type="file" title='Click to add Files' />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="row">
                                            <div id="csv_upload_spinner" class="csv-upload-spinner">
                                                <strong class="text-csv-importing">Importing Petugas...</strong>
                                                <strong class="text-csv-import-completed">completed!</strong>
                                                <div class="spinner-bounce">
                                                    <div class="bounce1"></div>
                                                    <div class="bounce2"></div>
                                                    <div class="bounce3"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="row">
                                            <div class="csv-uploaded-files-container">
                                                <ul id="csv_uploaded_files" class="list-group csv-uploaded-files"></ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-4">
                        <div class="card">
                            <div class="card-header card-header-tabs card-header-info">
                                <h4 class="card-title"><b>Help Documents</b></h4>
                                <p class="card-category">dokumen untuk menyiapkan CSV-mu</p>
                            </div>
                            <div class="card-body">
                                <form action="<?= base_url('admin/petugas/downloadCSVFilePost'); ?>" method="post">
                                    <?= csrf_field(); ?>
                                    <button type="button" class="btn btn-success btn-block" data-toggle="modal" data-target="#modalKelasPetugas">List Kelas</button>
                                    <button class="btn btn-success btn-block" name="submit" value="csv_petugas_template">Download CSV Template</button>
                                </form>
                                <!-- kolom id_kelas hanya dipakai kalau role=wali_kelas; untuk
                                     peran lain boleh dikosongkan di CSV -->
                                <p class="text-muted small mt-3 mb-0">
                                    Template yang diunduh hanya berisi baris header -- isi dulu
                                    dengan baris data (satu baris per petugas) sebelum diunggah
                                    kembali di sini. Mengunggah template kosong apa adanya tidak
                                    akan mengimpor apa pun.<br><br>
                                    Kolom <b>role</b> diisi salah satu:
                                    superadmin, guru, wali_kelas, pegawai, kepala_madrasah, siswa, atau orangtua.
                                    Kolom <b>id_kelas</b> hanya wajib diisi kalau role-nya wali_kelas.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalKelasPetugas" tabindex="-1" aria-labelledby="modalKelasPetugasTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalKelasPetugasTitle">List Kelas</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <table class="table table-hover">
                        <thead class="text-primary">
                            <th><b>ID</b></th>
                            <th><b>Kelas / Tingkat</b></th>
                            <th><b>Jurusan</b></th>
                        </thead>
                        <tbody>
                            <?php foreach ($kelas as $value) : ?>
                                <tr>
                                    <td><?= $value['id_kelas']; ?></td>
                                    <td><b><?= $value['kelas']; ?></b></td>
                                    <td><?= $value['jurusan']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(function() {
        $('#drag-and-drop-zone').dmUploader({
            url: '<?= base_url("admin/petugas/generateCSVObjectPost"); ?>',
            multiple: false,
            extFilter: ["csv"],
            extraData: function(id) {
                return {
                    '<?= csrf_token() ?>': '<?= csrf_hash(); ?>'
                };
            },
            onDragEnter: function() {
                this.addClass('active');
            },
            onDragLeave: function() {
                this.removeClass('active');
            },
            onNewFile: function(id, file) {
                $("#csv_upload_spinner").show();
                $("#csv_upload_spinner .spinner-bounce").show();
                $("#csv_upload_spinner .text-csv-importing").show();
                $("#csv_upload_spinner .text-csv-import-completed").hide();
                $("#csv_uploaded_files").empty();
            },
            onUploadSuccess: function(id, response) {
                var numberOfItems = 0;
                var txtFileName = "";
                try {
                    var obj = JSON.parse(response);
                    if (obj.result == 1) {
                        numberOfItems = obj.numberOfItems;
                        txtFileName = obj.txtFileName;
                        if (numberOfItems > 0) {
                            addCSVItem(numberOfItems, txtFileName, 1);
                        } else {
                            // CSV terbaca tapi tidak ada baris data (mis. yang
                            // diunggah cuma baris header/template kosong)
                            $("#csv_upload_spinner").hide();
                            alert("CSV tidak berisi baris data. Pastikan file yang diunggah sudah diisi (bukan cuma baris header template kosong).");
                        }
                    } else {
                        // generateCSVObjectPost mengembalikan result=0 tanpa data
                        // lebih lanjut -- paling sering karena baris data kosong,
                        // atau sesi login sudah habis sehingga ditolak sebagai
                        // bukan superadmin
                        $("#csv_upload_spinner").hide();
                        alert("Gagal memproses CSV. Pastikan file berisi data (bukan cuma baris header), dan sesi login belum habis.");
                    }
                } catch (e) {
                    alert("Invalid CSV file! Make sure there are no double quotes in your content. Double quotes can brake the CSV structure.");
                }
            }
        });
    });

    function addCSVItem(numberOfItems, txtFileName, index) {
        if (index <= numberOfItems) {
            var data = {
                'txtFileName': txtFileName,
                'index': index
            };
            $.ajax({
                type: "POST",
                url: '<?= base_url("admin/petugas/importCSVItemPost"); ?>',
                data: setAjaxData(data),
                success: function(response) {
                    var objSub = JSON.parse(response);
                    if (objSub.result == 1) {
                        $("#csv_uploaded_files").prepend('<li class="list-group-item list-group-item-success">&nbsp;' + objSub.index + '.&nbsp;' + objSub.petugas.username + '&nbsp;-&nbsp;' + (objSub.petugas.nama || '') + '</li>');
                    } else {
                        $("#csv_uploaded_files").prepend('<li class="list-group-item list-group-item-danger">&nbsp;' + objSub.index + '. Gagal diimpor (data tidak lengkap atau email/username sudah terdaftar)</li>');
                    }
                    if (objSub.index == numberOfItems) {
                        $("#csv_upload_spinner .text-csv-importing").hide();
                        $("#csv_upload_spinner .spinner-bounce").hide();
                        $("#csv_upload_spinner .text-csv-import-completed").css('display', 'block');
                    }
                    index = index + 1;
                    addCSVItem(numberOfItems, txtFileName, index);
                },
                error: function(xhr, status, thrown) {
                    swal({
                        text: 'Ada Kesalahan Pada CSV silahkan Cek Log',
                        icon: "warning"
                    }).then(function(willDelete) {
                        if (willDelete) {
                            location.reload();
                        }
                    });
                },
            });
        }
    }
</script>
<?= $this->endSection() ?>
