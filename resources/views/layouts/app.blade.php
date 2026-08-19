<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen" style="display: flex; flex-direction: column;">
        <x-iium-header />

        @auth
        <div class="iium-app-wrapper" style="display: flex; flex: 1;">
            <x-iium-sidebar />
            <!-- Content column owns the footer so it spans the content area
                 only, never running under the sidebar rail -->
            <div style="display: flex; flex-direction: column; flex: 1; min-width: 0;">
                <main style="flex: 1;">
                    {{ $slot }}
                </main>
                <x-iium-footer />
            </div>
        </div>
        @else
        <main style="flex: 1;">
            {{ $slot }}
        </main>
        <x-iium-footer />
        @endauth

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
