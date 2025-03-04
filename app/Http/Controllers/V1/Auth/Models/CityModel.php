<?php

namespace App\Http\Controllers\V1\Auth\Models;


use App\City;

/**
 * Class CityModel
 * @package App\V1\CMS\Models
 */
class CityModel extends AbstractModel
{
    /**
     * CityModel constructor.
     * @param City|null $model
     */
    public function __construct(City $model = null)
    {
        parent::__construct($model);
    }
}