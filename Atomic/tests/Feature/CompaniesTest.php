<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Companies;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompaniesTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_create(): void
    {
        $user = User::factory()->create();
        $userId = User::where('email', $user->email)->value('id');
        $company = Companies::factory()->create([
            'company_owner_id' =>  $userId
        ]);
        $this->assertInstanceOf(Companies::class, $company);
        $this->assertDatabaseHas('companies', ['id' => $company->id]);
    }

}
