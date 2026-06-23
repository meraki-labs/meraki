<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Meraki\Core\Contracts\Plugin;
use Meraki\Core\Plugins\PluginManager;
use Meraki\Core\Plugins\PluginRepository;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class PluginControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
        $this->withoutVite();
    }

    private function makePlugin(string $id, string $name = 'Test Plugin', string $version = '1.0.0'): Plugin
    {
        $plugin = Mockery::mock(Plugin::class);
        $plugin->shouldReceive('id')->andReturn($id);
        $plugin->shouldReceive('name')->andReturn($name);
        $plugin->shouldReceive('version')->andReturn($version);
        $plugin->shouldReceive('description')->andReturn('');

        return $plugin;
    }

    private function bindManager(array $plugins = [], array $activeIds = [], bool $mockActivate = false, bool $mockDeactivate = false): PluginManager
    {
        $manager = Mockery::mock(PluginManager::class);
        $manager->shouldReceive('all')->andReturn($plugins);
        $manager->shouldReceive('isActive')->andReturnUsing(fn (string $id) => in_array($id, $activeIds));

        if ($mockActivate) {
            $manager->shouldReceive('activate');
        }

        if ($mockDeactivate) {
            $manager->shouldReceive('deactivate');
        }

        $this->app->instance(PluginManager::class, $manager);

        return $manager;
    }

    private function bindRepo(array $records = []): PluginRepository
    {
        $repo = Mockery::mock(PluginRepository::class);
        $repo->shouldReceive('all')->andReturn($records);
        $this->app->instance(PluginRepository::class, $repo);

        return $repo;
    }

    public function test_unauthenticated_user_is_redirected_from_plugin_list(): void
    {
        $response = $this->get('/admin/plugins');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_sees_plugin_list_view(): void
    {
        $plugin = $this->makePlugin('meraki-auth', 'Meraki Auth');
        $this->bindManager([$plugin], ['meraki-auth']);
        $this->bindRepo();

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/plugins');

        $response->assertStatus(200);
        $response->assertViewIs('admin.plugins.index');
    }

    public function test_plugin_list_passes_plugins_and_records_to_view(): void
    {
        $plugin = $this->makePlugin('meraki-auth', 'Meraki Auth');
        $this->bindManager([$plugin]);
        $this->bindRepo();

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/plugins');

        $response->assertViewHas('plugins');
        $response->assertViewHas('records');
    }

    public function test_activate_redirects_with_success_flash(): void
    {
        $manager = Mockery::mock(PluginManager::class);
        $manager->shouldReceive('activate')->with('meraki-data-safety')->once();
        $this->app->instance(PluginManager::class, $manager);

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/admin/plugins')
            ->post('/admin/plugins/meraki-data-safety/activate');

        $response->assertRedirect('/admin/plugins');
        $response->assertSessionHas('success');
    }

    public function test_activate_redirects_with_error_flash_on_exception(): void
    {
        $manager = Mockery::mock(PluginManager::class);
        $manager->shouldReceive('activate')->with('meraki-bad')
            ->andThrow(new RuntimeException('Missing dependencies'));
        $this->app->instance(PluginManager::class, $manager);

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/admin/plugins')
            ->post('/admin/plugins/meraki-bad/activate');

        $response->assertRedirect('/admin/plugins');
        $response->assertSessionHas('error');
    }

    public function test_deactivate_redirects_with_success_flash(): void
    {
        $manager = Mockery::mock(PluginManager::class);
        $manager->shouldReceive('deactivate')->with('meraki-auth')->once();
        $this->app->instance(PluginManager::class, $manager);

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/admin/plugins')
            ->post('/admin/plugins/meraki-auth/deactivate');

        $response->assertRedirect('/admin/plugins');
        $response->assertSessionHas('success');
    }

    public function test_deactivate_redirects_with_error_flash_on_exception(): void
    {
        $manager = Mockery::mock(PluginManager::class);
        $manager->shouldReceive('deactivate')->with('meraki-auth')
            ->andThrow(new RuntimeException('Still required by meraki-cms'));
        $this->app->instance(PluginManager::class, $manager);

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/admin/plugins')
            ->post('/admin/plugins/meraki-auth/deactivate');

        $response->assertRedirect('/admin/plugins');
        $response->assertSessionHas('error');
    }
}
