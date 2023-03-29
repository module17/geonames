<?php

namespace MichaelDrennen\Geonames\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MichaelDrennen\Geonames\Models\GeoSetting;
use MichaelDrennen\Geonames\Models\Log;
use MichaelDrennen\Geonames\Models\CountryInfo as ModelsCountryInfo;
use MichaelDrennen\LocalFile\LocalFile;

/**
 * Class CountryInfo
 * @package MichaelDrennen\Geonames\Console
 */
class CountryInfo extends AbstractCommand {

    use GeonamesConsoleTrait;

    /**
     * @var string The name and signature of the console command.
     */
    protected $signature = 'geonames:country-info
    {--connection= : If you want to specify the name of the database connection you want used.}';

    /**
     * @var string The console command description.
     */
    protected $description = "Populate the country_info table.";

    /**
     *
     */
    const REMOTE_FILE_NAME = 'countryInfo.txt';


    /**
     * The name of our alternate names table in our database. Using constants here, so I don't need
     * to worry about typos in my code. My IDE will warn me if I'm sloppy.
     */
    const TABLE = 'geonames_country_info';

    /**
     * The name of our temporary/working table in our database.
     */
    const TABLE_WORKING = 'geonames_country_info_working';


    /**
     * Initialize constructor.
     */
    public function __construct() {
        parent::__construct();
    }


    /**
     * @return bool
     * @throws Exception
     */
    public function handle() {
        ini_set( 'memory_limit', -1 );
        $this->startTimer();
        $this->connectionName = $this->option( 'connection' );

        try {
            $this->setDatabaseConnectionName();
            $this->info( "The database connection name was set to: " . $this->connectionName );
            $this->comment( "Testing database connection..." );
            $this->checkDatabase();
            $this->info( "Confirmed database connection set up correctly." );
        } catch ( \Exception $exception ) {
            $this->error( $exception->getMessage() );
            $this->stopTimer();
            return FALSE;
        }


        try {
            GeoSetting::init(
                GeoSetting::DEFAULT_COUNTRIES_TO_BE_ADDED,
                GeoSetting::DEFAULT_LANGUAGES,
                GeoSetting::DEFAULT_STORAGE_SUBDIR,
                $this->connectionName );
        } catch ( \Exception $exception ) {
            Log::error( NULL,
                        "Unable to initialize the GeoSetting record.",
                        '',
                        $this->connectionName );
            $this->stopTimer();
            return -1;
        }

        $remoteUrl = GeoSetting::getDownloadUrlForFile( self::REMOTE_FILE_NAME );

        DB::connection( $this->connectionName )->table( self::TABLE )->truncate();

        try {
            $absoluteLocalPath = $this->downloadFile( $this, $remoteUrl, $this->connectionName );
        } catch ( \Exception $e ) {
            $this->error( $e->getMessage() );
            Log::error( $remoteUrl, $e->getMessage(), 'remote', $this->connectionName );

            return -2;
        }

        try {
            $this->insertWithEloquent( $absoluteLocalPath );
        } catch ( \Exception $exception ) {
            $this->error( $exception->getMessage() );
            return -3;
        }


        $this->info( "The country_info data was downloaded and inserted in " . $this->getRunTime() . " seconds." );
        return self::SUCCESS_EXIT;
    }

