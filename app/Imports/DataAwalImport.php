<?php

namespace App\Imports;

use App\DetailBarang;
use App\EntryManifest;
use App\Keterangan;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class DataAwalImport implements ToModel, WithHeadingRow
{
    use Importable;

    protected $models = [];

    public function model(array $row)
    {
        // $date = Date::excelToDateTimeObject($row['tgl_bc11']);
        $ts = Date::excelToTimestamp($row['tgl_bc11']);

        $m = new EntryManifest([
            'no_bc11' => (int) $row['no_bc11'],
            'tgl_bc11' => date('Y-m-d', $ts),
            'pos' => (int) $row['pos'],
            'subpos' => (int) $row['subpos'],
            'subsubpos' => (int) $row['subsubpos'],
            'kd_flight' => $row['sarana_pengangkut'],
            'koli' => (float) $row['koli'],
            'brutto' => (float) $row['berat'],
            'mawb' => (string) $row['mawb'],
            'hawb' => (string) $row['hawb'],
            'nama_importir' => (string) $row['nama_importir'],
            'alamat_importir' => (string) $row['alamat_importir']
        ]);

        // read data barang, first, grab string source
        $source = $row['uraian'] ?? $row['uraian_barang'];

        // split them
        $arrSource = explode(";", $source);

        // build them
        $detailBarang = [
            /* new DetailBarang([
                'uraian' => $row['uraian'] ?? $row['uraian_barang']
            ]) */
        ];

        foreach ($arrSource as $desc) {
            $detailBarang[] = DetailBarang::fromString($desc);
        }

        // read keterangan
        $keterangan = [];
        if ($row['keterangan']) {
            $keterangan[] = new Keterangan(['keterangan' => $row['keterangan']]);
        }
        
        if ($row['keterangan_tambahan']) {
            $keterangan[] = new Keterangan(['keterangan' => $row['keterangan_tambahan']]);
        }

        $m->detailBarang = collect($detailBarang);
        $m->keterangan = collect($keterangan);

        $this->models[] = $m;
    }

    public function importToModels($filePath = null, ?string $disk = null, ?string $readerType = null)
    {
        $this->import($filePath, $disk, $readerType);

        return $this->models;
    }
}
