<?php

namespace App\Http\Controllers\V1\Normal\Controllers;

use App\Category;
use App\Http\Controllers\V1\Normal\Resources\Category\CategoryCollection;
use App\Http\Controllers\V1\Normal\Resources\Category\CategoryResource;
use Illuminate\Http\Request;

class CategoryController extends BaseController
{
    protected $model;
    public function __construct()
    {
        $this->model = new Category();
    }
    public function search(Request $request)
    {
        $banners = $this->model->search($request->all());
        return new CategoryCollection($banners);
    }
    public function detailById($id)
    {
        $banner = $this->model->find($id);
        if (!$banner) {
            return response()->json([
                'status' => 'error',
                'message' => 'Category không tồn tại'
            ], 400);
        }
        return new CategoryResource($banner);
    }
    public function detailByCode($code)
    {
        $banner = $this->model->where('code', $code)->first();
        if (!$banner) {
            return response()->json([
                'status' => 'error',
                'message' => 'Category không tồn tại'
            ], 400);
        }
        return new CategoryResource($banner);
    }
    public function create(Request $request)
    {
        $attributes = $this->validate($request, [
            'code' => ['required', 'string', 'max:50', function ($attribute, $value, $fail) {
                $banner = $this->model->where('code', $value)->first();
                if ($banner) {
                    $fail('Mã banner [' . $value . '] đã tồn tại');
                }
            }],
            'name' => 'required|string|max:100',
            'parent_id' => 'nullable|integer|exists:categories,id,deleted_at,NULL',
        ], [
            'code.required' => 'Mã banner không được để trống',
            'code.unique' => 'Mã banner đã tồn tại',
            'code.string' => 'Mã banner phải là chuỗi',
            'code.max' => 'Mã banner không được quá 50 ký tự',
            'name.required' => 'Tên banner không được để trống',
            'name.string' => 'Tên banner phải là chuỗi',
            'name.max' => 'Tên banner không được quá 100 ký tự',
            'parent_id.integer' => 'Id danh mục cha phải là số nguyên',
            'parent_id.exists' => 'Id danh mục cha không tồn tại',
        ]);
        $attributes['slug'] = str_slug($attributes['name'] . '-' . $attributes['code']);

        $banner = $this->model->create($attributes);
        return new CategoryResource($banner);
    }

    public function update($id, Request $request)
    {
        $attributes = $this->validate($request, [
            'code' => ['required', 'string', 'max:50', function ($attribute, $value, $fail) use ($id) {
                $banner = $this->model->where('code', $value)->where('id', '!=', $id)->first();
                if ($banner) {
                    $fail('Mã banner [' . $value . '] đã tồn tại');
                }
            }],
            'name' => 'required|string|max:100',
            'parent_id' => 'nullable|integer|exists:categories,id,deleted_at,NULL',
        ], [
            'code.required' => 'Mã banner không được để trống',
            'code.unique' => 'Mã banner đã tồn tại',
            'code.string' => 'Mã banner phải là chuỗi',
            'code.max' => 'Mã banner không được quá 50 ký tự',
            'name.required' => 'Tên banner không được để trống',
            'name.string' => 'Tên banner phải là chuỗi',
            'name.max' => 'Tên banner không được quá 100 ký tự',
            'parent_id.integer' => 'Id danh mục cha phải là số nguyên',
            'parent_id.exists' => 'Id danh mục cha không tồn tại',
        ]);
        $attributes['slug'] = str_slug($attributes['name'] . '-' . $attributes['code']);

        $banner = $this->model->find($id);
        $banner->update($attributes);
        return new CategoryResource($banner);
    }
    public function delete($id)
    {
        $banner = $this->model->find($id);
        if (!$banner) {
            return response()->json([
                'status' => 'error',
                'message' => 'Banner không tồn tại'
            ], 400);
        }
        $banner->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Xóa banner thành công'
        ], 200);
    }
}
