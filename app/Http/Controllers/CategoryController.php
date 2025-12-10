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
    /*
        mendapatkan query kategori dengan relasi transaksi dan total transaksi
    */
    private function categoryQuery()
    {
        return Category::with('transaction.user')
            ->withSum('transaction as total_transaction', 'amount')->get();
    }

    /*
        FUNGSI MEMBUAT KATEGORI BARU UNTUK CUANKEY
    */
    public function createCategory(CategoryRequest $request)
    {
        // ambil data yang telah divalidasi
        $data = $request->validated();
        // tambahkan ID user ke data kategori
        $data['user_id'] = $request->user()->id;
        // Cek apakah nama sudah ada
        $category = Category::where('name', $data['name'])->first();

        // Jika belum ada, kategori baru akan dibuat
        if (!$category) {
            $data = Category::create($data);

            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Create category success',
                'data'    => new CategoryResource($data)
            ]);
        }
        // Jika sudah ada kembalikan error
        return response([
            'status'  => 'bad request',
            'code'    => 400,
            'message' => 'Input data is not valid',
            'errors'  => [
                'name' => 'Category name already exist'
            ]
        ]);
    }

    /*
        FUNGSI MENDAPATKAN KATEGORI UNTUK DITAMPILKAN
    */
    public function getCategory(Request $request)
    {
        // filter kategori berdasarkan user ID
        $categories = $this->categoryQuery()->where('user_id', $request->user()->id);

        return response()->json([
            'status'          => 'success',
            'code'            => 200,
            'message'         => 'Get category success',
            'all_transaction' => $categories->sum('total_transaction'),
            'data'            => CategoryResource::collection($categories),
        ], 200);
    }

    /*
        FUNGSI MEMASTIKAN AGAR KATEGORI SESUAI ID MASING2 DAN TIDAK ADA
        PIHAK TIDAK SAH YANG AKSES
    */
    public function getCategoryById(Request $request, $id)
    {
        // Cek apakah user memiliki izin untuk melihat kategori ini
        if (Gate::denies('private', category::find($id))) {
            return response()->json([
                'status'  => 'error',
                'code'    => 403,
                'message' => 'You can only see and modify your own category.'
            ], 403);
        }

        // Cek kategori berdasarkan ID
        $category = $this->categoryQuery()->find($id);

        if ($category) {
            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Get category success',
                'data'    => new CategoryResource($category)
            ], 200);
        }

        // Jika kategori tidak ditemukan
        return response([
            'status'  => 'not found',
            'code'    => 404,
            'message' => 'Category not found'
        ], 404);
    }

    // FUNGSI MEMPERBARUI KATEGORI
    public function updateCategory(CategoryEditRequest $request, $id)
    {
        // Cek apakah user memiliki izin untuk memperbarui kategori ini
        if (Gate::denies('private', category::find($id))) {
            return response()->json([
                'status'  => 'error',
                'code'    => 403,
                'message' => 'You can only see and modify your own category.'
            ], 403);
        }

        // Validasi  data input
        $data = $request->validated();

        // Cari kategori berdasarkan ID
        $category = Category::find($id);
        if ($category) {
            // Perbarui Kategori
            $category->update($data);
            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Update category success',
                'data'    => new CategoryResource($category)
            ]);
        }
        // Jika kategori tidak ditemukan
        return response([
            'status'  => 'not found',
            'code'    => 404,
            'message' => 'Category not found'
        ], 404);
    }

    /*
        FUNGSI DELETE CATEGORY
    */
    public function deleteCategory($id)
    {
        // Cek apakah user memiliki izin untuk menghapus kategori
        if (Gate::denies('private', category::find($id))) {
            return response()->json([
                'status'  => 'error',
                'code'    => 403,
                'message' => 'You can only see and modify your own category.'
            ], 403);
        }
        // Mencari kategori by id
        $category = Category::find($id);
        if ($category) {
            // Delete kategori
            $category->delete();
            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Delete category success'
            ]);
        }
        // Jika kategori tidak ditemukan diberi pesan error
        return response([
            'status'  => 'not found',
            'code'    => 404,
            'message' => 'Category not found'
        ], 404);
    }

}
