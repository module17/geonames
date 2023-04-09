<?php

namespace MichaelDrennen\Geonames\Console;

use Exception;
use Illuminate\Console\Command;
use MichaelDrennen\Geonames\Models\GeoSetting;
use MichaelDrennen\Geonames\Models\Log;

class AddCountryGeonames extends Command {

    use GeonamesConsoleTrait;

    /**
     * @var string The name and signature of the console command.
     */
    protected $signature = 'geonames:add-country
        {--connection= : If you want to specify the name of the database connection you want used.} 
        {--country=* : Add the 2 digit code for each country. One per option.}      
        {--language=* : Add the 2 character language code.} 
        {--storage=geonames : The name of the directory, rooted in the storage_dir() path, where we store all downloaded files.}';

    /**
     * @var string The console command description.
     */
    protected $description = "Download and insert geonames for a specific country.";

    /**
     * @var float When this command starts.
     */
    protected $startTime;

    /**
     * @var float When this command ends.
     */
    protected $endTime;

    /**
     * @var float The number of seconds that this command took to run.
     */
    protected $runTime;


    /**
     * Initialize constructor.
     */
    public function __construct() {
        parent::__construct();
    }


    /**
     * Execute the console command.
     *
     * @return bool
     * @throws \Exception
     */
    public function handle() {
        $this->startTimer();

        try {
            $this->setDatabaseConnectionName();
            $this->info( "The database connection name was set to: " . $this->connectionName );
            $this->comment( "Testing database connection..." );
            $this->checkDatabase();
            $this->info( "Confirmed database connection set up correctly." );
        } catch ( \Exception $exception ) {
            $this->error( $exception->getMessage() );
            $this->error( $exception->getTraceAsString() );
            $this->stopTimer();
            throw $exception;
        }

        try {
            $this->info( "GeoSetting::add-country() called on connection: " . $this->connectionName );

            GeoSetting::install(
                $this->option( 'country' ),
                $this->option( 'language' ),
                $this->option( 'storage' ),
                $this->connectionName
            );
        } catch ( \Exception $exception ) {
            Log::error( NULL,
                        "Unable to install the GeoSetting record: " . $exception->getMessage(),
                        'exception',
                        $this->connectionName );
            $this->stopTimer();
            throw $exception;
        }


        GeoSetting::setStatus( GeoSetting::STATUS_INSTALLING, $this->connectionName );

        $emptyDirResult = GeoSetting::emptyTheStorageDirectory( $this->connectionName );
        if ( $emptyDirResult === TRUE ):
            $this->line( "This storage dir has been emptied: " . GeoSetting::getAbsoluteLocalStoragePath( $this->connectionName ) );
        endif;

        $this->line( "Starting " . $this->signature );

        try {
                $this->call( 'geonames:geoname',
                             [ '--connection' => $this->connectionName ] );

                $this->call( 'geonames:alternate-name',
                [ '--country'    => $this->option( 'country' ),
                '--connection' => $this->connectionName ] );
        } catch ( \Exception $e ) {
            $this->error( $e->getMessage() );
            $this->error( $e->getFile() . ':' . $e->getLine() . "\n" . $e->getTraceAsString() );
            GeoSetting::setStatus( GeoSetting::STATUS_ERROR, $this->connectionName );

            throw $e;
        }

        GeoSetting::setInstalledAt( $this->connectionName );
        GeoSetting::setStatus( GeoSetting::STATUS_LIVE, $this->connectionName );
        $emptyDirResult = GeoSetting::emptyTheStorageDirectory( $this->connectionName );
        if ( $emptyDirResult === TRUE ):
            $this->line( "Our storage directory has been emptied." );
        else:
            $this->error( "We were unable to empty the storage directory." );
        endif;

        Log::insert(
            '',
            "Geonames has been installed. Runtime: " . $this->runTime,
            "install",
            $this->connectionName );

        $this->line( "Finished " . $this->signature );

        $this->call( 'geonames:status',
                     [ '--connection' => $this->connectionName ] );
    }

}