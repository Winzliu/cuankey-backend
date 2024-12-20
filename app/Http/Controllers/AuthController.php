<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\Category;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use DB;
use Hash;
use Illuminate\Http\Request;


/*
<!-- 
 AuthController adalah pengontrol yang mengatur proses otentikasi dan pengelolaan pengguna dalam aplikasi. 
 Pengontrol ini mencakup fungsi registrasi, login, logout, pembaruan data pengguna, pembaruan kata sandi, 
 pengambilan data pengguna, dan penghapusan akun pengguna.
-->
*/

class AuthController extends Controller
{
    /*
    1. register(UserRegisterRequest $request)

    Deskripsi:
        Mendaftarkan pengguna baru, membuat token akses, serta mengatur kategori dan dompet awal untuk pengguna tersebut.

    Parameter:

        UserRegisterRequest $request: Objek permintaan yang berisi data registrasi pengguna.

    Proses Utama:

        Memvalidasi data input.

        Membuat data pengguna baru.

        Membuat token akses.

        Menambahkan kategori default (“Food & Beverage”, “Salary”, “Groceries”).

        Menambahkan dompet default (“Cash Wallet” dan “Bank Wallet”).

        Mengembalikan respons berhasil.

    */
    public function register(UserRegisterRequest $request)
    {
        // Memulai transaksi
        DB::beginTransaction();

        try {
            $data = $request->validated();
            $user_registed = User::create($data);
            // Membuat token berdasarkan form registrasi user bagian email
            $token = $user_registed->createToken($data['email']);
            // generate token
            $user_registed->token = $token->plainTextToken;
            $currentTimestamp = Carbon::now();

            $category_registed = [
                [
                    'name'        => 'Food & Beverage',
                    'icon'        => '🍕',
                    'description' => 'Cost for food and beverage',
                    'budget'      => null,
                    'type'        => 'Pengeluaran',
                    'user_id'     => $user_registed->id
                ],
                [
                    'name'        => 'Salary',
                    'icon'        => '💰',
                    'description' => 'Income from salary',
                    'budget'      => null,
                    'type'        => 'Pemasukan',
                    'user_id'     => $user_registed->id
                ],
                [
                    'name'        => 'Groceries',
                    'icon'        => '🛒',
                    'description' => 'Cost for groceries',
                    'budget'      => null,
                    'type'        => 'Pengeluaran',
                    'user_id'     => $user_registed->id
                ]
            ];
            // Membuat kategori default saat user registrasi
            Category::insert($category_registed);

            $wallet_registed = [
                [
                    "name"            => "Cash Wallet",
                    "initial_balance" => 0,
                    "is_active"       => 1,
                    'user_id'         => $user_registed->id,
                    'created_at'      => $currentTimestamp,
                    'updated_at'      => $currentTimestamp,
                ],
                [
                    "name"            => "Bank Wallet",
                    "initial_balance" => 0,
                    "is_active"       => 0,
                    'user_id'         => $user_registed->id,
                    'created_at'      => $currentTimestamp,
                    'updated_at'      => $currentTimestamp,
                ]
            ];
            // Membuat wallet default saat user registrasi
            Wallet::insert($wallet_registed);
            $user_registed->profile_picture = 1;

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Register success',
                'data'    => new UserResource($user_registed)
            ], 200);

