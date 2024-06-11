<?php

namespace App;

use Illuminate\Support\Arr;

class Banner extends BaseModel
{
    protected $table = 'banners';
    protected $fillable = ['code', 'name', 'short_description','slug', 'is_active'];

    // Tìm kiếm banner theo các tiêu chí của client truyền xuống
    public function scopeSearch($query, $params)
    {
        $query->select('*');
        if (isset($params['code'])) {
            $query->where('code', $params['code']);
        }
        if (isset($params['name'])) {
            $query->where('name', 'like', '%' . $params['name'] . '%');
        }
        if (isset($params['slug'])) {
            $query->where('slug', $params['slug']);
        }
        if (isset($params['is_active'])) {
            $query->where('is_active', $params['is_active']);
        }
        return $query->paginate(Arr::get('limit', 10));
    }

    // Lấy chi tiết banner theo id (Relationship)
    public function details(){
        return $this->hasMany(BannerDetail::class, 'banner_id', 'id');
    }
}
