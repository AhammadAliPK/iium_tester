<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Run the database seeders
        $this->seed();
    }

    /**
     * Matrix row: Guest opens /projects → Unauthenticated → Redirect to /login
     */
    public function test_guests_cannot_access_projects_page(): void
    {
        $response = $this->get(route('projects'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Matrix row: Non-admin views dashboard → role != admin → Active list visible, zero management controls in HTML
     */
    public function test_non_admin_sees_project_list_without_management_controls(): void
    {
        // Create a non-admin user
        $user = User::factory()->create(['role' => UserRole::TESTER]);
        $this->actingAs($user);

        // Create a test project
        Project::create(['name' => 'Test Project']);

        $response = $this->get(route('dashboard'));

        // The component itself doesn't abort, so the response should be OK
        $response->assertOk();

        // Should see the project name
        $response->assertSee('Test Project');

        // Should NOT see management controls
        $response->assertDontSee('Create Project');
        $response->assertDontSee('New Project Name');
        $response->assertDontSee('Rename');
        $response->assertDontSee('Archive');
    }

    /**
     * Matrix row: Non-admin POSTs create → role != admin → 403 with "not permitted for your role"
     */
    public function test_non_admin_cannot_create_project(): void
    {
        $user = User::factory()->create(['role' => UserRole::TESTER]);
        $this->actingAs($user);

        // Verify no projects exist initially
        $this->assertDatabaseCount('projects', 0);

        // Non-admin should not be able to access the management route directly
        $response = $this->get(route('projects'));
        $response->assertStatus(403);
    }

    /**
     * Matrix row: Admin creates project → Name "Student Portal" → Row created, appears in active list
     */
    public function test_admin_can_create_project(): void
    {
        $admin = User::where('email', 'admin@iium.edu.my')->first();
        $this->actingAs($admin);

        $response = $this->get(route('dashboard'));
        $response->assertOk();

        // Should see create form
        $response->assertSee('Create Project');
        $response->assertSee('New Project Name');

        // Create a project via Livewire
        Livewire::test('projects.manage-projects')
            ->set('newProjectName', 'Student Portal')
            ->call('create')
            ->assertHasNoErrors();

        // Verify project was created
        $this->assertDatabaseHas('projects', [
            'name' => 'Student Portal',
            'archived_at' => null,
        ]);

        // Verify it appears in the active list
        $response = $this->get(route('dashboard'));
        $response->assertSee('Student Portal');
    }

    /**
     * Matrix row: Create with empty name → Missing name → Validation error, no row
     */
    public function test_cannot_create_project_with_empty_name(): void
    {
        $admin = User::where('email', 'admin@iium.edu.my')->first();
        $this->actingAs($admin);

        Livewire::test('projects.manage-projects')
            ->set('newProjectName', '')
            ->call('create')
            ->assertHasErrors(['newProjectName' => 'required']);

        // Verify no project was created
        $this->assertDatabaseMissing('projects', [
            'name' => '',
        ]);
    }

    /**
     * Matrix row: Create with duplicate name → Same name twice → Allowed (names not unique in v1)
     */
    public function test_can_create_projects_with_duplicate_names(): void
    {
        $admin = User::where('email', 'admin@iium.edu.my')->first();
        $this->actingAs($admin);

        // Create first project
        Project::create(['name' => 'Duplicate Name']);

        // Create second project with same name
        Livewire::test('projects.manage-projects')
            ->set('newProjectName', 'Duplicate Name')
            ->call('create')
            ->assertHasNoErrors();

        // Verify both projects exist
        $this->assertDatabaseCount('projects', 2);
        $projects = Project::where('name', 'Duplicate Name')->get();
        $this->assertCount(2, $projects);
    }

    /**
     * Matrix row: Admin renames → New valid name → Updated everywhere on next render
     */
    public function test_admin_can_rename_project(): void
    {
        $admin = User::where('email', 'admin@iium.edu.my')->first();
        $this->actingAs($admin);

        $project = Project::create(['name' => 'Original Name']);

        Livewire::test('projects.manage-projects')
            ->call('startEditing', $project->id)
            ->set('newNames.' . $project->id, 'New Name')
            ->call('saveRename', $project->id)
            ->assertHasNoErrors();

        // Verify project was renamed
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'New Name',
        ]);

        // Verify new name appears in UI
        $response = $this->get(route('dashboard'));
        $response->assertSee('New Name');
        $response->assertDontSee('Original Name');
    }

    /**
     * Matrix row: Admin archives → Click archive + confirm → Moves to archived list, gone from active
     */
    public function test_admin_can_archive_project(): void
    {
        $admin = User::where('email', 'admin@iium.edu.my')->first();
        $this->actingAs($admin);

        $project = Project::create(['name' => 'To Be Archived']);

        Livewire::test('projects.manage-projects')
            ->call('confirmArchive', $project->id)
            ->call('archive', $project->id)
            ->assertHasNoErrors();

        // Verify project was archived
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'To Be Archived',
        ]);

        $project->refresh();
        $this->assertNotNull($project->archived_at);

        // Verify it appears in archived section
        $response = $this->get(route('dashboard'));
        $response->assertSee('Archived Projects');
        $response->assertSee('To Be Archived');
        $response->assertSee('Archived');
    }

    /**
     * Matrix row: Mutation of archived project → Rename/archive on archived → Rejected 422/403, row unchanged
     */
    public function test_cannot_modify_archived_project(): void
    {
        $admin = User::where('email', 'admin@iium.edu.my')->first();
        $this->actingAs($admin);

        $project = Project::create([
            'name' => 'Archived Project',
            'archived_at' => now(),
        ]);

        // Try to rename archived project - should throw exception
        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);

        try {
            Livewire::test('projects.manage-projects')
                ->call('startEditing', $project->id)
                ->set('newNames.' . $project->id, 'New Name')
                ->call('saveRename', $project->id);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            // Verify name did not change
            $this->assertDatabaseHas('projects', [
                'id' => $project->id,
                'name' => 'Archived Project',
            ]);

            // Re-throw for the test expectation
            throw $e;
        }
    }

    /**
     * Test that admin sees all management controls
     */
    public function test_admin_sees_all_management_controls(): void
    {
        $admin = User::where('email', 'admin@iium.edu.my')->first();
        $this->actingAs($admin);

        Project::create(['name' => 'Test Project']);

        $response = $this->get(route('dashboard'));
        $response->assertOk();

        // Should see all management controls
        $response->assertSee('Create Project');
        $response->assertSee('New Project Name');
        $response->assertSee('Rename');
        $response->assertSee('Archive');
    }

    /**
     * Test that projects route is accessible for admins
     */
    public function test_admin_can_access_projects_route(): void
    {
        $admin = User::where('email', 'admin@iium.edu.my')->first();
        $this->actingAs($admin);

        $response = $this->get(route('projects'));
        $response->assertOk();
    }

    /**
     * Test that non-admins cannot access projects route directly
     */
    public function test_non_admin_cannot_access_projects_route(): void
    {
        $user = User::factory()->create(['role' => UserRole::TESTER]);
        $this->actingAs($user);

        $response = $this->get(route('projects'));
        $response->assertStatus(403);
    }
}
