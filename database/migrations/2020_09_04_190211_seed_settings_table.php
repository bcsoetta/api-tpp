<?php

use App\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeedSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // just create a new setting
        // TARIF_PNBP
        $s = Setting::create([
            'key' => 'tarif_pnbp',
            'description' => 'Tarif PNBP per kg per hari',
            'value' => 1125.0,   // sejauh ini masih Rp 1125/kg/hari
            'type' => 'NUMBER'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // should empty that? yep
        // DB::table('setting')->truncate();
    }
}
