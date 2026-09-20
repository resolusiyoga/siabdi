<?php
/**
 * Toggle Jenis Kelamin (dipakai form tambah & edit siswa).
 *
 * @var string|null $jkNilai nilai saat ini: '1'/'2' atau 'Laki-laki'/'Perempuan'
 * @var \CodeIgniter\Validation\Validation $validation
 */
$jkNilai = $jkNilai ?? null;
$jkLaki = in_array($jkNilai, ['1', 'Laki-laki'], true);
$jkPerempuan = in_array($jkNilai, ['2', 'Perempuan'], true);
?>
<style>
   /* Toggle segmented sederhana (bukan radio bawaan Bootstrap): dua
      pilihan besar yang mudah disentuh, jelas mana yang aktif. */
   .jk-toggle {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
   }

   /* .form-control dipertahankan hanya sebagai kait CSS supaya pesan error
      tetap muncul lewat aturan bawaan Bootstrap
      ".form-control.is-invalid ~ .invalid-feedback"; gaya kotaknya sendiri
      (border/padding) dinetralkan di sini -- sebelumnya inilah yang
      membuat garis aneh melintasi kedua pilihan. */
   .jk-toggle.form-control {
      border: 0;
      padding: 0;
      height: auto;
      background: transparent;
   }

   .jk-toggle__opsi {
      position: relative;
      flex: 1 1 140px;
      margin: 0;
      cursor: pointer;
   }

   .jk-toggle__opsi input {
      position: absolute;
      opacity: 0;
      width: 1px;
      height: 1px;
   }

   .jk-toggle__label {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      padding: 10px 12px;
      border: 1px solid #d2d2d2;
      border-radius: 6px;
      font-weight: 600;
      color: #555;
      transition: background-color .15s ease, border-color .15s ease, color .15s ease;
   }

   .jk-toggle__label i {
      font-size: 20px;
   }

   .jk-toggle__opsi:hover .jk-toggle__label {
      border-color: #1c655a;
   }

   .jk-toggle__opsi input:checked + .jk-toggle__label {
      background: #1c655a;
      border-color: #1c655a;
      color: #fff;
   }

   .jk-toggle__opsi input:focus + .jk-toggle__label,
   .jk-toggle__opsi input:focus-visible + .jk-toggle__label {
      outline: 2px solid #1c655a;
      outline-offset: 2px;
   }
</style>
<!-- BUKAN .form-group: kelas itu diberi margin-top 8px lewat aturan tema Material (".form-group { margin: 8px 0 0 }"), sedangkan kolom Kelas di sebelahnya polos tanpa pembungkus sama sekali -- kalau dipakai di sini label & kontrolnya turun 8px dan tidak sejajar. -->
<div>
   <label for="jk">Jenis Kelamin</label>
   <div class="jk-toggle form-control <?= $validation->getError('jk') ? 'is-invalid' : ''; ?>" id="jk">
      <label class="jk-toggle__opsi">
         <input type="radio" name="jk" value="1" <?= $jkLaki ? 'checked' : ''; ?>>
         <span class="jk-toggle__label"><i class="material-icons">male</i>Laki-laki</span>
      </label>
      <label class="jk-toggle__opsi">
         <input type="radio" name="jk" value="2" <?= $jkPerempuan ? 'checked' : ''; ?>>
         <span class="jk-toggle__label"><i class="material-icons">female</i>Perempuan</span>
      </label>
   </div>
   <div class="invalid-feedback">
      <?= $validation->getError('jk'); ?>
   </div>
</div>
