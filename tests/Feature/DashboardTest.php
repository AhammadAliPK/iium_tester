<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_admin_sees_workspace_wide_stat_cards(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Project::create(['name' => 'Alpha']);
        $archived = Project::create(['name' => 'Legacy']);
        $archived->update(['archived_at' => now()]);
        Project::create(['name' => 'Beta']);
        User::factory()->count(2)->create(['role' => 'tester']);
        User::factory()->create(['role' => 'developer']);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Active Projects', false);
        $response->assertSee('Team Members', false);
        $response->assertSee('My Role', false);

        // Exact counts: 2 active, 1 archived, 4 users (1 admin · 2 testers · 1 dev)
        $this->assertMatchesRegularExpression(
            '/Active Projects<\/div>\s*<div class="iium-stat-value">\s*2\s*<\/div>/',
            $response->getContent()
        );
        $response->assertSee('1 archived', false);
        $this->assertMatchesRegularExpression(
            '/Team Members<\/div>\s*<div class="iium-stat-value">\s*4\s*<\/div>/',
            $response->getContent()
        );
        $response->assertSee('2 testers', false);
    }

    public function test_non_admin_stats_are_scoped_to_assigned_projects(): void
    {
        $tester = User::factory()->create(['role' => 'tester']);
        $assigned = Project::create(['name' => 'Assigned Project']);
        $assignedArchived = Project::create(['name' => 'Assigned Old Project']);
        $assignedArchived->update(['archived_at' => now()]);
        Project::create(['name' => 'Other Project']); // exists, but not assigned
        $tester->projects()->attach([$assigned->id, $assignedArchived->id]);

        // A colleague on one shared project: team count = tester + colleague
        $colleague = User::factory()->create(['role' => 'tester']);
        $colleague->projects()->attach($assigned->id);

        $response = $this->actingAs($tester)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('My Active Projects', false);
        $response->assertSee('My Team', false);
        $response->assertSee('across your projects', false);
        $response->assertSee('Tester', false);

        // Scoped counts: 1 active, 1 archived, 2 teammates — the unassigned
        // 'Other Project' must not leak into any number
        $this->assertMatchesRegularExpression(
            '/My Active Projects<\/div>\s*<div class="iium-stat-value">\s*1\s*<\/div>/',
            $response->getContent()
        );
        $response->assertSee('1 archived', false);
        $this->assertMatchesRegularExpression(
            '/My Team<\/div>\s*<div class="iium-stat-value">\s*2\s*<\/div>/',
            $response->getContent()
        );
    }

    public function test_dashboard_is_distinct_from_projects_management_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $dashboard = $this->actingAs($admin)->get(route('dashboard'));
        $projects = $this->actingAs($admin)->get(route('projects'));

        // The dashboard owns a welcome heading; the management page keeps
        // the plain Projects heading
        $dashboard->assertSee('Welcome back,', false);
        $projects->assertDontSee('Welcome back,', false);
        $projects->assertDontSee('iium-stat-grid', false);
    }
}
