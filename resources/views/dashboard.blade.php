<x-layouts::app :title="__('Dashboard')">
    <x-iium-container>
        <div style="padding: 40px 0;">
            <x-iium-brand-label>Dashboard</x-iium-brand-label>
            <h1 style="font-family: var(--font-heading); font-size: 42px; line-height: 1.1; margin: 24px 0; color: var(--color-iium-text-primary);">
                Projects
            </h1>
            <p style="color: var(--color-iium-text-secondary); font-size: 18px; margin-bottom: 40px; max-width: 600px;">
                Manage your testing projects — create, rename, and archive projects to organize your testing workflows.
            </p>

            <livewire:projects.manage-projects />
        </div>
    </x-iium-container>
</x-layouts::app>
