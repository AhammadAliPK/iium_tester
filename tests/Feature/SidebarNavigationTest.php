<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_see_no_sidebar(): void
    {
        $response = $this->get(route('login'));
        $response->assertOk();
        $response->assertDontSee('class="iium-sidebar"');
    }

    public function test_authenticated_users_see_dashboard_link(): void
    {
        $user = User::factory()->create(['role' => 'tester']);
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Dashboard');
        $response->assertSee('href="' . route('dashboard') . '"', false);
    }

    public function test_admins_see_projects_and_team_links(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Projects');
        $response->assertSee('href="' . route('projects') . '"', false);
        $response->assertSee('Team');
        $response->assertSee('href="' . route('users') . '"', false);
    }

    public function test_non_admins_do_not_see_projects_or_team_links(): void
    {
        $nonAdmin = User::factory()->create(['role' => 'tester']);
        $this->actingAs($nonAdmin);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
        $response->assertDontSee('href="' . route('projects') . '"', false);
        $response->assertDontSee('href="' . route('users') . '"', false);
    }

    public function test_non_admin_html_has_no_team_link_markup(): void
    {
        $businessUser = User::factory()->create(['role' => 'business_user']);
        $this->actingAs($businessUser);

        $response = $this->get(route('dashboard'));
        $response->assertOk();

        // Ensure no Team link in the sidebar
        $html = $response->getContent();
        // Look for the specific Team navigation link
        $this->assertDoesNotMatchRegularExpression(
            '/<a[^>]*href="' . preg_quote(route('users'), '/') . '"[^>]*>Team<\/a>/',
            $html
        );
    }

    public function test_dashboard_active_state_on_dashboard_route(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get(route('dashboard'));
        $response->assertOk();

        // Check that the Dashboard link has the active class
        $html = $response->getContent();
        $this->assertMatchesRegularExpression(
            '/<a[^>]*href="' . preg_quote(route('dashboard'), '/') . '"[^>]*class="[^"]*sidebar-nav-item active[^"]*"/',
            $html
        );
    }

    public function test_projects_active_state_on_projects_route(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get(route('projects'));
        $response->assertOk();

        // Check that the Projects link has the active class
        $html = $response->getContent();
        $this->assertMatchesRegularExpression(
            '/<a[^>]*href="' . preg_quote(route('projects'), '/') . '"[^>]*class="[^"]*sidebar-nav-item active[^"]*"/',
            $html
        );
    }

    public function test_users_active_state_on_users_route(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get(route('users'));
        $response->assertOk();

        // Check that the Team link has the active class
        $html = $response->getContent();
        $this->assertMatchesRegularExpression(
            '/<a[^>]*href="' . preg_quote(route('users'), '/') . '"[^>]*class="[^"]*sidebar-nav-item active[^"]*"/',
            $html
        );
    }

    public function test_business_user_active_state_on_dashboard(): void
    {
        $businessUser = User::factory()->create(['role' => 'business_user']);
        $this->actingAs($businessUser);

        $response = $this->get(route('dashboard'));
        $response->assertOk();

        // Business users should see active state on their dashboard
        $html = $response->getContent();
        $this->assertMatchesRegularExpression(
            '/<a[^>]*href="' . preg_quote(route('dashboard'), '/') . '"[^>]*class="[^"]*sidebar-nav-item active[^"]*"/',
            $html
        );
    }

    public function test_developer_does_not_see_admin_links(): void
    {
        $developer = User::factory()->create(['role' => 'developer']);
        $this->actingAs($developer);

        $response = $this->get(route('dashboard'));
        $response->assertOk();

        // Developers should only see Dashboard
        $response->assertSee('Dashboard');
        $response->assertDontSee('href="' . route('projects') . '"', false);
        $response->assertDontSee('href="' . route('users') . '"', false);
    }

    public function test_sidebar_appears_on_all_authenticated_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        // Check sidebar exists on dashboard
        $response = $this->get(route('dashboard'));
        $response->assertSee('class="iium-sidebar"', false);

        // Check sidebar exists on projects
        $response = $this->get(route('projects'));
        $response->assertSee('class="iium-sidebar"', false);

        // Check sidebar exists on users
        $response = $this->get(route('users'));
        $response->assertSee('class="iium-sidebar"', false);
    }
}
