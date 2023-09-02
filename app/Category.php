<?php

namespace App;

use Illuminate\Support\Arr;

class Category extends BaseModel
{
    protected $table = 'categories';
    protected $fillable = ['id','code', 'name', 'slug', 'image', 'parent_id'];

    public function scopeSearch($query, $params)
    {
        $query->select('id', 'code', 'name', 'slug', 'parent_id', 'created_at', 'updated_at');
        if (isset($params['code'])) {
            $query->where('code', $params['code']);
        }
        if (isset($params['name'])) {
            $query->where('name', 'like', '%' . $params['name'] . '%');
        }
        if (isset($params['slug'])) {
            $query->where('slug', $params['slug']);
        }
        if (isset($params['parent_id'])) {
            $query->where('parent_id', $params['parent_id']);
        }
        return $query->paginate(Arr::get($params,'limit', 10));
    }

    public function child()
    {
        return $this->hasMany(Category::class, 'parent_id', 'id');
    }
}
