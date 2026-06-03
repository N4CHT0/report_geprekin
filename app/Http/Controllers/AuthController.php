<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * =========================
     * 1) VIEW LOGIN
     * =========================
     */
    public function index()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        return view('Auth.login');
    }

    /**
     * =========================
     * 2) PROSES LOGIN - OPTIMIZED
     * =========================
     *
     * Optimasi:
     * - Throttle login per email + IP supaya login serentak/bruteforce tidak menghajar server.
     * - Normalisasi email lowercase.
     * - Regenerate session hanya setelah login sukses.
     * - Redirect dipusatkan di redirectByRole().
     * - Lookup investor_id dicache agar tidak query tbl_investor berulang setiap login.
     */
    public function prosesLogin(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $email = Str::lower(trim((string) $request->email));
        $throttleKey = $this->loginThrottleKey($request, $email);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam ' . $seconds . ' detik.',
                ]);
        }

        $credentials = [
            'email'    => $email,
            'password' => $request->password,
        ];

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);

            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Email atau password salah!',
                ]);
        }

        RateLimiter::clear($throttleKey);

        $request->session()->regenerate();

        return $this->redirectByRole(Auth::user());
    }

    /**
     * Redirect user berdasarkan role/email.
     * Tidak menambah route baru, tetap memakai route yang sudah ada.
     */
    private function redirectByRole($user)
    {
        if (!$user) {
            return redirect()->route('login');
        }

        // Superadmin khusus investor dashboard
        if ($user->email === 'superadmin@gmail.com') {
            return redirect()->route('investor.sales.dashboard');
        }

        // Audit
        if ($user->email === 'adminaudit@gmail.com' || $user->role === 'superadmin_audit') {
            return redirect()->route('auditDashboard.backOffice');
        }

        // Operasional
        if (in_array($user->role, ['tm_manager', 'spv', 'leader'], true)) {
            return redirect()->route('master.dsc.index');
        }

        // Crew
        if ($user->role === 'crew') {
            return redirect()->route('crew.menus');
        }

        // SCM / DC
        if ($user->role === 'admindc') {
            return redirect()->route('dashboard.scm.dc');
        }

        // Maintenance
        if ($user->role === 'maintenance') {
            return redirect()->route('ticketing.index');
        }

        // Default investor
        $investorId = $this->getInvestorIdByUserId((int) $user->id);

        if (!$investorId) {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Akun tidak terkait investor.',
                ]);
        }

        session(['investor_id' => $investorId]);

        return redirect()->route('investor.sales.dashboard');
    }

    /**
     * Cache investor_id supaya login berulang tidak selalu query tbl_investor.
     */
    private function getInvestorIdByUserId(int $userId): ?int
    {
        return Cache::remember("auth:investor_id:user:{$userId}", now()->addMinutes(30), function () use ($userId) {
            $investor = DB::table('tbl_investor')
                ->where('user_id', $userId)
                ->select('id')
                ->first();

            return $investor ? (int) $investor->id : null;
        });
    }

    /**
     * Key throttle login.
     */
    private function loginThrottleKey(Request $request, string $email): string
    {
        return 'login:' . sha1($email . '|' . $request->ip());
    }

    /**
     * =========================
     * 3) LOGOUT
     * =========================
     */
    public function prosesLogout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * =========================
     * 4) USER INVESTOR
     * =========================
     */
    public function userInvestor()
    {
        $user = auth()->user();

        if (!$user) {
            abort(403, 'User tidak ditemukan.');
        }

        if ($user->role === 'superadmin') {
            $investors = Cache::remember('profile:investors:all', now()->addMinutes(5), function () {
                return DB::table('tbl_investor as i')
                    ->join('users as u', 'u.id', '=', 'i.user_id')
                    ->select(
                        'i.id as investor_id',
                        'i.nama_investor',
                        'u.email',
                        'u.name as nama_user'
                    )
                    ->get();
            });

            $outlets = Cache::remember('profile:outlets:all', now()->addMinutes(5), function () {
                return DB::table('tbl_outlets')->get();
            });

            return view('Investor.Profile.profileInvestor', compact('investors', 'outlets'));
        }

        $investorId = session('investor_id') ?: $this->getInvestorIdByUserId((int) $user->id);

        if (!$investorId) {
            abort(403, 'Investor tidak ditemukan.');
        }

        session(['investor_id' => $investorId]);

        $investor = Cache::remember("profile:investor:{$investorId}", now()->addMinutes(5), function () use ($investorId) {
            return DB::table('tbl_investor as i')
                ->join('users as u', 'u.id', '=', 'i.user_id')
                ->select(
                    'i.id as investor_id',
                    'i.nama_investor',
                    'u.email',
                    'u.name as nama_user'
                )
                ->where('i.id', $investorId)
                ->first();
        });

        $outlets = Cache::remember("profile:investor:{$investorId}:outlets", now()->addMinutes(5), function () use ($investorId) {
            return DB::table('tbl_outlets')
                ->whereIn('mitra_id', function ($q) use ($investorId) {
                    $q->select('id')
                        ->from('tbl_mitra')
                        ->where('investor_id', $investorId);
                })
                ->get();
        });

        return view('Investor.Profile.profileInvestor', compact('investor', 'outlets'));
    }

    /**
     * =========================
     * 5) FORGOT PASSWORD
     * =========================
     */
    public function showForgotPasswordForm()
    {
        return view('Auth.forgotPassword');
    }

    public function sendResetOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
        ]);

        $email = Str::lower(trim((string) $request->email));
        $throttleKey = 'forgot-password:' . sha1($email . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()
                ->withInput()
                ->withErrors([
                    'email' => 'Terlalu banyak permintaan OTP. Silakan coba lagi dalam ' . $seconds . ' detik.',
                ]);
        }

        RateLimiter::hit($throttleKey, 300);

        $user = DB::table('users')
            ->where('email', $email)
            ->select('id', 'email')
            ->first();

        if (!$user) {
            return back()
                ->withErrors([
                    'email' => 'Akun tidak ditemukan.',
                ])
                ->withInput();
        }

        $blockedDomains = ['dummy.com', 'example.com', 'noemail.local'];

        $userEmail = trim((string) $user->email);
        $domain = strtolower(substr(strrchr($userEmail, '@'), 1) ?: '');

        if (!filter_var($userEmail, FILTER_VALIDATE_EMAIL) || in_array($domain, $blockedDomains, true)) {
            return back()
                ->withErrors([
                    'email' => 'Email akun ini tidak aktif untuk reset password. Silakan hubungi admin.',
                ])
                ->withInput();
        }

        $otp = (string) random_int(100000, 999999);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $userEmail],
            [
                'token'      => $otp,
                'created_at' => now(),
            ]
        );

        session([
            'reset_email'   => $userEmail,
            'reset_user_id' => $user->id,
            'reset_step'    => 2,
        ]);

        return redirect()
            ->route('password.request')
            ->with('success', 'OTP berhasil dibuat. Untuk sementara OTP: ' . $otp);
    }

    public function verifyResetOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ], [
            'otp.required' => 'OTP wajib diisi.',
            'otp.digits'   => 'OTP harus 6 digit.',
        ]);

        $email = session('reset_email');

        if (!$email) {
            return redirect()
                ->route('password.request')
                ->withErrors([
                    'email' => 'Sesi reset tidak ditemukan. Silakan ulangi dari awal.',
                ]);
        }

        $reset = DB::table('password_resets')
            ->where('email', $email)
            ->where('token', $request->otp)
            ->select('email', 'token', 'created_at')
            ->first();

        if (!$reset) {
            return redirect()
                ->route('password.request')
                ->withErrors([
                    'otp' => 'OTP tidak valid.',
                ]);
        }

        if (now()->diffInMinutes($reset->created_at) > 10) {
            DB::table('password_resets')
                ->where('email', $email)
                ->delete();

            session()->forget([
                'reset_email',
                'reset_user_id',
                'reset_step',
            ]);

            return redirect()
                ->route('password.request')
                ->withErrors([
                    'otp' => 'OTP sudah kedaluwarsa. Silakan ulangi lagi.',
                ]);
        }

        session([
            'reset_step'     => 3,
            'reset_verified' => true,
        ]);

        return redirect()
            ->route('password.request')
            ->with('success', 'OTP berhasil diverifikasi. Silakan buat password baru.');
    }

    public function resetPassword(Request $request)
    {
        if (!session('reset_email') || !session('reset_user_id') || !session('reset_verified')) {
            return redirect()
                ->route('password.request')
                ->withErrors([
                    'email' => 'Silakan mulai reset password dari langkah pertama.',
                ]);
        }

        $request->validate([
            'password' => ['required', 'min:6', 'confirmed'],
        ], [
            'password.required'  => 'Password baru wajib diisi.',
            'password.min'       => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
        ]);

        DB::table('users')
            ->where('id', session('reset_user_id'))
            ->update([
                'password'   => Hash::make($request->password),
                'updated_at' => now(),
            ]);

        DB::table('password_resets')
            ->where('email', session('reset_email'))
            ->delete();

        session()->forget([
            'reset_email',
            'reset_user_id',
            'reset_step',
            'reset_verified',
        ]);

        return redirect()
            ->route('login')
            ->with('success', 'Password berhasil diubah. Silakan login.');
    }
}
