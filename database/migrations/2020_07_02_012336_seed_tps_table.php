<?php

use App\TPS;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SeedTpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tps', function (Blueprint $table) {
            // read our csv files
            $csv = fopen(__DIR__.'/tps.csv' , 'r');

            while ($r = fgetcsv($csv)) {
                // dump($r);
                TPS::create([
                    'kode' => $r[0],
                    'nama' => $r[1],
                    'alamat' => $r[2],
                    'kode_kantor' => $r[3]
                ]);
            }

            fclose($csv);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tps', function (Blueprint $table) {
            //
        });
    }
}
