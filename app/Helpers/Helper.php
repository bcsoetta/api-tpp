<?php

use App\Services\SSO;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

// function: getSequence(kode_sequence, tahun)
// desc: mengembalikan sequence dari database (call getSequence() from database)
// return: sequence in integer
if (!function_exists('getSequence')) {
    // declare if not exists
    function getSequence($kode_sequence, $tahun = null ) {
        // if tahun is unspecified, use current year
        $tahun = $tahun ?? (int)date('Y');
        return collect( DB::select("SELECT getSequence(?, ?) AS seq", [$kode_sequence, $tahun]) )->first()->seq;
    }
}

// function: getUserInfo(access_token) 
// desc: mengambil data user dari SSO menggunakan jasny/sso library
// return: data user dalam array
if (!function_exists('getUserInfo')) {
    // declare
    function getUserInfo() {
        // cek environment, untuk development gunakan mockup
        if (App::environment('production')) {
            // belum didefinisikan, jadi trigger error aja heheh 
            // nanti klo udh integrasi, taro di sini
            // required: secret_id, secret_key, trus bkin token buat 
            //              request ke SSO
            // trigger_error("Belum diintegrasikan dengan SSO!", E_USER_ERROR);

            /* $broker = new Broker(
                'http://sso.soetta.xyz/',
                '5',
                '5h1n74aPPs'
            );

            // set token
            $broker->token = $access_token;

            // call get user
            try {
                $userInfo = $broker->getUserInfo();
                return $userInfo;
            } catch (\Exception $e) {
                // echo "getUserInfo error: {$e->getMessage()}";
                return null;
            } */
            $sso = app(SSO::class);

            return $sso->getUserInfo();
        } 

        // mockup token dengan mockup data usernya
        $mockupUsers = [
            // mockup profil pdtt
            'token_pelaksana'  => [
                "user_id" => "572",
                "username" => "setiadi.001",
                "name" => "Setiadi",
                "nip" => "198911102010011001",
                "pangkat" => "Pengatur - II/c",
                "status" => true,
                "apps_data" => [
                  3 => [
                    "app_name" => "AKANG",
                    "roles" => [
                      "PEMERIKSA",
                    ],
                  ],
                  5 => [
                    "app_name" => "SiBAPE",
                    "roles" => [
                      "PDTT",
                    ],
                  ],
                  11 => [
                    "app_name" => "TePePe",
                    "roles" => [
                      "PELAKSANA"
                    ]
                  ]
                ],
              ],

            // mockup profil admin
            'token_admin' => [
                "user_id" => "612",
                "username" => "tri.mulyadi",
                "name" => "Tri Mulyadi Wibowo",
                "nip" => "199103112012101001",
                "pangkat" => "Penata Muda - III/a",
                "status" => true,
                "apps_data" => [
                  1 => [
                    "app_name" => "SSO",
                    "roles" => [
                      "Administrator",
                    ],
                  ],
                  2 => [
                    "app_name" => "APPFOTO",
                    "roles" => [
                      "Administrator",
                    ],
                  ],
                  3 => [
                    "app_name" => "AKANG",
                    "roles" => [
                      "PJT",
                      "ADMIN_PABEAN",
                      "SUPERUSER",
                    ],
                  ],
                  5 => [
                    "app_name" => "SiBAPE",
                    "roles" => [
                      "KASI",
                      "CONSOLE",
                    ],
                  ],
                  11 => [
                    "app_name" => "TePePe",
                    "roles" => [
                      "CONSOLE"
                    ]
                  ]
                ],
              ],

              'token_pemeriksa' => array (
                'user_id' => '296',
                'username' => 'hendro.laksono',
                'name' => 'Hendro Laksono',
                'nip' => '198807172009121002',
                'pangkat' => 'Penata Muda - III/a',
                'kode' => 'kpu.03',
                'posisi' => 'Kepala Kantor',
                'tempat' => 'Kepala Kantor',
                'status' => true,
                'apps_data' => 
                array (
                  5 => 
                  array (
                    'app_name' => 'Patops',
                    'app_style' => 'fa fa-trash',
                    'app_desc' => 'Passenger Monitoring and Payment System',
                    'app_url' => 'patops.soetta.xyz',
                    'roles' => 
                    array (
                      0 => 'PEMERIKSA',
                    ),
                    'rolex' => 
                    array (
                      0 => 'sibape.pemeriksa',
                    ),
                  ),
                ),
              )
        ];

        $access_token = app('request')->bearerToken();

        // ambil data, atau return null klo gk ketemu
        if (!array_key_exists($access_token, $mockupUsers)) {
            return null;
        }

        // ada
        return $mockupUsers[$access_token];
    }
}

