@props(['currentRoute' => request()->route()?->getName()])

@php
    $isDashboard = request()->routeIs('dashboard');
    $isProjects = request()->routeIs('projects');
    $isUsers = request()->routeIs('users');
@endphp

<!-- Backdrop: rendered only with the sidebar (auth), visible only below 900px -->
<div class="sidebar-backdrop" x-data aria-hidden="true"
     :class="$store.nav.open ? 'backdrop-visible' : ''"
     @click="$store.nav.close()"></div>

<nav id="app-sidebar" class="iium-sidebar" x-data
     :class="$store.nav.open ? 'drawer-open' : ''"
     @keydown.escape.window="$store.nav.close()">
    <div class="sidebar-nav-items">
        <!-- Dashboard - available to all authenticated users -->
        <a href="{{ route('dashboard') }}" @click="$store.nav.close()"
           class="sidebar-nav-item {{ $isDashboard ? 'active' : '' }}">
            Dashboard
        </a>

        @can('admin')
        <!-- Projects / Team - admin only -->
        <a href="{{ route('projects') }}" @click="$store.nav.close()"
           class="sidebar-nav-item {{ $isProjects ? 'active' : '' }}">
            Projects
        </a>
        <a href="{{ route('users') }}" @click="$store.nav.close()"
           class="sidebar-nav-item {{ $isUsers ? 'active' : '' }}">
            Team
        </a>
        @endcan
    </div>
</nav>

<script>
    // Shared drawer state: header toggle and sidebar are separate Blade
    // components, so an Alpine store is the lightest shared mechanism on
    // the stack (no Livewire round-trips, per spec). Registers before the
    // header toggle binds: both render only for authed users, and this
    // script is parsed first in DOM order via the sidebar include.
    document.addEventListener('alpine:init', () => {
        Alpine.store('nav', {
            open: false,
            toggle() { this.open ? this.close() : this.show(); },
            show() {
                this.open = true;
                document.documentElement.classList.add('nav-locked');
            },
            close() {
                this.open = false;
                document.documentElement.classList.remove('nav-locked');
            },
        });

        // The drawer exists only below the 900px breakpoint; crossing to
        // desktop closes it and releases the body scroll lock
        window.matchMedia('(min-width: 901px)').addEventListener('change', (e) => {
            if (e.matches) { Alpine.store('nav').close(); }
        });
    });
</script>

<style>
    .iium-sidebar {
        background: var(--color-iium-surface);
        border-right: 1px solid var(--color-iium-border-subtle);
        padding: 24px 0;
        width: 240px;
        flex-shrink: 0;
        /* Rail stretches the wrapper's full height (default align-self),
           so the sidebar column reaches the page bottom alongside the
           content column and its footer */
    }

    .sidebar-nav-items {
        display: flex;
        flex-direction: column;
        gap: 8px;
        /* Pinned inside the stretching rail: links stay on screen while the
           page scrolls. max-height (not height) keeps the box only as tall
           as its links, so the sticky element always has travel room —
           a fixed height would wedge it against the rail's bottom edge
           and lever the first link under the header while scrolling */
        position: sticky;
        top: var(--iium-header-h, 76px);
        max-height: calc(100vh - var(--iium-header-h, 76px));
        overflow-y: auto;
    }

    .sidebar-nav-item {
        display: block;
        padding: 12px 24px;
        text-decoration: none;
        color: var(--color-iium-text-secondary);
        font-family: var(--font-condensed);
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-left: 3px solid transparent;
        transition: all 250ms cubic-bezier(.34, 1.56, .64, 1);
    }

    .sidebar-nav-item:hover {
        color: var(--color-iium-turquoise);
        border-left-color: var(--color-iium-border-active);
    }

    .sidebar-nav-item.active {
        color: var(--color-iium-turquoise);
        border-left-color: var(--color-iium-gold);
    }

    /* Off-canvas drawer (<900px): the header hamburger slides the sidebar
       in as an overlay. Desktop (>=901px) is untouched above. */
    @media (max-width: 900px) {
        html.nav-locked {
            overflow: hidden;
        }

        .iium-sidebar {
            position: fixed;
            top: var(--iium-header-h, 76px);
            bottom: 0;
            left: 0;
            width: 280px;
            z-index: 90;
            transform: translateX(-100%);
            transition: transform var(--iium-duration, 250ms) var(--iium-ease-spring, cubic-bezier(.34, 1.56, .64, 1));
            overflow-y: auto;
        }

        .iium-sidebar.drawer-open {
            transform: translateX(0);
        }

        .sidebar-nav-items {
            /* Undo the desktop sticky panel behavior: the drawer itself
               is the fixed panel now */
            position: static;
            top: auto;
            max-height: none;
            overflow-y: visible;
        }

        .sidebar-backdrop {
            position: fixed;
            top: var(--iium-header-h, 76px);
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 80;
            opacity: 0;
            visibility: hidden;
            transition: opacity var(--iium-duration, 250ms) ease, visibility var(--iium-duration, 250ms) ease;
        }

        .sidebar-backdrop.backdrop-visible {
            opacity: 1;
            visibility: visible;
        }
    }
</style>
