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
}
