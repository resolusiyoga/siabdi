<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header card-header-info mb-48">
                        <h4 class="card-title">Register Petugas</h4>
                        <p class="card-category">Buat akun petugas</p>
                    </div>
                    <div class="card-body mx-5 my-3">
                        <?php $validation = $validation ?? \Config\Services::validation(); ?>

                        <form action="<?= base_url('admin/petugas/register'); ?>" method="post">
                            <?= csrf_field() ?>

                            <div class="form-group mt-4">
                                <label for="email">Email</label>
                                <input type="email" id="email" class="form-control <?= $validation->getError('email') ? 'is-invalid' : ''; ?>" name="email" placeholder="example@email.com" value="<?= old('email') ?? $oldInput['email'] ?? ''; ?>">
                                <div class="invalid-feedback">
                                    <?= $validation->getError('email'); ?>
                                </div>
                            </div>

                            <div class="form-group mt-4">
                                <label for="username">Username</label>
                                <input type="text" id="username" class="form-control <?= $validation->getError('username') ? 'is-invalid' : ''; ?>" name="username" placeholder="yourusername" value="<?= old('username') ?? $oldInput['username'] ?? ''; ?>">
                                <div class="invalid-feedback">
                                    <?= $validation->getError('username'); ?>
                                </div>
                            </div>

                            <div class="form-group mt-4">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" class="form-control <?= $validation->getError('password') ? 'is-invalid' : ''; ?>" autocomplete="off">
                                <div class="invalid-feedback">
                                    <?= $validation->getError('password'); ?>
                                </div>
                            </div>

                            <div class="form-group mt-4">
                                <label for="pass_confirm">Ulangi Password</label>
                                <input type="password" id="pass_confirm" name="pass_confirm" class="form-control <?= $validation->getError('pass_confirm') ? 'is-invalid' : ''; ?>" autocomplete="off">
                                <div class="invalid-feedback">
                                    <?= $validation->getError('pass_confirm'); ?>
                                </div>
                            </div>

                            <div class="form-group mt-4">
                                <label for="role">Peran</label>
                                <?php $selectedRole = old('role') ?? $oldInput['role'] ?? ''; ?>
                                <select class="custom-select <?= $validation->getError('role') ? 'is-invalid' : ''; ?>" id="role" name="role" onchange="toggleKelasField()">
                                    <option value="">--Pilih peran--</option>
                                    <option value="superadmin" <?= $selectedRole == 'superadmin' ? 'selected' : ''; ?>>Super Admin</option>
                                    <option value="guru" <?= $selectedRole == 'guru' ? 'selected' : ''; ?>>Guru</option>
                                    <option value="wali_kelas" <?= $selectedRole == 'wali_kelas' ? 'selected' : ''; ?>>Wali Kelas</option>
                                    <option value="siswa" <?= $selectedRole == 'siswa' ? 'selected' : ''; ?>>Siswa</option>
                                    <option value="orangtua" <?= $selectedRole == 'orangtua' ? 'selected' : ''; ?>>Orang Tua</option>
                                </select>
                                <div class="invalid-feedback">
                                    <?= $validation->getError('role'); ?>
                                </div>
                            </div>

                            <div class="form-group mt-4" id="kelasFieldWrapper" style="display: none;">
                                <label for="id_kelas">Kelas / Lokal</label>
                                <?php $selectedKelas = old('id_kelas') ?? $oldInput['id_kelas'] ?? ''; ?>
                                <select class="custom-select <?= $validation->getError('id_kelas') ? 'is-invalid' : ''; ?>" id="id_kelas" name="id_kelas">
                                    <option value="">--Pilih kelas--</option>
                                    <?php foreach ($kelas ?? [] as $value) : ?>
                                        <option value="<?= $value['id_kelas']; ?>" <?= $selectedKelas == $value['id_kelas'] ? 'selected' : ''; ?>>
                                            <?= labelKelas($value['kelas'], $value['jurusan']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    <?= $validation->getError('id_kelas'); ?>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-info btn-block mt-3">Register</button>
                        </form>

                        <script>
                            function toggleKelasField() {
                                var role = document.getElementById('role').value;
                                var wrapper = document.getElementById('kelasFieldWrapper');
                                wrapper.style.display = role === 'wali_kelas' ? 'block' : 'none';
                            }
                            toggleKelasField();
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
