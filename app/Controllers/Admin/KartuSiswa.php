<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\KartuRenderer;
use App\Models\KartuTemplateModel;
use App\Models\KelasModel;
use App\Models\SiswaModel;
use App\Models\UploadModel;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;

/**
 * Menu Kartu Siswa: mengatur base template (SVG) & posisi elemen data,
 * lalu mencetak/mengunduh kartu siswa ukuran 53,98 x 85,60 mm.
 */
class KartuSiswa extends BaseController
{
   protected KartuTemplateModel $kartuModel;
   protected SiswaModel $siswaModel;
   protected KelasModel $kelasModel;

   /** Folder penyimpanan base template SVG */
   private const DIR_TEMPLATE = 'uploads/kartu/';

   /** Kelas yang benar-benar dipakai menyaring siswa (null = seluruh kelas) */
   private ?string $kelasTersaring = null;

   public function __construct()
   {
      $this->kartuModel = new KartuTemplateModel();
      $this->siswaModel = new SiswaModel();
      $this->kelasModel = new KelasModel();
   }

   public function index()
   {
      if (!$this->bolehCetak()) {
         session()->setFlashdata(['msg' => 'Aksi ini tidak diizinkan', 'error' => true]);
         return redirect()->to('/admin/dashboard');
      }

      $template = $this->kartuModel->getTemplateAktif();
      $kelasWali = currentUserRole() === 'wali_kelas' ? currentUserKelas() : null;

      $data = [
         'title'       => 'Kartu Siswa',
         'ctx'         => 'kartu',
         'template'    => $template,
         'elemen'      => KartuTemplateModel::ELEMEN,
         'lebarMm'     => KartuTemplateModel::LEBAR_MM,
         'tinggiMm'    => KartuTemplateModel::TINGGI_MM,
         'layoutDefault' => $this->kartuModel->layoutDefault(),
         'kelas'       => $this->kelasModel->getDataKelas(),
         'kelasWali'   => $kelasWali,
         'bolehUbah'   => isSuperadmin(),
         'contoh'      => $this->dataContoh(),
         'daftarSiswa' => $this->daftarSiswaRingkas(),
      ];

      return view('admin/kartu/index', $data);
   }

   /**
    * Unggah / ganti base template SVG (sisi depan &/atau belakang).
    */
   public function simpanTemplate()
   {
      if (!isSuperadmin()) {
         session()->setFlashdata(['msg' => 'Aksi ini khusus untuk superadmin', 'error' => true]);
         return redirect()->to('/admin/kartu');
      }

      $template = $this->kartuModel->getTemplateAktif();
      $upload = new UploadModel();
      $data = [];

      if (!file_exists(FCPATH . self::DIR_TEMPLATE)) {
         mkdir(FCPATH . self::DIR_TEMPLATE, recursive: true);
      }

      foreach (['depan' => 'svg_depan', 'belakang' => 'svg_belakang'] as $sisi => $kolom) {
         $berkas = $this->request->getFile('svg_' . $sisi);

         if (empty($berkas) || $berkas->getName() === '') {
            continue;
         }

         if (!$berkas->isValid()) {
            session()->setFlashdata(['msg' => "Gagal mengunggah template sisi $sisi: " . $berkas->getErrorString(), 'error' => true]);
            return redirect()->to('/admin/kartu');
         }

         $ext = strtolower($berkas->getClientExtension());
         if (!in_array($ext, ['svg', 'png', 'jpg', 'jpeg'], true)) {
            session()->setFlashdata(['msg' => "Format template sisi $sisi harus SVG (atau PNG/JPG)", 'error' => true]);
            return redirect()->to('/admin/kartu');
         }

         $namaBaru = 'kartu-' . $sisi . '-' . generateToken(true) . '.' . $ext;
         if (!$berkas->move(FCPATH . self::DIR_TEMPLATE, $namaBaru)) {
            session()->setFlashdata(['msg' => "Gagal menyimpan template sisi $sisi", 'error' => true]);
            return redirect()->to('/admin/kartu');
         }

         $data[$kolom] = self::DIR_TEMPLATE . $namaBaru;

         // buang berkas lama supaya folder tidak menumpuk
         $lama = $template[$kolom] ?? null;
         if (!empty($lama) && $lama !== $data[$kolom] && file_exists(FCPATH . $lama)) {
            @unlink(FCPATH . $lama);
         }
      }

      $nama = trim((string) $this->request->getVar('nama'));
      if ($nama !== '') {
         $data['nama'] = mb_substr($nama, 0, 100);
      }

      if (empty($data)) {
         session()->setFlashdata(['msg' => 'Tidak ada perubahan template', 'error' => true]);
         return redirect()->to('/admin/kartu');
      }

      $this->kartuModel->simpan($data);
      session()->setFlashdata(['msg' => 'Base template kartu berhasil disimpan', 'error' => false]);

      return redirect()->to('/admin/kartu');
   }

