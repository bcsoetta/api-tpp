<?php

namespace App\Imports;

use App\BCP;
use App\DetailBarang;
use App\EntryManifest;
use App\Keterangan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class KepBDNImport implements ToModel, WithStartRow
{
    use Importable;

    protected $models = [];

    function startRow(): int
    {
        return 9;
    }

    function model(array $r)
    {
        // if row incomplete, bail
        if (!$r[0] && !$r[1])
            return;
        // convert excel date to sql date
        $tgl_bdn = date('Y-m-d', Date::excelToTimestamp($r[3]) );

        // SPAWN BDN
        $bcp = new BCP([
            'no_dok' => (int) $r[2],
            'tgl_dok' => $tgl_bdn,
            'jenis' => 'BDN'
        ]);
        $bcp->nomor_lengkap_dok = $bcp->nomor_lengkap;

        $awb = (string) trim($r[6]);

        // SPAWN ENTRY MANIFEST
        $m = new EntryManifest([
            'mawb' => $awb,
            'hawb' => $awb,
            'nama_importir' => (string) $r[9],
            'alamat_importir' => (string) $r[10],
            'koli' => (float) $r[4],
            'brutto' => (float) $r[5]
        ]);

        // SPAWN detail barang
        $detailBarang = [
            DetailBarang::fromString(trim($r[8]))
        ];

        // SPAWN KETERANGAN
        $keterangan = [
            new Keterangan([
                'keterangan' => trim($r[16])
            ])
        ];

        // assemble
        $m->detailBarang = collect($detailBarang);
        $m->bcp = $bcp;
        $m->keterangan = $keterangan;

        // store
        $this->models[] = $m;
    }

    /* function collection(Collection $rows)
    {
        foreach ($rows as $r) {
            // break once we reach end of data
            if (!$r[0] && !$r[1]) break;

            $this->models[] = [
                'no_bdn' => $r[2],
                'tgl_bdn' => date('Y-m-d', Date::excelToTimestamp($r[3]) ),
                'koli' => (float) $r[4],
                'berat' => (float) $r[5],
                'mawb' => $r[6],
                'hawb' => $r[6],
                'uraian' => $r[8],
                'nama_importir' => $r[9],
                'alamat_importir' => $r[10],

                'uraian_cacah' => $r[11],
                'jumlah_cacah' => $r[12],
                'satuan_cacah' => $r[13],

                'kondisi_cacah' => $r[14],

                'gudang_asal_tps' => $r[15],
                'keterangan' => $r[16]
            ];
        }
    } */

    public function importToModels($filePath = null, ?string $disk = null, ?string $readerType = null)
    {
        $this->import($filePath, $disk, $readerType);

        return $this->models;
    }
}
