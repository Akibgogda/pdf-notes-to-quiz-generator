<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_quiz_generator(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Guest Mode');
    }

    public function test_unauthenticated_user_is_redirected_to_login_on_protected_routes(): void
    {
        $response = $this->get('/documents');
        $response->assertRedirect('/login');

        $response = $this->get('/history');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_quiz_generator(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
    }
}
