<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    const TABLE = 'geonames_country_info';

    /**
     * Run the migrations.
     * 
     * Data source: http://download.geonames.org/export/dump/countryInfo.txt
     * 
     * Sample row:
     * 
     * #ISO	ISO3	ISO-Numeric	fips	Country	Capital	Area(in sq km)	Population	Continent	tld	CurrencyCode	CurrencyName	Phone	Postal Code Format	Postal Code Regex	Languages	geonameid	neighbours	EquivalentFipsCode
     * AD	AND	020	AN	Andorra	Andorra la Vella	468	77006	EU	.ad	EUR	Euro	376	AD###	^(?:AD)*(\d{3})$	ca	3041565	ES,FR	
     */
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->integer( 'geonameid', FALSE, TRUE )->primary();
            $table->char( 'iso2_code', 2 );
            $table->char( 'iso3_code', 3 );
            $table->char( 'iso_numeric', 3 );
            $table->char( 'fips_code', 2 );
            $table->string( 'country_name', 150 );
            $table->string( 'capital_city', 150 );
            $table->integer( 'area_sq_km');
            $table->integer( 'population');
            $table->char( 'continent', 2 );
            $table->string('tld', 4);
            $table->string('curreny_code', 4);
            $table->string('curreny_name', 100);
            $table->string('phone_format', 20);
            $table->string('postal_code_format', 250);
            $table->string('postal_code_regex', 250);
            $table->string('languages', 100);
            $table->string('neighbours', 100);
            $table->string('equivalent_fips_code', 100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(self::TABLE);
    }
};
