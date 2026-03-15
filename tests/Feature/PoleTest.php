<?php

namespace Tests\Feature;

use App\Models\Pole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    protected function superAdminUser(): User
    {
        $role = Role::where('slug', 'super_admin')->first();

        return User::factory()->create(['role_id' => $role->id]);
    }

    public function test_poles_index_requires_super_admin(): void
    {
        $role = Role::where('slug', 'staff')->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($user)->get(route('poles.index'));

        $response->assertForbidden();
    }

    public function test_poles_index_displays_for_super_admin(): void
    {
        $user = $this->superAdminUser();
        $this->seed(\Database\Seeders\PoleSeeder::class);

        $response = $this->actingAs($user)->get(route('poles.index'));

        $response->assertOk();
        $response->assertSee('Ostéopathie');
    }

    public function test_pole_can_be_created(): void
    {
        $user = $this->superAdminUser();

        $response = $this->actingAs($user)->post(route('poles.store'), [
            'name' => 'Nouveau pôle',
            'description' => 'Description',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('poles.index'));
        $this->assertDatabaseHas('poles', ['name' => 'Nouveau pôle']);
    }

    public function test_pole_can_be_updated(): void
    {
        $user = $this->superAdminUser();
        $pole = Pole::create([
            'name' => 'Test',
            'slug' => 'test',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->patch(route('poles.update', $pole), [
            'name' => 'Test modifié',
            'description' => 'Desc',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $response->assertRedirect(route('poles.index'));
        $pole->refresh();
        $this->assertSame('Test modifié', $pole->name);
    }
}