    /**
     * @param $localFilePath
     * @return array
     */
    protected function fileToArray($localFilePath) {
        $rows = [];
        if (($handle = fopen($localFilePath, "r")) !== false) {
            while (($data = fgetcsv($handle, 0, "\t")) !== false) {
                $rows[] = $data;
            }
            fclose($handle);
        }

        return $rows;
    }
    /**
     * Using Eloquent instead of LOAD DATA INFILE, because the rows in the downloaded file need to
     * be munged before they can be inserted.
     * Sample row:
     * AD	AND	020	AN	Andorra	Andorra la Vella	468	77006	EU	.ad	EUR	Euro	376	AD###	^(?:AD)*(\d{3})$	ca	3041565	ES,FR	
     *
     * @param string $localFilePath
     *
     * @throws \Exception
     */
    protected function insertWithEloquent( string $localFilePath ) {
        $numLines = LocalFile::lineCount( $localFilePath );

        $geonamesBar = $this->output->createProgressBar( $numLines );
        $geonamesBar->setFormat( "Inserting %message% %current%/%max% [%bar%] %percent:3s%%\n" );
        $geonamesBar->setMessage( 'country info' );
        $geonamesBar->advance();

        $rows = $this->fileToArray( $localFilePath );

        foreach ( $rows as $fields ) {
            // skip bad rows, including those that are commented out at the top of the file
            if (sizeof($fields) !== 19 || str_starts_with($fields[0], '#')) {
                continue;
            }
            $iso2Code = $fields[ 0 ];   // AD
            $iso3Code = $fields[ 1 ];   // AND
            $isoNumeric = $fields[ 2 ];   // 020
            $fipsCode = $fields[ 3 ];   // AN
            $countryName = $fields[ 4 ];   // Andorra
            $capitalCity = $fields[ 5 ];   // Andorra la Vella
            $areaSqKm = (int)$fields[ 6 ];   // 468
            $population = (int)$fields[ 7 ];
            $continent = $fields[ 8 ];
            $tld = $fields[ 9 ];
            $currencyCode = $fields[ 10 ];
            $currencyName = $fields[ 11 ];
            $phoneFormat = $fields[ 12 ];
            $postalCodeFormat = $fields[ 13 ];
            $postalCodeRegex = $fields[ 14 ];
            $languages = $fields[ 15 ];
            $geonameId = $fields[ 16 ];
            $neighbours = $fields[ 17 ];
            $equivalentFipsCode = $fields[ 18 ];
            
           

            ModelsCountryInfo::on( $this->connectionName )->create(
                [
                    'geonameid'    => $geonameId,
                    'iso2_code' => $iso2Code,
                    'iso3_code' => $iso3Code,
                    'iso_numeric' => $isoNumeric,
                    'fips_code' => $fipsCode,
                    'country_name' => $countryName,
                    'capital_city' => $capitalCity,
                    'area_sq_km' => $areaSqKm,
                    'population' => $population,
                    'continent' => $continent,
                    'tld' => $tld,
                    'curreny_code' => $currencyCode,
                    'curreny_name' => $currencyName,
                    'phone_format' => $phoneFormat,
                    'postal_code_format' => $postalCodeFormat,
                    'postal_code_regex' => $postalCodeRegex,
                    'languages' => $languages,
                    'neighbours' => $neighbours,
                    'equivalent_fips_code' => $equivalentFipsCode
                ] );
            $geonamesBar->advance();
        }
    }


    /**
     * TODO Why do I have this if I am using Eloquent?
     * @param $localFilePath
     * @throws \Exception
     */
    // protected function insertWithLoadDataInfile( $localFilePath ) {
    //     Schema::connection( $this->connectionName )->dropIfExists( self::TABLE_WORKING );
    //     DB::connection( $this->connectionName )
    //       ->statement( 'CREATE TABLE ' . self::TABLE_WORKING . ' LIKE ' . self::TABLE . ';' );

    //     // Windows patch
    //     $localFilePath = $this->fixDirectorySeparatorForWindows( $localFilePath );

    //     $query = "LOAD DATA LOCAL INFILE '" . $localFilePath . "'
    // INTO TABLE " . self::TABLE_WORKING . "
    //       ( geonameid,
    //         country_code,
    //         admin1_code,
    //         name,
    //         asciiname,
    //         @created_at, 
    //         @updated_at)
    // SET created_at=NOW(),updated_at=null";

    //     $this->line( "Inserting via LOAD DATA INFILE: " . $localFilePath );
    //     $rowsInserted = DB::connection( $this->connectionName )->getpdo()->exec( $query );
    //     if ( $rowsInserted === FALSE ) {
    //         Log::error( '', "Unable to load data infile for " . self::TABLE, 'database', $this->connectionName );
    //         throw new Exception( "Unable to execute the load data infile query. " . print_r( DB::connection( $this->connectionName )
    //                                                                                            ->getpdo()
    //                                                                                            ->errorInfo(), TRUE ) );
    //     }

    //     Schema::connection( $this->connectionName )->dropIfExists( self::TABLE );
    //     Schema::connection( $this->connectionName )->rename( self::TABLE_WORKING, self::TABLE );
    // }
}
