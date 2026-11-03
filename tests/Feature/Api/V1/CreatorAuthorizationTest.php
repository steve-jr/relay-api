<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreatorAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_creator_directory_requires_authentication(): void
    {
        $this->getJson('/api/v1/creators')->assertUnauthorized();
    }

    public function test_creator_cannot_browse_creator_directory(): void
    {
        $creator = User::factory()->create(['role'=>'creator','country'=>'GH','currency'=>'GHS']);
        $this->actingAs($creator)->getJson('/api/v1/creators')->assertForbidden();
    }
}
