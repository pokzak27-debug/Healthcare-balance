<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\Pole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PoleSeeder::class);
    }

    protected function createToken(): string
    {
        $role = Role::where('slug', 'staff')->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->postJson('/api/token', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['token']);

        return $response->json('token');
    }

    public function test_token_creation_requires_valid_credentials(): void
    {
        $response = $this->postJson('/api/token', [
            'email' => 'invalid@example.com',
            'password' => 'wrong',
        ]);

        $response->assertUnprocessable();
    }

    public function test_api_patients_index_requires_auth(): void
    {
        $response = $this->getJson('/api/v1/patients');

        $response->assertUnauthorized();
    }

    public function test_api_patients_index_returns_patients_with_token(): void
    {
        $token = $this->createToken();
        Patient::create(['first_name' => 'Jean', 'last_name' => 'Dupont']);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/patients');

        $response->assertOk();
        $response->assertJsonStructure(['data']);
    }

    public function test_api_poles_index_returns_poles_with_token(): void
    {
        $token = $this->createToken();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/poles');

        $response->assertOk();
    }
}
