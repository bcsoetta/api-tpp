<?php

namespace App\Exports;

use App\BACacah;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class ExportLampiranBACacah implements FromCollection, WithMapping,
WithCustomStartCell, WithColumnFormatting, WithEvents
{
    public function __construct()
    {
        $this->baCacah = null;
        $this->bdnMode = false;
    }

    function startCell(): string
    {
        return 'A9';
    }

    public function columnFormats(): array
    {
        return [
            'F' => '@',
            'G' => '@',
        ];
    }

    // grab data
    public function byId($id) {
        $this->baCacah = BACacah::findOrFail($id);

        return $this;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // collect data
        $data = [];

        $src = $this->baCacah->entryManifest()->with('bcp')->get();
        $ms = $src->sortBy('bcp.nomor_lengkap_dok');

        $cnt = 0;
        foreach ($ms as $m) {
            $m->no = ++$cnt;

            $data [] = $m;
        }

        return collect($data);
    }

    public function map($row): array
    {
        $data =  [
            $row->no,
            $row->bcp->no_dok,
            $row->bcp->tgl_dok,
            $row->koli,
            $row->brutto,
            (string) $row->mawb,
            (string) $row->hawb,
        ];

        if (!$this->baCacah->bdn_mode) {
            $data = array_merge($data, [
                str_pad($row->no_bc11, 6, '0', STR_PAD_LEFT),
                $row->tgl_bc11
            ]);
        }

        $data = array_merge($data, [
            $row->ringkasan_uraian_barang,
            $row->nama_importir,
            $row->alamat_importir,
            $row->pencacahan->ringkasan_uraian_barang,
            $row->koli,
            'koli',
            $row->pencacahan->kondisi_barang,
            $row->tps ? $row->tps->kode : 'P2 SH',
            // nomor kep penetapan
            $row->penetapan[0]->nomor_lengkap_dok
        ]);

        return $data;
            
    }

    function registerEvents(): array
    {
        $ba = $this->baCacah;

        return [
            BeforeSheet::class => function(BeforeSheet $e) use ($ba) {
                $s = $e->sheet->getDelegate();

                $endColumn = $ba->bdn_mode ? 'P' : 'R';

                // Kop Lampiran?
                $s->mergeCells("N1:{$endColumn}1");
                $s->mergeCells("N2:{$endColumn}2");
                $s->mergeCells("N3:{$endColumn}3");

                $s->setCellValue("N1", "Lampiran Berita Acara Pencacahan");
                $s->setCellValue("N2", "Nomor : {$ba->nomor_lengkap_dok}");
                $tgl = formatTanggal($ba->tgl_dok);
                $s->setCellValue("N3", "Tanggal : {$tgl}");

                // JUDUL
                $s->mergeCells("A4:{$endColumn}4");
                $s->mergeCells("A5:{$endColumn}5");

                $s->setCellValue("A4", $ba->bdn_mode ? "PENCACAHAN BARANG-BARANG DIKUASAI NEGARA" : "PENCACAHAN BARANG-BARANG TIDAK DIKUASAI");
                $s->setCellValue("A5", $ba->bdn_mode ? "YANG DITIMBUN DI GUDANG TPP" : "YANG DILIMPAHKAN KE SEKSI KEPABEANAN DAN CUKAI (TPP)");

                // set style?
                $s->getStyle("A4:A5")->applyFromArray([
                    'font' => [
                        'bold' => true
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center'
                    ]
                ]);

                /**
                 * Headings
                 */
                // no
                $s->mergeCells("A7:A8");
                $s->setCellValue("A7", "NO");

                // BCP
                $s->mergeCells("B7:C7");
                $s->setCellValue("B7", "BCP " . ($ba->bdn_mode ? 'BDN' : 'BTD') );
                $s->setCellValue("B8", "NO");
                $s->setCellValue("C8", "TGL");

                // PETIKEMAS
                $s->mergeCells("D7:E7");
                $s->setCellValue("D7", 'PETIKEMAS');
                $s->setCellValue("D8", "Koli");
                $s->setCellValue("E8", "Berat");

                // Nomor dan merek
                $s->mergeCells("F7:G7");
                $s->setCellValue("F7", "NOMOR DAN MEREK");
                $s->setCellValue("F8", "MAWB");
                $s->setCellValue("G8", "HAWB");

                // BC 11 (IF NOT BDN MODE)
                $a = 'H';   // denote active column
                if (!$ba->bdn_mode) {
                    $s->mergeCells("H7:I7");
                    $s->setCellValue("H7", "BC 1.1");
                    $s->setCellValue("H8", "NO");
                    $s->setCellValue("I8", "TGL");

                    $a = 'J';
                }

                // Uraian barang
                $s->mergeCells("{$a}7:{$a}8");
                $s->setCellValue("{$a}7", "URAIAN BARANG");
                ++$a;

                // IMPORTIR
                $s->mergeCells("{$a}7:{$a}8");
                $s->setCellValue("{$a}7", "IMPORTIR");
                ++$a;

                // ALAMAT
                $s->mergeCells("{$a}7:{$a}8");
                $s->setCellValue("{$a}7", "ALAMAT");
                ++$a;

                // PENCACAHAN
                $start = $a;
                $end = chr(ord($a)+3);

                $s->mergeCells("{$start}7:{$end}7");
                $s->setCellValue("{$start}7", "HASIL PENCACAHAN");
                
                $s->setCellValue("{$a}8", "URAIAN BARANG");
                ++$a;
                $s->setCellValue("{$a}8", "JML");
                ++$a;
                $s->setCellValue("{$a}8", "SATUAN");
                ++$a;
                $s->setCellValue("{$a}8", "KONDISI");
                ++$a;

                // TPS
                $s->mergeCells("{$a}7:{$a}8");
                $s->setCellValue("{$a}7", "GUDANG TPS");
                ++$a;

                // ALAMAT
                $s->mergeCells("{$a}7:{$a}8");
                $s->setCellValue("{$a}7", "DASAR PENETAPAN");

                // just set style now
                $s->getStyle("A7:{$endColumn}8")->applyFromArray([
                    'font' => [
                        'bold' => true
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center'
                    ]
                ]);
            },

            // After we done with it
            AfterSheet::class => function (AfterSheet $e) use ($ba) {
                $s = $e->sheet->getDelegate();

                // compute col and row End
                $endColumn = $ba->bdn_mode ? 'P' : 'R';
                $endRow = $ba->entryManifest()->count() + 8;

                // set all to center and auto wrap?
                $s->getStyle("A9:{$endColumn}{$endRow}")->applyFromArray([
                    'alignment' => [
                        'horizontal' => 'left',
                        'vertical' => 'top'
                    ]
                ])->getAlignment()->setWrapText(true);

                // autosize some columns
                $s->getColumnDimension('A')->setAutoSize(true);
                $s->getColumnDimension('B')->setAutoSize(true);
                $s->getColumnDimension('C')->setAutoSize(true);
                $s->getColumnDimension('D')->setAutoSize(true);
                $s->getColumnDimension('E')->setAutoSize(true);
                $s->getColumnDimension('F')->setAutoSize(true);
                $s->getColumnDimension('G')->setAutoSize(true);

                $a = 'H';
                if (!$ba->bdn_mode) {
                    $s->getColumnDimension('H')->setAutoSize(true);
                    $s->getColumnDimension('I')->setAutoSize(true);
                    $a = 'J';
                }

                // some we set to fixed
                $s->getColumnDimension($a++)->setWidth(25);
                $s->getColumnDimension($a++)->setWidth(25);
                $s->getColumnDimension($a++)->setWidth(25);
                $s->getColumnDimension($a++)->setWidth(25);

                $s->getColumnDimension($a++)->setAutoSize(true);
                $s->getColumnDimension($a++)->setAutoSize(true);
                $s->getColumnDimension($a++)->setAutoSize(true);
                $s->getColumnDimension($a++)->setAutoSize(true);
                $s->getColumnDimension($a++)->setAutoSize(true);

                // set border
                $s->getStyle("A7:{$endColumn}{$endRow}")->applyFromArray([
                    'borders' => [ 'allBorders' => [ 'borderStyle' => 'thin' ] ]
                ]);

                // kolom TTD
                $a = $endRow + 2;
                $s->mergeCells("A{$a}:{$endColumn}$a");
                $s->setCellValue("A{$a}", "Yang Melaksanakan Pencacahan");
                $s->getStyle("A{$a}")->applyFromArray([
                    'font' => [
                        'bold' => true
                    ],
                    'alignment' => [
                        'horizontal' => 'center'
                    ]
                ]);

                $a += 6;

                // list pelaksana
                $startCols = ['B', 'I', 'O'];
                $i = 0;
                foreach ($ba->pelaksana as $p) {
                    $b = $a+1;

                    $col = $startCols[$i++ % count($startCols)];

                    // write em down
                    $s->setCellValue("{$col}{$a}", $p->name);
                    $s->setCellValueExplicit("{$col}{$b}", "NIP " . $p->nip, DataType::TYPE_STRING);

                    // go down if we're past 3
                    while ($i >= 3) {
                        $i -= 3;
                        $a += 6;
                    }
                }

                // Mengetahui
                $a += 3;
                $s->setCellValue("B{$a}", "Mengetahui");
                ++$a;
                $s->setCellValue("B{$a}", "Kepala Seksi Pabean dan Cukai");
                $a += 6;
                $s->setCellValue("B{$a}", $ba->pejabat->name);
                ++$a;
                $s->setCellValueExplicit("B{$a}", "NIP " . $ba->pejabat->nip, DataType::TYPE_STRING);
            }
        ];
    }
}
