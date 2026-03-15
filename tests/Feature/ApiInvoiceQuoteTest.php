<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Quote;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiInvoiceQuoteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
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

        return $response->json('token');
    }

    public function test_api_invoices_index_requires_auth(): void
    {
        $response = $this->getJson('/api/v1/invoices');
        $response->assertUnauthorized();
    }

    public function test_api_invoices_index_returns_invoices_with_token(): void
    {
        $token = $this->createToken();
        $patient = Patient::create(['first_name' => 'Jean', 'last_name' => 'Dupont']);
        Invoice::create([
            'number' => Invoice::generateNumber(),
            'patient_id' => $patient->id,
            'amount' => 300,
            'status' => 'draft',
            'invoice_date' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/invoices');

        $response->assertOk();
        $response->assertJsonStructure(['data']);
    }

    public function test_api_invoices_show_returns_invoice(): void
    {
        $token = $this->createToken();
        $patient = Patient::create(['first_name' => 'Marie', 'last_name' => 'Martin']);
        $invoice = Invoice::create([
            'number' => Invoice::generateNumber(),
            'patient_id' => $patient->id,
            'amount' => 400,
            'status' => 'paid',
            'invoice_date' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/invoices/' . $invoice->id);

        $response->assertOk();
        $response->assertJsonPath('number', $invoice->number);
    }

    public function test_api_quotes_index_returns_quotes_with_token(): void
    {
        $token = $this->createToken();
        $patient = Patient::create(['first_name' => 'Paul', 'last_name' => 'Durand']);
        Quote::create([
            'number' => Quote::generateNumber(),
            'patient_id' => $patient->id,
            'amount' => 350,
            'status' => 'draft',
            'quote_date' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/quotes');

        $response->assertOk();
        $response->assertJsonStructure(['data']);
    }

    public function test_api_quotes_show_returns_quote(): void
    {
        $token = $this->createToken();
        $patient = Patient::create(['first_name' => 'Anne', 'last_name' => 'Leroy']);
        $quote = Quote::create([
            'number' => Quote::generateNumber(),
            'patient_id' => $patient->id,
            'amount' => 450,
            'status' => 'sent',
            'quote_date' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/quotes/' . $quote->id);

        $response->assertOk();
        $response->assertJsonPath('number', $quote->number);
    }
}