   /**
    * Hapus base template pada salah satu sisi.
    */
   public function hapusTemplate($sisi = null)
   {
      if (!isSuperadmin()) {
         session()->setFlashdata(['msg' => 'Aksi ini khusus untuk superadmin', 'error' => true]);
         return redirect()->to('/admin/kartu');
      }

      if (!in_array($sisi, KartuTemplateModel::SISI, true)) {
         session()->setFlashdata(['msg' => 'Sisi kartu tidak dikenal', 'error' => true]);
         return redirect()->to('/admin/kartu');
      }

      $kolom = 'svg_' . $sisi;
      $template = $this->kartuModel->getTemplateAktif();

      if (!empty($template[$kolom]) && file_exists(FCPATH . $template[$kolom])) {
         @unlink(FCPATH . $template[$kolom]);
      }

      $this->kartuModel->simpan([$kolom => null]);
      session()->setFlashdata(['msg' => "Base template sisi $sisi dihapus", 'error' => false]);

      return redirect()->to('/admin/kartu');
   }

   /**
    * Simpan posisi & gaya elemen (dikirim editor via AJAX).
    */
   public function simpanLayout()
   {
      if (!isSuperadmin()) {
         return $this->response->setStatusCode(403)->setJSON([
            'sukses' => false,
            'pesan'  => 'Aksi ini khusus untuk superadmin',
         ]);
      }

      // Editor mengirim layout sebagai field form 'layout'; badan JSON mentah
      // juga diterima. getJSON() sengaja tidak dipakai karena melempar
      // HTTPException bila badan permintaan bukan JSON.
      $layout = json_decode((string) $this->request->getVar('layout'), true);

      if (!is_array($layout)) {
         $layout = json_decode((string) $this->request->getBody(), true);
      }

      if (!is_array($layout)) {
         return $this->response->setStatusCode(400)->setJSON([
            'sukses' => false,
            'pesan'  => 'Data tata letak tidak valid',
         ]);
      }

      $this->kartuModel->simpanLayout($layout);

      return $this->response->setJSON([
         'sukses' => true,
         'pesan'  => 'Tata letak tersimpan',
         'layout' => $this->kartuModel->getTemplateAktif()['layout'],
      ]);
   }

   /**
    * Kembalikan tata letak ke bawaan.
    */
   public function resetLayout()
   {
      if (!isSuperadmin()) {
         session()->setFlashdata(['msg' => 'Aksi ini khusus untuk superadmin', 'error' => true]);
         return redirect()->to('/admin/kartu');
      }

      $this->kartuModel->simpanLayout($this->kartuModel->layoutDefault());
      session()->setFlashdata(['msg' => 'Tata letak dikembalikan ke bawaan', 'error' => false]);

      return redirect()->to('/admin/kartu');
   }

