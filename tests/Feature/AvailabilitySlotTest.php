<?php

namespace Tests\Feature;

use App\Models\AvailabilitySlot;
use App\Models\Pole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilitySlotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PoleSeeder::class);
    }

    protected function superAdminUser(): User
    {
        $role = Role::where('slug', 'super_admin')->first();

        return User::factory()->create(['role_id' => $role->id]);
    }

    public function test_availability_slots_index_requires_super_admin(): void
    {
        $role = Role::where('slug', 'staff')->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($user)->get(route('availability-slots.index'));

        $response->assertForbidden();
    }

    public function test_availability_slot_can_be_created(): void
    {
        $user = $this->superAdminUser();
        $pole = Pole::first();

        $response = $this->actingAs($user)->post(route('availability-slots.store'), [
            'pole_id' => $pole->id,
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '18:00',
            'slot_minutes' => 30,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('availability-slots.index'));
        $this->assertDatabaseHas('availability_slots', [
            'pole_id' => $pole->id,
            'day_of_week' => 1,
        ]);
    }
}
