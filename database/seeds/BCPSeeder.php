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
            }

            // $b->entryManifest()->associate(EntryManifest::belumBCP()->inRandomOrder()->first());

            // $b->save();

            // update status of entryManifest too
            // $b->entryManifest()
        }

        echo "BCP generated.\n";
    }
}
