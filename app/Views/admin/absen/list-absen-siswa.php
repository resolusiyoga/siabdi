<?php helper('datatable'); ?>
<div class="card-body">
    <div class="row">
        <div class="col-auto me-auto">
            <div class="pt-3 pl-3">
                <h4><b>Absen Siswa</b></h4>
                <p>Daftar siswa muncul disini</p>
            </div>
        </div>
        <div class="col">
            <a href="#" class="btn btn-primary pl-3 mr-3 mt-3" onclick="onDateChange()" data-toggle="tab">
                <i class="material-icons mr-2">refresh</i> Refresh
            </a>
        </div>
    </div>

    <div id="dataSiswa" class="card-body table-responsive pb-5">
        <?php if (!empty($data)) : ?>
            <table class="table table-hover" id="tableAbsenSiswa">
                <thead class="text-primary">
                    <th><b>No.</b></th>
                    <th><b>NIS</b></th>
                    <th><b>Nama Siswa</b></th>
                    <th><b>Kelas</b></th>
                    <th><b>Kehadiran</b></th>
                    <th><b>Jam masuk</b></th>
                    <th><b>Jam dzuhur</b></th>
                    <th><b>Jam ashar</b></th>
                    <th><b>Jam pulang</b></th>
                    <th><b>Keterangan</b></th>
                    <th><b>Aksi</b></th>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    <?php
                    $batasAbsenMasuk = $generalSettings->batas_absen_masuk ?? '07:20:00';
                    ?>
                    <?php foreach ($data as $value) : ?>
                        <?php
                        $idKehadiran = intval($value['id_kehadiran'] ?? ($lewat ? 5 : 4));
                        $kehadiran = kehadiran($idKehadiran);
                        $terlambat = !empty($value['jam_masuk']) && $value['jam_masuk'] > $batasAbsenMasuk;
                        ?>
                        <tr>
                            <td><?= $no; ?></td>
                            <td><?= $value['nis']; ?></td>
                            <td><b><?= $value['nama_siswa']; ?></b></td>
                            <td><?= ($value['kelas'] ?? '-') . (!empty($value['jurusan']) ? '.' . $value['jurusan'] : ''); ?></td>
                            <td>
                                <p class="p-2 w-100 btn btn-<?= $kehadiran['color']; ?> text-center">
                                    <b><?= $kehadiran['text']; ?></b>
                                </p>
                            </td>
                            <td>
                                <b class="<?= $terlambat ? 'text-danger' : ''; ?>"><?= $value['jam_masuk'] ?? '-'; ?></b>
                                <?php if ($terlambat) : ?>
                                    <b class="text-danger">(Terlambat)</b>
                                <?php endif; ?>
                            </td>
                            <td><b><?= $value['jam_dzuhur'] ?? '-'; ?></b></td>
                            <td><b><?= $value['jam_ashar'] ?? '-'; ?></b></td>
                            <td><b><?= $value['jam_keluar'] ?? '-'; ?></b></td>
                            <td><?= $value['keterangan'] ?? '-'; ?></td>
                            <td>
                                <?php if (!$lewat) : ?>
                                    <button data-toggle="modal" data-target="#ubahModal" onclick="getDataKehadiran(<?= $value['id_presensi'] ?? '-1'; ?>, <?= $value['id_siswa']; ?>)" class="btn btn-info p-2" id="<?= $value['nis']; ?>">
                                        <i class="material-icons">edit</i>
                                        Edit
                                    </button>
                                <?php else : ?>
                                    <button class="btn btn-disabled p-2">No Action</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php $no++;
                    endforeach ?>
                </tbody>
            </table>
        <?php
        else :
        ?>
            <div class="row">
                <div class="col">
                    <h4 class="text-center text-danger">Data tidak ditemukan</h4>
                </div>
            </div>
        <?php
        endif; ?>
    </div>
</div>
<?php if (!empty($data)) : ?>
    <script>
        $('#tableAbsenSiswa').DataTable({
            destroy: true,
            columnDefs: [{
                orderable: false,
                targets: [0, 10]
            }],
            language: <?= json_encode(datatable_lang_id()) ?>
        });
    </script>
<?php endif; ?>

<?php
function kehadiran($kehadiran): array
{
    $text = '';
    $color = '';
    switch ($kehadiran) {
        case 1:
            $color = 'success';
            $text = 'Hadir';
            break;
        case 2:
            $color = 'warning';
            $text = 'Sakit';
            break;
        case 3:
            $color = 'info';
            $text = 'Izin';
            break;
        case 4:
            $color = 'danger';
            $text = 'Tanpa keterangan';
            break;
        case 5:
        default:
            $color = 'disabled';
            $text = 'Belum tersedia';
            break;
    }

    return ['color' => $color, 'text' => $text];
}
?>