<div>
    <!-- Create User Section (Admin Only) -->
    @if($this->isAdmin())
        <div style="margin-bottom: 48px;">
            <div style="margin-bottom: 24px;">
                <x-iium-brand-label>Team Management</x-iium-brand-label>
                <h2 style="font-family: var(--font-heading); font-size: 28px; margin: 8px 0; color: var(--color-iium-text-primary);">
                    Team Management
                </h2>
                <p style="color: var(--color-iium-text-secondary); font-size: 14px; margin: 8px 0;">
                    Create users, manage roles, and assign them to projects.
                </p>
            </div>

            <x-iium-card padding="24px">
                <form wire:submit="create">
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 16px;">
                        <div>
                            <label for="newUserName" style="display: block; font-size: 14px; font-weight: 600; color: var(--color-iium-text-primary); margin-bottom: 8px;">
                                Name
                            </label>
                            <input
                                type="text"
                                id="newUserName"
                                wire:model.live="newUserName"
                                placeholder="Enter user name..."
                                style="width: 100%; padding: 12px 16px; border: 1px solid var(--color-iium-border-default); border-radius: var(--radius-iium-md); font-size: 14px; color: var(--color-iium-text-primary); background: var(--color-iium-surface-base); transition: border-color var(--iium-duration) var(--iium-ease-spring);"
                                onfocus="this.style.borderColor='var(--color-iium-turquoise)'"
                                onblur="this.style.borderColor='var(--color-iium-border-default)'"
                            >
                            @error('newUserName')
                                <p style="color: var(--color-iium-danger); font-size: 13px; margin-top: 8px;">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="newUserEmail" style="display: block; font-size: 14px; font-weight: 600; color: var(--color-iium-text-primary); margin-bottom: 8px;">
                                Email
                            </label>
                            <input
                                type="email"
                                id="newUserEmail"
                                wire:model.live="newUserEmail"
                                placeholder="user@iium.edu.my"
                                style="width: 100%; padding: 12px 16px; border: 1px solid var(--color-iium-border-default); border-radius: var(--radius-iium-md); font-size: 14px; color: var(--color-iium-text-primary); background: var(--color-iium-surface-base); transition: border-color var(--iium-duration) var(--iium-ease-spring);"
                                onfocus="this.style.borderColor='var(--color-iium-turquoise)'"
                                onblur="this.style.borderColor='var(--color-iium-border-default)'"
                            >
                            @error('newUserEmail')
                                <p style="color: var(--color-iium-danger); font-size: 13px; margin-top: 8px;">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="newUserPassword" style="display: block; font-size: 14px; font-weight: 600; color: var(--color-iium-text-primary); margin-bottom: 8px;">
                                Password
                            </label>
                            <input
                                type="password"
                                id="newUserPassword"
                                wire:model.live="newUserPassword"
                                placeholder="••••••••"
                                style="width: 100%; padding: 12px 16px; border: 1px solid var(--color-iium-border-default); border-radius: var(--radius-iium-md); font-size: 14px; color: var(--color-iium-text-primary); background: var(--color-iium-surface-base); transition: border-color var(--iium-duration) var(--iium-ease-spring);"
                                onfocus="this.style.borderColor='var(--color-iium-turquoise)'"
                                onblur="this.style.borderColor='var(--color-iium-border-default)'"
                            >
                            @error('newUserPassword')
                                <p style="color: var(--color-iium-danger); font-size: 13px; margin-top: 8px;">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="newUserRole" style="display: block; font-size: 14px; font-weight: 600; color: var(--color-iium-text-primary); margin-bottom: 8px;">
                                Role
                            </label>
                            <select
                                id="newUserRole"
                                wire:model.live="newUserRole"
                                style="width: 100%; padding: 12px 16px; border: 1px solid var(--color-iium-border-default); border-radius: var(--radius-iium-md); font-size: 14px; color: var(--color-iium-text-primary); background: var(--color-iium-surface-base); transition: border-color var(--iium-duration) var(--iium-ease-spring); cursor: pointer;"
                                onfocus="this.style.borderColor='var(--color-iium-turquoise)'"
                                onblur="this.style.borderColor='var(--color-iium-border-default)'"
                            >
                                <option value="admin">Admin</option>
                                <option value="business_user">Business User</option>
                                <option value="tester">Tester</option>
                                <option value="developer">Developer</option>
                            </select>
                            @error('newUserRole')
                                <p style="color: var(--color-iium-danger); font-size: 13px; margin-top: 8px;">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 14px; font-weight: 600; color: var(--color-iium-text-primary); margin-bottom: 8px;">
                            Assign to Projects (optional)
                        </label>
                        @if($this->activeProjects->count() > 0)
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 8px;">
                                @foreach($this->activeProjects as $project)
                                    <label style="display: flex; align-items: center; gap: 8px; padding: 8px 12px; border: 1px solid var(--color-iium-border-default); border-radius: var(--radius-iium-md); cursor: pointer; transition: background var(--iium-duration) var(--iium-ease-spring);" onmouseenter="this.style.background='var(--color-iium-surface-hover)'" onmouseleave="this.style.background='transparent'">
                                        <input
                                            type="checkbox"
                                            value="{{ $project->id }}"
                                            wire:model.live="newUserProjects"
                                            style="cursor: pointer;"
                                        >
                                        <span style="font-size: 14px; color: var(--color-iium-text-primary);">{{ $project->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <p style="color: var(--color-iium-text-secondary); font-size: 13px;">No active projects available.</p>
                        @endif
                    </div>

                    <button
                        type="submit"
                        style="padding: 12px 24px; background: var(--color-iium-turquoise); color: white; border: none; border-radius: var(--radius-iium-md); font-size: 14px; font-weight: 600; cursor: pointer; transition: background var(--iium-duration) var(--iium-ease-spring);"
                        onmouseenter="this.style.background='var(--color-iium-turquoise-dark)'"
                        onmouseleave="this.style.background='var(--color-iium-turquoise)'"
                    >
                        Create User
                    </button>
                </form>
            </x-iium-card>
        </div>
    @endif

    <!-- Team List -->
    <div>
        @if($this->users->count() > 0)
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 24px;">
                @foreach($this->users as $user)
                    <x-iium-card padding="24px">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                            <div>
                                <h3 style="font-family: var(--font-heading); font-size: 18px; margin: 0 0 4px 0; color: var(--color-iium-text-primary);">
                                    {{ $user->name }}
                                </h3>
                                <p style="color: var(--color-iium-text-secondary); font-size: 13px; margin: 0;">{{ $user->email }}</p>
                            </div>
                            @if($this->isAdmin() && $user->id !== auth()->id())
                                <button
                                    wire:click="startEditingRole({{ $user->id }})"
                                    style="padding: 6px 12px; background: var(--color-iium-surface-raised); color: var(--color-iium-text-primary); border: 1px solid var(--color-iium-border-default); border-radius: var(--radius-iium-md); font-size: 12px; font-weight: 600; cursor: pointer; transition: background var(--iium-duration) var(--iium-ease-spring);"
                                    onmouseenter="this.style.background='var(--color-iium-surface-hover)'"
                                    onmouseleave="this.style.background='var(--color-iium-surface-raised)'"
                                >
                                    Change Role
                                </button>
                            @endif
                        </div>

                        @if($editingRole[$user->id] ?? false)
                            <!-- Role Edit Mode -->
                            <form wire:submit="saveRole({{ $user->id }})" style="margin-bottom: 16px;">
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <select
                                        wire:model.live="newRoles.{{ $user->id }}"
                                        style="flex: 1; padding: 8px 12px; border: 1px solid var(--color-iium-border-default); border-radius: var(--radius-iium-md); font-size: 13px; color: var(--color-iium-text-primary); background: var(--color-iium-surface-base);"
                                    >
                                        <option value="admin">Admin</option>
                                        <option value="business_user">Business User</option>
                                        <option value="tester">Tester</option>
                                        <option value="developer">Developer</option>
                                    </select>
                                    <button
                                        type="submit"
                                        style="padding: 8px 16px; background: var(--color-iium-turquoise); color: white; border: none; border-radius: var(--radius-iium-md); font-size: 13px; font-weight: 600; cursor: pointer;"
                                    >
                                        Save
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="cancelEditingRole({{ $user->id }})"
                                        style="padding: 8px 16px; background: var(--color-iium-surface-raised); color: var(--color-iium-text-primary); border: 1px solid var(--color-iium-border-default); border-radius: var(--radius-iium-md); font-size: 13px; font-weight: 600; cursor: pointer;"
                                    >
                                        Cancel
                                    </button>
                                </div>
                                @error("newRoles.{$user->id}")
                                    <p style="color: var(--color-iium-danger); font-size: 13px; margin-top: 8px;">{{ $message }}</p>
                                @enderror
                            </form>
                        @else
                            <!-- Display Role -->
                            <div style="margin-bottom: 16px;">
                                <span style="display: inline-block; padding: 4px 12px; background: var(--color-iium-gold); color: white; border-radius: var(--radius-iium-md); font-size: 11px; font-weight: 700; text-transform: uppercase;">
                                    {{ \Illuminate\Support\Str::title(str_replace('_', ' ', $user->role->value)) }}
                                </span>
                            </div>
                        @endif

                        <!-- Project Assignments -->
                        @if($this->isAdmin())
                            <div>
                                <label style="display: block; font-size: 13px; font-weight: 600; color: var(--color-iium-text-secondary); margin-bottom: 8px;">
                                    Assigned Projects
                                </label>
                                @if($this->activeProjects->count() > 0)
                                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 8px;">
                                        @foreach($this->activeProjects as $project)
                                            @php $isAssigned = $user->projects->contains($project->id); @endphp
                                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 6px 10px; border: 1px solid var(--color-iium-border-default); border-radius: var(--radius-iium-md); transition: background var(--iium-duration) var(--iium-ease-spring);">
                                                <div style="display: flex; align-items: center; gap: 6px; flex: 1;">
                                                    <input
                                                        type="checkbox"
                                                        id="project_{{ $user->id }}_{{ $project->id }}"
                                                        wire:model.live="userProjects.{{ $user->id }}.{{ $project->id }}"
                                                        wire:change="toggleUserProject({{ $user->id }}, {{ $project->id }})"
                                                        @if($isAssigned) checked @endif
                                                        style="cursor: pointer;"
                                                    >
                                                    <label for="project_{{ $user->id }}_{{ $project->id }}" style="font-size: 13px; color: var(--color-iium-text-primary); cursor: pointer;">{{ $project->name }}</label>
                                                </div>
                                                @if($isAssigned)
                                                    <button
                                                        wire:click.prevent="removeFromProject({{ $user->id }}, {{ $project->id }})"
                                                        style="padding: 2px 8px; background: var(--color-iium-danger); color: white; border: none; border-radius: var(--radius-iium-sm); font-size: 11px; font-weight: 600; cursor: pointer;"
                                                    >
                                                        Remove
                                                    </button>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p style="color: var(--color-iium-text-secondary); font-size: 12px;">No active projects available.</p>
                                @endif
                            </div>
                        @else
                            <!-- Non-admin view: just show assigned projects -->
                            <div>
                                <label style="display: block; font-size: 13px; font-weight: 600; color: var(--color-iium-text-secondary); margin-bottom: 8px;">
                                    Assigned Projects
                                </label>
                                @if($user->projects->count() > 0)
                                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                        @foreach($user->projects as $project)
                                            <span style="padding: 4px 12px; background: var(--color-iium-surface-raised); color: var(--color-iium-text-primary); border: 1px solid var(--color-iium-border-default); border-radius: var(--radius-iium-md); font-size: 12px;">
                                                {{ $project->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <p style="color: var(--color-iium-text-secondary); font-size: 12px;">No projects assigned.</p>
                                @endif
                            </div>
                        @endif
                    </x-iium-card>
                @endforeach
            </div>
        @else
            <x-iium-card>
                <div style="text-align: center; padding: 40px 0;">
                    <p style="color: var(--color-iium-text-secondary); font-size: 16px; margin: 0;">
                        No users yet. @if($this->isAdmin())Create your first user above.@endif
                    </p>
                </div>
            </x-iium-card>
        @endif
    </div>
</div>