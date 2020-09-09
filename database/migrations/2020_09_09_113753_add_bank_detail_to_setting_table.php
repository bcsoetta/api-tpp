<?php

use App\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBankDetailToSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // nama bank
        Setting::create([
            'key' => 'nama_bank',
            'description' => 'Nama dan cabang Bank rekening PNBP',
            'value' => 'Bank Mandiri Cab. Terminal Kargo Bandara Soekarno-Hatta',
            'type' => 'STRING'
        ]);

        // nomor rekening
        Setting::create([
            'key' => 'nomor_rekening_pnbp',
            'description' => 'Nomor Rekening Bank akun PNBP',
            'value' => '116-00-8900352-0',
            'type' => 'STRING'
        ]);

        // nama rekening
        Setting::create([
            'key' => 'nama_rekening_pnbp',
            'description' => 'Nama pemilik Rekening Bank akun PNBP',
            'value' => 'RPL 127 KPUBC Tipe C Soekarno-Hatta',
            'type' => 'STRING'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Setting::where('key', 'nama_rekening_pnbp')->first()->delete();
        Setting::where('key', 'nomor_rekening_pnbp')->first()->delete();
        Setting::where('key', 'nama_bank')->first()->delete();
    }
}
