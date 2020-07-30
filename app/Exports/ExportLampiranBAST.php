<?php

namespace App\Exports;

use App\BAST;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;

class ExportLampiranBAST implements FromCollection, WithMapping, WithCustomStartCell,
WithEvents, WithColumnFormatting
{
    use Exportable;

    public function __construct()
    {
        // set default parameter
        $this->bast = null;
    }

    function startCell(): string
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

    // grab id, throw error if necessary
    public function byId($id) {
        $this->bast = BAST::findOrFail($id);

        if ($this->bast->ex_p2) {
            throw new \Exception("BAST dari P2 lampirannya liat KEP BDN nya aja cong!");
        }

        return $this;
    }

    /**
    * Simply return data with added columns (numbering)
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = [];

        $ms = $this->bast->entryManifest;

        $cnt = 0;
        foreach ($ms as $value) {
            // append nomor (index)
            $value->no = ++$cnt;

            $data [] = $value;
        }

        return collect($data);
    }

    function map($row): array
    {
        return [
            $row->bcp->nomor_lengkap_dok,
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
            $row->waktu_gate_in->toDateString(),
            '-'
        ];
    }

    function registerEvents(): array
    {
        $bast = $this->bast;

        return [
            BeforeSheet::class => function (BeforeSheet $e) use ($bast) {
                $s = $e->sheet->getDelegate();
                // write kop
                // merge kop cells
                $s->mergeCells('A1:H1');
                $s->mergeCells('A2:H2');
                $s->mergeCells('A3:H3');
                // embolden
                $s->getStyle('A1:H3')->applyFromArray([
                    'font' => [
                        'bold' => true
                    ]
                ]);
                // write em
                $s->setCellValue('A1', 'KEMENTERIAN KEUANGAN REPUBLIK INDONESIA');
                $s->setCellValue('A2', 'DIREKTORAT JENDERAL BEA DAN CUKAI');
                $s->setCellValue('A3', 'KANTOR PELAYANAN UTAMA BEA DAN CUKAI TIPE C SOEKARNO HATTA');

                // merge lampiran cells
                $s->mergeCells('K1:L1');
                $s->mergeCells('K2:L2');
                $s->mergeCells('K3:L3');
                // write me
                $s->setCellValue('K1', "Lampiran Berita Acara Pemindahan");
                $s->setCellValue('K2', "Nomor : {$bast->nomor_lengkap_dok}");
                $tanggal = formatTanggal($bast->tgl_dok);
                $s->setCellValue('K3', "Tanggal : {$tanggal}");

                // TITLES
                // merge titles cells
                $s->mergeCells('A5:N5');
                $s->mergeCells('A6:N6');
                // embolden
                $s->getStyle('A5:N6')->applyFromArray([
                    'font' => [
                        'bold' => true
                    ],
                    'alignment' => [ 'horizontal' => 'center', 'vertical' => 'center' ]
                ]);
                // write them
                $s->setCellValue('A5', 'DAFTAR BARANG-BARANG IMPOR');
                $s->setCellValue('A6', 'YANG DINYATAKAN SEBAGAI BARANG TIDAK DIKUASAI');

                // TPS ASAL
                // merge them
                $s->mergeCells('A7:H7');
                // embolden
                $s->getStyle('A7')->applyFromArray([
                    'font' => [
                        'bold' => true
                    ]
                ]);
                // write them
                $s->setCellValue('A7', "TPS ASAL : " . $bast->entryManifest[0]->tps->nama);

                /**
                 * HEADING ROWS
                 */
                // no, merged cells
                $s->mergeCells('A8:A9');
                $s->setCellValue('A8', 'No. BCP');
                // BC11
                $s->mergeCells('B8:D8');
                $s->setCellValue('B8', 'BC 1.1');
                $s->setCellValue('B9', 'NOMOR');
                $s->setCellValue('C9', 'TANGGAL');
                $s->setCellValue('D9', 'POS');
                // Nama sarana
                $s->mergeCells('E8:E9');
                $s->setCellValue('E8', 'NAMA SARANA');
                // Jumlah & Jenis
                $s->mergeCells('F8:G8');
                $s->setCellValue('F8', 'JUMLAH & JENIS');
                $s->setCellValue('F9', 'KOLI');
                $s->setCellValue('G9', 'BERAT');
                // NOMOR & MERK?
                $s->mergeCells('H8:I8');
                $s->setCellValue('H8', 'NOMOR & MERK');
                $s->setCellValue('H9', 'MAWB');
                $s->setCellValue('I9', 'HAWB');
                // URAIAN BARANG
                $s->mergeCells('J8:J9');
                $s->setCellValue('J8', 'URAIAN BARANG');
                // IMPORTIR
                $s->mergeCells('K8:L8');
                $s->setCellValue('K8','IMPORTIR');
                $s->setCellValue('K9', 'NAMA');
                $s->setCellValue('L9', 'ALAMAT');
                // Tgl Masuk
                $s->mergeCells('M8:M9');
                $s->setCellValue('M8', 'TGL MASUK');
                // Ket
                $s->mergeCells('N8:N9');
                $s->setCellValue('N8', 'KET');

                // embolden heading rows
                $s->getStyle('A8:N9')->applyFromArray([
                    'font' => [
                        'bold' => true
                    ],
                ]);
            },

            AfterSheet::class => function (AfterSheet $e) use ($bast) {
                $s = $e->sheet->getDelegate();
                // compute row end
                $rowEnd = $bast->entryManifest()->count() + 10;

                // summary row (total)
                $s->mergeCells("A{$rowEnd}:E{$rowEnd}");
                $s->mergeCells("H{$rowEnd}:N{$rowEnd}");

                // write em
                $s->setCellValue("A{$rowEnd}", "TOTAL");
                $s->setCellValue("F{$rowEnd}", (float) $bast->entryManifest()->sum('koli'));
                $s->setCellValue("G{$rowEnd}", (float) $bast->entryManifest()->sum('brutto'));

                // set their style
                $s->getStyle("A{$rowEnd}:N{$rowEnd}")->applyFromArray([
                    'font' => [
                        'bold' => true
                    ]
                ]);

                // border them all and center em all?
                $s->getStyle("A8:N{$rowEnd}")->applyFromArray([
                    'alignment' => [ 'horizontal' => 'center', 'vertical' => 'center' ],
                    'borders' => [ 'allBorders' => [ 'borderStyle' => 'thin' ] ]
                ])->getAlignment()->setWrapText(true);

                // autosize some columns
                $s->getColumnDimension('A')->setAutoSize(true);
                $s->getColumnDimension('B')->setAutoSize(true);
                $s->getColumnDimension('C')->setAutoSize(true);
                $s->getColumnDimension('D')->setAutoSize(true);
                $s->getColumnDimension('H')->setAutoSize(true);
                $s->getColumnDimension('I')->setAutoSize(true);
                $s->getColumnDimension('M')->setAutoSize(true);
                $s->getColumnDimension('N')->setAutoSize(true);

                // set fixed width
                $s->getColumnDimension('E')->setWidth(10);
                $s->getColumnDimension('F')->setWidth(8.5);
                $s->getColumnDimension('G')->setWidth(8.5);
                $s->getColumnDimension('J')->setWidth(25);
                $s->getColumnDimension('K')->setWidth(25);
                $s->getColumnDimension('L')->setWidth(35);
                
                // Yang Melakukan Penarikan
                ++$rowEnd;
                $s->mergeCells("A{$rowEnd}:N{$rowEnd}");
                $s->setCellValue("A{$rowEnd}", "Yang Melakukan Penarikan");
                $s->getStyle("A{$rowEnd}")->applyFromArray([
                    'font' => [
                        'bold' => true
                    ],
                    'alignment' => [ 'horizontal' => 'center' ]
                ]);

                // nama petugas
                $rowEnd += 4;
                $s->mergeCells("A{$rowEnd}:D{$rowEnd}");
                $s->setCellValue("A{$rowEnd}", $bast->petugas->name);

                $s->mergeCells("L{$rowEnd}:N{$rowEnd}");
                $s->setCellValue("L{$rowEnd}", "(                                                     )");
                
                // NIP Petugas + Petugas dari XXX
                ++$rowEnd;
                $s->mergeCells("A{$rowEnd}:D{$rowEnd}");
                $s->setCellValue("A{$rowEnd}", "NIP " . $bast->petugas->nip);

                $s->mergeCells("L{$rowEnd}:N{$rowEnd}");
                $s->setCellValue("L{$rowEnd}", "Petugas TPS " . $bast->entryManifest[0]->tps->nama);
                // right align it
                $rowStart = $rowEnd-1;
                $s->getStyle("A{$rowStart}:L{$rowEnd}")->applyFromArray([
                    'alignment' => [ 'horizontal' => 'center' ]
                ]);
            }
        ];
    }
}
