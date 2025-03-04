<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Class UserSession
 *
 * @package App
 */
class VietNamListDistrict extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'vietnam_list_districts';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getDistrict(){
        return $this->hasMany(VietNamListDistrict::class,'city_code','city_code');
    }

    public function getWard(){
        return $this->hasMany(VietNamListDistrict::class,'district_code','district_code');
    }

    /**
     * @param $query
     * @param $request
     * @return mixed.
     */
    public function scopeSearchProvince($query, $request)
    {
        if ($value = $request->get('code')) {
            $value = Str::upper($value);
            $query->whereRaw("UPPER(city_code) LIKE '%{$value}%'");
        }
        if ($value = $request->get('name')) {
            $value = Str::upper($value);
            $query->whereRaw("UPPER(city_full_name) LIKE '%{$value}%'");
        }
        $query->select(['city_code', 'city_type', 'city_name', 'city_full_name']);
        $query->distinct();
        return $query;
    }

    /**
     * @param $query
     * @param $request
     * @return mixed
     */
    public function scopeSearchDistrict($query, $request)
    {
        if ($value = $request->get('code')) {
            $value = Str::upper($value);
            $query->whereRaw("UPPER(district_code) LIKE '%{$value}%'");
        }
        if ($value = $request->get('name')) {
            $value = Str::upper($value);
            $query->whereRaw("UPPER(district_full_name) LIKE '%{$value}%'");
        }
        $query->select(['district_code', 'district_type', 'district_name', 'district_full_name']);
        $query->distinct();
        return $query;
    }

    /**
     * @param $query
     * @param $request
     * @return mixed
     */
    public function scopeSearchWard($query, $request)
    {
        if ($value = $request->get('code')) {
            $value = Str::upper($value);
            $query->whereRaw("UPPER(ward_code) LIKE '%{$value}%'");
        }
        if ($value = $request->get('name')) {
            $value = Str::upper($value);
            $query->whereRaw("UPPER(ward_full_name) LIKE '%{$value}%'");
        }
        $query->select(['ward_code', 'ward_type', 'ward_name', 'ward_full_name','level']);
        $query->distinct();
        return $query;
    }
}
