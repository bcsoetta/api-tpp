<?php

use App\BCP;
use App\EntryManifest;
use Illuminate\Database\Seeder;

class BCPSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // generate less than all entry manifest
        $n = random_int(2, EntryManifest::count());

        echo "Generating $n random BCP...\n";

        $i = 0;
        while ($i++ < $n) {
            // spawn a BCP
            $b = new BCP([
                'tgl_dok' => date('Y-m-d'),
                'jenis' => 'BTD'
            ]);

            $b->entryManifest()->associate(EntryManifest::belumBCP()->inRandomOrder()->first());

            $b->save();
            $b->setNomorDokumen();
        }

        echo "BCP generated.\n";
    }
}
