<?php

use Doctrine\DBAL\Schema\Schema as SchemaSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddManualFlagToPnbpTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pnbp', function (Blueprint $table) {
            // add manual flag with default value
            $table->boolean('manual')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pnbp', function (Blueprint $table) {
            // remove manual flag?
            if (Schema::hasColumn('pnbp','manual'))
                $table->dropColumn('manual');
        });
    }
}
