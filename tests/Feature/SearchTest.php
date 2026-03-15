<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    protected function staffUser(): User
    {
        $role = Role::where('slug', 'staff')->first();

        return User::factory()->create(['role_id' => $role->id]);
    }

    public function test_search_requires_auth(): void
    {
        $response = $this->get(route('search'));

        $response->assertRedirect(route('login'));
    }

    public function test_search_displays_for_authenticated_user(): void
    {
        $user = $this->staffUser();

        $response = $this->actingAs($user)->get(route('search', ['q' => 'test']));

        $response->assertOk();
    }

    public function test_search_finds_patients(): void
    {
        $user = $this->staffUser();
        Patient::create([
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'jean@example.com',
        ]);

        $response = $this->actingAs($user)->get(route('search', ['q' => 'Dupont']));

        $response->assertOk();
        $response->assertSee('Dupont');
    }
}
