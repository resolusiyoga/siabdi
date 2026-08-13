<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter4.github.io/CodeIgniter4/
 */


//clean string
if (!function_exists('cleanStr')) {
    function cleanStr($str)
    {
        $str = strTrim($str);
        $str = removeSpecialCharacters($str);
        return esc($str);
    }
}

//clean number
if (!function_exists('cleanNumber')) {
    function cleanNumber($num)
    {
        $num = strTrim($num);
        $num = esc($num);
        if (empty($num)) {
            return 0;
        }
        return intval($num);
    }
}

//clean number
if (!function_exists('clrQuotes')) {
    function clrQuotes($str)
    {
        $str = strReplace('"', '', $str);
        $str = strReplace("'", '', $str);
        return $str;
    }
}

/**
 * Get validation rules
 *
 * @return Rules
 */
if (!function_exists('getValRules')) {
    function getValRules($val)
    {
        $rules = $val->getRules();
        $newRules = array();
        if (!empty($rules)) {
            foreach ($rules as $key => $rule) {
                $newRules[$key] = [
                    'label' => $rule['label'],
                    'rules' => $rule['rules'],
                    'errors' => [
                        'required' => lang("Validation.form_validation_required"),
                        'min_length' => lang("Validation.form_validation_min_length"),
                        'max_length' => lang("Validation.form_validation_max_length"),
                        'matches' => lang("Validation.form_validation_matches"),
                        'is_unique' => lang("Validation.form_validation_is_unique")
                    ]
                ];
            }
        }
        return $newRules;
    }
}

/**
 * STR TRIM
 *
 * TRIM string
 *
 * @return string
 */
if (!function_exists('strTrim')) {
    function strTrim($str)
    {
        if (!empty($str)) {
            return trim($str);
        }
    }
}

/**
 * STR Replace
 *
 * Replace string
 *
 * @return string
 */
if (!function_exists('strReplace')) {
    function strReplace($search, $replace, $str)
    {
        if (!empty($str)) {
            return str_replace($search, $replace, $str);
        }
    }
}

/**
 * POST Request
 *
 * Sanitaze Input Post
 *
 * @return string
 */
if (!function_exists('inputPost')) {
    function inputPost($input_name, $removeForbidden = false)
    {
        $input = \Config\Services::request()->getPost($input_name);
        if (!is_array($input)) {
            $input = strTrim($input);
        }
        if ($removeForbidden) {
            $input = removeForbiddenCharacters($input);
        }
        return $input;
    }
}

/**
 * GET Request
 *
 * Sanitaze Input GET
 *
 * @return string
 */
if (!function_exists('inputGet')) {
    function inputGet($input_name, $removeForbidden = false)
    {
        $input = \Config\Services::request()->getGet($input_name);
        if (!is_array($input)) {
            $input = strTrim($input);
        }
        if ($removeForbidden) {
            $input = removeForbiddenCharacters($input);
        }
        return $input;
    }
}

/**
 * remove forbidden characters
 *
 *
 * @return string
 */
if (!function_exists('removeForbiddenCharacters')) {
    function removeForbiddenCharacters($str)
    {
        $str = strTrim($str);
        $str = strReplace(';', '', $str);
        $str = strReplace('"', '', $str);
        $str = strReplace('$', '', $str);
        $str = strReplace('%', '', $str);
        $str = strReplace('*', '', $str);
        $str = strReplace('/', '', $str);
        $str = strReplace('\'', '', $str);
        $str = strReplace('<', '', $str);
        $str = strReplace('>', '', $str);
        $str = strReplace('=', '', $str);
        $str = strReplace('?', '', $str);
        $str = strReplace('[', '', $str);
        $str = strReplace(']', '', $str);
        $str = strReplace('\\', '', $str);
        $str = strReplace('^', '', $str);
        $str = strReplace('`', '', $str);
        $str = strReplace('{', '', $str);
        $str = strReplace('}', '', $str);
        $str = strReplace('|', '', $str);
        $str = strReplace('~', '', $str);
        $str = strReplace('+', '', $str);
        return $str;
    }
}

/**
 * remove special characters
 *
 *
 * @return string
 */
if (!function_exists('removeSpecialCharacters')) {
    function removeSpecialCharacters($str, $removeQuotes = false)
    {
        $str = removeForbiddenCharacters($str);
        $str = strReplace('#', '', $str);
        $str = strReplace('!', '', $str);
        $str = strReplace('(', '', $str);
        $str = strReplace(')', '', $str);
        if ($removeQuotes) {
            $str = clrQuotes($str);
        }
        return $str;
    }
}

