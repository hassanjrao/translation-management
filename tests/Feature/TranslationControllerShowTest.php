<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ApiToken;
use App\Models\Locale;
use App\Models\Tag;
use App\Models\Translation;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\MakesApiRequests;
use Tests\TestCase;

class TranslationControllerShowTest extends TestCase
{
    use RefreshDatabase;
    use MakesApiRequests;

    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = app(AuthService::class);
    }

    public function test_show_returns_translation_successfully(): void
    {
        $user = User::factory()->create();
        $token = $this->authService->issueToken($user);

        $locale = Locale::factory()->create(['code' => 'en', 'name' => 'English']);
        $translation = Translation::factory()->create([
            'locale_id' => $locale->id,
            'key' => 'test.key',
            'value' => 'Test Value',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson($this->apiUrl("translations/{$translation->id}"));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'locale_id',
                    'key',
                    'value',
                    'created_at',
                    'updated_at',
                    'locale' => [
                        'id',
                        'code',
                        'name',
                    ],
                    'tags' => [],
                ],
            ])
            ->assertJson([
                'message' => 'Translation retrieved',
                'data' => [
                    'id' => $translation->id,
                    'key' => 'test.key',
                    'value' => 'Test Value',
                    'locale' => [
                        'id' => $locale->id,
                        'code' => 'en',
                        'name' => 'English',
                    ],
                ],
            ]);
    }

    public function test_show_includes_tags_when_translation_has_tags(): void
    {
        $user = User::factory()->create();
        $token = $this->authService->issueToken($user);

        $locale = Locale::factory()->create();
        $tag1 = Tag::factory()->create(['name' => 'frontend']);
        $tag2 = Tag::factory()->create(['name' => 'ui']);

        $translation = Translation::factory()->create([
            'locale_id' => $locale->id,
            'key' => 'test.key',
            'value' => 'Test Value',
        ]);
        $translation->tags()->attach([$tag1->id, $tag2->id]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson($this->apiUrl("translations/{$translation->id}"));

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(2, $data['tags']);
        $this->assertContains('frontend', array_column($data['tags'], 'name'));
        $this->assertContains('ui', array_column($data['tags'], 'name'));
    }

    public function test_show_returns_empty_tags_array_when_translation_has_no_tags(): void
    {
        $user = User::factory()->create();
        $token = $this->authService->issueToken($user);

        $locale = Locale::factory()->create();
        $translation = Translation::factory()->create([
            'locale_id' => $locale->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson($this->apiUrl("translations/{$translation->id}"));

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertIsArray($data['tags']);
        $this->assertEmpty($data['tags']);
    }

    public function test_show_returns_404_when_translation_not_found(): void
    {
        $user = User::factory()->create();
        $token = $this->authService->issueToken($user);

        $nonExistentId = 99999;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson($this->apiUrl("translations/{$nonExistentId}"));

        $response->assertStatus(404);
    }

    public function test_show_returns_401_when_no_token_provided(): void
    {
        $locale = Locale::factory()->create();
        $translation = Translation::factory()->create([
            'locale_id' => $locale->id,
        ]);

        $response = $this->getJson($this->apiUrl("translations/{$translation->id}"));

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthorized',
            ]);
    }

    public function test_show_returns_401_when_invalid_token_provided(): void
    {
        $locale = Locale::factory()->create();
        $translation = Translation::factory()->create([
            'locale_id' => $locale->id,
        ]);

        $invalidToken = 'invalid-token-12345';

        $response = $this->withHeader('Authorization', "Bearer {$invalidToken}")
            ->getJson($this->apiUrl("translations/{$translation->id}"));

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthorized',
            ]);
    }

    public function test_show_returns_401_when_token_format_is_incorrect(): void
    {
        $locale = Locale::factory()->create();
        $translation = Translation::factory()->create([
            'locale_id' => $locale->id,
        ]);

        $response = $this->withHeader('Authorization', "InvalidFormat token123")
            ->getJson($this->apiUrl("translations/{$translation->id}"));

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthorized',
            ]);
    }

    public function test_show_uses_route_model_binding_correctly(): void
    {
        $user = User::factory()->create();
        $token = $this->authService->issueToken($user);

        $locale = Locale::factory()->create();
        $translation1 = Translation::factory()->create([
            'locale_id' => $locale->id,
            'key' => 'first.key',
        ]);
        $translation2 = Translation::factory()->create([
            'locale_id' => $locale->id,
            'key' => 'second.key',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson($this->apiUrl("translations/{$translation1->id}"));

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $translation1->id,
                    'key' => 'first.key',
                ],
            ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson($this->apiUrl("translations/{$translation2->id}"));

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $translation2->id,
                    'key' => 'second.key',
                ],
            ]);
    }
}
