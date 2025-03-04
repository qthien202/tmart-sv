<?php

namespace App\Http\Controllers\V1\Normal\Controllers;

use App\Banner;
use App\Http\Controllers\V1\Normal\Resources\Banner\BannerCollection;
use App\Http\Controllers\V1\Normal\Resources\Banner\BannerResource;
use Illuminate\Http\Request;

class BannerController extends BaseController
{
    protected $model;
    public function __construct()
    {
        $this->model = new Banner();
    }
    public function search(Request $request)
    {
        $banners = $this->model->search($request->all());
        return new BannerCollection($banners);
    }
    public function detailById($id)
    {
        $banner = $this->model->find($id);
        if (!$banner) {
            return response()->json([
                'status' => 'error',
                'message' => 'Banner không tồn tại'
            ], 400);
        }
        return new BannerResource($banner);
    }
    public function detailByCode($code)
    {
        $banner = $this->model->where('code', $code)->first();
        if (!$banner) {
            return response()->json([
                'status' => 'error',
                'message' => 'Banner không tồn tại'
            ], 400);
        }
        return new BannerResource($banner);
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
            'is_active' => 'required|boolean',
            'details' => 'required|array',
            'details.*.banner_id' => 'nullable|integer|exists:banners,id,deleted_at,NULL',
            'details.*.image' => 'required|string',
            'details.*.link' => 'nullable|string',
        ], [
            'code.required' => 'Mã banner không được để trống',
            'code.unique' => 'Mã banner đã tồn tại',
            'code.string' => 'Mã banner phải là chuỗi',
            'code.max' => 'Mã banner không được quá 50 ký tự',
            'name.required' => 'Tên banner không được để trống',
            'name.string' => 'Tên banner phải là chuỗi',
            'name.max' => 'Tên banner không được quá 100 ký tự',
            'is_active.required' => 'Trạng thái không được để trống',
            'is_active.boolean' => 'Trạng thái phải là boolean',
            'details.required' => 'Chi tiết banner không được để trống',
            'details.array' => 'Chi tiết banner phải là mảng',
            'details.*.banner_id.integer' => 'Id banner phải là số nguyên',
            'details.*.banner_id.exists' => 'Id banner không tồn tại',
            'details.*.image.required' => 'Hình ảnh không được để trống',
            'details.*.image.string' => 'Hình ảnh phải là chuỗi',
            'details.*.link.string' => 'Link phải là chuỗi',
        ]);
        $attributes['slug'] = str_slug($attributes['name'] . '-' . $attributes['code']);

        $banner = $this->model->create($attributes);
        $banner->details()->createMany($attributes['details']);
        return new BannerResource($banner);
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
            'is_active' => 'required|boolean',
            'details' => 'required|array',
            'details.*.banner_id' => 'nullable|integer|exists:banners,id,deleted_at,NULL',
            'details.*.image' => 'required|string',
            'details.*.link' => 'nullable|string',
        ], [
            'code.required' => 'Mã banner không được để trống',
            'code.unique' => 'Mã banner đã tồn tại',
            'code.string' => 'Mã banner phải là chuỗi',
            'code.max' => 'Mã banner không được quá 50 ký tự',
            'name.required' => 'Tên banner không được để trống',
            'name.string' => 'Tên banner phải là chuỗi',
            'name.max' => 'Tên banner không được quá 100 ký tự',
            'is_active.required' => 'Trạng thái không được để trống',
            'is_active.boolean' => 'Trạng thái phải là boolean',
            'details.required' => 'Chi tiết banner không được để trống',
            'details.array' => 'Chi tiết banner phải là mảng',
            'details.*.banner_id.integer' => 'Id banner phải là số nguyên',
            'details.*.banner_id.exists' => 'Id banner không tồn tại',
            'details.*.image.required' => 'Hình ảnh không được để trống',
            'details.*.image.string' => 'Hình ảnh phải là chuỗi',
            'details.*.link.string' => 'Link phải là chuỗi',
        ]);
        $attributes['slug'] = str_slug($attributes['name'] . '-' . $attributes['code']);

        $banner = $this->model->find($id);
        $banner->update($attributes);
        $banner->details()->delete();
        $banner->details()->createMany($attributes['details']);
        return new BannerResource($banner);
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
        $banner->details()->delete();
        $banner->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Xóa banner thành công'
        ], 200);
    }
}
