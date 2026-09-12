<?php

namespace App\Models;

class UploadModel extends BaseModel
{
    protected $jpgQuality;
    protected $webpQuality;
    protected $imgExt;

    public function __construct()
    {
        parent::__construct();
        $this->jpgQuality = 85;
        $this->webpQuality = 80;
        $this->imgExt = '.jpg';
    }

    //upload file
    private function upload($inputName, $directory, $namePrefix, $allowedExtensions = null)
    {
        if ($allowedExtensions != null && is_array($allowedExtensions) && !empty($allowedExtensions[0])) {
            if (!$this->checkAllowedFileTypes($inputName, $allowedExtensions)) {
                return null;
            }
        }
        $file = $this->request->getFile($inputName);
        if (!empty($file) && !empty($file->getName())) {
            $orjName = $file->getName();
            $name = pathinfo($orjName, PATHINFO_FILENAME);
            $ext = pathinfo($orjName, PATHINFO_EXTENSION);
            $uniqueName = $namePrefix . generateToken(true) . '.' . $ext;
            if (!$file->hasMoved()) {
                if ($file->move(FCPATH . $directory, $uniqueName)) {
                    return ['name' => $uniqueName, 'orjName' => $orjName, 'path' => $directory . $uniqueName, 'ext' => $ext];
                }
            }
        }
        return null;
    }

    //upload temp file
    public function uploadTempFile($inputName, $isImage = false)
    {
        $allowedExtensions = array();
        if ($isImage) {
            $allowedExtensions = ['jpg', 'jpeg', 'webp', 'png', 'gif'];
        }
        return $this->upload($inputName, 'uploads/tmp/', 'temp_', $allowedExtensions);
    }

    //logo upload
    public function uploadLogo($inputName)
    {
        return $this->upload($inputName, "uploads/logo/", "logo_", ['jpg', 'jpeg', 'png', 'gif', 'svg']);
    }

     //upload CSV file
     public function uploadCSVFile($inputName)
     {
         return $this->upload($inputName, 'uploads/tmp/', 'temp_', ['csv']);
     }

    //save foto siswa dari data URI base64 (hasil crop/edit di sisi klien)
    public function uploadFotoSiswaBase64(?string $dataUri, string $nama = '', string $nis = '')
    {
        if (empty($dataUri) || !preg_match('/^data:image\/(jpe?g|png);base64,(.+)$/', $dataUri, $m)) {
            return null;
        }

        $ext = $m[1] === 'png' ? 'png' : 'jpg';
        $binary = base64_decode($m[2]);
        if ($binary === false) {
            return null;
        }

        $directory = 'uploads/foto-siswa/';
        if (!file_exists(FCPATH . $directory)) {
            mkdir(FCPATH . $directory, recursive: true);
        }

        $namaSlug = trim(preg_replace('/[^A-Za-z0-9]+/', '-', $nama), '-');
        $nisSlug = trim(preg_replace('/[^A-Za-z0-9]+/', '-', $nis), '-');
        $uniqueName = trim($namaSlug . '-' . $nisSlug, '-') . '.' . $ext;
        if ($uniqueName === '.' . $ext) {
            $uniqueName = 'foto_' . generateToken(true) . '.' . $ext;
        }
        file_put_contents(FCPATH . $directory . $uniqueName, $binary);

        return ['name' => $uniqueName, 'path' => $directory . $uniqueName, 'ext' => $ext];
    }

    //check allowed file types
    public function checkAllowedFileTypes($fileName, $allowedTypes)
    {
        if (!isset($_FILES[$fileName])) {
            return false;
        }
        if (empty($_FILES[$fileName]['name'])) {
            return false;
        }

        $ext = pathinfo($_FILES[$fileName]['name'], PATHINFO_EXTENSION);
        if (!empty($ext)) {
            $ext = strtolower($ext);
        }
        $extArray = array();
        if (!empty($allowedTypes) && is_array($allowedTypes)) {
            foreach ($allowedTypes as $item) {
                if (!empty($item)) {
                    $item = trim($item, '"');
                }
                if (!empty($item)) {
                    $item = trim($item, "'");
                }
                array_push($extArray, $item);
            }
        }
        if (!empty($extArray) && in_array($ext, $extArray)) {
            return true;
        }
        return false;
    }
}
