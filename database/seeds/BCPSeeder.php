<?php

use App\BCP;
use App\EntryManifest;
use App\Lokasi;
use App\TPS;
use App\Tracking;
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
        $faker = Faker\Factory::create();
        // generate less than all entry manifest
        $n = random_int(2, EntryManifest::count());

        echo "Generating $n random BCP...\n";

        $i = 0;
        while ($i++ < $n) {
            $m = EntryManifest::belumBCP()->inRandomOrder()->first();
            
            if ($m) {
                // spawn a BCP
                $b = new BCP([
                    'tgl_dok' => date('Y-m-d'),
                    'jenis' => $faker->randomElement(['BTD','BDN'])
                ]);
                // associate em?
                $m->bcp()->save($b);
                $b->setNomorDokumen();
                $m->appendStatus('GATEIN');

                // if it's BDN, nullify tps and add tracking to P2SH
                if ($b->jenis == 'BDN') {
                    $m->tps()->dissociate();
                    
                    $t = new Tracking();
                    $t->trackable()->associate($m);
                    $t->lokasi()->associate(Lokasi::byKode('P2SH')->first());
                    $t->save();
                } else {
                    // welp, just add tracking to it
                    $t = new Tracking();
                    $t->trackable()->associate($m);
                    $t->lokasi()->associate($m->tps);
                    $t->save();
                }

                // add new tracking location to TPP
                $t = new Tracking();
                $t->trackable()->associate($m);
                $t->lokasi()->associate(Lokasi::byKode('TPPSH')->first());
                $t->save();
            }

            // $b->entryManifest()->associate(EntryManifest::belumBCP()->inRandomOrder()->first());

            // $b->save();

            // update status of entryManifest too
            // $b->entryManifest()
        }

        echo "BCP generated.\n";
    }
}
