<?php

namespace MichaelDrennen\Geonames\Models;

use Illuminate\Database\Eloquent\Model;

class CountryInfo extends Model {

    protected $primaryKey = 'geonameid';
    protected $table      = 'geonames_country_info';
    protected $guarded    = [];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'geonameid' => 'integer',
        'area_sq_km' => 'integer',
        'population' => 'integer'
    ];
}