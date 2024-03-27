<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class CheckGlobalAdminTest extends TestCase
{
    use RefreshDatabase;

    public function testGlobalAdminCanAccessRoute()
    {
        // Criar um usuário com global_admin = 1
        $user = User::factory()->create(['global_admin' => 1]);

        // Simular uma requisição GET para a rota protegida
        $response = $this->actingAs($user)->get('/gadmin');

        // Verificar se a resposta tem status 200 (OK)
        $response->assertStatus(200);
    }

    public function testNonGlobalAdminCannotAccessRoute()
    {
        // Criar um usuário com global_admin = 0
        $user = User::factory()->create(['global_admin' => 0]);

        // Simular uma requisição GET para a rota protegida
        $response = $this->actingAs($user)->get('/gadmin');

        // Verificar se a resposta tem status 302 (Redirecionamento)
        $response->assertStatus(302);

        // Verificar se o usuário foi redirecionado para a página inicial
        $response->assertRedirect('/');
    }
}