   /**
    * Halaman siap cetak (Ctrl+P / Simpan sebagai PDF) berisi kartu siswa
    * satu kelas, seluruh siswa, atau satu siswa tertentu.
    */
   public function cetak()
   {
      if (!$this->bolehCetak()) {
         session()->setFlashdata(['msg' => 'Aksi ini tidak diizinkan', 'error' => true]);
         return redirect()->to('/admin/dashboard');
      }

      $idKelas = $this->request->getVar('id_kelas') ?: null;
      $idSiswa = $this->request->getVar('id_siswa') ?: null;

      // wali kelas hanya boleh mencetak kelasnya sendiri
      if (currentUserRole() === 'wali_kelas') {
         $kelasWali = currentUserKelas();
         if (empty($kelasWali)) {
            session()->setFlashdata(['msg' => 'Kelas anda tidak ditemukan', 'error' => true]);
            return redirect()->to('/admin/kartu');
         }
         $idKelas = $kelasWali['id_kelas'];
         $idSiswa = null;
      }

      if ($idSiswa) {
         $siswa = $this->siswaModel->getAllSiswaWithKelas();
         $siswa = array_values(array_filter($siswa, fn($s) => (string) $s['id_siswa'] === (string) $idSiswa));
      } else {
         $siswa = $idKelas
            ? $this->siswaModel->getSiswaByKelas($idKelas)
            : $this->siswaModel->getAllSiswaWithKelas();
      }

      if (empty($siswa)) {
         session()->setFlashdata(['msg' => 'Data siswa tidak ditemukan', 'error' => true]);
         return redirect()->to('/admin/kartu');
      }

      $sisi = $this->request->getVar('sisi');
      $sisi = in_array($sisi, ['depan', 'belakang', 'keduanya'], true) ? $sisi : 'keduanya';

      $template = $this->kartuModel->getTemplateAktif();

      $data = [
         'title'      => 'Cetak Kartu Siswa',
         'template'   => $template,
         'lebarMm'    => KartuTemplateModel::LEBAR_MM,
         'tinggiMm'   => KartuTemplateModel::TINGGI_MM,
         'sisiCetak'  => $sisi === 'keduanya' ? ['depan', 'belakang'] : [$sisi],
         'elemen'     => KartuTemplateModel::ELEMEN,
         'garisPotong' => (bool) $this->request->getVar('garis_potong'),
         'kartu'      => array_map(fn($s) => $this->dataKartu($s), $siswa),
      ];

      return view('admin/kartu/cetak', $data);
   }

   /**
    * Data satu siswa untuk pratinjau editor (foto & QR berupa data URI).
    */
   public function pratinjau()
   {
      if (!$this->bolehCetak()) {
         return $this->response->setStatusCode(403)->setJSON(['sukses' => false]);
      }

      $idSiswa = $this->request->getVar('id_siswa');
      $siswa = null;

      foreach ($this->siswaModel->getAllSiswaWithKelas() as $s) {
         if ((string) $s['id_siswa'] === (string) $idSiswa) {
            $siswa = $s;
            break;
         }
      }

      if (empty($siswa)) {
         return $this->response->setStatusCode(404)->setJSON([
            'sukses' => false,
            'pesan'  => 'Siswa tidak ditemukan',
         ]);
      }

      return $this->response->setJSON([
         'sukses' => true,
         'data'   => $this->dataKartu($siswa),
      ]);
   }

   /**
    * Daftar siswa ringkas (tanpa foto/QR) untuk kolom pencarian editor.
    */
   private function daftarSiswaRingkas(): array
   {
      $kelasWali = currentUserRole() === 'wali_kelas' ? currentUserKelas() : null;

      $siswa = $kelasWali
         ? $this->siswaModel->getSiswaByKelas($kelasWali['id_kelas'])
         : $this->siswaModel->getAllSiswaWithKelas();

      return array_map(fn($s) => [
         'id'    => $s['id_siswa'],
         'nama'  => $s['nama_siswa'],
         'nis'   => $s['nis'],
         'kelas' => labelKelas($s['kelas'] ?? null, $s['jurusan'] ?? null, ''),
      ], $siswa);
   }

