<?php

namespace App\Livewire\Team;

use App\Enums\UserRole;
use App\Models\Membership;
use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ManageTeam extends Component
{
    /**
     * New user form fields.
     */
    public string $newUserName = '';
    public string $newUserEmail = '';
    public string $newUserPassword = '';
    public string $newUserRole = UserRole::TESTER->value;
    public array $newUserProjects = [];

    /**
     * User project assignments tracking.
     * Array keyed by user ID, then project ID with boolean value.
     */
    public array $userProjects = [];

    /**
     * Users being edited for role changes.
     * Array keyed by user ID.
     */
    public array $editingRole = [];

    /**
     * New roles for users being changed.
     * Array keyed by user ID.
     */
    public array $newRoles = [];

    /**
     * Mount the component.
     * Enforces admin gate per AD-5.
     */
    public function mount(): void
    {
        $this->authorize('admin');
    }

    /**
     * Check if current user is admin.
     */
    public function isAdmin(): bool
    {
        return Gate::allows('admin');
    }

    /**
     * Get all users.
     */
    public function getUsersProperty()
    {
        $users = User::with('projects')->latest()->get();

        // Initialize userProjects array for UI state
        foreach ($users as $user) {
            foreach ($this->activeProjects as $project) {
                $this->userProjects[$user->id][$project->id] = $user->projects->contains($project->id);
            }
        }

        return $users;
    }

    /**
     * Get active projects for assignment.
     */
    public function getActiveProjectsProperty()
    {
        return Project::isActive()->latest()->get();
    }

    /**
     * Create a new user.
     */
    public function create(): void
    {
        $this->authorize('admin');

        // Trim inputs to prevent whitespace-only submissions
        $this->newUserName = trim($this->newUserName);
        $this->newUserEmail = trim($this->newUserEmail);
        $this->newUserPassword = trim($this->newUserPassword);

        $this->validate([
            'newUserName' => 'required|string|max:255',
            'newUserEmail' => 'required|email|max:255|unique:users,email',
            'newUserPassword' => 'required|string|min:8',
            'newUserRole' => 'required|in:admin,business_user,tester,developer',
            'newUserProjects' => 'array',
            'newUserProjects.*' => 'exists:projects,id',
        ]);

        // Create user with email_verified_at set to now (admin-verified identity)
        $user = User::create([
            'name' => $this->newUserName,
            'email' => $this->newUserEmail,
            'password' => $this->newUserPassword,
            'role' => $this->newUserRole,
            'email_verified_at' => now(),
        ]);

        // Assign to selected active projects only (intersection guards against archived IDs)
        $activeProjectIds = $this->activeProjects->pluck('id')->toArray();
        $projectIdsToAssign = array_intersect($this->newUserProjects, $activeProjectIds);

        if (!empty($projectIdsToAssign)) {
            $user->projects()->sync($projectIdsToAssign);
        }

        // Reset form
        $this->newUserName = '';
        $this->newUserEmail = '';
        $this->newUserPassword = '';
        $this->newUserRole = UserRole::TESTER->value;
        $this->newUserProjects = [];

        $this->dispatch('user-created');
    }

    /**
     * Start editing a user's role.
     */
    public function startEditingRole(int $userId): void
    {
        $this->authorize('admin');

        $user = User::findOrFail($userId);

        // Self-lockout guard: admin cannot change their own role
        if ($user->id === auth()->id()) {
            throw ValidationException::withMessages([
                'role' => 'You cannot change your own role.',
            ]);
        }

        $this->editingRole[$userId] = true;
        $this->newRoles[$userId] = $user->role->value;
    }

    /**
     * Cancel editing a user's role.
     */
    public function cancelEditingRole(int $userId): void
    {
        unset($this->editingRole[$userId]);
        unset($this->newRoles[$userId]);
    }

    /**
     * Save the user's role change.
     */
    public function saveRole(int $userId): void
    {
        $this->authorize('admin');

        $user = User::findOrFail($userId);

        // Self-lockout guard: double-check on save
        if ($user->id === auth()->id()) {
            throw ValidationException::withMessages([
                'role' => 'You cannot change your own role.',
            ]);
        }

        $this->validate([
            "newRoles.{$userId}" => 'required|in:admin,business_user,tester,developer',
        ]);

        $user->update([
            'role' => $this->newRoles[$userId],
        ]);

        unset($this->editingRole[$userId]);
        unset($this->newRoles[$userId]);

        $this->dispatch('role-updated');
    }

    /**
     * Update user's project assignments.
     */
    public function updateProjects(int $userId, array $projectIds): void
    {
        $this->authorize('admin');

        $user = User::findOrFail($userId);

        // Only assign to active projects (server-side guard against forged requests)
        $activeProjectIds = $this->activeProjects->pluck('id')->toArray();
        $projectIdsToAssign = array_intersect($projectIds, $activeProjectIds);

        $user->projects()->sync($projectIdsToAssign);

        $this->dispatch('projects-updated');
    }

    /**
     * Toggle a user's assignment to a specific project.
     */
    public function toggleUserProject(int $userId, int $projectId): void
    {
        $this->authorize('admin');

        $user = User::findOrFail($userId);
        $project = Project::findOrFail($projectId);

        // Only allow toggling active projects
        if ($project->isArchived()) {
            return;
        }

        $currentAssignments = $user->projects->pluck('id')->toArray();

        if (in_array($projectId, $currentAssignments)) {
            // Remove assignment
            $user->projects()->detach($projectId);
        } else {
            // Add assignment
            $user->projects()->attach($projectId);
        }

        // Refresh the users to update the UI state
        $this->users = $this->users->fresh();
    }

    /**
     * Remove user from a specific project.
     */
    public function removeFromProject(int $userId, int $projectId): void
    {
        $this->authorize('admin');

        $user = User::findOrFail($userId);
        $project = Project::findOrFail($projectId);

        // Only allow removal from active projects
        if ($project->isArchived()) {
            throw new AuthorizationException('Cannot remove users from archived projects.');
        }

        // Remove the membership row
        Membership::where('user_id', $userId)
            ->where('project_id', $projectId)
            ->delete();

        $this->dispatch('project-removed');
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.team.manage-team');
    }
}