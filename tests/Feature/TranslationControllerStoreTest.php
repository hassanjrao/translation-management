<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Locale;
use App\Models\Tag;
use Tests\Concerns\MakesApiRequests;
use Tests\TestCase;

class TranslationControllerStoreTest extends TestCase
{
    use RefreshDatabase;
    use MakesApiRequests;

    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = app(AuthService::class);
    }

    public function test_store_store_translation_successfully(): void
    {
        $user = User::factory()->create();
        $token = $this->authService->issueToken($user);
        $locale = Locale::factory()->create(['code' => 'en', 'name' => 'English']);
        $key = 'test.key';
        $value = 'Test Value';
        $tags = Tag::factory()->count(2)->create();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson($this->apiUrl("translations"), [
                'locale' => $locale->code,
                'key' => $key,
                'value' => $value,
                'tags' => $tags->pluck('name')->toArray(),
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Translation created',
                'data' => [
                    'id' => $response->json('data.id'),
                    'locale_id' => $locale->id,
                    'key' => $key,
                    'value' => $value,
                    'locale' => [
                        'id' => $locale->id,
                        'code' => $locale->code,
                        'name' => $locale->name,
                    ],
                ],
            ]);

        $this->assertNotNull($response->json('data.created_at'));
        $this->assertNotNull($response->json('data.updated_at'));
        $this->assertCount(2, $response->json('data.tags'));
        $this->assertEqualsCanonicalizing(
            $tags->pluck('id')->all(),
            collect($response->json('data.tags'))->pluck('id')->all()
        );
    }

    public function test_store_requires_authentication(): void
    {
        $locale = Locale::factory()->create(['code' => 'en']);

        $response = $this->postJson($this->apiUrl('translations'), [
            'locale' => $locale->code,
            'key' => 'test.key',
            'value' => 'Test Value',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthorized',
            ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $user = User::factory()->create();
        $token = $this->authService->issueToken($user);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson($this->apiUrl('translations'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['locale', 'key', 'value']);
    }

    public function test_store_rejects_invalid_locale(): void
    {
        $user = User::factory()->create();
        $token = $this->authService->issueToken($user);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson($this->apiUrl('translations'), [
                'locale' => 'xx',
                'key' => 'test.key',
                'value' => 'Test Value',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['locale']);
    }

    public function test_store_rejects_duplicate_key_within_same_locale(): void
    {
        $user = User::factory()->create();
        $token = $this->authService->issueToken($user);
        $locale = Locale::factory()->create(['code' => 'en']);
        Tag::factory()->create(); // ensure tags table not empty

        // First create
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson($this->apiUrl('translations'), [
                'locale' => $locale->code,
                'key' => 'duplicate.key',
                'value' => 'First',
            ])
            ->assertStatus(201);

        // Second with same key + locale should fail
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson($this->apiUrl('translations'), [
                'locale' => $locale->code,
                'key' => 'duplicate.key',
                'value' => 'Second',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['key']);
    }

    public function test_store_allows_same_key_in_different_locale(): void
    {
        $user = User::factory()->create();
        $token = $this->authService->issueToken($user);
        $localeEn = Locale::factory()->create(['code' => 'en']);
        $localeFr = Locale::factory()->create(['code' => 'fr']);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson($this->apiUrl('translations'), [
                'locale' => $localeEn->code,
                'key' => 'shared.key',
                'value' => 'Value EN',
            ])
            ->assertStatus(201);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson($this->apiUrl('translations'), [
                'locale' => $localeFr->code,
                'key' => 'shared.key',
                'value' => 'Valeur FR',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.locale.code', $localeFr->code);
    }

    public function test_store_rejects_invalid_tags(): void
    {
        $user = User::factory()->create();
        $token = $this->authService->issueToken($user);
        $locale = Locale::factory()->create(['code' => 'en']);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson($this->apiUrl('translations'), [
                'locale' => $locale->code,
                'key' => 'test.key',
                'value' => 'Test Value',
                'tags' => ['does-not-exist'],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tags.0']);
    }

    public function test_store_accepts_empty_tags(): void
    {
        $user = User::factory()->create();
        $token = $this->authService->issueToken($user);
        $locale = Locale::factory()->create(['code' => 'en']);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson($this->apiUrl('translations'), [
                'locale' => $locale->code,
                'key' => 'key.no.tags',
                'value' => 'No tags value',
                'tags' => [],
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.tags', []);
    }
}
