<?php

namespace App\Exports;

use App\Penetapan;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;

use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;

class ExportLampiranPenetapan implements FromCollection, WithMapping, 
WithCustomStartCell, WithEvents, WithColumnFormatting
{
    use Exportable;

    public function __construct()
    {
        // set default parameter
        $this->penetapan = null;
    }

    public function registerEvents(): array
    {
        $rowEnd = $this->penetapan->entryManifest()->count();
        $noSurat = $this->penetapan->nomor_lengkap_dok;
        $tglSurat = formatTanggal($this->penetapan->tgl_dok);
        $namaTps = 'TPS: ' . $this->penetapan->entryManifest[0]->tps->nama;

        $dataCellRange = 'A12:N' . (12+$this->penetapan->entryManifest()->count());

        $penetapan = $this->penetapan;

        return [
            // tambah data dan style setelah selesai nulis sheet
            AfterSheet::class => function (AfterSheet $e) use ($rowEnd, $dataCellRange, $penetapan) {
                $rowEnd += 12;

                $e->sheet->getDelegate()->mergeCells("A{$rowEnd}:E{$rowEnd}");
                $e->sheet->getDelegate()->getStyle("A{$rowEnd}:M{$rowEnd}")->applyFromArray([
                    'alignment' => ['horizontal' => 'center'],
                    'font' => ['bold' => true],
                    'borders' => [ 'allBorders' => [ 'borderStyle' => 'thin' ] ],
                ]);

                // merge and set color for empty last row
                $e->sheet->getDelegate()->mergeCells("H{$rowEnd}:N{$rowEnd}");
                $e->sheet->getDelegate()->getStyle("H{$rowEnd}:N{$rowEnd}")->applyFromArray([
                    'fill' => [
                        'fillType' => 'solid',
                        'color' => [ 'rgb' => 'DDDDDD' ]
                    ]
                ]);
                $e->sheet->getDelegate()->getStyle("A{$rowEnd}:E{$rowEnd}")->applyFromArray([
                    'fill' => [
                        'fillType' => 'solid',
                        'color' => [ 'rgb' => 'DDDDDD' ]
                    ]
                ]);

                // fill summary row data
                $e->sheet->getDelegate()->setCellValue("A{$rowEnd}", 'TOTAL');
                $e->sheet->getDelegate()->setCellValue("F{$rowEnd}", (float) $penetapan->entryManifest()->sum('koli'));
                $e->sheet->getDelegate()->setCellValue("G{$rowEnd}", (float) $penetapan->entryManifest()->sum('brutto'));
    
                // set all data cells to have border and center aligned
                $e->sheet->getDelegate()->getStyle($dataCellRange)->applyFromArray([
                    'alignment' => ['horizontal'=>'center', 'vertical'=>'center'],
                    'borders' => [ 'allBorders' => [ 'borderStyle' => 'thin' ] ],
                ]);
                // wrap text
                $e->sheet->getDelegate()->getStyle('A10:N11')->getAlignment()->setWrapText(true);
                $e->sheet->getDelegate()->getStyle($dataCellRange)->getAlignment()->setWrapText(true);

                // append kolom ttd
                $rowEnd += 3;   // skip 3 rows
                $e->sheet->getDelegate()->getStyle("J{$rowEnd}")->applyFromArray([
                    'alignment' => [ 'horizontal' => 'right' ]
                ]);
                // a.n.
                $e->sheet->getDelegate()->setCellValue("J{$rowEnd}", "a.n.");

                // Titel jabatan
                $e->sheet->getDelegate()->setCellValue("K{$rowEnd}", 'Kepala Bidang Pelayanan Fasilitas dan Pabean dan Cukai I');
                
                ++$rowEnd;
                $e->sheet->getDelegate()->setCellValue("K{$rowEnd}", 'Kepala Seksi Pabean dan Cukai');

                $rowEnd += 5;
                $e->sheet->getDelegate()->setCellValue("K{$rowEnd}", $penetapan->pejabat->name);

                // set all column width
                $e->sheet->getDelegate()->getColumnDimension('D')->setWidth(14);
                $e->sheet->getDelegate()->getColumnDimension('E')->setWidth(13.5);
                $e->sheet->getDelegate()->getColumnDimension('J')->setWidth(25);
                $e->sheet->getDelegate()->getColumnDimension('K')->setWidth(30);
                $e->sheet->getDelegate()->getColumnDimension('L')->setWidth(35);
                $e->sheet->getDelegate()->getColumnDimension('M')->setWidth(10);
                
                // auto size cols
                $e->sheet->getDelegate()->getColumnDimension('C')->setAutoSize(true);
                $e->sheet->getDelegate()->getColumnDimension('H')->setAutoSize(true);
                $e->sheet->getDelegate()->getColumnDimension('I')->setAutoSize(true);
            },

            // Sebelum nulis sheet, kita tulis KOP dan heading rows
            BeforeSheet::class => function (BeforeSheet $e) use ($noSurat, $tglSurat, $namaTps) {
                // Merge kop rows
                $e->sheet->getDelegate()->mergeCells('A1:J1');
                $e->sheet->getDelegate()->mergeCells('A2:J2');
                $e->sheet->getDelegate()->mergeCells('A3:J3');
                // kop style
                $e->sheet->getDelegate()->getStyle('A1:A3')->applyFromArray([
                    'font' => [
                        'bold' => true
                    ]
                ]);
                // kop text
                $e->sheet->append(['KEMENTERIAN KEUANGAN REPUBLIK INDONESIA'],'A1');
                $e->sheet->append(['DIREKTORAT JENDERAL BEA DAN CUKAI'],'A2');
                $e->sheet->append(['KANTOR PELAYANAN UTAMA BEA DAN CUKAI TIPE C SOEKARNO HATTA'],'A3');

                // lampiran
                $e->sheet->getDelegate()->mergeCells('L1:N1');
                $e->sheet->getDelegate()->mergeCells('L2:N2');
                $e->sheet->getDelegate()->mergeCells('L3:N3');
                $e->sheet->getDelegate()->mergeCells('L4:N4');

                $e->sheet->append(['Lampiran'], 'L1');
                $e->sheet->append(['Surat Kepala Bidang PFPC I KPU BC Tipe C Soekarno Hatta'], 'L2');
                $e->sheet->append(["Nomor: {$noSurat}"], 'L3');
                $e->sheet->append(["Tanggal: {$tglSurat}"], 'L4');

                // title
                $e->sheet->getDelegate()->mergeCells('A7:N7');
                $e->sheet->getDelegate()->mergeCells('A8:N8');
                $e->sheet->getDelegate()->mergeCells('A9:N9');

                $e->sheet->getDelegate()->getStyle('A7:A8')->applyFromArray([
                    'font' => [
                        'bold' => true
                    ],
                    'alignment' => [
                        'horizontal' => 'center'
                    ]
                ]);

                $e->sheet->append(['DAFTAR BARANG-BARANG IMPOR YANG DINYATAKAN'],'A7');
                $e->sheet->append(['SEBAGAI BARANG TIDAK DIKUASAI'],'A8');
                
                /* // nama TPS
                $e->sheet->getDelegate()->getStyle('A9:N9')->applyFromArray([
                    'font' => [
                        'bold' => true
                    ]
                ]);
                $e->sheet->append([$namaTps], 'A9'); */

                // append heading rows (with style)
                // set all common heading styles (center align, bold)
                $e->sheet->getDelegate()->getStyle('A10:N11')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal'=>'center', 'vertical'=>'center']
                ]);
                // No (merge A10:A11)
                $e->sheet->getDelegate()->mergeCells('A10:A11');
                $e->sheet->getDelegate()->setCellValue('A10', 'No.');
                
                // BC11 (nomor, tgl, pos)
                $e->sheet->getDelegate()->mergeCells('B10:D10');
                $e->sheet->getDelegate()->setCellValue('B10', 'BC 1.1');

                $e->sheet->getDelegate()->setCellValue('B11', 'NOMOR');
                $e->sheet->getDelegate()->setCellValue('C11', 'TANGGAL');
                $e->sheet->getDelegate()->setCellValue('D11', 'POS');

                // SARANA PENGANGKUT
                $e->sheet->getDelegate()->mergeCells('E10:E11');
                $e->sheet->getDelegate()->setCellValue('E10', 'SARANA PENGANGKUT');

                // KEMASAN (koli, berat)
                $e->sheet->getDelegate()->mergeCells('F10:G10');
                $e->sheet->getDelegate()->setCellValue('F10', 'KEMASAN');

                $e->sheet->getDelegate()->setCellValue('F11', 'KOLI');
                $e->sheet->getDelegate()->setCellValue('G11', 'BERAT (KG)');

                // NOMOR AWB (mawb, hawb)
                $e->sheet->getDelegate()->mergeCells('H10:I10');
                $e->sheet->getDelegate()->setCellValue('H10', 'NOMOR AWB');

                $e->sheet->getDelegate()->setCellValue('H11', 'MAWB');
                $e->sheet->getDelegate()->setCellValue('I11', 'HAWB');

                // URAIAN BARANG
                $e->sheet->getDelegate()->mergeCells('J10:J11');
                $e->sheet->getDelegate()->setCellValue('J10', 'URAIAN BARANG');

                // IMPORTIR (nama, alamat)
                $e->sheet->getDelegate()->mergeCells('K10:L10');
                $e->sheet->getDelegate()->setCellValue('K10', 'IMPORTIR');

                $e->sheet->getDelegate()->setCellValue('K11', 'NAMA');
                $e->sheet->getDelegate()->setCellValue('L11', 'ALAMAT');

                // TPS
                $e->sheet->getDelegate()->mergeCells('M10:M11');
                $e->sheet->getDelegate()->setCellValue('M10', 'KODE TPS');

                // KETERANGAN
                $e->sheet->getDelegate()->mergeCells('N10:N11');
                $e->sheet->getDelegate()->setCellValue('N10', 'KETERANGAN');

                // Color and border all heading row
                $e->sheet->getDelegate()->getStyle("A10:N11")->applyFromArray([
                    'fill' => [
                        'fillType' => 'solid',
                        'color' => [ 'rgb' => 'DDDDDD' ]
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => 'thin'
                        ]
                    ]
                ]);
            }
        ];
    }

    public function startCell(): string
    {
        return 'A10';
    }

    public function columnFormats(): array
    {
        return [
            'G' => '#,##0.0000',
            'H' => '@',
            'I' => '@'
        ];
    }

    public function byId($id) {
        $this->penetapan = Penetapan::findOrFail($id);

        return $this;
    }

    public function collection()
    {
        $data = [];

        $ms = $this->penetapan->entryManifest;

        $cnt = 0;
        foreach ($ms as $value) {
            // append nomor (index)
            $value->no = ++$cnt;

            $data [] = $value;
        }

        return collect($data);
    }

    public function map($row): array
    {
        return [
            $row->no,
            str_pad($row->no_bc11, 6, '0', STR_PAD_LEFT),
            $row->tgl_bc11,
            $row->pos_formatted,
            $row->kd_flight,
            $row->koli,
            $row->brutto,
            (string) $row->mawb,
            (string) $row->hawb,
            $row->ringkasan_uraian_barang,
            $row->nama_importir,
            $row->alamat_importir,
            $row->tps->kode,
            ''
        ];
    }
}
