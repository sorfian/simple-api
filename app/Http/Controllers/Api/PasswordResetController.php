<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Notifications\PasswordResetRequest;
use App\Notifications\PasswordResetSuccess;
use Illuminate\Support\Str;
use App\User;
use App\PasswordReset;
use Hash;

class PasswordResetController extends Controller
{
    // Membuat request untuk reset password berdasarkan
    // email yang telah terdaftar.

    // Menambahkan data email di tabel password_resets.
    // Menambahkan data token dengan 60 string random di tabel password_resets.

    // Mengirimkan token untuk verifikasi perubahan 
    // password ke email.


    public function create(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);
        $user = User::where('email', $request->email)->first();
        if (!$user){
            return response()->json([
                'message' => 'We cant find a user with that e-mail address.'
            ], 404);
        }
        $passwordReset = PasswordReset::updateOrCreate(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'token' => Str::random(60)
             ]
        );
        if ($user && $passwordReset){
            $user->notify(new PasswordResetRequest($passwordReset->token));
        }
        return response()->json([
            'message' => 'We have e-mailed your password reset link!'
        ]);
    }

    // Validasi link yang berisi token yang ada di email.

    // Jika token tidak sesuai maka tidak bisa
    // lanjut ke update password.

    // Jika melebihi 12 jam sejak token di create maka rows yang
    // berisi email dan token otomatis dihapus.

    public function find($token)
    {
        $passwordReset = PasswordReset::where('token', $token)->first();
        if (!$passwordReset){
            return response()->json([
                'message' => 'This password reset token is invalid.'
            ], 404);
        }
        if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
            $passwordReset->delete();
            return response()->json([
                'message' => 'This password reset token is invalid.'
            ], 404);
        }
        return response()->json($passwordReset);
    }

    // Input email, password dan token_get_all

    // Validasi jika email tidak terdaftar, maka update 
    // password akan gagal.

    // Validasi jika token dan email cocok dengan database, maka
    // password di kolom password tabel user akan diupdate.

    // Rows yang berisi email dan token untuk request
    // reset password dihapus setelah sukses update password.

    // Mengirimkan email notifikasi sukses jika update password
    // berhasil dilakukan.

    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|confirmed',
            'token' => 'required|string'
        ]);
        $passwordReset = PasswordReset::where([
            ['token', $request->token],
            ['email', $request->email]
        ])->first();
        if (!$passwordReset){
            return response()->json([
                'message' => 'This password reset token is invalid.'
            ], 404);
        }

        $user = User::where('email', $passwordReset->email)->first();
        if (!$user){
            return response()->json([
                'message' => 'We cant find a user with that e-mail address.'
            ], 404);
        }
        $user->password = Hash::make($request->password);
        $user->save();
        $passwordReset->delete();
        $user->notify(new PasswordResetSuccess($passwordReset));
        return response()->json($user);
    }
}
