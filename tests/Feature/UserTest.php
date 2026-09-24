<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ゲストは/loginが表示される
     */
    public function test_guest_can_access_login_page(): void
    {
        $response = $this->get('/login');
        $response->assertOk();
        $response->assertSee('ログイン');
    }

    /**
     * バリデーション通過時はログインできる
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('books.index'));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * バリデーションエラー時はリダイレクトされエラーメッセージが表示される
     */
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'invalid@example.com',
            'password' => 'invalid-password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * 認証済みユーザーは/loginにアクセスすると/へリダイレクトされる
     */
    public function test_authenticated_user_is_redirected_from_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/login');

        $response->assertRedirect(route('books.index'));
    }

    /**
     * 認証済みユーザーはログアウトできる
     */
    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }

    /**
     * ゲストは会員登録ページを表示できる
     */
    public function test_guest_can_access_register_page(): void
    {
        $response = $this->get('/register');
        $response->assertOk();
    }

    /**
     * バリデーション通過時は会員登録できて、/にリダイレクトされる
     */
    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('books.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'testuser@example.com',
        ]);
        $this->assertAuthenticated();
    }

    /**
     * バリデーションエラー時はリダイレクトされエラーメッセージが表示される
     */
    public function test_user_cannot_register_with_invalid_data(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'password',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertGuest();
    }

    /**
     * ゲストは/homeにアクセスすると/booksにリダイレクトされる
     */
    public function test_guest_user_is_redirected_from_home_to_books(): void
    {
        $response = $this
            ->get(route('home'));

        $response->assertRedirect(route('books.index'));
    }

    /**
     * 認証済みユーザーは/homeにアクセスすると/booksにリダイレクトされる
     */
    public function test_authenticated_user_is_redirected_from_home_to_books(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertRedirect(route('books.index'));
    }
}