   /**
    * Unduh kartu sebagai berkas PNG 300 dpi: satu siswa (satu berkas per
    * sisi) atau satu kelas/seluruh siswa dalam bentuk zip.
    */
   public function download()
   {
      if (!$this->bolehCetak()) {
         session()->setFlashdata(['msg' => 'Aksi ini tidak diizinkan', 'error' => true]);
         return redirect()->to('/admin/dashboard');
      }

      $sisiCetak = $this->sisiDiminta();

      try {
         $siswa = $this->siswaTerpilih();
      } catch (\RuntimeException $e) {
         session()->setFlashdata(['msg' => $e->getMessage(), 'error' => true]);
         return redirect()->to('/admin/kartu');
      }

      $template = $this->kartuModel->getTemplateAktif();
      $renderer = new KartuRenderer();

      // satu siswa, satu sisi -> langsung berkas PNG
      if (count($siswa) === 1 && count($sisiCetak) === 1) {
         $png = $renderer->render(
            $template['layout'],
            $sisiCetak[0],
            $this->dataKartu($siswa[0]),
            $template['svg_' . $sisiCetak[0]]
         );

         return $this->response
            ->setHeader('Content-Type', 'image/png')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $this->namaBerkas($siswa[0], $sisiCetak[0]) . '"')
            ->setBody($png);
      }

      $tmp = FCPATH . 'uploads/tmp/';
      if (!is_dir($tmp)) {
         mkdir($tmp, 0777, true);
      }

      $namaZip = 'kartu-siswa';
      if (count($siswa) === 1) {
         $namaZip .= '_' . $this->slug($siswa[0]['nama_siswa'] ?? '');
      } elseif ($this->kelasTersaring !== null && !empty($siswa[0]['kelas'])) {
         // nama kelas hanya ditempelkan bila unduhan memang disaring per kelas
         $namaZip .= '_' . $this->slug(labelKelas($siswa[0]['kelas'], $siswa[0]['jurusan'] ?? null, ''));
      }

      $output = $tmp . $namaZip . '.zip';

      $zip = new \ZipArchive();
      if ($zip->open($output, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
         session()->setFlashdata(['msg' => 'Gagal membuat berkas zip', 'error' => true]);
         return redirect()->to('/admin/kartu');
      }

      // PNG 600 dpi berukuran besar, jadi ditulis dulu ke berkas sementara:
      // addFromString menahan seluruh isi berkas di memori sampai zip ditutup.
      $sementara = [];

      foreach ($siswa as $s) {
         $data = $this->dataKartu($s);

         foreach ($sisiCetak as $sisi) {
            $nama = $this->namaBerkas($s, $sisi);
            $berkas = $tmp . 'kartu-' . bin2hex(random_bytes(6)) . '.png';

            file_put_contents(
               $berkas,
               $renderer->render($template['layout'], $sisi, $data, $template['svg_' . $sisi])
            );

            $zip->addFile($berkas, $nama);
            $sementara[] = $berkas;
         }
      }

      $zip->close();

      foreach ($sementara as $berkas) {
         @unlink($berkas);
      }

      return $this->response->download($output, null, true);
   }

   /**
    * Siswa yang diminta lewat parameter: satu siswa, satu kelas, atau semua.
    * Wali kelas selalu dibatasi pada kelasnya sendiri.
    *
    * @throws \RuntimeException bila data tidak ditemukan / tidak diizinkan
    */
   private function siswaTerpilih(): array
   {
      $idKelas = $this->request->getVar('id_kelas') ?: null;
      $idSiswa = $this->request->getVar('id_siswa') ?: null;

      $kelasWali = null;
      if (currentUserRole() === 'wali_kelas') {
         $kelasWali = currentUserKelas();
         if (empty($kelasWali)) {
            throw new \RuntimeException('Kelas anda tidak ditemukan');
         }
         $idKelas = $kelasWali['id_kelas'];
      }

      if ($idSiswa) {
         $semua = $this->siswaModel->getAllSiswaWithKelas();
         $siswa = array_values(array_filter(
            $semua,
            fn($s) => (string) $s['id_siswa'] === (string) $idSiswa
         ));

         // wali kelas tidak boleh mengunduh kartu siswa kelas lain
         if ($kelasWali && !empty($siswa) && (string) $siswa[0]['id_kelas'] !== (string) $kelasWali['id_kelas']) {
            throw new \RuntimeException('Siswa tersebut bukan siswa kelas anda');
         }
      } else {
         $this->kelasTersaring = $idKelas ? (string) $idKelas : null;

         $siswa = $idKelas
            ? $this->siswaModel->getSiswaByKelas($idKelas)
            : $this->siswaModel->getAllSiswaWithKelas();
      }

      if (empty($siswa)) {
         throw new \RuntimeException('Data siswa tidak ditemukan');
      }

      return $siswa;
   }

