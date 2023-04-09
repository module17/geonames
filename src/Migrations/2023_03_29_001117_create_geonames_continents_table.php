<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class CreateGeonamesContinentsTable extends Migration {
    /**
     * This small table is filled with static continents data from geonames.org.
     * 
     * @throws \Exception
     */
    public function up() {
        Schema::create( 'geonames_continents', function ( Blueprint $table ) {
            // $table->engine = 'MyISAM'; @TODO: Do we need this?
            $table->integer('geonameid');
            $table->char('code', 2);
            $table->string('name', 40);
            $table->primary('geonameid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists( 'geonames_continents' );
    }
}
