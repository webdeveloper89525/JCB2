<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'Zip_Code',
        'lng',
        'lat',
    ];

    public function scopeInCircle($query, $lat, $lon, $radius)
    {
        return $query->whereRaw('( 3958.7657 * acos( cos( radians(?) ) * cos( radians( lat ) ) * cos( radians( lng ) -  radians(?) ) + sin( radians(?) ) * sin( radians( lat ) ) ) ) <= ?', [$lat, $lon, $lat, $radius]);
    }
}
