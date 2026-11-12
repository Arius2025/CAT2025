<?php
namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class ProfileUpdateTest extends TestCase
{
    public function test_profile_update()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->put('/profil', [
            'name' => 'Updated Name',
            'email' => $user->email,
            'instansi' => 'Updated Instansi',
            'no_hp' => '089999999',
            'password' => '',
            'password_confirmation' => ''
        ]);

        $response->dumpSession();
        $response->dump();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'instansi' => 'Updated Instansi'
        ]);
        
        $response->assertStatus(302);
    }
}
