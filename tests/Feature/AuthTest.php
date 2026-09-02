<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** F-A5: 会員登録すると自動ログイン後books.indexへ */
    public function test_registration_logs_in_and_redirects(): void
    {
        $response = $this->post('/register', [
            'name' => '新規太郎',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/books');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
    }

    /** F-A6: 登録バリデーション */
    public function test_registration_validation_errors(): void
    {
        // 必須未入力
        $this->post('/register', [])
            ->assertSessionHasErrors(['name', 'email', 'password']);

        // メール形式不正
        $this->post('/register', [
            'name' => 'a',
            'email' => 'invalid',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors('email');

        // パスワード不一致
        $this->post('/register', [
            'name' => 'a',
            'email' => 'ok@example.com',
            'password' => 'password',
            'password_confirmation' => 'different',
        ])->assertSessionHasErrors('password');
    }

    /** F-A7: ログイン成功 */
    public function test_login_success(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/books');
        $this->assertAuthenticatedAs($user);
    }

    /** F-A8: ログイン失敗メッセージ */
    public function test_login_failure_message(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'wrong',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        $errors = session('errors');
        $this->assertSame('メールアドレスまたはパスワードが正しくありません', $errors->first('email'));
    }

    /** F-A9: ログアウト後は認証必須画面でログインが要求される */
    public function test_logout_requires_login_again(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout');
        $this->assertGuest();

        $this->get('/books/create')->assertRedirect('/login');
    }
}
