<?php

namespace App\Http\Controllers\V1\Auth\Controllers;

use App\Category;
use App\Http\Controllers\V1\Auth\Resources\Category\CategoryCollection;
use App\Http\Controllers\V1\Auth\Resources\Category\CategoryResource;
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
        $categories = $this->model->search($request->all());
        return new CategoryCollection($categories);
    }
    public function detailById($id)
    {
        $category = $this->model->find($id);
        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Category không tồn tại'
            ], 400);
        }
        return new CategoryResource($category);
    }
    public function detailByCode($code)
    {
        $category = $this->model->where('code', $code)->first();
        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Category không tồn tại'
            ], 400);
        }
        return new CategoryResource($category);
    }
    public function create(Request $request)
    {
        $attributes = $this->validate($request, [
            'code' => ['required', 'string', 'max:50', function ($attribute, $value, $fail) {
                $category = $this->model->where('code', $value)->first();
                if ($category) {
                    $fail('Mã category [' . $value . '] đã tồn tại');
                }
            }],
            'slug' => 'required|string|max:100',
            'name' => 'required|string|max:100',
            'parent_id' => 'nullable|integer|exists:categories,id,deleted_at,NULL',
        ], [
            'code.required' => 'Mã category không được để trống',
            'code.unique' => 'Mã category đã tồn tại',
            'code.string' => 'Mã category phải là chuỗi',
            'code.max' => 'Mã category không được quá 50 ký tự',
            'name.required' => 'Tên category không được để trống',
            'name.string' => 'Tên category phải là chuỗi',
            'name.max' => 'Tên category không được quá 100 ký tự',
            'parent_id.integer' => 'Id danh mục cha phải là số nguyên',
            'parent_id.exists' => 'Id danh mục cha không tồn tại',
        ]);
        // $attributes['slug'] = str_slug($attributes['name'] . '-' . $attributes['code']);
        $attributes['slug'] = str_slug($attributes['slug']);

        $category = $this->model->create($attributes);
        return $this->responseSuccess("Thêm category [$category->name] thành công");
    }

    public function update($id, Request $request)
    {
        $attributes = $this->validate($request, [
            'code' => ['sometimes','required', 'string', 'max:50', function ($attribute, $value, $fail) use ($id) {
                $category = $this->model->where('code', $value)->where('id', '!=', $id)->first();
                if ($category) {
                    $fail('Mã category [' . $value . '] đã tồn tại');
                }
            }],
            'slug' => 'sometimes|required|string|max:100',
            'name' => 'sometimes|required|string|max:100',
            'parent_id' => 'nullable|integer|exists:categories,id,deleted_at,NULL',
        ], [
            'code.required' => 'Mã category không được để trống',
            'code.unique' => 'Mã category đã tồn tại',
            'code.string' => 'Mã category phải là chuỗi',
            'code.max' => 'Mã category không được quá 50 ký tự',
            'name.required' => 'Tên category không được để trống',
            'name.string' => 'Tên category phải là chuỗi',
            'name.max' => 'Tên category không được quá 100 ký tự',
            'parent_id.integer' => 'Id danh mục cha phải là số nguyên',
            'parent_id.exists' => 'Id danh mục cha không tồn tại',
        ]);
        // $attributes['slug'] = str_slug($attributes['name'] . '-' . $attributes['code']);
        if (!empty($attributes['slug'])) {
            $attributes['slug'] = str_slug($attributes['slug']);
        }
        $category = $this->model->find($id);
        $category->update($attributes);
        return $this->responseSuccess("Sửa category [$category->name] thành công");
    }
    public function delete($id)
    {
        $category = $this->model->find($id);
        if (!$category) {
            return $this->responseError("Category không tồn tại");
            // return response()->json([
            //     'status' => 'error',
            //     'message' => 'Category không tồn tại'
            // ], 400);
        }
        $category->delete();
        return $this->responseSuccess("Xóa category thành công");
        // return response()->json([
        //     'status' => 'success',
        //     'message' => 'Xóa category thành công'
        // ], 200);
    }
}
