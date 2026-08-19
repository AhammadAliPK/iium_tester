<x-layouts::app :title="__('Dashboard')">
    <x-iium-container>
        <div style="padding: 40px 0;">
            <x-iium-brand-label>Dashboard</x-iium-brand-label>
            <h1 style="font-family: var(--font-heading); font-size: 42px; line-height: 1.1; margin: 24px 0; color: var(--color-iium-text-primary);">
                Welcome to iium_tester
            </h1>
            <p style="color: var(--color-iium-text-secondary); font-size: 18px; margin-bottom: 40px; max-width: 600px;">
                The IIUM Test Case Management System — streamlining testing workflows with modern tools and branded experience.
            </p>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
                <x-iium-card>
                    <x-iium-brand-label>Getting Started</x-iium-brand-label>
                    <h3 style="font-family: var(--font-heading); font-size: 20px; margin-bottom: 12px; color: var(--color-iium-text-primary);">
                        Projects & Tests
                    </h3>
                    <p style="color: var(--color-iium-text-secondary); font-size: 14px; margin-bottom: 24px;">
                        Organize your test suites and cases with our structured approach.
                    </p>
                </x-iium-card>

                <x-iium-card>
                    <x-iium-brand-label>Test Execution</x-iium-brand-label>
                    <h3 style="font-family: var(--font-heading); font-size: 20px; margin-bottom: 12px; color: var(--color-iium-text-primary);">
                        Run & Track
                    </h3>
                    <p style="color: var(--color-iium-text-secondary); font-size: 14px; margin-bottom: 24px;">
                        Execute test cycles with comprehensive tracking and reporting.
                    </p>
                </x-iium-card>

                <x-iium-card>
                    <x-iium-brand-label>Reporting</x-iium-brand-label>
                    <h3 style="font-family: var(--font-heading); font-size: 20px; margin-bottom: 12px; color: var(--color-iium-text-primary);">
                        Results & Analysis
                    </h3>
                    <p style="color: var(--color-iium-text-secondary); font-size: 14px; margin-bottom: 24px;">
                        Generate detailed reports and track quality metrics over time.
                    </p>
                </x-iium-card>
            </div>
        </div>
    </x-iium-container>
</x-layouts::app>