            // memberi pesan error jika transaksi gagal
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status'  => 'bad request',
                'code'    => 400,
                'message' => 'Input data is not valid',
                'errors'  => [
                    'message' => $th->getMessage()
                ]
            ]);
        }
    }


    /*
        
    2. login(UserLoginRequest $request)

        Deskripsi:
            Mengotentikasi pengguna berdasarkan email dan kata sandi.

        Parameter:

            UserLoginRequest $request: Objek permintaan yang berisi kredensial login.

        Proses Utama:

            Memvalidasi data input.

            Mengecek kecocokan kata sandi.

            Membuat token akses jika valid.

            Mengembalikan respons berhasil atau gagal.
    */
    public function login(UserLoginRequest $request)
    {
        $data = $request->validated();
        $user = User::where('email', $data['email'])->first();
        // Mengecek kredensial user
        if (Hash::check($data['password'], $user->password ?? null)) {
            $token = $user->createToken($data['email']);
            $user->token = $token->plainTextToken;

            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Login success',
                'data'    => new UserResource($user)
            ], 200);
        }

        return response()->json([
            'status'  => 'bad request',
            'code'    => 400,
            'message' => 'Input data is not valid',
            'errors'  => [
                'message' => 'Email or Password is not valid'
            ]
        ], 400);
    }

    /*
        


3. logout(Request $request)

    Deskripsi:
        Menghapus semua token akses milik pengguna untuk melakukan logout.

    Parameter:

        Request $request: Objek permintaan.

    Proses Utama:

        Menghapus token akses pengguna saat ini.

        Mengembalikan respons berhasil.

    Respons:

        Status: 200 (Berhasil)

    */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();


        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Logout success'
        ], 200);
    }

    /*
        4. getUser(Request $request)

    Deskripsi:
        Mengambil informasi detail pengguna yang sedang login.

    Parameter:

        Request $request: Objek permintaan.

    Proses Utama:

        Mengambil data pengguna yang sedang login.

        Menyertakan pesan berhasil.

        Mengembalikan respons berhasil.

    Respons:

        Status: 200 (Berhasil)
    */
    public function getUser(Request $request)
    {
        $request->user()->message = "Get user success";

        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Get user success',
            'data'    => new UserResource($request->user())
        ], 200);
    }

    /*
        5. updateUser(Request $request)

    Deskripsi:
        Memperbarui data profil pengguna.

    Parameter:

        Request $request: Objek permintaan yang berisi data pembaruan.

    Proses Utama:

        Memvalidasi data input.

        Memperbarui data pengguna.

        Mengembalikan respons berhasil.

    Respons:

        Status: 200 (Berhasil)

    Validasi Input:

        fullname: string, panjang 3-255 karakter.

        phone_number: angka, panjang 8-15 digit.

        profile_picture: angka, nilai di antara 1-8.

    */
    public function updateUser(Request $request)
    {
        $data = $request->validate([
            "fullname"        => "sometimes|string|max:255|min:3",
            "phone_number"    => "sometimes|numeric|digits_between: 8,15",
            "profile_picture" => "sometimes|numeric|in:1,2,3,4,5,6,7,8"
        ]);

        $request->user()->update($data);

        $user = $request->user();
        $user->token = null;

        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Update user success',
            'data'    => new UserResource($user)
        ], 200);
    }

    /*
        
6. updateUserPassword(Request $request)

    Deskripsi:
        Memperbarui kata sandi pengguna.

    Parameter:

        Request $request: Objek permintaan yang berisi data pembaruan kata sandi.

    Proses Utama:

        Memvalidasi data input.

        Mengecek kesesuaian kata sandi lama.

        Memperbarui kata sandi jika valid.

        Mengembalikan respons berhasil atau gagal.

    Respons:

        Status: 200 (Berhasil)

        Status: 400 (Gagal)

    Validasi Input:

        old_password: wajib.

        new_password: wajib.
    */
    public function updateUserPassword(Request $request)
    {
        $data = $request->validate([
            "old_password" => "required",
            "new_password" => "required"
        ]);

        if (Hash::check($data['old_password'], $request->user()->password)) {
            $request->user()->update([
                "password" => Hash::make($data['new_password'])
            ]);

            return response()->json([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Update password success'
            ], 200);
        }

        return response()->json([
            'status'  => 'bad request',
            'code'    => 400,
            'message' => 'Input data is not valid',
            'errors'  => [
                'message' => 'Old password is not valid'
            ]
        ], 400);
    }

    /*
    7. deleteUser(Request $request)

    Deskripsi:
        Menghapus akun pengguna yang sedang login.

    Parameter:

        Request $request: Objek permintaan.

    Proses Utama:

        Menghapus token akses pengguna.

        Menghapus data pengguna.

        Mengembalikan respons berhasil.

    Validasi Input

        Semua data yang diterima akan divalidasi menggunakan aturan yang didefinisikan dalam request terkait seperti UserRegisterRequest dan UserLoginRequest.


    */
    public function deleteUser(Request $request)
    {
        $request->user()->tokens()->delete();
        $request->user()->delete();

        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Delete user success'
        ], 200);
    }
}
