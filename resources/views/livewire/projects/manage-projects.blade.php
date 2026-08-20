<div>
    <!-- Active Projects Section -->
    <div style="margin-bottom: 48px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <div>
                <x-iium-brand-label>Active Projects</x-iium-brand-label>
                <h2 style="font-family: var(--font-heading); font-size: 28px; margin: 8px 0; color: var(--color-iium-text-primary);">
                    Active Projects
                </h2>
            </div>
        </div>

        @if($this->isAdmin())
            <!-- Create Project Form (Admin Only) -->
            <x-iium-card padding="24px" style="margin-bottom: 32px;">
                <form wire:submit="create">
                    <div style="display: flex; gap: 16px; align-items: flex-end;">
                        <div style="flex: 1;">
                            <label for="newProjectName" style="display: block; font-size: 14px; font-weight: 600; color: var(--color-iium-text-primary); margin-bottom: 8px;">
                                New Project Name
                            </label>
                            <input
                                type="text"
                                id="newProjectName"
                                wire:model.live="newProjectName"
                                placeholder="Enter project name..."
                                style="width: 100%; padding: 12px 16px; border: 1px solid var(--color-iium-border-default); border-radius: var(--radius-iium-md); font-size: 14px; color: var(--color-iium-text-primary); background: var(--color-iium-surface-base); transition: border-color var(--iium-duration) var(--iium-ease-spring);"
                                onfocus="this.style.borderColor='var(--color-iium-turquoise)'"
                                onblur="this.style.borderColor='var(--color-iium-border-default)'"
                            >
                            @error('newProjectName')
                                <p style="color: var(--color-iium-danger); font-size: 13px; margin-top: 8px;">{{ $message }}</p>
                            @enderror
                        </div>
                        <button
                            type="submit"
                            style="padding: 12px 24px; background: var(--color-iium-turquoise); color: white; border: none; border-radius: var(--radius-iium-md); font-size: 14px; font-weight: 600; cursor: pointer; transition: background var(--iium-duration) var(--iium-ease-spring);"
                            onmouseenter="this.style.background='var(--color-iium-turquoise-dark)'"
                            onmouseleave="this.style.background='var(--color-iium-turquoise)'"
                        >
                            Create Project
                        </button>
                    </div>
                </form>
            </x-iium-card>
        @endif

        <!-- Active Projects List -->
        @if($this->activeProjects->count() > 0)
            <x-iium-table :headers="$this->isAdmin() ? ['Project', 'Created', 'Actions'] : ['Project', 'Created']">
                @foreach($this->activeProjects as $project)
                    @if($editing[$project->id] ?? false)
                        <!-- Rename Mode (inline row) -->
                        <tr>
                            <td colspan="{{ $this->isAdmin() ? 3 : 2 }}">
                                <form wire:submit="saveRename({{ $project->id }})">
                                    <div style="display: flex; gap: 8px; align-items: center;">
                                        <input
                                            type="text"
                                            wire:model.live="newNames.{{ $project->id }}"
                                            placeholder="Rename project..."
                                            style="flex: 1; max-width: 360px; padding: 8px 14px; border: 1px solid var(--color-iium-border-default); border-radius: var(--radius-iium-md); font-size: 14px; color: var(--color-iium-text-primary); background: var(--color-iium-surface-base);"
                                        >
                                        <button
                                            type="submit"
                                            style="padding: 8px 16px; background: var(--color-iium-turquoise); color: white; border: none; border-radius: var(--radius-iium-md); font-size: 13px; font-weight: 600; cursor: pointer;"
                                        >
                                            Save
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="cancelEditing({{ $project->id }})"
                                            style="padding: 8px 16px; background: var(--color-iium-surface-raised); color: var(--color-iium-text-primary); border: 1px solid var(--color-iium-border-default); border-radius: var(--radius-iium-md); font-size: 13px; font-weight: 600; cursor: pointer;"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                    @error("newNames.{$project->id}")
                                        <p style="color: var(--color-iium-danger); font-size: 13px; margin-top: 8px;">{{ $message }}</p>
                                    @enderror
                                </form>
                            </td>
                        </tr>
                    @else
                        <!-- Display Mode -->
                        <tr>
                            <td style="font-weight: 600;">
                                {{ $project->name }}
                            </td>
                            <td style="color: var(--color-iium-text-secondary);">
                                {{ $project->created_at->format('M j, Y') }}
                            </td>
                            @if($this->isAdmin())
                                <td>
                                    <div style="display: inline-flex; gap: 8px;">
                                        <button
                                            wire:click="startEditing({{ $project->id }})"
                                            style="padding: 8px 14px; background: var(--color-iium-surface-raised); color: var(--color-iium-text-primary); border: 1px solid var(--color-iium-border-default); border-radius: var(--radius-iium-md); font-size: 12px; font-weight: 600; cursor: pointer; transition: background var(--iium-duration) var(--iium-ease-spring);"
                                            onmouseenter="this.style.background='var(--color-iium-surface-hover)'"
                                            onmouseleave="this.style.background='var(--color-iium-surface-raised)'"
                                        >
                                            Rename
                                        </button>
                                        <button
                                            wire:click="confirmArchive({{ $project->id }})"
                                            style="padding: 8px 14px; background: var(--color-iium-surface-raised); color: var(--color-iium-danger); border: 1px solid var(--color-iium-border-default); border-radius: var(--radius-iium-md); font-size: 12px; font-weight: 600; cursor: pointer; transition: background var(--iium-duration) var(--iium-ease-spring);"
                                            onmouseenter="this.style.background='var(--color-iium-danger-subtle)'"
                                            onmouseleave="this.style.background='var(--color-iium-surface-raised)'"
                                        >
                                            Archive
                                        </button>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endif
                @endforeach
            </x-iium-table>
        @else
            <x-iium-card>
                <div style="text-align: center; padding: 40px 0;">
                    <p style="color: var(--color-iium-text-secondary); font-size: 16px; margin: 0;">
                        @if($this->isAdmin())
                            No active projects yet. Create your first project above.
                        @else
                            You haven't been assigned to any active projects yet.
                        @endif
                    </p>
                </div>
            </x-iium-card>
        @endif
    </div>

    <!-- Archived Projects Section -->
    @if($this->archivedProjects->count() > 0)
        <div>
            <div style="margin-bottom: 24px;">
                <x-iium-brand-label>Archived</x-iium-brand-label>
                <h2 style="font-family: var(--font-heading); font-size: 28px; margin: 8px 0; color: var(--color-iium-text-primary);">
                    Archived Projects
                </h2>
                <p style="color: var(--color-iium-text-secondary); font-size: 14px; margin: 8px 0;">
                    Read-only view of archived projects
                </p>
            </div>

            <x-iium-table :headers="['Project', 'Archived On']">
                @foreach($this->archivedProjects as $project)
                    <tr>
                        <td style="font-weight: 600;">
                            {{ $project->name }}
                            <span style="display: inline-block; margin-left: 10px; padding: 3px 10px; background: var(--color-iium-gold); color: white; border-radius: var(--radius-iium-md); font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
                                Archived
                            </span>
                        </td>
                        <td style="color: var(--color-iium-text-secondary);">
                            {{ $project->archived_at->format('F j, Y') }}
                        </td>
                    </tr>
                @endforeach
            </x-iium-table>
        </div>
    @endif

    <!-- Archive Confirmation Dialog -->
    @if($confirmingArchive)
        <div
            x-data="{ open: true }"
            x-show="open"
            style="position: fixed; inset: 0; background: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center; z-index: 50;"
            @click.away="$wire.cancelArchive()"
        >
            <div
                style="background: var(--color-iium-surface-raised); border-radius: var(--radius-iium-lg); padding: 32px; max-width: 480px; width: 90%;"
                @click.stop
            >
                <h3 style="font-family: var(--font-heading); font-size: 20px; margin: 0 0 12px 0; color: var(--color-iium-text-primary);">
                    Archive Project
                </h3>
                <p style="color: var(--color-iium-text-secondary); font-size: 14px; margin: 0 0 24px 0; line-height: 1.5;">
                    Are you sure you want to archive this project? It will be moved to the archived section and will be read-only. This action cannot be undone.
                </p>
                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <button
                        wire:click="cancelArchive"
                        style="padding: 10px 20px; background: var(--color-iium-surface-raised); color: var(--color-iium-text-primary); border: 1px solid var(--color-iium-border-default); border-radius: var(--radius-iium-md); font-size: 14px; font-weight: 600; cursor: pointer;"
                    >
                        Cancel
                    </button>
                    <button
                        wire:click="archive({{ $confirmingArchive }})"
                        style="padding: 10px 20px; background: var(--color-iium-danger); color: white; border: none; border-radius: var(--radius-iium-md); font-size: 14px; font-weight: 600; cursor: pointer;"
                    >
                        Archive Project
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