// function: userHasRole(user_info) 
// desc: ngecek apakah user punya role? parameter ambil dr getUserInfo($access_token)
// return: true klo punya, false klo enggak
if (!function_exists('userHasRole')) {
    // declare function
    function userHasRole($roleName, $userInfo = null) {
        // empty data means false
        if (!$userInfo) {
            return false;
        }

        // check if user has sibape
        if (!array_key_exists('apps_data', $userInfo)) {
            return false;
        }

        if (!array_key_exists('11', $userInfo['apps_data'])) {
            return false;
        }

        // check existence of key 'role'
        if (!array_key_exists('roles', $userInfo['apps_data']['11'])) {
            return false;
        }

        // okay, find it
        return in_array($roleName, $userInfo['apps_data']['11']['roles']);
    }
}

// function: sqlDate()
// converts any of matching format to sql date
if (!function_exists('sqlDate')) {
  // declare
  function sqlDate($strDate) {
    if (!$strDate)
      return null;

    $matches = [];
    if (preg_match('/(\d{1,2})-(\d{1,2})-(\d{4})/i', $strDate, $matches)) {
      // match!! store it
      return "{$matches[3]}-{$matches[2]}-{$matches[1]}";
    } else if (preg_match('/(\d{4})-(\d{1,2})-(\d{1,2})/', $strDate, $matches)) {
      return "{$matches[1]}-{$matches[2]}-{$matches[3]}";
    }
    return null;
  }
}

// function expectSomething
// return something other than empty
if (!function_exists('expectSomething')) {
  function expectSomething($var, $name) {
    if (is_null($var)) throw new \Exception("{$name} tidak valid -> {$var}");
    return $var;
  }
}

// function userCanEdit
// return true if user can edit
if (!function_exists('canEdit')) {
  function canEdit($docIsLocked, $userInfo) {
    // only can edit if: 1. doc is unlocked
    // or 2. doc is locked and user is (KASI | CONSOLE)
    return !$docIsLocked 
            || ( $docIsLocked && (userHasRole('KASI', $userInfo) || userHasRole('CONSOLE', $userInfo)) );
  }
}

// function accumulate
// simple accumulator for various map/reducer
if (!function_exists('accumulate')) {
  function accumulate($acc, $e) {
    $acc += $e;
    return $acc;
  }
}

// function formatNpwp
// to format default npwp into formatted form
if (!function_exists('formatNpwp')) {
  function formatNpwp($npwp) {
    // pertama, ambil angka aja (buang tanda baca)
    $cleanNpwp = trim(str_replace([" ", ".", "-"], "", $npwp));

    // replace dengan pattern
    $pattern = '/(\d{2})(\d{3})(\d{3})(\d)(\d{3})(\d{3})/i';

    // replacement
    $replacement = '${1}.${2}.${3}.${4}-${5}.${6}';

    return preg_replace($pattern, $replacement, $cleanNpwp);
  }
}

// function getBulan
// ambil nama bulan dalam bahasa indonesia
if (!function_exists('getBulan')) {
  function getBulan($sqlDate) {
    // the mapping
    $bulan = [
      '01'  => "Januari",
      '02'  => "Februari",
      '03'  => "Maret",
      '04'  => "April",
      '05'  => "Mei",
      '06'  => "Juni",
      '07'  => "Juli",
      '08'  => "Agustus",
      '09'  => "September",
      '10'  => "Oktober",
      '11'  => "November",
      '12'  => "Desember"
    ];
    // gotta grab the middle
    if(preg_match('/\d{4}\-(\d{2})\-\d{2}/i', $sqlDate, $matches)) {
      if (count($matches) >= 2) {
        return $bulan[$matches[1]];
      }
    }
    return null;
  }
}

