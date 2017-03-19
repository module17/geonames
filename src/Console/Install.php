<?php

namespace MichaelDrennen\Geonames\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use MichaelDrennen\Geonames\GeoSetting;

class Install extends Command {

    use GeonamesConsoleTrait;

    /**
     * @var string The name and signature of the console command.
     */
    protected $signature = 'geonames:install 
        {--country=* : Add the 2 digit code for each country. One per option.} 
        {--language=* : Add the 2 character language code.} 
        {--storage=geonames : The name of the directory, rooted in the storage_dir() path, where we store all downloaded files.}';

    /**
     * @var string The console command description.
     */
    protected $description = "Run this after the migrations to populate the tables.";

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
     */
    public function handle() {

        print_r( $this->options() );

        GeoSetting::install( $this->option( 'country' ), $this->option( 'language' ), $this->option( 'storage' ) );

        GeoSetting::setStatus(GeoSetting::STATUS_INSTALLING);

        $emptyDirResult = GeoSetting::emptyTheStorageDirectory();
        if ( $emptyDirResult === true ) {
            $this->line( "This storage dir has been emptied: " . GeoSetting::getAbsoluteLocalStoragePath() );
        }


        $this->startTime = microtime(true);
        $this->line("Starting " . $this->signature);

        try {
            $this->call('geonames:feature-class');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            $this->error($e->getFile() . ':' . $e->getLine() . "\n" . $e->getTraceAsString());
            GeoSetting::setStatus(GeoSetting::STATUS_ERROR);
            return false;
        }

        try {
            $this->call('geonames:feature-code');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            $this->error($e->getTraceAsString());
            GeoSetting::setStatus(GeoSetting::STATUS_ERROR);
            return false;
        }

        try {
            $this->call( 'geonames:iso-language_code' );
        } catch ( \Exception $e ) {
            $this->error( $e->getMessage() );
            $this->error( $e->getTraceAsString() );
            GeoSetting::setStatus( GeoSetting::STATUS_ERROR );

            return false;
        }

        try {
            $this->call( 'geonames:alternate-name' );
        } catch ( \Exception $e ) {
            $this->error( $e->getMessage() );
            $this->error( $e->getTraceAsString() );
            GeoSetting::setStatus( GeoSetting::STATUS_ERROR );

            return false;
        }

        try {
            $this->call( 'geonames:geoname' );
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            $this->error($e->getTraceAsString());
            GeoSetting::setStatus(GeoSetting::STATUS_ERROR);
            return false;
        }


        $this->endTime = microtime(true);
        $this->runTime = $this->endTime - $this->startTime;

        GeoSetting::setInstalledAt();
        GeoSetting::setStatus(GeoSetting::STATUS_LIVE);
        $emptyDirResult = GeoSetting::emptyTheStorageDirectory();
        if ( $emptyDirResult === true ) {
            $this->line( "Our storage directory has been emptied." );
        }
        $this->line("Finished " . $this->signature);

        $this->call( 'geonames:status' );
    }





}
