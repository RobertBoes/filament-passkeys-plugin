<?php

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use RobertBoes\FilamentPasskeys\Http\Controllers\ConfirmedPasswordStatusController;
use RobertBoes\FilamentPasskeys\Http\Controllers\ConfirmPasswordController;

beforeEach(function () {
    config()->set('auth.providers.users.model', TestUser::class);

    Route::middleware(['web', 'auth'])->group(function (): void {
        Route::post('/test/confirm-password', ConfirmPasswordController::class);
        Route::get('/test/confirm-password/status', ConfirmedPasswordStatusController::class);
    });
});

it('rejects an incorrect password', function () {
    $user = TestUser::makeAuthenticated('correct-password');

    $this->actingAs($user)
        ->postJson('/test/confirm-password', ['password' => 'wrong-password'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('password');
});

it('confirms with the correct password and stores the timestamp in session', function () {
    $user = TestUser::makeAuthenticated('correct-password');

    $this->actingAs($user)
        ->postJson('/test/confirm-password', ['password' => 'correct-password'])
        ->assertNoContent();

    expect(session('auth.password_confirmed_at'))->toBeInt();
});

it('reports unconfirmed status by default', function () {
    $user = TestUser::makeAuthenticated();

    $this->actingAs($user)
        ->getJson('/test/confirm-password/status')
        ->assertOk()
        ->assertJson(['confirmed' => false]);
});

it('reports confirmed status when the session timestamp is fresh', function () {
    $user = TestUser::makeAuthenticated();

    session()->put('auth.password_confirmed_at', Date::now()->unix());

    $this->actingAs($user)
        ->getJson('/test/confirm-password/status')
        ->assertOk()
        ->assertJson(['confirmed' => true]);
});

it('reports unconfirmed status when the session timestamp is stale', function () {
    $user = TestUser::makeAuthenticated();

    session()->put('auth.password_confirmed_at', Date::now()->subDay()->unix());

    $this->actingAs($user)
        ->getJson('/test/confirm-password/status')
        ->assertOk()
        ->assertJson(['confirmed' => false]);
});

class TestUser extends Authenticatable
{
    protected $guarded = [];

    public static function makeAuthenticated(string $password = 'password'): self
    {
        $user = new self();
        $user->id = 1;
        $user->email = 'user@example.com';
        $user->password = Hash::make($password);
        $user->exists = true;

        return $user;
    }
}
