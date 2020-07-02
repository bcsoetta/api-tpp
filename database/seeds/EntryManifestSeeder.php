<?php

use App\EntryManifest;
use App\TPS;
use Illuminate\Database\Seeder;

class EntryManifestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // spawn a faker
        $faker = Faker\Factory::create();

        $num = random_int(10, 32);
        $i=0;

        echo "Generating $num random EntryManifest...\n";

        while ($i++ < $num) {
            
            $e = new EntryManifest([
                'no_bc11' => $faker->numberBetween(100, 23456),
                'tgl_bc11' => $faker->date('Y-m-d'),

                'pos' => $faker->numberBetween(1,9999),
                'subpos' => $faker->numberBetween(1,9999),
                'subsubpos' => $faker->numberBetween(1,9999),

                'kd_flight' => strtoupper($faker->bothify("*****")),

                'koli' => $faker->numberBetween(1, 20),
                'brutto' => $faker->randomFloat(2,0.1,1000.0),

                'mawb' => strtoupper($faker->bothify("************")),
                'hawb' => strtoupper($faker->bothify("************")),

                'uraian' => $faker->text(),

                'nama_importir' => $faker->company,
                'alamat_importir' => $faker->address
            ]);

            // associate with random tps
            $e->tps()->associate(TPS::inRandomOrder()->first());

            $e->save();
        }

        echo "EntryManifest seeded.\n";
    }
}