// getTahun
// ambil nomor tahun dari tanggal sqlDate
if (!function_exists('getTahun')) {
  function getTahun($sqlDate) {
    // just grab 4 leftmost char, denying everything
    return substr($sqlDate, 0, 4);
  }
}

// getTanggal
// ambil data tanggal dari sqlDate
if (!function_exists('getTanggal')) {
  function getTanggal($sqlDate) {
    return substr($sqlDate, -2, 2);
  }
}

// formatTanggal
// format tanggal dalam bahasa Indonesia
if (!function_exists('formatTanggal')) {
  function formatTanggal($sqlDate) {
    return getTanggal($sqlDate) . " " . getBulan($sqlDate) . " " . getTahun($sqlDate);
  }
}

// penyebutRupiah
if (!function_exists('penyebutRupiah')) {
  function penyebutRupiah($nilai) {
    $nilai = abs($nilai);
		$huruf = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
		$temp = "";
		if ($nilai < 12) {
			$temp = " ". $huruf[$nilai];
		} else if ($nilai <20) {
			$temp = penyebutRupiah($nilai - 10). " belas";
		} else if ($nilai < 100) {
			$temp = penyebutRupiah($nilai/10)." puluh". penyebutRupiah($nilai % 10);
		} else if ($nilai < 200) {
			$temp = " seratus" . penyebutRupiah($nilai - 100);
		} else if ($nilai < 1000) {
			$temp = penyebutRupiah($nilai/100) . " ratus" . penyebutRupiah($nilai % 100);
		} else if ($nilai < 2000) {
			$temp = " seribu" . penyebutRupiah($nilai - 1000);
		} else if ($nilai < 1000000) {
			$temp = penyebutRupiah($nilai/1000) . " ribu" . penyebutRupiah($nilai % 1000);
		} else if ($nilai < 1000000000) {
			$temp = penyebutRupiah($nilai/1000000) . " juta" . penyebutRupiah($nilai % 1000000);
		} else if ($nilai < 1000000000000) {
			$temp = penyebutRupiah($nilai/1000000000) . " milyar" . penyebutRupiah(fmod($nilai,1000000000));
		} else if ($nilai < 1000000000000000) {
			$temp = penyebutRupiah($nilai/1000000000000) . " trilyun" . penyebutRupiah(fmod($nilai,1000000000000));
		}     
		return $temp;
  }
}

// formatTanggalDMY
if (!function_exists('formatTanggalDMY')) {
  function formatTanggalDMY($sqlDate) {
    $matches = null;
    if (preg_match('/(\d{4})\-(\d{2})\-(\d{2})/i', $sqlDate, $matches)) {
      return $matches[3] . '-' . $matches[2] . '-' . $matches[1];
      // return $matches;
    }
    return null;
  }
}

// splitDatetime
if (!function_exists('splitDatetime')) {
  function splitTime($datetime) {
    $matches = [];
    if (preg_match('/^(\d{4}-\d{2}-\d{2})\s(\d{2}:\d{2}:\d{2})$/', $datetime, $matches) ) {
        return [
            'date'  => $matches[1],
            'time'  => $matches[2]
        ];
    }
    return null;
  }

  // add another helper
  function timePart($datetime) {
    $split = splitTime($datetime);
    return $split ? $split['time'] : null;
  }

  function datePart($datetime) {
    $split = splitTime($datetime);
    return $split ? $split['date'] : null;
  }
}

if (!function_exists('spawnTransformer')) {
  /**
   * this function returns a new instance of transformer for a particular object
   * or null if it's unable to
   */
  function spawnTransformer($object) {

    $baseclassname = class_basename($object);

    if ($baseclassname) {
      $transformerName = "App\\Transformers\\$baseclassname" . "Transformer";
      
      if (class_exists($transformerName)) {
        return new $transformerName;
      }
    }

    return null;
  }
}

if (!function_exists('stringifyQuery')) {
  function stringifyQuery($q) {
    $bindings = array_map(function ($e) {
      // replace \ with double \\
      return preg_replace('/\\\\/si', '\\\\\\\\', $e);
    }, $q->getBindings());
    return vsprintf(str_replace(array('?'), array('\'%s\''), $q->toSql()), $bindings);
  }
}
