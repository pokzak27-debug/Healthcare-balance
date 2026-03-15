<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportRoomPrestationExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    protected function doctorUser(): User
    {
        $role = Role::where('slug', 'doctor')->first();

        return User::factory()->create(['role_id' => $role->id]);
    }

    public function test_export_pdf_returns_pdf(): void
    {
        $user = $this->doctorUser();
        $response = $this->actingAs($user)->get(route('reports.by-room-prestation.export-pdf'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_export_excel_returns_excel(): void
    {
        $user = $this->doctorUser();
        $response = $this->actingAs($user)->get(route('reports.by-room-prestation.export-excel'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }
}
