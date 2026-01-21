<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | Controller ini menangani otentikasi user untuk aplikasi dan
    | mengarahkan mereka ke halaman utama setelah login sukses.
    |
    */

    use AuthenticatesUsers;

    /**
     * Mau diarahkan kemana setelah login?
     * Kita set ke '/' (Halaman Kalender) agar tidak 404 Not Found.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Tamu (guest) boleh masuk sini (untuk login).
        // Tapi fitur 'logout' hanya boleh diakses yang sudah login.
        $this->middleware('guest')->except('logout');
    }

    /**
     * KITA TAMBAHKAN INI UNTUK MEMPERBAIKI LOGOUT
     * Agar setelah logout, user diarahkan kembali ke halaman login,
     * bukan ke halaman kosong atau error.
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Redirect ke halaman login setelah logout sukses
        return redirect('/login');
    }
}