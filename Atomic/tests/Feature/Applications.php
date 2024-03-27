<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Companies;
use App\Models\Applications;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompaniesTest extends TestCase
{
    use RefreshDatabase;

    public function test_applications_create(): void
    {
        $user = User::factory()->create();
        $userId = User::where('email', $user->email)->value('id');
        $company = Companies::factory()->create([
            'company_owner_id' =>  $userId
        ]);
        $application = Applications::factory()->create([
            'company_id' => $company->id
        ]);
        $this->assertInstanceOf(Applications::class, $application);
        $this->assertDatabaseHas('applications', ['id' => $company->id]);
    }

}
