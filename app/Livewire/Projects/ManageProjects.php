<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ManageProjects extends Component
{
    /**
     * New project name for creation.
     */
    public string $newProjectName = '';

    /**
     * Projects being edited (rename mode).
     * Array keyed by project ID.
     */
    public array $editing = [];

    /**
     * New names for projects being renamed.
     * Array keyed by project ID.
     */
    public array $newNames = [];

    /**
     * Project ID being confirmed for archive.
     */
    public ?int $confirmingArchive = null;

    /**
     * Mount the component.
     * Enforces admin gate per AD-5.
     */
    public function mount(): void
    {
        // Only check admin gate for actions, not for viewing
        // Non-admins should be able to see the list but without management controls
    }

    /**
     * Check if current user is admin.
     */
    public function isAdmin(): bool
    {
        return Gate::allows('admin');
    }

    /**
     * Create a new project.
     */
    public function create(): void
    {
        $this->authorize('admin');

        $this->validate([
            'newProjectName' => 'required|string|max:255',
        ]);

        Project::create([
            'name' => $this->newProjectName,
        ]);

        $this->newProjectName = '';
        $this->dispatch('project-created');
    }

    /**
     * Start editing a project name.
     */
    public function startEditing(int $projectId): void
    {
        $this->authorizeAction($projectId);

        $project = Project::findOrFail($projectId);
        $this->editing[$projectId] = true;
        $this->newNames[$projectId] = $project->name;
    }

    /**
     * Cancel editing a project name.
     */
    public function cancelEditing(int $projectId): void
    {
        unset($this->editing[$projectId]);
        unset($this->newNames[$projectId]);
    }

    /**
     * Save the renamed project.
     */
    public function saveRename(int $projectId): void
    {
        $this->authorizeAction($projectId);

        $this->validate([
            "newNames.{$projectId}" => 'required|string|max:255',
        ]);

        $project = Project::findOrFail($projectId);

        if ($project->isArchived()) {
            throw new AuthorizationException('Cannot modify archived projects.');
        }

        $project->update([
            'name' => $this->newNames[$projectId],
        ]);

        unset($this->editing[$projectId]);
        unset($this->newNames[$projectId]);
        $this->dispatch('project-renamed');
    }

    /**
     * Start archive confirmation dialog.
     */
    public function confirmArchive(int $projectId): void
    {
        $this->authorizeAction($projectId);

        $project = Project::findOrFail($projectId);

        if ($project->isArchived()) {
            throw new AuthorizationException('Project is already archived.');
        }

        $this->confirmingArchive = $projectId;
    }

    /**
     * Archive the project.
     */
    public function archive(int $projectId): void
    {
        $this->authorizeAction($projectId);

        $project = Project::findOrFail($projectId);

        if ($project->isArchived()) {
            throw new AuthorizationException('Project is already archived.');
        }

        $project->update([
            'archived_at' => now(),
        ]);

        $this->confirmingArchive = null;
        $this->dispatch('project-archived');
    }

    /**
     * Cancel archive confirmation.
     */
    public function cancelArchive(): void
    {
        $this->confirmingArchive = null;
    }

    /**
     * Get active projects.
     */
    public function getActiveProjectsProperty()
    {
        return Project::isActive()->latest()->get();
    }

    /**
     * Get archived projects.
     */
    public function getArchivedProjectsProperty()
    {
        return Project::archived()->latest('archived_at')->get();
    }

    /**
     * Check if user can perform action on project.
     */
    protected function authorizeAction(int $projectId): void
    {
        $this->authorize('admin');

        $project = Project::findOrFail($projectId);

        if ($project->isArchived()) {
            throw new AuthorizationException('Cannot modify archived projects.');
        }
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.projects.manage-projects');
    }
}
