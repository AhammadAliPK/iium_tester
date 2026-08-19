<x-layouts::app :title="__('Dashboard')">
    <x-iium-container>
        <div style="padding: 40px 0;">
            <x-iium-brand-label>Dashboard</x-iium-brand-label>
            <div style="display: flex; justify-content: space-between; align-items: center; margin: 24px 0 40px 0;">
                <div>
                    <h1 style="font-family: var(--font-heading); font-size: 42px; line-height: 1.1; margin: 0; color: var(--color-iium-text-primary);">
                        Projects
                    </h1>
                    <p style="color: var(--color-iium-text-secondary); font-size: 18px; margin-top: 8px; max-width: 600px;">
                        Manage your testing projects — create, rename, and archive projects to organize your testing workflows.
                    </p>
                </div>
                @if(auth()->check() && auth()->user()->role === \App\Enums\UserRole::ADMIN)
                    <a
                        href="{{ route('users') }}"
                        style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 20px; background: var(--color-iium-turquoise); color: white; border: none; border-radius: var(--radius-iium-md); font-size: 14px; font-weight: 600; cursor: pointer; text-decoration: none; transition: background var(--iium-duration) var(--iium-ease-spring);"
                        onmouseenter="this.style.background='var(--color-iium-turquoise-dark)'"
                        onmouseleave="this.style.background='var(--color-iium-turquoise)'"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        Team
                    </a>
                @endif
            </div>

            <livewire:projects.manage-projects />
        </div>
    </x-iium-container>
</x-layouts::app>
