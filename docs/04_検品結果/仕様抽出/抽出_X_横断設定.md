# 抽出台帳 X 横断設定（例外処理・ミドルウェア・プロバイダ・列挙・認証アクション・言語・認証設定）

- 件数: 26
- 記入規則: docs/02_発注書/98_実装仕様抽出調査.md の §6
- ブロック・F行の削除、並べ替え、F行本文と「- 」で始まる入口情報の編集は禁止。「事実:」行の記入と追加のみ行う。

---

### X001 app/Actions/Fortify/CreateNewUser.php
- F1: このファイルが決めている挙動（1挙動につき事実1行）
  - 事実: app/Providers/FortifyServiceProvider.php:28 Fortify::createUsersUsing(CreateNewUser::class);
  - 事実: app/Actions/Fortify/CreateNewUser.php:23 'name' => ['required', 'string', 'max:255'],
  - 事実: app/Actions/Fortify/CreateNewUser.php:24 'email' => ['required', 'email', 'max:255', 'unique:users,email'],
  - 事実: app/Actions/Fortify/CreateNewUser.php:25 'password' => ['required', 'string', 'min:8', 'confirmed'],
  - 事実: app/Actions/Fortify/CreateNewUser.php:27 'name.required' => 'お名前を入力してください',
  - 事実: app/Actions/Fortify/CreateNewUser.php:28 'name.max' => 'お名前は255文字以内で入力してください',
  - 事実: app/Actions/Fortify/CreateNewUser.php:29 'email.required' => 'メールアドレスを入力してください',
  - 事実: app/Actions/Fortify/CreateNewUser.php:30 'email.email' => 'メールアドレスの形式が正しくありません',
  - 事実: app/Actions/Fortify/CreateNewUser.php:31 'email.max' => 'メールアドレスは255文字以内で入力してください',
  - 事実: app/Actions/Fortify/CreateNewUser.php:32 'email.unique' => 'このメールアドレスは既に登録されています',
  - 事実: app/Actions/Fortify/CreateNewUser.php:33 'password.required' => 'パスワードを入力してください',
  - 事実: app/Actions/Fortify/CreateNewUser.php:34 'password.min' => 'パスワードは8文字以上で入力してください',
  - 事実: app/Actions/Fortify/CreateNewUser.php:35 'password.confirmed' => 'パスワード確認が一致しません',
  - 事実: app/Actions/Fortify/CreateNewUser.php:36 ])->validate();
  - 事実: app/Actions/Fortify/CreateNewUser.php:38 return User::create([
  - 事実: app/Actions/Fortify/CreateNewUser.php:39 'name' => $input['name'],
  - 事実: app/Actions/Fortify/CreateNewUser.php:40 'email' => $input['email'],
  - 事実: app/Actions/Fortify/CreateNewUser.php:41 'password' => Hash::make($input['password']),
  - 事実: tests/Feature/AuthTest.php:15 public function test_registration_logs_in_and_redirects(): void
  - 事実: tests/Feature/AuthTest.php:30 public function test_registration_validation_errors(): void
  - 事実: 照合R91 Actions/Fortify/CreateNewUser ....................................... 100.0%

### X002 app/Enums/NotificationTiming.php
- F1: このファイルが決めている挙動（1挙動につき事実1行）
  - 事実: app/Enums/NotificationTiming.php:5 enum NotificationTiming: string
  - 事実: app/Enums/NotificationTiming.php:7 case ThreeDaysBefore = 'three_days_before';
  - 事実: app/Enums/NotificationTiming.php:8 case OnDueDate = 'on_due_date';
  - 事実: app/Enums/NotificationTiming.php:9 case ThreeDaysAfter = 'three_days_after';
  - 事実: 該当なし（メソッド） grep -n "function" app/Enums/NotificationTiming.php → 出力0件
  - 事実: 該当なし（Eloquent casts への紐付け） grep -rn "NotificationTiming::class" app → 出力0件
  - 事実: app/Notifications/ReadingPlanReminder.php:37 'timing' => $this->timing->value,
  - 事実: resources/views/notifications/index.blade.php:33 'three_days_before' => [

### X003 app/Enums/ReadingPlanStatus.php
- F1: このファイルが決めている挙動（1挙動につき事実1行）
  - 事実: app/Enums/ReadingPlanStatus.php:5 enum ReadingPlanStatus: string
  - 事実: app/Enums/ReadingPlanStatus.php:7 case InProgress = 'in_progress';
  - 事実: app/Enums/ReadingPlanStatus.php:8 case Completed = 'completed';
  - 事実: app/Enums/ReadingPlanStatus.php:9 case Expired = 'expired';
  - 事実: app/Enums/ReadingPlanStatus.php:17 self::InProgress => '進行中',
  - 事実: app/Enums/ReadingPlanStatus.php:18 self::Completed => '完了',
  - 事実: app/Enums/ReadingPlanStatus.php:19 self::Expired => '期限切れ',
  - 事実: app/Enums/ReadingPlanStatus.php:29 self::InProgress => 'bg-blue-100 text-blue-800',
  - 事実: app/Enums/ReadingPlanStatus.php:30 self::Completed => 'bg-green-100 text-green-800',
  - 事実: app/Enums/ReadingPlanStatus.php:31 self::Expired => 'bg-red-100 text-red-800',
  - 事実: app/Models/ReadingPlan.php:28 'status' => ReadingPlanStatus::class,
  - 事実: resources/views/reading-plans/index.blade.php:15 @foreach (\App\Enums\ReadingPlanStatus::cases() as $statusOption)
  - 事実: resources/views/reading-plans/index.blade.php:64 {{ $plan->status->label() }}
  - 事実: resources/views/reading-plans/index.blade.php:63 <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $plan->status->badgeClass() }}">
  - 事実: tests/Unit/ReadingPlanStatusTest.php:17 $this->assertSame('進行中', ReadingPlanStatus::InProgress->label());
  - 事実: tests/Unit/ReadingPlanStatusTest.php:25 $this->assertSame('bg-blue-100 text-blue-800', ReadingPlanStatus::InProgress->badgeClass());

### X004 app/Exceptions/Handler.php
- F1: このファイルが決めている挙動（1挙動につき事実1行）
  - 事実: app/Exceptions/Handler.php:21 'current_password',
  - 事実: app/Exceptions/Handler.php:22 'password',
  - 事実: app/Exceptions/Handler.php:23 'password_confirmation',
  - 事実: app/Exceptions/Handler.php:31-33 $this->reportable(function (Throwable $e) { // });
  - 事実: app/Exceptions/Handler.php:35-37 NotFoundHttpException かつ $request->is('api/*') → return response()->json(['message' => '指定された書籍が見つかりません。'], 404);
  - 事実: app/Exceptions/Handler.php:41-43 AuthenticationException かつ $request->is('api/*') → return response()->json(['message' => '認証が必要です。'], 401);
  - 事実: app/Exceptions/Handler.php:47-49 AccessDeniedHttpException かつ $request->is('api/*') → return response()->json(['message' => 'この操作を実行する権限がありません。'], 403);
  - 事実: app/Exceptions/Handler.php:53-55 NotFoundHttpException かつ ! $request->is('api/*') → return response()->view('errors.404', [], 404);
  - 事実: app/Exceptions/Handler.php:59-61 AccessDeniedHttpException かつ ! $request->is('api/*') → return response()->view('errors.403', [], 403);
  - 事実: app/Exceptions/Handler.php:65-67 TokenMismatchException かつ ! $request->is('api/*') → return response()->view('errors.419', [], 419);
  - 事実: 該当なし（ValidationException・ModelNotFoundException・ThrottleRequestsException の個別処理） grep -n "ValidationException\|ModelNotFoundException\|ThrottleRequestsException" app/Exceptions/Handler.php → 出力0件
  - 事実: 照合R22 GET /api/v1/books/99999 -> 404
  - 事実: 照合R22 GET /books/99999 -> 404
  - 事実: tests/Feature/WebErrorPageTest.php:22 public function test_web_404_returns_japanese_page(): void
  - 事実: tests/Feature/WebErrorPageTest.php:32 public function test_web_403_returns_japanese_page(): void
  - 事実: tests/Feature/WebErrorPageTest.php:45 public function test_web_419_returns_japanese_page(): void
  - 事実: tests/Feature/WebErrorPageTest.php:58 public function test_api_404_returns_json(): void
  - 事実: tests/Feature/WebErrorPageTest.php:68 public function test_api_401_returns_json(): void
  - 事実: tests/Feature/WebErrorPageTest.php:78 public function test_api_403_returns_json(): void
  - 事実: 照合R91 Exceptions/Handler .......................................... 66..67 / 92.3%

### X005 app/Http/Kernel.php
- F1: このファイルが決めている挙動（1挙動につき事実1行）
  - 事実: app/Http/Kernel.php:17 // \App\Http\Middleware\TrustHosts::class,
  - 事実: app/Http/Kernel.php:18 \App\Http\Middleware\TrustProxies::class,
  - 事実: app/Http/Kernel.php:19 \Illuminate\Http\Middleware\HandleCors::class,
  - 事実: app/Http/Kernel.php:20 \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
  - 事実: app/Http/Kernel.php:21 \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
  - 事実: app/Http/Kernel.php:22 \App\Http\Middleware\TrimStrings::class,
  - 事実: app/Http/Kernel.php:23 \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
  - 事実: app/Http/Kernel.php:33 （'web' グループ） \App\Http\Middleware\EncryptCookies::class,
  - 事実: app/Http/Kernel.php:34 （'web' グループ） \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
  - 事実: app/Http/Kernel.php:35 （'web' グループ） \Illuminate\Session\Middleware\StartSession::class,
  - 事実: app/Http/Kernel.php:36 （'web' グループ） \Illuminate\View\Middleware\ShareErrorsFromSession::class,
  - 事実: app/Http/Kernel.php:37 （'web' グループ） \App\Http\Middleware\VerifyCsrfToken::class,
  - 事実: app/Http/Kernel.php:38 （'web' グループ） \Illuminate\Routing\Middleware\SubstituteBindings::class,
  - 事実: app/Http/Kernel.php:42 （'api' グループ） // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
  - 事実: app/Http/Kernel.php:43 （'api' グループ） \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
  - 事実: app/Http/Kernel.php:44 （'api' グループ） \Illuminate\Routing\Middleware\SubstituteBindings::class,
  - 事実: app/Http/Kernel.php:56 'auth' => \App\Http\Middleware\Authenticate::class,
  - 事実: app/Http/Kernel.php:57 'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
  - 事実: app/Http/Kernel.php:58 'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
  - 事実: app/Http/Kernel.php:59 'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
  - 事実: app/Http/Kernel.php:60 'can' => \Illuminate\Auth\Middleware\Authorize::class,
  - 事実: app/Http/Kernel.php:61 'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
  - 事実: app/Http/Kernel.php:62 'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
  - 事実: app/Http/Kernel.php:63 'precognitive' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
  - 事実: app/Http/Kernel.php:64 'signed' => \App\Http\Middleware\ValidateSignature::class,
  - 事実: app/Http/Kernel.php:65 'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
  - 事実: app/Http/Kernel.php:66 'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

### X006 app/Http/Middleware/Authenticate.php
- F1: このファイルが決めている挙動（1挙動につき事実1行）
  - 事実: app/Http/Middleware/Authenticate.php:15 return $request->expectsJson() ? null : route('login');
  - 事実: app/Http/Kernel.php:56 'auth' => \App\Http\Middleware\Authenticate::class,
  - 事実: 照合R22 GET /notifications -> 302 http://localhost:8022/login
  - 事実: tests/Feature/ScreenAccessTest.php:25 public function test_guest_redirected_from_auth_pages(): void

### X007 app/Http/Middleware/EncryptCookies.php
- F1: このファイルが決めている挙動（1挙動につき事実1行）
  - 事実: app/Http/Middleware/EncryptCookies.php:14-16 protected $except = [ // ];
  - 事実: app/Http/Kernel.php:33 \App\Http\Middleware\EncryptCookies::class,
  - 事実: config/sanctum.php:79 'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,

### X008 app/Http/Middleware/PreventRequestsDuringMaintenance.php
- F1: このファイルが決めている挙動（1挙動につき事実1行）
  - 事実: app/Http/Middleware/PreventRequestsDuringMaintenance.php:14-16 protected $except = [ // ];
  - 事実: app/Http/Kernel.php:20 \App\Http\Middleware\PreventRequestsDuringMaintenance::class,

### X009 app/Http/Middleware/RedirectIfAuthenticated.php
- F1: このファイルが決めている挙動（1挙動につき事実1行）
  - 事実: app/Http/Middleware/RedirectIfAuthenticated.php:20 $guards = empty($guards) ? [null] : $guards;
  - 事実: app/Http/Middleware/RedirectIfAuthenticated.php:23 if (Auth::guard($guard)->check()) {
  - 事実: app/Http/Middleware/RedirectIfAuthenticated.php:24 return redirect(RouteServiceProvider::HOME);
  - 事実: app/Http/Middleware/RedirectIfAuthenticated.php:28 return $next($request);
  - 事実: app/Providers/RouteServiceProvider.php:20 public const HOME = '/books';
  - 事実: app/Http/Kernel.php:61 'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
  - 事実: tests/Feature/ScreenAccessTest.php:20 $this->actingAs($user)->get('/register')->assertRedirect('/books');
  - 事実: tests/Feature/ScreenAccessTest.php:21 $this->actingAs($user)->get('/login')->assertRedirect('/books');

### X010 app/Http/Middleware/TrimStrings.php
- F1: このファイルが決めている挙動（1挙動につき事実1行）
  - 事実: app/Http/Middleware/TrimStrings.php:15 'current_password',
  - 事実: app/Http/Middleware/TrimStrings.php:16 'password',
  - 事実: app/Http/Middleware/TrimStrings.php:17 'password_confirmation',
  - 事実: app/Http/Kernel.php:22 \App\Http\Middleware\TrimStrings::class,

### X011 app/Http/Middleware/TrustHosts.php
- F1: このファイルが決めている挙動（1挙動につき事実1行）
  - 事実: app/Http/Middleware/TrustHosts.php:17 $this->allSubdomainsOfApplicationUrl(),
  - 事実: app/Http/Kernel.php:17 // \App\Http\Middleware\TrustHosts::class,
  - 事実: 照合R91 Http/Middleware/TrustHosts ............................................ 0.0%

### X012 app/Http/Middleware/TrustProxies.php
- F1: このファイルが決めている挙動（1挙動につき事実1行）
  - 事実: app/Http/Middleware/TrustProxies.php:15 protected $proxies;
  - 事実: app/Http/Middleware/TrustProxies.php:22-27 protected $headers = Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO | Request::HEADER_X_FORWARDED_AWS_ELB;
  - 事実: app/Http/Kernel.php:18 \App\Http\Middleware\TrustProxies::class,

### X013 app/Http/Middleware/ValidateSignature.php
- F1: このファイルが決めている挙動（1挙動につき事実1行）
  - 事実: app/Http/Middleware/ValidateSignature.php:14-21 protected $except = [ // 'fbclid', // 'utm_campaign', // 'utm_content', // 'utm_medium', // 'utm_source', // 'utm_term', ];
  - 事実: app/Http/Kernel.php:64 'signed' => \App\Http\Middleware\ValidateSignature::class,
  - 事実: 該当なし（routes 内での signed 指定） grep -rn "'signed'\|middleware('signed" routes → 出力0件

### X014 app/Http/Middleware/VerifyCsrfToken.php
- F1: このファイルが決めている挙動（1挙動につき事実1行）
  - 事実: app/Http/Middleware/VerifyCsrfToken.php:14-16 protected $except = [ // ];
  - 事実: app/Http/Kernel.php:37 \App\Http\Middleware\VerifyCsrfToken::class,
  - 事実: config/sanctum.php:80 'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
  - 事実: app/Exceptions/Handler.php:65-67 TokenMismatchException かつ ! $request->is('api/*') → return response()->view('errors.419', [], 419);

### X015 app/Providers/AppServiceProvider.php
- F1: このファイルが決めている挙動（1挙動につき事実1行）
  - 事実: app/Providers/AppServiceProvider.php:17 Sanctum::ignoreMigrations();
  - 事実: app/Providers/AppServiceProvider.php:25 // （boot() の中身はこの1行のみ）
  - 事実: S203 5:database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php
  - 事実: S200 166:        App\Providers\AppServiceProvider::class,

### X016 app/Providers/AuthServiceProvider.php
- F1: このファイルが決めている挙動（1挙動につき事実1行）
  - 事実: app/Providers/AuthServiceProvider.php:16 \App\Models\Book::class => \App\Policies\BookPolicy::class,
  - 事実: app/Providers/AuthServiceProvider.php:17 \App\Models\Review::class => \App\Policies\ReviewPolicy::class,
  - 事実: app/Providers/AuthServiceProvider.php:25 // （boot() の中身はこの1行のみ）
  - 事実: S204 git ls-files app/Policies の出力は BookPolicy.php・ReadingPlanPolicy.php・ReviewPolicy.php の3件、grep 'Gate::\|policies\|guessPolicyNamesUsing' app config のヒットは app/Providers/AuthServiceProvider.php:15 の1件
  - 事実: S200 167:        App\Providers\AuthServiceProvider::class,

### X017 app/Providers/BroadcastServiceProvider.php
- F1: このファイルが決めている挙動（1挙動につき事実1行）
  - 事実: app/Providers/BroadcastServiceProvider.php:15 Broadcast::routes();
  - 事実: app/Providers/BroadcastServiceProvider.php:17 require base_path('routes/channels.php');
  - 事実: S200 168:        // App\Providers\BroadcastServiceProvider::class,
  - 事実: S205 routes/channels.php:16:Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
  - 事実: 照合R91 Providers/BroadcastServiceProvider .................................... 0.0%

### X018 app/Providers/EventServiceProvider.php
- F1: このファイルが決めている挙動（1挙動につき事実1行）
  - 事実: app/Providers/EventServiceProvider.php:18-19 Registered::class => [ SendEmailVerificationNotification::class,
  - 事実: app/Providers/EventServiceProvider.php:28 // （boot() の中身はこの1行のみ）
  - 事実: app/Providers/EventServiceProvider.php:36 return false;（shouldDiscoverEvents）
  - 事実: app/Models/User.php:5 // use Illuminate\Contracts\Auth\MustVerifyEmail;
  - 事実: S200 169:        App\Providers\EventServiceProvider::class,

### X019 app/Providers/FortifyServiceProvider.php
- F1: このファイルが決めている挙動（1挙動につき事実1行）
  - 事実: app/Providers/FortifyServiceProvider.php:28 Fortify::createUsersUsing(CreateNewUser::class);
  - 事実: app/Providers/FortifyServiceProvider.php:30-31 Fortify::loginView(function () { return view('auth.login');
  - 事実: app/Providers/FortifyServiceProvider.php:34-35 Fortify::registerView(function () { return view('auth.register');
  - 事実: app/Providers/FortifyServiceProvider.php:38 RateLimiter::for('login', function (Request $request) {
  - 事実: app/Providers/FortifyServiceProvider.php:39 $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());
  - 事実: app/Providers/FortifyServiceProvider.php:41 return Limit::perMinute(5)->by($throttleKey);
  - 事実: 該当なし（ログイン処理の上書き） grep -rn "authenticateUsing\|authenticateThrough" app config → 出力0件
  - 事実: config/fortify.php:118 'login' => 'login',
  - 事実: S208 vendor/laravel/fortify/src/Http/Responses/LockoutResponse.php:44:                    trans('auth.throttle', [
  - 事実: 該当なし（ログイン試行制限のテスト） grep -rn "throttle\|429\|RateLimiter" tests → 出力0件
  - 事実: 照合R91 Providers/FortifyServiceProvider ............................ 31..35 / 83.3%
  - 事実: S200 170:        App\Providers\FortifyServiceProvider::class,

### X020 app/Providers/RouteServiceProvider.php
- F1: このファイルが決めている挙動（1挙動につき事実1行）
  - 事実: app/Providers/RouteServiceProvider.php:20 public const HOME = '/books';
  - 事実: app/Providers/RouteServiceProvider.php:27-28 RateLimiter::for('api', function (Request $request) { return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
  - 事実: app/Providers/RouteServiceProvider.php:32-34 Route::middleware('api') ->prefix('api') ->group(base_path('routes/api.php'));
  - 事実: app/Providers/RouteServiceProvider.php:36-37 Route::middleware('web') ->group(base_path('routes/web.php'));
  - 事実: config/fortify.php:76 'home' => \App\Providers\RouteServiceProvider::HOME,
  - 事実: app/Http/Middleware/RedirectIfAuthenticated.php:24 return redirect(RouteServiceProvider::HOME);
  - 事実: tests/Feature/AuthTest.php:24 $response->assertRedirect('/books');
  - 事実: S200 171:        App\Providers\RouteServiceProvider::class,

### X021 config/auth.php
- F1: このファイルが決めている挙動（1挙動につき事実1行）
  - 事実: config/auth.php:17 'guard' => 'web',
  - 事実: config/auth.php:18 'passwords' => 'users',
  - 事実: config/auth.php:39-41 'web' => [ 'driver' => 'session', 'provider' => 'users',
  - 事実: config/auth.php:64-65 'driver' => 'eloquent', 'model' => App\Models\User::class,
  - 事実: config/auth.php:95 'provider' => 'users',（passwords.users）
  - 事実: config/auth.php:96 'table' => 'password_reset_tokens',
  - 事実: config/auth.php:97 'expire' => 60,
  - 事実: config/auth.php:98 'throttle' => 60,
  - 事実: config/auth.php:113 'password_timeout' => 10800,
  - 事実: 該当なし（sanctum ガードの定義） grep -n "sanctum" config/auth.php → 出力0件

### X022 config/fortify.php
- F1: このファイルが決めている挙動（1挙動につき事実1行）
  - 事実: config/fortify.php:18 'guard' => 'web',
  - 事実: config/fortify.php:31 'passwords' => 'users',
  - 事実: config/fortify.php:48 'username' => 'email',
  - 事実: config/fortify.php:50 'email' => 'email',
  - 事実: config/fortify.php:63 'lowercase_usernames' => true,
  - 事実: config/fortify.php:76 'home' => \App\Providers\RouteServiceProvider::HOME,
  - 事実: config/fortify.php:89 'prefix' => '',
  - 事実: config/fortify.php:91 'domain' => null,
  - 事実: config/fortify.php:104 'middleware' => ['web'],
  - 事実: config/fortify.php:118 'login' => 'login',
  - 事実: config/fortify.php:119 'two-factor' => 'two-factor',
  - 事実: config/fortify.php:133 'views' => true,
  - 事実: config/fortify.php:146-148 'features' => [ Features::registration(), ],（配列の要素はこの1件のみ）

### X023 config/sanctum.php
- F1: このファイルが決めている挙動（1挙動につき事実1行）
  - 事実: config/sanctum.php:18-22 'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf( '%s%s', 'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1', Sanctum::currentApplicationUrlWithPort() ))),
  - 事実: config/sanctum.php:36 'guard' => ['web'],
  - 事実: config/sanctum.php:49 'expiration' => null,
  - 事実: config/sanctum.php:64 'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),
  - 事実: config/sanctum.php:78 'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
  - 事実: config/sanctum.php:79 'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
  - 事実: config/sanctum.php:80 'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
  - 事実: app/Http/Kernel.php:42 // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
  - 事実: 照合R22 GET /sanctum/csrf-cookie -> 204

### X024 config/services.php
- F1: このファイルが決めている挙動（1挙動につき事実1行）
  - 事実: config/services.php:18 'domain' => env('MAILGUN_DOMAIN'),
  - 事実: config/services.php:19 'secret' => env('MAILGUN_SECRET'),
  - 事実: config/services.php:20 'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
  - 事実: config/services.php:21 'scheme' => 'https',
  - 事実: config/services.php:25 'token' => env('POSTMARK_TOKEN'),
  - 事実: config/services.php:29 'key' => env('AWS_ACCESS_KEY_ID'),
  - 事実: config/services.php:30 'secret' => env('AWS_SECRET_ACCESS_KEY'),
  - 事実: config/services.php:31 'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
  - 事実: config/services.php:35 'key' => env('GOOGLE_BOOKS_API_KEY'),（'google_books'）
  - 事実: app/Http/Controllers/BookController.php:68 if ($apiKey = config('services.google_books.key')) {
  - 事実: 該当なし（mailgun・postmark・ses の参照） grep -rn "services.mailgun\|services.postmark\|services.ses" app → 出力0件

### X025 lang/ja/auth.php
- F1: このファイルが決めている挙動（1挙動につき事実1行）
  - 事実: lang/ja/auth.php:6 'failed' => 'メールアドレスまたはパスワードが正しくありません',
  - 事実: lang/ja/auth.php:7 'password' => 'パスワードが正しくありません。',
  - 事実: lang/ja/auth.php:8 'throttle' => 'ログインの試行回数が多すぎます。:seconds 秒後にお試しください。',
  - 事実: S207 86:    'locale' => 'ja',
  - 事実: S208 vendor/laravel/fortify/src/Actions/AttemptToAuthenticate.php:102:            Fortify::username() => [trans('auth.failed')],
  - 事実: S208 vendor/laravel/fortify/src/Http/Responses/LockoutResponse.php:44:                    trans('auth.throttle', [
  - 事実: tests/Feature/AuthTest.php:80 $this->assertSame('メールアドレスまたはパスワードが正しくありません', $errors->first('email'));

### X026 lang/ja/validation.php
- F1: このファイルが決めている挙動（1挙動につき事実1行）
  - 事実: lang/ja/validation.php:6 'required' => ':attributeは必須です。',
  - 事実: lang/ja/validation.php:7 'string' => ':attributeは文字列で入力してください。',
  - 事実: lang/ja/validation.php:8 'integer' => ':attributeは整数で入力してください。',
  - 事実: lang/ja/validation.php:9 'array' => ':attributeは配列で入力してください。',
  - 事実: lang/ja/validation.php:10 'date' => ':attributeは正しい日付形式で入力してください。',
  - 事実: lang/ja/validation.php:11 'email' => ':attributeはメールアドレス形式で入力してください。',
  - 事実: lang/ja/validation.php:12 'confirmed' => ':attributeが確認用と一致しません。',
  - 事実: lang/ja/validation.php:13 'unique' => 'その:attributeは既に使用されています。',
  - 事実: lang/ja/validation.php:14 'exists' => '選択された:attributeは存在しません。',
  - 事実: lang/ja/validation.php:15 'in' => '選択された:attributeは正しくありません。',
  - 事実: lang/ja/validation.php:17 （'max'） 'string' => ':attributeは:max文字以内で入力してください。',
  - 事実: lang/ja/validation.php:18 （'max'） 'numeric' => ':attributeは:max以下で指定してください。',
  - 事実: lang/ja/validation.php:21 （'min'） 'string' => ':attributeは:min文字以上で入力してください。',
  - 事実: lang/ja/validation.php:22 （'min'） 'numeric' => ':attributeは:min以上で指定してください。',
  - 事実: lang/ja/validation.php:26 （'attributes'） 'name' => '名前',
  - 事実: lang/ja/validation.php:27 （'attributes'） 'email' => 'メールアドレス',
  - 事実: lang/ja/validation.php:28 （'attributes'） 'password' => 'パスワード',
  - 事実: lang/ja/validation.php:29 （'attributes'） 'title' => 'タイトル',
  - 事実: lang/ja/validation.php:30 （'attributes'） 'description' => '説明',
  - 事実: lang/ja/validation.php:31 （'attributes'） 'status' => '状態',
  - 事実: lang/ja/validation.php:32 （'attributes'） 'due_date' => '期限',
  - 事実: lang/ja/validation.php:33 （'attributes'） 'category_id' => 'カテゴリ',
  - 事実: lang/ja/validation.php:34 （'attributes'） 'tags' => 'タグ',
  - 事実: lang/ja/validation.php:35 （'attributes'） 'per_page' => '1ページあたりの件数',
  - 事実: lang/ja/validation.php:36 （'attributes'） 'page' => 'ページ番号',
  - 事実: lang/ja/validation.php:37 （'attributes'） 'user_id' => '登録者',
  - 事実: lang/ja/validation.php:38 （'attributes'） 'keyword' => 'キーワード',
  - 事実: S201 due_date / category_id / tags の3行は app/Http/Requests・app/Actions でのヒットファイル0件（例 "due_date "）
  - 事実: S201 status app/Http/Requests/ReadingPlanStoreRequest.php
  - 事実: S201 user_id app/Http/Requests/ReadingPlanStoreRequest.php
  - 事実: S207 86:    'locale' => 'ja',
  - 事実: S207 99:    'fallback_locale' => 'en',
  - 事実: S202 git ls-files lang の出力は lang/ja/auth.php・lang/ja/validation.php の2件、vendor/laravel/framework/src/Illuminate/Translation/lang/en の出力は auth.php・pagination.php・passwords.php・validation.php
