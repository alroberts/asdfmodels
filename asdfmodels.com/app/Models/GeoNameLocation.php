<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeoNameLocation extends Model
{
    protected $table = 'geonames_locations';

    protected $primaryKey = 'geoname_id';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'geoname_id',
        'name',
        'ascii_name',
        'alternate_names',
        'latitude',
        'longitude',
        'feature_class',
        'feature_code',
        'country_code',
        'admin1_code',
        'admin2_code',
        'population',
        'timezone',
        'modification_date',
    ];
}


