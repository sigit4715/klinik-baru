<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
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
     * New users are inactive until admin approves
     *
     * @return 
     */
    public function signup(Request $request)
    {
        $validators = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);
        if ($validators->fails()) {
            return redirect()->route('register')->withErrors($validators)->withInput();
        } else {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->is_active = false; // Inactive until admin approves
            $user->role = 'staff';
            $user->save();

            return redirect()->route('login')->with('message', 'Pendaftaran berhasil! Akun Anda sedang menunggu verifikasi dari admin. Silakan hubungi admin untuk mengaktifkan akun.');
        }
    }

    /**
     * make the user able to login
     * Check if user is active before allowing login
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
        }

        // Brute-force protection: 5 failed attempts per email + IP, then wait
        $throttleKey = Str::lower($request->email) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return redirect()->route('login')->withInput($request->only('email'))
                ->with('message', "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.");
        }

        // Check the password first so the response does not reveal which emails exist
        $credentials = ['email' => $request->email, 'password' => $request->password];
        if (! Auth::validate($credentials)) {
            RateLimiter::hit($throttleKey, 60);
            // Auth::validate() does not fire this itself; it feeds the audit log (no password stored)
            event(new \Illuminate\Auth\Events\Failed('web', Auth::getLastAttempted(), ['email' => $request->email]));
            return redirect()->route('login')->withInput($request->only('email'))
                ->with('message', 'Login gagal! Email atau password salah.');
        }
        RateLimiter::clear($throttleKey);

        $user = Auth::getLastAttempted();
        if (! $user->is_active) {
            return redirect()->route('login')->with('message', 'Akun Anda belum diaktifkan oleh admin. Silakan tunggu verifikasi dari admin.');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('clinic.dashboard', 'en'))->with('message', 'Selamat datang kembali!');
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
     * Auto-register new users but set as inactive until admin approves
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
                // Auto-register new user with inactive status
                $user = new User([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => bcrypt(Str::random(40)), // Random password
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(), // Google profile photo
                ]);
                // Not mass assignable on purpose: new accounts wait for admin approval
                $user->is_active = false;
                $user->role = 'staff';
                $user->save();

                return redirect()->route('login')->with('message', 'Pendaftaran berhasil menggunakan Google! Akun Anda (' . $googleUser->getEmail() . ') sedang menunggu verifikasi dari admin. Anda akan dapat login setelah admin mengaktifkan akun Anda.');
            }

            // User exists, check if active
            if (!$user->is_active) {
                return redirect()->route('login')->with('message', 'Akun Anda belum diaktifkan oleh admin. Silakan tunggu verifikasi atau hubungi admin.');
            }

            // Update avatar and google_id if not set (for users registered before this feature)
            if (!$user->avatar || !$user->google_id) {
                $user->update([
                    'avatar' => $googleUser->getAvatar(),
                    'google_id' => $googleUser->getId(),
                ]);
            }

            // Login the user
            Auth::login($user);
            request()->session()->regenerate();

            return redirect()->intended(route('clinic.dashboard', 'en'))->with('message', 'Selamat datang kembali, ' . $user->name . '!');

        } catch (\Exception $e) {
            report($e);

            return redirect()->route('login')->with('message', 'Terjadi kesalahan saat login dengan Google. Silakan coba lagi.');
        }
    }

    /**
     * make the user able to logout
     *
     * @return 
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('message', 'Anda berhasil logout!');
    }
}
