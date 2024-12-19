<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryEditRequest;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    private function categoryQuery()
    {
        return Category::with('transaction')
            ->withSum('transaction as total_transaction', 'amount')->get();
    }

    public function createCategory(CategoryRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        $category = Category::where('name', $data['name'])->first();

        if (!$category) {
            $data = Category::create($data);

            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Create category success',
                'data'    => new CategoryResource($data)
            ]);
        }

        return response([
            'status'  => 'bad request',
            'code'    => 400,
            'message' => 'Input data is not valid',
            'errors'  => [
                'name' => 'Category name already exist'
            ]
        ]);
    }

    public function getCategory(Request $request)
    {
        $categories = $this->categoryQuery()->where('user_id', $request->user()->id);

        return response()->json([
            'status'          => 'success',
            'code'            => 200,
            'message'         => 'Get category success',
            'all_transaction' => $categories->sum('total_transaction'),
            'data'            => CategoryResource::collection($categories),
        ], 200);
    }

    public function getCategoryById(Request $request, $id)
    {
        if (Gate::denies('private', category::find($id))) {
            return response()->json([
                'status'  => 'error',
                'code'    => 403,
                'message' => 'You can only see and modify your own category.'
            ], 403);
        }

        $category = $this->categoryQuery()->find($id);

        if ($category) {
            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Get category success',
                'data'    => new CategoryResource($category)
            ], 200);
        }

        return response([
            'status'  => 'not found',
            'code'    => 404,
            'message' => 'Category not found'
        ], 404);
    }

    public function updateCategory(CategoryEditRequest $request, $id)
    {
        if (Gate::denies('private', category::find($id))) {
            return response()->json([
                'status'  => 'error',
                'code'    => 403,
                'message' => 'You can only see and modify your own category.'
            ], 403);
        }

        $data = $request->validated();

        $category = Category::find($id);
        if ($category) {
            $category->update($data);
            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Update category success',
                'data'    => new CategoryResource($category)
            ]);
        }

        return response([
            'status'  => 'not found',
            'code'    => 404,
            'message' => 'Category not found'
        ], 404);
    }

    public function deleteCategory($id)
    {
        if (Gate::denies('private', category::find($id))) {
            return response()->json([
                'status'  => 'error',
                'code'    => 403,
                'message' => 'You can only see and modify your own category.'
            ], 403);
        }

        $category = Category::find($id);
        if ($category) {
            $category->delete();
            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Delete category success'
            ]);
        }

        return response([
            'status'  => 'not found',
            'code'    => 404,
            'message' => 'Category not found'
        ], 404);
    }

}
