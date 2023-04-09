<?php
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Class ContinentSeeder
 *
 * Continent codes :
 * AF : Africa			geonameId=6255146
 * AS : Asia			geonameId=6255147
 * EU : Europe			geonameId=6255148
 * NA : North America		geonameId=6255149
 * OC : Oceania			geonameId=6255151
 * SA : South America		geonameId=6255150
 * AN : Antarctica			geonameId=6255152
 * 
 */
class ContinentSeeder extends Seeder {


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        DB::table('geonames_continents')->insert(
            ['code' => 'AF', 'name' => 'Africa', 'geonameid' => 6255146]);
        DB::table('geonames_continents')->insert(
            ['code' => 'AS', 'name' => 'Asia', 'geonameid' => 6255147]);
        DB::table('geonames_continents')->insert(
            ['code' => 'EU', 'name' => 'Europe', 'geonameid' => 6255148]);
        DB::table('geonames_continents')->insert(
            ['code' => 'NA', 'name' => 'North America', 'geonameid' => 6255149]);
        DB::table('geonames_continents')->insert(
            ['code' => 'OC', 'name' => 'Oceania', 'geonameid' => 6255151]);
        DB::table('geonames_continents')->insert(
            ['code' => 'SA', 'name' => 'South America', 'geonameid' => 6255150]);
        DB::table('geonames_continents')->insert(
            ['code' => 'AN', 'name' => 'Antarctica', 'geonameid' => 6255152]);
    }
}