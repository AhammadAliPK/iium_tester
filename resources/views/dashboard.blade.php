@php
    $user = auth()->user();
    $isAdmin = $user->role === \App\Enums\UserRole::ADMIN;

    // Counts mirror ManageProjects scoping: admins see the whole workspace,
    // everyone else sees only what their memberships grant (AD-5)
    $activeProjects = $isAdmin
        ? \App\Models\Project::isActive()->count()
        : $user->projects()->isActive()->count();
    $archivedProjects = $isAdmin
        ? \App\Models\Project::archived()->count()
        : $user->projects()->archived()->count();

    if ($isAdmin) {
        $teamMembers = \App\Models\User::count();
        $teamSub = sprintf(
            '%d admin · %d business · %d testers · %d developers',
            \App\Models\User::where('role', \App\Enums\UserRole::ADMIN)->count(),
            \App\Models\User::where('role', \App\Enums\UserRole::BUSINESS_USER)->count(),
            \App\Models\User::where('role', \App\Enums\UserRole::TESTER)->count(),
            \App\Models\User::where('role', \App\Enums\UserRole::DEVELOPER)->count(),
        );
    } else {
        $projectIds = $user->projects()->pluck('projects.id');
        $teamMembers = \App\Models\User::whereHas('projects', fn ($query) => $query->whereIn('projects.id', $projectIds))->count();
        $teamSub = 'across your projects';
    }

    $roles = [
        'admin' => ['Admin', 'Create projects, manage the team, and oversee testing across the platform.'],
        'business_user' => ['Business User', 'Author and maintain test cases within your assigned projects.'],
        'tester' => ['Tester', 'Execute assigned test runs and keep case statuses up to date.'],
        'developer' => ['Developer', 'Review submitted test reports and mark failures as fixed.'],
    ];
    // Null-safe: self-registered users may carry no role until an admin assigns one
    [$roleName, $roleBlurb] = $roles[$user->role?->value ?? 'tester'];
@endphp

<x-layouts::app :title="__('Dashboard')">
    <x-iium-container>
        <div style="padding: 40px 0;">
            <x-iium-brand-label>Dashboard</x-iium-brand-label>
            <h1 style="font-family: var(--font-heading); font-size: 42px; line-height: 1.1; margin: 24px 0 8px 0; color: var(--color-iium-text-primary);">
                Welcome back, {{ $user->name }}
            </h1>
            <p style="color: var(--color-iium-text-secondary); font-size: 18px; margin: 0 0 40px 0; max-width: 600px;">
                An at-a-glance view of your testing workspace.
            </p>

            <div class="iium-stat-grid">
                <x-iium-card>
                    <div class="iium-stat-accent" style="background: var(--color-iium-turquoise);"></div>
                    <div class="iium-stat-label">{{ $isAdmin ? 'Active Projects' : 'My Active Projects' }}</div>
                    <div class="iium-stat-value">{{ $activeProjects }}</div>
                    <div class="iium-stat-sub">{{ $archivedProjects }} archived</div>
                </x-iium-card>

                <x-iium-card>
                    <div class="iium-stat-accent" style="background: var(--color-iium-gold);"></div>
                    <div class="iium-stat-label">{{ $isAdmin ? 'Team Members' : 'My Team' }}</div>
                    <div class="iium-stat-value">{{ $teamMembers }}</div>
                    <div class="iium-stat-sub">{{ $teamSub }}</div>
                </x-iium-card>

                <x-iium-card>
                    <div class="iium-stat-accent" style="background: var(--color-iium-deep);"></div>
                    <div class="iium-stat-label">My Role</div>
                    <div class="iium-stat-value iium-stat-value-text">{{ $roleName }}</div>
                    <div class="iium-stat-sub">{{ $roleBlurb }}</div>
                </x-iium-card>
            </div>

            <h2 style="font-family: var(--font-heading); font-size: 24px; margin: 48px 0 24px 0; color: var(--color-iium-text-primary);">
                Projects
            </h2>
            <livewire:projects.manage-projects />
        </div>
    </x-iium-container>

    <style>
        .iium-stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
        }

        .iium-stat-accent {
            width: 40px;
            height: 3px;
            border-radius: var(--radius-iium-sm);
            margin-bottom: 20px;
        }

        .iium-stat-label {
            font-family: var(--font-condensed);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--color-iium-text-secondary);
        }

        .iium-stat-value {
            font-family: var(--font-heading);
            font-size: 44px;
            line-height: 1.1;
            color: var(--color-iium-text-primary);
            margin-top: 12px;
            /* Fixed box keeps numeral and text values bottom-aligned so the
               sub-lines line up across cards */
            min-height: 48px;
            display: flex;
            align-items: flex-end;
        }

        .iium-stat-value-text {
            font-size: 28px;
        }

        .iium-stat-sub {
            font-size: 13px;
            color: var(--color-iium-text-secondary);
            margin-top: 6px;
        }
    </style>
</x-layouts::app>
