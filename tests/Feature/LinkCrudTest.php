<?php

namespace Tests\Feature;

use App\Models\Link;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinkCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_user_can_view_links_page(): void
    {
        $response = $this->get(route('links'));

        $response->assertStatus(200);
        $response->assertSee('Semua Link');
    }

    public function test_dashboard_redirects_to_links(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('links'));
    }

    public function test_public_can_create_link_with_custom_alias(): void
    {
        $payload = [
            'target_url' => 'https://example.com/very/long/destination/url',
            'name' => 'Test Campaign',
            'custom_alias' => 'my-custom-test',
            'is_active' => '1',
        ];

        $response = $this->post(route('links.store'), $payload);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('links', [
            'name' => 'Test Campaign',
            'true_link' => 'https://example.com/very/long/destination/url',
            'new_link' => 'my-custom-test',
            'is_active' => true,
        ]);
    }

    public function test_public_can_create_link_with_auto_generated_alias(): void
    {
        $payload = [
            'target_url' => 'https://example.com/auto-alias',
            'name' => 'Auto Alias Link',
            'is_active' => '1',
        ];

        $response = $this->post(route('links.store'), $payload);

        $response->assertSessionHas('success');
        $link = Link::where('name', 'Auto Alias Link')->first();
        $this->assertNotNull($link);
        $this->assertEquals(6, strlen($link->new_link));
    }

    public function test_create_link_validation_requires_target_url_and_name(): void
    {
        $response = $this->post(route('links.store'), []);

        $response->assertSessionHasErrors(['target_url', 'name'], null, 'new');
    }

    public function test_public_can_update_link(): void
    {
        $link = Link::create([
            'name' => 'Original Name',
            'true_link' => 'https://original.com',
            'new_link' => 'orig-alias',
            'is_active' => true,
        ]);

        $payload = [
            'target_url' => 'https://updated.com',
            'name' => 'Updated Name',
            'custom_alias' => 'updated-alias',
            'is_active' => '1',
        ];

        $response = $this->post(route('links.update', ['id' => $link->id_link]), $payload);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('links', [
            'id_link' => $link->id_link,
            'name' => 'Updated Name',
            'true_link' => 'https://updated.com',
            'new_link' => 'updated-alias',
        ]);
    }

    public function test_public_can_delete_link(): void
    {
        $link = Link::create([
            'name' => 'Link To Delete',
            'true_link' => 'https://delete-me.com',
            'new_link' => 'delete-me',
            'is_active' => true,
        ]);

        $response = $this->delete(route('links.destroy', ['id' => $link->id_link]));

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('links', [
            'id_link' => $link->id_link,
        ]);
    }

    public function test_active_link_redirects_to_true_url(): void
    {
        $link = Link::create([
            'name' => 'Redirect Link',
            'true_link' => 'https://google.com',
            'new_link' => 'go-google',
            'is_active' => true,
        ]);

        $response = $this->get(route('redirect', ['new_link' => $link->new_link]));

        $response->assertRedirect('https://google.com');
    }

    public function test_inactive_link_does_not_redirect_to_target(): void
    {
        $link = Link::create([
            'name' => 'Inactive Link',
            'true_link' => 'https://secret.com',
            'new_link' => 'secret-page',
            'is_active' => false,
        ]);

        $response = $this->get(route('redirect', ['new_link' => $link->new_link]));

        $response->assertRedirect(route('links'));
        $response->assertSessionHasErrors(['form']);
    }

    public function test_search_and_filter_links(): void
    {
        Link::create([
            'name' => 'Alpha Project',
            'true_link' => 'https://alpha.com',
            'new_link' => 'alpha1',
            'is_active' => true,
        ]);

        Link::create([
            'name' => 'Beta Project',
            'true_link' => 'https://beta.com',
            'new_link' => 'beta1',
            'is_active' => false,
        ]);

        // Search test
        $searchResponse = $this->get(route('links', ['search' => 'Alpha']));
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Alpha Project');
        $searchResponse->assertDontSee('Beta Project');

        // Status filter test (inactive)
        $filterResponse = $this->get(route('links', ['status' => 'inactive']));
        $filterResponse->assertStatus(200);
        $filterResponse->assertSee('Beta Project');
        $filterResponse->assertDontSee('Alpha Project');
    }

    public function test_guest_shorten_endpoint(): void
    {
        $response = $this->post(route('shorten'), [
            'target_url' => 'https://quick-shorten.com',
            'custom_alias' => 'quick-url',
        ]);

        $response->assertRedirect(route('links'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('links', [
            'true_link' => 'https://quick-shorten.com',
            'new_link' => 'quick-url',
        ]);
    }
}
