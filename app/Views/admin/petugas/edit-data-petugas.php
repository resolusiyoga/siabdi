<?= $this->extend('templates/admin_page_layout') ?>
<?= $this->section('content') ?>
<div class="content">
   <div class="container-fluid">
      <div class="row">
         <div class="col-lg-12 col-md-12">
            <div class="card">
               <div class="card-header card-header-primary">
                  <h4 class="card-title"><b>Form Edit Petugas</b></h4>

               </div>
               <div class="card-body mx-5 my-3">

                  <form action="<?= base_url('admin/petugas/edit'); ?>" method="post">
                     <?= csrf_field() ?>
                     <?php $validation = \Config\Services::validation(); ?>

                     <?php if (session()->getFlashdata('msg')) : ?>
                        <div class="pb-2">
                           <div class="alert alert-<?= session()->getFlashdata('error') == true ? 'danger' : 'success'  ?> ">
                              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                 <i class="material-icons">close</i>
                              </button>
                              <?= session()->getFlashdata('msg') ?>
                           </div>
                        </div>
                     <?php endif; ?>

                     <input type="hidden" name="id" value="<?= $data['id']; ?>">

                     <div class="form-group mt-4">
                        <label for="username">Username</label>
                        <input type="text" id="username" class="form-control <?= $validation->getError('username') ? 'is-invalid' : ''; ?>" name="username" placeholder="username123" value="<?= old('username') ?? $oldInput['username'] ?? $data['username'] ?>">
                        <div class="invalid-feedback">
                           <?= $validation->getError('username'); ?>
                        </div>
                     </div>

                     <div class="form-group mt-4">
                        <label for="email">Email</label>
                        <input type="email" id="email" class="form-control <?= $validation->getError('email') ? 'is-invalid' : ''; ?>" name="email" placeholder="email@example.com" value="<?= old('email') ?? $oldInput['email'] ?? $data['email'] ?>">
                        <div class="invalid-feedback">
                           <?= $validation->getError('email'); ?>
                        </div>
                     </div>

                     <div class="form-group mt-4">
                        <label for="password">Password baru</label>
                        <input type="password" id="password" class="form-control <?= $validation->getError('password') ? 'is-invalid' : ''; ?>" name="password">
                        <div class="invalid-feedback">
                           <?= $validation->getError('password'); ?>
                        </div>
                     </div>

                     <label for="role">Peran</label>
                     <select class="custom-select <?= $validation->getError('role') ? 'is-invalid' : ''; ?>" id="role" name="role" onchange="toggleKelasField()">
                        <option value="">--Pilih peran--</option>
                        <?php $selectedRole = old('role') ?? $oldInput['role'] ?? $data['role'] ?? 'guru'; ?>
                        <option value="superadmin" <?= $selectedRole == 'superadmin' ? 'selected' : ''; ?>>
                           Super Admin
                        </option>
                        <option value="guru" <?= $selectedRole == 'guru' ? 'selected' : ''; ?>>
                           Guru
                        </option>
                        <option value="wali_kelas" <?= $selectedRole == 'wali_kelas' ? 'selected' : ''; ?>>
                           Wali Kelas
                        </option>
                        <option value="siswa" <?= $selectedRole == 'siswa' ? 'selected' : ''; ?>>
                           Siswa
                        </option>
                        <option value="orangtua" <?= $selectedRole == 'orangtua' ? 'selected' : ''; ?>>
                           Orang Tua
                        </option>
                     </select>
                     <div class="invalid-feedback">
                        <?= $validation->getError('role'); ?>
                     </div>

                     <div class="form-group mt-4" id="kelasFieldWrapper" style="display: none;">
                        <label for="id_kelas">Kelas / Lokal</label>
                        <?php $selectedKelas = old('id_kelas') ?? $oldInput['id_kelas'] ?? $data['id_kelas'] ?? ''; ?>
                        <select class="custom-select <?= $validation->getError('id_kelas') ? 'is-invalid' : ''; ?>" id="id_kelas" name="id_kelas">
                           <option value="">--Pilih kelas--</option>
                           <?php foreach ($kelas ?? [] as $value) : ?>
                              <option value="<?= $value['id_kelas']; ?>" <?= $selectedKelas == $value['id_kelas'] ? 'selected' : ''; ?>>
                                 <?= $value['kelas'] . '.' . $value['jurusan']; ?>
                              </option>
                           <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">
                           <?= $validation->getError('id_kelas'); ?>
                        </div>
                     </div>

                     <button type="submit" class="btn btn-primary btn-block mt-3">Simpan</button>
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