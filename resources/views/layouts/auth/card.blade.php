<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen" style="display: flex; flex-direction: column; background-color: var(--color-iium-deep);">
        <div style="display: flex; min-height: 100vh; flex-direction: column; align-items: center; justify-content: center; gap: 24px; padding: 24px;">
            <div style="display: flex; flex-direction: column; gap: 24px; max-width: 100%; width: 100%; max-width: 448px;">
                <a href="{{ route('home') }}" style="display: flex; flex-direction: column; align-items: center; gap: 8px; text-decoration: none;">
                    <h1 style="font-family: var(--font-heading); font-size: 24px; margin: 0; color: var(--color-iium-text-primary);">
                        IIUM <span style="color: var(--color-iium-gold)">DIGITAL</span>
                    </h1>
                </a>

                <div style="display: flex; flex-direction: column; gap: 24px;">
                    <x-iium-card padding="32px">
                        {{ $slot }}
                    </x-iium-card>
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