   /** @return string[] sisi kartu yang diminta */
   private function sisiDiminta(): array
   {
      $sisi = $this->request->getVar('sisi');
      $sisi = in_array($sisi, ['depan', 'belakang', 'keduanya'], true) ? $sisi : 'keduanya';

      return $sisi === 'keduanya' ? ['depan', 'belakang'] : [$sisi];
   }

   private function namaBerkas(array $siswa, string $sisi): string
   {
      $nama = $this->slug($siswa['nama_siswa'] ?? 'kartu');
      $nis = $this->slug((string) ($siswa['nis'] ?? ''));

      return trim($nama . '-' . $nis, '-') . '-' . $sisi . '.png';
   }

   private function slug(string $teks): string
   {
      return trim(preg_replace('/[^A-Za-z0-9]+/', '-', $teks), '-');
   }

   /**
    * Ambil daftar siswa satu kelas untuk dropdown "cetak per siswa".
    */
   public function siswaByKelas()
   {
      if (!$this->bolehCetak()) {
         return $this->response->setStatusCode(403)->setJSON([]);
      }

      $idKelas = $this->request->getVar('id_kelas');

      $siswa = $idKelas
         ? $this->siswaModel->getSiswaByKelas($idKelas)
         : $this->siswaModel->getAllSiswaWithKelas();

      return $this->response->setJSON(array_map(fn($s) => [
         'id_siswa'   => $s['id_siswa'],
         'nama_siswa' => $s['nama_siswa'],
         'nis'        => $s['nis'],
      ], $siswa));
   }

   /**
    * Nilai tiap elemen untuk satu siswa (foto & QR sudah jadi data URI,
    * supaya halaman cetak tidak bergantung request tambahan).
    */
   private function dataKartu(array $siswa): array
   {
      return [
         'foto'    => $this->fotoDataUri($siswa['foto'] ?? null),
         'qrcode'  => $this->qrDataUri((string) ($siswa['unique_code'] ?? '')),
         'nama'    => $siswa['nama_siswa'] ?? '',
         'nis'     => $siswa['nis'] ?? '',
         'nisn'    => $siswa['nisn'] ?? '',
         'kelas'   => labelKelas($siswa['kelas'] ?? null, $siswa['jurusan'] ?? null, ''),
         'sekolah' => $this->generalSettings->school_name ?? '',
         'tahun'   => $this->generalSettings->school_year ?? '',
      ];
   }

   /**
    * Contoh data untuk pratinjau di editor: pakai siswa pertama yang
    * punya foto agar pratinjau mendekati hasil cetak.
    */
   private function dataContoh(): array
   {
      $semua = $this->siswaModel->getAllSiswaWithKelas();

      foreach ($semua as $s) {
         if (!empty($s['foto']) && file_exists(FCPATH . $s['foto'])) {
            return $this->dataKartu($s);
         }
      }

      if (!empty($semua)) {
         return $this->dataKartu($semua[0]);
      }

      return $this->dataKartu([
         'foto'        => null,
         'unique_code' => 'CONTOH-KODE-UNIK',
         'nama_siswa'  => 'Nama Siswa Contoh',
         'nis'         => '1234567',
         'nisn'        => '0071234567',
         'kelas'       => 'X',
         'jurusan'     => 'BDP',
      ]);
   }

   private function fotoDataUri(?string $path): ?string
   {
      if (empty($path) || !file_exists(FCPATH . $path)) {
         return null;
      }

      $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
      $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';

      return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents(FCPATH . $path));
   }

   private function qrDataUri(string $kode): ?string
   {
      if ($kode === '') {
         return null;
      }

      $qr = QrCode::create($kode)
         ->setEncoding(new Encoding('UTF-8'))
         ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh())
         ->setSize(900)
         ->setMargin(0)
         ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin());

      return (new PngWriter())->write($qr)->getDataUri();
   }

   private function bolehCetak(): bool
   {
      return in_array(currentUserRole(), ['superadmin', 'wali_kelas'], true);
   }
}
