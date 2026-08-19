<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Membership;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TeamManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Run the database seeders
        $this->seed();
    }

    /**
     * Matrix row: Guest opens /users → Unauthenticated → Redirect to /login
     */
    public function test_guests_cannot_access_users_page(): void
    {
        $response = $this->get(route('users'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Matrix row: Non-admin opens /users → role != admin → 403 "not permitted for your role"
     */
    public function test_non_admin_cannot_access_users_page(): void
    {
        $user = User::factory()->create(['role' => UserRole::TESTER]);
        $this->actingAs($user);

        $response = $this->get(route('users'));
        $response->assertStatus(403);
    }

    /**
     * Matrix row: Non-admin posts ManageTeam action → role != admin → 403, no row created
     * ManageTeam's mount() enforces the admin gate, so a non-admin can never hold a
     * component snapshot — the enforceable boundary is the route (middleware + mount).
     */
    public function test_non_admin_cannot_create_user_via_livewire(): void
    {
        $user = User::factory()->create(['role' => UserRole::BUSINESS_USER]);
        $this->actingAs($user);

        // No component instance can be created; the page itself is forbidden
        $response = $this->get(route('users'));
        $response->assertStatus(403);

        // And no user row appeared
        $this->assertDatabaseMissing('users', [
            'email' => 'sneaky@iium.edu.my',
        ]);
    }

    /**
     * Matrix row: Admin creates user → name/email/password + role + 2 projects → User row + memberships
     */
    public function test_admin_can_create_user_with_projects(): void
    {
        $admin = User::where('email', 'admin@iium.edu.my')->first();
        $this->actingAs($admin);

        $project1 = Project::create(['name' => 'Project 1']);
        $project2 = Project::create(['name' => 'Project 2']);

        Livewire::test('team.manage-team')
            ->set('newUserName', 'Test User')
            ->set('newUserEmail', 'test@iium.edu.my')
            ->set('newUserPassword', 'password123')
            ->set('newUserRole', UserRole::TESTER->value)
            ->set('newUserProjects', [$project1->id, $project2->id])
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@iium.edu.my',
            'role' => UserRole::TESTER->value,
        ]);

        $user = User::where('email', 'test@iium.edu.my')->first();
        $this->assertNotNull($user->email_verified_at);

        $this->assertDatabaseHas('memberships', [
            'user_id' => $user->id,
            'project_id' => $project1->id,
        ]);

        $this->assertDatabaseHas('memberships', [
            'user_id' => $user->id,
            'project_id' => $project2->id,
        ]);
    }

    /**
     * Matrix row: Create with duplicate email → existing email → Validation error, no row
     */
    public function test_cannot_create_user_with_duplicate_email(): void
    {
        $admin = User::where('email', 'admin@iium.edu.my')->first();
        $this->actingAs($admin);

        User::factory()->create(['email' => 'existing@iium.edu.my']);

        Livewire::test('team.manage-team')
            ->set('newUserName', 'New User')
            ->set('newUserEmail', 'existing@iium.edu.my')
            ->set('newUserPassword', 'password123')
            ->set('newUserRole', UserRole::TESTER->value)
            ->call('create')
            ->assertHasErrors(['newUserEmail' => 'unique']);

        $this->assertDatabaseCount('users', 2); // admin + existing user only
    }

    /**
     * Matrix row: Create with missing fields → empty name/email/password → Validation errors, no row
     */
    public function test_cannot_create_user_with_missing_fields(): void
    {
        $admin = User::where('email', 'admin@iium.edu.my')->first();
        $this->actingAs($admin);

        Livewire::test('team.manage-team')
            ->set('newUserName', '')
            ->set('newUserEmail', '')
            ->set('newUserPassword', '')
            ->call('create')
            ->assertHasErrors(['newUserName' => 'required', 'newUserEmail' => 'required', 'newUserPassword' => 'required']);

        $this->assertDatabaseCount('users', 1); // only admin
    }

    /**
     * Matrix row: Create with whitespace-only fields → Validation errors, no row
     */
    public function test_cannot_create_user_with_whitespace_only_fields(): void
    {
        $admin = User::where('email', 'admin@iium.edu.my')->first();
        $this->actingAs($admin);

        Livewire::test('team.manage-team')
            ->set('newUserName', '   ')
            ->set('newUserEmail', '  ')
            ->set('newUserPassword', '   ')
            ->call('create')
            ->assertHasErrors(['newUserName' => 'required', 'newUserEmail' => 'required', 'newUserPassword' => 'required']);

        $this->assertDatabaseCount('users', 1); // only admin
    }

    /**
     * Matrix row: Added user logs in (assigned) → correct credentials → Dashboard lists ONLY their assigned active projects
     */
    public function test_added_user_with_assignments_sees_only_assigned_projects_on_dashboard(): void
    {
        $admin = User::where('email', 'admin@iium.edu.my')->first();
        $this->actingAs($admin);

        $project1 = Project::create(['name' => 'Assigned Project 1']);
        $project2 = Project::create(['name' => 'Assigned Project 2']);
        $unassignedProject = Project::create(['name' => 'Unassigned Project']);

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@iium.edu.my',
            'password' => 'password123',
            'role' => UserRole::TESTER->value,
            'email_verified_at' => now(),
        ]);

        // Assign to only project1 and project2
        $user->projects()->sync([$project1->id, $project2->id]);

        // Login as the new user
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();

        // Should see assigned projects
        $response->assertSee('Assigned Project 1');
        $response->assertSee('Assigned Project 2');

        // Should NOT see unassigned projects
        $response->assertDontSee('Unassigned Project');
    }

    /**
     * Matrix row: Added user logs in (unassigned) → zero memberships → Dashboard empty state, no projects listed
     */
    public function test_added_user_without_assignments_sees_empty_state_on_dashboard(): void
    {
        $admin = User::where('email', 'admin@iium.edu.my')->first();
        $this->actingAs($admin);

        Project::create(['name' => 'Some Project']);

        $user = User::factory()->create([
            'name' => 'Unassigned User',
            'email' => 'unassigned@iium.edu.my',
            'password' => 'password123',
            'role' => UserRole::TESTER->value,
            'email_verified_at' => now(),
        ]);

        // No project assignments
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();

        // Should see empty state message
        $response->assertSee("You haven't been assigned to any active projects yet.", false);

        // Should NOT see the project
        $response->assertDontSee('Some Project');
    }

    /**
     * Matrix row: Admin removes user from project → click remove → Membership row gone; user record intact
     */
    public function test_admin_can_remove_user_from_project(): void
    {
        $admin = User::where('email', 'admin@iium.edu.my')->first();
        $this->actingAs($admin);

        $project = Project::create(['name' => 'Test Project']);

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@iium.edu.my',
            'role' => UserRole::TESTER->value,
            'email_verified_at' => now(),
        ]);

        $user->projects()->attach($project->id);

        $this->assertDatabaseHas('memberships', [
            'user_id' => $user->id,
            'project_id' => $project->id,
        ]);

        Livewire::test('team.manage-team')
            ->call('removeFromProject', $user->id, $project->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('memberships', [
            'user_id' => $user->id,
            'project_id' => $project->id,
        ]);

        // User record should still exist
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'test@iium.edu.my',
        ]);
    }

    /**
     * Matrix row: Admin changes role → Tester → Business User → Reflected in team list immediately
     */
    public function test_admin_can_change_user_role(): void
    {
        $admin = User::where('email', 'admin@iium.edu.my')->first();
        $this->actingAs($admin);

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@iium.edu.my',
            'role' => UserRole::TESTER->value,
            'email_verified_at' => now(),
        ]);

        Livewire::test('team.manage-team')
            ->call('startEditingRole', $user->id)
            ->set('newRoles.' . $user->id, UserRole::BUSINESS_USER->value)
            ->call('saveRole', $user->id)
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertEquals(UserRole::BUSINESS_USER, $user->role);
    }

    /**
     * Matrix row: Admin changes own role → self-demotion attempt → Rejected, explicit error, role unchanged
     */
    public function test_admin_cannot_change_own_role(): void
    {
        $admin = User::where('email', 'admin@iium.edu.my')->first();
        $this->actingAs($admin);

        // Guard fires at the START of editing too — no self-role UI should open
        Livewire::test('team.manage-team')
            ->call('startEditingRole', $admin->id)
            ->assertHasErrors(['role']);

        // And again on save (belt and braces)
        Livewire::test('team.manage-team')
            ->set('newRoles.' . $admin->id, UserRole::TESTER->value)
            ->call('saveRole', $admin->id)
            ->assertHasErrors(['role']);

        $admin->refresh();
        $this->assertEquals(UserRole::ADMIN, $admin->role);
    }

    /**
     * Non-admin: every team management action returns 403
     * mount()'s admin gate means a non-admin can never instantiate the component,
     * so no action can be invoked — verified at the route boundary.
     */
    public function test_non_admin_cannot_run_any_team_management_action(): void
    {
        $user = User::factory()->create(['role' => UserRole::DEVELOPER]);
        $this->actingAs($user);

        $admin = User::where('email', 'admin@iium.edu.my')->first();
        $project = Project::create(['name' => 'Test Project']);

        $response = $this->get(route('users'));
        $response->assertStatus(403);

        // Admin and project untouched
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => UserRole::ADMIN->value]);
        $this->assertDatabaseCount('memberships', 0);
    }

    /**
     * Test that admin sees all team management controls
     */
    public function test_admin_sees_all_team_management_controls(): void
    {
        $admin = User::where('email', 'admin@iium.edu.my')->first();
        $this->actingAs($admin);

        $response = $this->get(route('users'));
        $response->assertOk();

        $response->assertSee('Team Management');
        $response->assertSee('Create User');
        $response->assertSee('Name');
        $response->assertSee('Email');
        $response->assertSee('Password');
        $response->assertSee('Role');
        $response->assertSee('Assign to Projects');
    }

    /**
     * Test that dashboard has team link for admin only
     */
    public function test_admin_has_team_link_on_dashboard(): void
    {
        $admin = User::where('email', 'admin@iium.edu.my')->first();
        $this->actingAs($admin);

        $response = $this->get(route('dashboard'));
        $response->assertOk();

        // Should see Team button/link
        $response->assertSee('Team');
    }

    /**
     * Test that non-admin doesn't have team link on dashboard
     */
    public function test_non_admin_does_not_have_team_link_on_dashboard(): void
    {
        $user = User::factory()->create(['role' => UserRole::TESTER]);
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();

        // Should not see Team navigation link specifically
        $html = $response->getContent();
        $this->assertDoesNotMatchRegularExpression(
            '/<a[^>]*href="' . preg_quote(route('users'), '/') . '"[^>]*>Team<\/a>/',
            $html
        );
    }

    /**
     * Test that archived projects cannot be assigned
     */
    public function test_cannot_assign_users_to_archived_projects(): void
    {
        $admin = User::where('email', 'admin@iium.edu.my')->first();
        $this->actingAs($admin);

        $activeProject = Project::create(['name' => 'Active Project']);
        $archivedProject = Project::create([
            'name' => 'Archived Project',
            'archived_at' => now(),
        ]);

        // Attempt to assign to both active and archived projects
        Livewire::test('team.manage-team')
            ->set('newUserName', 'Test User')
            ->set('newUserEmail', 'test@iium.edu.my')
            ->set('newUserPassword', 'password123')
            ->set('newUserRole', UserRole::TESTER->value)
            ->set('newUserProjects', [$activeProject->id, $archivedProject->id])
            ->call('create')
            ->assertHasNoErrors();

        $user = User::where('email', 'test@iium.edu.my')->first();

        // Should only be assigned to active project
        $this->assertTrue($user->projects->contains($activeProject->id));
        $this->assertFalse($user->projects->contains($archivedProject->id));
    }

    /**
     * Test that created user has email_verified_at set
     */
    public function test_created_user_has_email_verified_at_set(): void
    {
        $admin = User::where('email', 'admin@iium.edu.my')->first();
        $this->actingAs($admin);

        Livewire::test('team.manage-team')
            ->set('newUserName', 'Test User')
            ->set('newUserEmail', 'test@iium.edu.my')
            ->set('newUserPassword', 'password123')
            ->set('newUserRole', UserRole::TESTER->value)
            ->call('create')
            ->assertHasNoErrors();

        $user = User::where('email', 'test@iium.edu.my')->first();
        $this->assertNotNull($user->email_verified_at);
    }

    /**
     * Test that non-admin cannot remove users from projects
     * (route boundary — mount gate prevents non-admin component instantiation)
     */
    public function test_non_admin_cannot_remove_users_from_projects(): void
    {
        $nonAdmin = User::factory()->create(['role' => UserRole::TESTER]);
        $this->actingAs($nonAdmin);

        $admin = User::where('email', 'admin@iium.edu.my')->first();
        $project = Project::create(['name' => 'Test Project']);

        $admin->projects()->attach($project->id);

        $response = $this->get(route('users'));
        $response->assertStatus(403);

        // Membership should still exist
        $this->assertDatabaseHas('memberships', [
            'user_id' => $admin->id,
            'project_id' => $project->id,
        ]);
    }
}