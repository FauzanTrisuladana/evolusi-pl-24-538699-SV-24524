<?php

namespace Tests\Feature\Api;

use App\Models\Link;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinkApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_links_list(): void
    {
        Link::create([
            'name' => 'First Link',
            'true_link' => 'https://first.com',
            'new_link' => 'first-alias',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/links');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Daftar link berhasil diambil.')
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'target_url',
                        'custom_alias',
                        'short_url',
                        'is_active',
                    ],
                ],
            ]);

        // Pastikan created_at dan updated_at TIDAK ada di data resource
        $firstItem = $response->json('data.0');
        $this->assertArrayNotHasKey('created_at', $firstItem);
        $this->assertArrayNotHasKey('updated_at', $firstItem);
    }

    public function test_can_filter_and_search_links(): void
    {
        Link::create([
            'name' => 'Laravel Guide',
            'true_link' => 'https://laravel.com',
            'new_link' => 'laravel-docs',
            'is_active' => true,
        ]);

        Link::create([
            'name' => 'Old Inactive Link',
            'true_link' => 'https://old.com',
            'new_link' => 'old-link',
            'is_active' => false,
        ]);

        // Search test
        $searchResponse = $this->getJson('/api/links?search=Laravel');
        $searchResponse->assertStatus(200);
        $this->assertCount(1, $searchResponse->json('data'));
        $this->assertEquals('Laravel Guide', $searchResponse->json('data.0.name'));

        // Status filter test (inactive)
        $filterResponse = $this->getJson('/api/links?status=inactive');
        $filterResponse->assertStatus(200);
        $this->assertCount(1, $filterResponse->json('data'));
        $this->assertEquals('Old Inactive Link', $filterResponse->json('data.0.name'));
    }

    public function test_can_get_single_link(): void
    {
        $link = Link::create([
            'name' => 'Single Link',
            'true_link' => 'https://single.com',
            'new_link' => 'single-test',
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/links/{$link->id_link}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Detail link berhasil diambil.')
            ->assertJsonPath('data.id', $link->id_link)
            ->assertJsonPath('data.name', 'Single Link')
            ->assertJsonPath('data.target_url', 'https://single.com')
            ->assertJsonPath('data.custom_alias', 'single-test')
            ->assertJsonPath('data.is_active', true);

        // Pastikan created_at dan updated_at tidak ada di resource
        $this->assertArrayNotHasKey('created_at', $response->json('data'));
        $this->assertArrayNotHasKey('updated_at', $response->json('data'));
    }

    public function test_returns_404_for_nonexistent_link(): void
    {
        $response = $this->getJson('/api/links/999999');

        $response->assertStatus(404);
    }

    public function test_can_create_link_with_custom_alias(): void
    {
        $payload = [
            'name' => 'Google Search',
            'target_url' => 'https://google.com',
            'custom_alias' => 'my-google',
            'is_active' => true,
        ];

        $response = $this->postJson('/api/links', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'URL berhasil diperpendek!')
            ->assertJsonPath('data.name', 'Google Search')
            ->assertJsonPath('data.target_url', 'https://google.com')
            ->assertJsonPath('data.custom_alias', 'my-google')
            ->assertJsonPath('data.is_active', true);

        $this->assertDatabaseHas('links', [
            'name' => 'Google Search',
            'true_link' => 'https://google.com',
            'new_link' => 'my-google',
            'is_active' => true,
        ]);
    }

    public function test_can_create_link_with_auto_generated_alias(): void
    {
        $payload = [
            'name' => 'Auto Link',
            'target_url' => 'https://example.com/auto',
        ];

        $response = $this->postJson('/api/links', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success');

        $alias = $response->json('data.custom_alias');
        $this->assertNotNull($alias);
        $this->assertEquals(6, strlen($alias));
    }

    public function test_create_link_validation_requires_target_url_and_name(): void
    {
        $response = $this->postJson('/api/links', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'target_url']);
    }

    public function test_can_update_link(): void
    {
        $link = Link::create([
            'name' => 'Before Update',
            'true_link' => 'https://before.com',
            'new_link' => 'before-alias',
            'is_active' => true,
        ]);

        $payload = [
            'name' => 'After Update',
            'target_url' => 'https://after.com',
            'custom_alias' => 'after-alias',
            'is_active' => false,
        ];

        $response = $this->putJson("/api/links/{$link->id_link}", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Link berhasil diperbarui!')
            ->assertJsonPath('data.name', 'After Update')
            ->assertJsonPath('data.target_url', 'https://after.com')
            ->assertJsonPath('data.custom_alias', 'after-alias')
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('links', [
            'id_link' => $link->id_link,
            'name' => 'After Update',
            'true_link' => 'https://after.com',
            'new_link' => 'after-alias',
            'is_active' => false,
        ]);
    }

    public function test_update_link_validation_fails_on_duplicate_alias(): void
    {
        Link::create([
            'name' => 'Other Link',
            'true_link' => 'https://other.com',
            'new_link' => 'existing-alias',
            'is_active' => true,
        ]);

        $linkToUpdate = Link::create([
            'name' => 'Target Link',
            'true_link' => 'https://target.com',
            'new_link' => 'my-alias',
            'is_active' => true,
        ]);

        $response = $this->putJson("/api/links/{$linkToUpdate->id_link}", [
            'name' => 'Target Link',
            'target_url' => 'https://target.com',
            'custom_alias' => 'existing-alias',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['custom_alias']);
    }

    public function test_can_delete_link(): void
    {
        $link = Link::create([
            'name' => 'To Delete',
            'true_link' => 'https://delete.com',
            'new_link' => 'delete-alias',
            'is_active' => true,
        ]);

        $response = $this->deleteJson("/api/links/{$link->id_link}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Link berhasil dihapus!');

        $this->assertDatabaseMissing('links', [
            'id_link' => $link->id_link,
        ]);
    }

    public function test_can_quick_shorten_link(): void
    {
        $response = $this->postJson('/api/links/shorten', [
            'target_url' => 'https://quick.com',
            'custom_alias' => 'quick-test',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.target_url', 'https://quick.com')
            ->assertJsonPath('data.custom_alias', 'quick-test');

        $this->assertDatabaseHas('links', [
            'true_link' => 'https://quick.com',
            'new_link' => 'quick-test',
        ]);
    }

    public function test_can_get_links_stats(): void
    {
        Link::create([
            'name' => 'Active 1',
            'true_link' => 'https://a1.com',
            'new_link' => 'a1',
            'is_active' => true,
        ]);

        Link::create([
            'name' => 'Inactive 1',
            'true_link' => 'https://i1.com',
            'new_link' => 'i1',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/links/stats/count');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.active_links_count', 1)
            ->assertJsonPath('data.total_links_count', 2);
    }
}
