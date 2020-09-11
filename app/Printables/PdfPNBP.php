<?php
namespace App\Printables;

use App\PNBP;
use App\Setting;
use Fpdf\Fpdf;

class PdfPNBP extends Fpdf {
    protected $pnbp;

    public function __construct(PNBP $p)
    {
        parent::__construct('P', 'mm', 'A4');

        $this->pnbp = $p;
    }

    public function printFirstPage() {
        if (!$this->pnbp) {
            throw new \Exception("Can't print PNBP without PNBP-data");
        }

        $nama_importir = $this->pnbp->entryManifest->nama_importir;
        $alamat_importir = $this->pnbp->entryManifest->alamat_importir;

        $dok_penyelesaian = $this->pnbp->entryManifest->penyelesaian[0];
        if (!$dok_penyelesaian) {
            throw new \Exception("Dokumen penyelesaian tidak ditemukan!");
        }

        $jenis_dok_pengeluaran = $dok_penyelesaian->referensiJenisDokumen->nama;
        $no_dok_pengeluaran = $dok_penyelesaian->nomor_lengkap_dok;
        $tgl_dok_pengeluaran = formatTanggalDMY($dok_penyelesaian->tgl_dok);
        $mawb = $this->pnbp->entryManifest->mawb;
        $hawb = $this->pnbp->entryManifest->hawb;
        $koli = (int) $this->pnbp->entryManifest->koli;
        $brutto = (float) $this->pnbp->entryManifest->brutto;
        $uraian = implode(";\n", $this->pnbp->entryManifest->detailBarang()->pluck('uraian')->toArray());

        $tgl_gate_in = formatTanggalDMY($this->pnbp->tgl_gate_in);
        $tgl_gate_out = formatTanggalDMY($this->pnbp->tgl_gate_out);
        $total_hari = (int) $this->pnbp->total_hari;
        $tarif_per_kg_per_hari = (float) $this->pnbp->tarif_pnbp;
        $nilai_sewa_gudang_tpp = $brutto * $total_hari * $tarif_per_kg_per_hari;

        $no_pnbp = $this->pnbp->nomor_lengkap_dok;
        $tgl_pnbp = formatTanggalDMY($this->pnbp->tgl_dok);

        $nm_jabatan_big_boss = $this->pnbp->nama_bidang;
        $nm_jabatan = $this->pnbp->nama_jabatan;
        $nm_pejabat = $this->pnbp->pejabat->name;

        // generic data
        $alamat_kop = "AREA CARGO BANDARA SOEKARNO-HATTAKOTAK POS - 1023, CENGKARENG 19111\nTELEPON: 021-5502072, 5502056, 5507056 FAKSIMILI:5502105";

        $nama_bank = Setting::getValue('nama_bank');
        $nomor_rekening_pnbp = Setting::getValue('nomor_rekening_pnbp');
        $nama_rekening_pnbp = Setting::getValue('nama_rekening_pnbp');

        if (!$nama_bank || !$nomor_rekening_pnbp || !$nama_rekening_pnbp) {
            throw new \Exception("Data setting akun rekening PNBP tidak lengkap! Cek kembali setting aplikasi!");
        }

        // make new page
        $p = $this;
        $p->SetAutoPageBreak(true, 5);
        $p->SetMargins(15, 5, 15);

        $p->AddPage();
        $p->SetFont('Arial','',8);

        // print logo
        $p->Image(__DIR__.'/logo.jpg',null,null,25,25);

        // kop surat
        $p->SetXY(47.5, 8.5);
        $p->SetFont('Arial', 'B', 12);

        $p->MultiCell(
            125, 
            5, 
            "KEMENTERIAN KEUANGAN REPUBLIK INDONESIA\nDIREKTORAT JENDERAL BEA DAN CUKAI\nKANTOR PELAYANAN UTAMA BEA DAN CUKAI\nTIPE C SOEKARNO-HATTA",
            0,
            'C'
        );

        // alamat kop
        $p->SetFont('Arial', 'B', 8);
        $p->SetX(47.5);
        $p->MultiCell(125, 4, $alamat_kop, 0, 'C');

        // double line
        $p->SetY($p->GetY()+1);
        $p->Line(15, $p->GetY(), $p->GetPageWidth()-15, $p->GetY());
        $p->SetY($p->GetY()+0.75);
        $p->Line(15, $p->GetY(), $p->GetPageWidth()-15, $p->GetY());

        // add some more line
        $p->Ln(8);
        $p->SetFont('Arial', 'BU', 11);
        $p->Cell(0, 5.5, 'SURAT PENAGIHAN BIAYA PENIMBUNAN GUDANG TPP', 0, 1, 'C');

        // Nomor + tgl PNBP
        $p->SetFont('Arial','',11);

        $p->SetX(40+15);
        $p->Cell(25, 5.5, "NOMOR", 0, 0);
            $p->Cell(0, 5.5, ": " . $no_pnbp, 0, 1);
        $p->SetX(40+15);
        $p->Cell(25, 5.5, "TANGGAL", 0, 0);
            $p->Cell(0, 5.5, ": " . $tgl_pnbp, 0, 1);

        // Kepada yth,
        $p->Cell(0, 6, "Kepada Yth,", 0, 1);

        // importir
        $p->Cell(25, 5.5, "Nama",0,0);
            $p->Cell(0, 5.5, ": " . $nama_importir, 0, 1);
        $p->Cell(25, 5.5, "Alamat",0,0);
            $p->Cell(0, 5.5, ": " . $alamat_importir, 0, 1);

        $p->Ln();

        // jenis dok pengeluaran
        $p->Cell(0, 6, "Dengan ini diberitahukan atas {$jenis_dok_pengeluaran}:", 0, 1);

        // nomor
        $p->Cell(40, 6, "Nomor", 0, 0);
            $p->Cell(0, 6, ": {$no_dok_pengeluaran} tanggal {$tgl_dok_pengeluaran}", 0, 1);
        // mawb/hawb
        $p->Cell(40, 6, "MAWB/HAWB", 0, 0);
            $p->Cell(0, 6, ": {$mawb}" . ($hawb ? " / {$hawb}" : ""), 0, 1);
        // jumlah/berat
        $berat = (float) $brutto;
        $p->Cell(40, 6, "Jumlah/Berat", 0, 0);
            $p->Cell(0, 6, ": {$koli} koli / {$berat} Kg", 0, 1);
        // uraian
        $p->Cell(40, 6, "Uraian Barang", 0, 0);
            $p->Cell(2, 6, ": ");
            $p->MultiCell(0, 6, $uraian);

        // ditetapkan blabla
        $p->Cell(0, 6, "Ditetapkan biaya penimbunan Gudang TPP dengan rincian sebagai berikut:", 0, 1);

        $bullet = chr(149);
        $bullet_width = 10;

        // tgl gate in
        $p->Cell(10, 6, $bullet, 0, 0, 'C');
        $p->Cell(105, 6, "Tanggal barang masuk Gudang TPP", 0, 0);
        $p->Cell(0, 6, ": {$tgl_gate_in}", 0, 1);
        // tgl gate out
        $p->Cell(10, 6, $bullet, 0, 0, 'C');
        $p->Cell(105, 6, "Tanggal barang dikeluarkan dari Gudang TPP", 0, 0);
        $p->Cell(0, 6, ": {$tgl_gate_out}", 0, 1);
        // total hari
        $p->Cell(10, 6, $bullet, 0, 0, 'C');
        $p->Cell(105, 6, "Total hari barang disimpan di Gudang TPP", 0, 0);
        $p->Cell(0, 6, ": {$total_hari} hari", 0, 1);
        // berat barang
        $p->Cell(10, 6, $bullet, 0, 0, 'C');
        $p->Cell(105, 6, "Berat barang", 0, 0);
        $p->Cell(0, 6, ": {$brutto} Kg", 0, 1);
        // biaya sewa
        $tarif_sewa = number_format($tarif_per_kg_per_hari, 2);
        $p->Cell(10, 6, $bullet, 0, 0, 'C');
        $p->Cell(105, 6, "Biaya sewa penumpukan barang di Gudang TPP", 0, 0);
        $p->Cell(0, 6, ": Rp {$tarif_sewa}/kg/hari", 0, 1);
        // biaya sewa
        $nilai_sewa = number_format($nilai_sewa_gudang_tpp, 2);
        $p->Cell(10, 6, $bullet, 0, 0, 'C');

        $text ="Nilai sewa Gudang di TPP: {$total_hari} hari x {$brutto} kgs x Rp {$tarif_sewa} = ";
        $endPosX = $p->GetX() + $p->GetStringWidth($text) + 1;
        $p->Cell(105, 6, $text, 0, 0);

        // nudge a bit to the right
        $p->SetX($endPosX);
        $p->SetFont('Arial', 'B', 18);
        $p->Cell(0, 9, "Rp {$nilai_sewa}", 1, 1, 'C');

        // add another line
        $p->Ln(3);
        $p->SetFont('Arial', '', 11);
        $text = "Pembayaran Biaya Penimbunan Gudang TPP harus dibayarkan pada tanggal dikeluarkan surat ini, jika pembayaran dilakukan di kemudian hari mohon konfirmasi terlebih dahulu pada Seksi Pabean dan Cukai (TPP).";
        $p->MultiCell(0, 5.5, $text);

        $p->SetX(25);
        $p->Cell(0, 6, "Atas perhatian dan kerjasamanya kami ucapkan terima kasih.", 0, 1);

        // 3 linespaces
        $p->Ln(18);

        $cur_x = $p->GetX();
        $cur_y = $p->GetY();

        // nomor rekening
        $nomor_rekening = "Nomor Rekening {$nama_bank}\n{$nomor_rekening_pnbp}\nAtas nama {$nama_rekening_pnbp}";
        $p->MultiCell(80, 5.5, $nomor_rekening, 1, 'L');

        // a.n.
        $p->SetXY(102.5+15, $cur_y-2.5);
        $p->Cell(7.5, 5.5, 'a.n.', 0, 0);

        $p->MultiCell(0, 5.5, "Kepala {$nm_jabatan_big_boss}\nKepala Seksi {$nm_jabatan}\n\n\n\n{$nm_pejabat}", 0, 'L');

        // rangkap 2
        $p->Ln();
        $p->Cell(0, 6, "Surat ini dibuat rangkap 2 (dua):", 0, 1);
        $p->Cell(0, 6, "Rangkap ke-1 untuk importir;", 0, 1);
        $p->Cell(0, 6, "Rangkap ke-2 untuk KPUBC Tipe C Soekarno-Hatta", 0);
    }
}


