<?php

namespace Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('id_ID');

        $activities = [];
        $machines   = [];
        $operators  = [];
        $sites      = [];

        for ($i = 0; $i < 3; $i++) {
            $machines[] = DB::table('machines')->insertGetId([
                'created_at'    => date('Y-m-d H:i:s'),
                'name'          => ucwords(generateRandomString(5))
            ]);

            $operators[] = DB::table('operators')->insertGetId([
                'created_at'    => date('Y-m-d H:i:s'),
                'name'          => $faker->name($faker->randomElement($array = ['male', 'female']))
            ]);

            $sites[] = DB::table('sites')->insertGetId([
                'created_at'    => date('Y-m-d H:i:s'),
                'code'          => ucwords(generateRandomString(4))
            ]);
        }

        for ($i = 0; $i < 5; $i++) {
            $activities[] = $faker->sentence($nbWords = 3, $variableNbWords = true);
        }

        for ($i = 0; $i < 10; $i++) {
            DB::table('activities')->insert([
                'created_at'    => generateRandomTimestamp(),
                'operator_id'   => $faker->randomElement($array = $operators),
                'site_id'       => $faker->randomElement($array = $sites),
                'machine_id'    => $faker->randomElement($array = $machines),
                'activity'      => $faker->randomElement($array = $activities),
                'uom'           => $faker->randomElement($array = ['Pokok', 'Meter']),
                'block'         => rand(100, 200),
                'task'          => $faker->randomElement($array = ['A1', 'A2']),
                'start'         => '07:00',
                'end'           => '17:00',
                'fuel'          => rand(100, 200),
                'duty'          => 'On Duty'
            ]);
        }
    }
}
