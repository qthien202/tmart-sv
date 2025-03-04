<?php

namespace App;

class BannerDetail extends BaseModel
{
    protected $table = 'banner_details';
    protected $fillable = ['banner_id', 'image', 'link'];
}