<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Auth;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{

    /**
     * Display login of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function login()
    {
        $title = "Login";
        $description = "Some description for the page";
        return view('auth.login', compact('title', 'description'));
    }

    /**
     * Display register of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function register()
    {
        $title = "Register";
        $description = "Some description for the page";
        return view('auth.register', compact('title', 'description'));
    }

    /**
     * Display forget password of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function forgetPassword()
    {
        $title = "Forget Password";
        $description = "Some description for the page";
        return view('auth.forget_password', compact('title', 'description'));
    }

    /**
     * make the user able to register
     *
     * @return 
     */
    public function signup(Request $request)
    {
        $validators = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required'
        ]);
        if ($validators->fails()) {
            return redirect()->route('register')->withErrors($validators)->withInput();
        } else {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->save();
            auth()->login($user);
            return redirect()->intended(route('clinic.dashboard', 'en'))->with('message', 'Registration was successfull !');
        }
    }

    /**
     * make the user able to login
     *
     * @return 
     */
    public function authenticate(Request $request)
    {
        $validators = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if ($validators->fails()) {
            return redirect()->route('login')->withErrors($validators)->withInput();
        } else {
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                return redirect()->intended(route('clinic.dashboard', 'en'))->with('message', 'Welcome back !');
            } else {
                return redirect()->route('login')->with('message', 'Login failed !Email/Password is incorrect !');
            }
        }
    }

    /**
     * Redirect to Google OAuth
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback
     * Only allow login if email is already registered
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Check if user exists in database
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                // Email not registered, reject login
                return redirect()->route('login')->with('message', 'Email ' . $googleUser->getEmail() . ' belum terdaftar di sistem. Silakan hubungi admin untuk didaftarkan terlebih dahulu.');
            }

            // Check if user is active
            if (isset($user->is_active) && !$user->is_active) {
                return redirect()->route('login')->with('message', 'Akun Anda tidak aktif. Silakan hubungi admin.');
            }

            // Login the user
            Auth::login($user);

            return redirect()->intended(route('clinic.dashboard', 'en'))->with('message', 'Selamat datang kembali, ' . $user->name . '!');

        } catch (\Exception $e) {
            return redirect()->route('login')->with('message', 'Terjadi kesalahan saat login dengan Google. Silakan coba lagi.');
        }
    }

    /**
     * make the user able to logout
     *
     * @return 
     */
    public function logout()
    {
        Auth::logout();
        return redirect()->route('login')->with('message', 'Successfully Logged out !');
    }
}