/**
 * Ambil satu nilai dari tabel Pengaturan (general_settings).
 *
 * Dibaca langsung dari config, bukan dari data view, supaya tetap tersedia
 * di halaman yang dirender controller Myth\Auth (login, register, forgot)
 * yang tidak mewarisi App\Controllers\BaseController.
 *
 * @return string
 */
if (!function_exists('generalSetting')) {
    function generalSetting(string $key, string $fallback = '')
    {
        $schoolConfigurations = new \Config\School();
        $generalSettings      = $schoolConfigurations::$generalSettings;

        if (!empty($generalSettings) && !empty($generalSettings->{$key})) {
            return $generalSettings->{$key};
        }

        return $fallback;
    }
}

/**
 * Label kelas standar: {Kelas}-{Jurusan}, mis. "X-BDP".
 *
 * Dipakai di seluruh aplikasi (tabel, dropdown, hasil scan, laporan)
 * supaya penulisannya seragam.
 *
 * @param mixed $kelas
 * @param mixed $jurusan
 *
 * @return string
 */
if (!function_exists('labelKelas')) {
    function labelKelas($kelas, $jurusan = null, string $fallback = '-')
    {
        $kelas   = trim((string) ($kelas ?? ''));
        $jurusan = trim((string) ($jurusan ?? ''));

        if ($kelas === '' && $jurusan === '') {
            return $fallback;
        }

        if ($kelas === '' || $jurusan === '') {
            return $kelas !== '' ? $kelas : $jurusan;
        }

        return $kelas . '-' . $jurusan;
    }
}

/**
 * Asset URL dengan cache busting otomatis.
 *
 * Versi diambil dari waktu modifikasi file, sehingga setiap perubahan
 * CSS/JS langsung terbaca browser tanpa perlu hard refresh. Sebelumnya
 * versi ditulis manual (?v=1.0.0) dan tidak pernah berubah, sehingga
 * browser terus memakai salinan lama.
 *
 * @return string
 */
if (!function_exists('assetUrl')) {
    function assetUrl(string $path)
    {
        $file = FCPATH . ltrim($path, '/');

        return base_url($path . (is_file($file) ? '?v=' . filemtime($file) : ''));
    }
}

/**
 * Get Logo
 *
 * @return string
 */
if (!function_exists('getLogo')) {
    function getLogo()
    {
        $schoolConfigurations  = new \Config\School();
        $generalSettings = $schoolConfigurations::$generalSettings;
        if (!empty($generalSettings)) {
            if (!empty($generalSettings->logo) && file_exists(FCPATH . $generalSettings->logo)) {
                return base_url($generalSettings->logo);
            }
            return base_url("assets/img/logo_sekolah.jpg");
        }
        return base_url("assets/img/logo_sekolah.jpg");
    }
}

/**
 * Invalid Feedback
 *
 * @return
 */
if (!function_exists('invalidFeedback')) {
    function invalidFeedback($input)
    {
        $session = session();
        if ($session->getFlashdata('errors')) {
            $errors = $session->getFlashdata('errors');
            if (!empty($errors[$input])) {
                return esc($errors[$input]);
            }

            return null;
        }
        
        return null;
    }
}

//generate unique id
if (!function_exists('generateToken')) {
    function generateToken($short = false)
    {
        $token = uniqid('', true);
        $token = strReplace('.', '-', $token);
        if ($short == false) {
            $token = $token . '-' . rand(10000000, 99999999);
        }
        return $token;
    }
}

//current full url
if (!function_exists('currentFullURL')) {
    function currentFullURL()
    {
        $currentURL = current_url();
        if (!empty($_SERVER['QUERY_STRING'])) {
            $currentURL = $currentURL . "?" . $_SERVER['QUERY_STRING'];
        }
        return $currentURL;
    }
}

//count items
if (!function_exists('countItems')) {
    function countItems($items)
    {
        if (!empty($items) && is_array($items)) {
            return count($items);
        }
        return 0;
    }
}

//get csv value
if (!function_exists('getCSVInputValue')) {
    function getCSVInputValue($array, $key, $dataType = 'string')
    {
        if (!empty($array)) {
            if (!empty($array[$key])) {
                return $array[$key];
            }
        }
        if ($dataType == 'int') {
            return 0;
        }
        return '';
    }
}