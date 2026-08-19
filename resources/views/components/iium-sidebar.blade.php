@props(['currentRoute' => request()->route()?->getName()])

@php
    $isDashboard = request()->routeIs('dashboard');
    $isProjects = request()->routeIs('projects');
    $isUsers = request()->routeIs('users');
@endphp

<nav class="iium-sidebar">
    <div class="sidebar-nav-items">
        <!-- Dashboard - available to all authenticated users -->
        <a href="{{ route('dashboard') }}"
           class="sidebar-nav-item {{ $isDashboard ? 'active' : '' }}">
            Dashboard
        </a>

        <!-- Projects - admin only -->
        @can('admin')
        <a href="{{ route('projects') }}"
           class="sidebar-nav-item {{ $isProjects ? 'active' : '' }}">
            Projects
        </a>
        @endcan

        <!-- Team - admin only -->
        @can('admin')
        <a href="{{ route('users') }}"
           class="sidebar-nav-item {{ $isUsers ? 'active' : '' }}">
            Team
        </a>
        @endcan
    </div>
</nav>

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

    /* Horizontal nav strip for mobile/tablet (<900px) */
    @media (max-width: 900px) {
        /* Stack the shell: strip on top, content below — a flex ROW here
           makes the 100%-wide strip and the content fight and overflow */
        .iium-app-wrapper {
            flex-direction: column;
        }

        .iium-sidebar {
            width: 100%;
            border-right: none;
            border-bottom: 1px solid var(--color-iium-border-subtle);
            padding: 16px 0;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .sidebar-nav-items {
            flex-direction: row;
            white-space: nowrap;
            /* Undo the desktop sticky panel behavior */
            position: static;
            top: auto;
            height: auto;
            max-height: none;
            overflow-y: visible;
        }

        .sidebar-nav-item {
            border-left: none;
            border-bottom: 3px solid transparent;
            padding: 12px 16px;
        }

        .sidebar-nav-item:hover {
            border-bottom-color: var(--color-iium-border-active);
            border-left-color: transparent;
        }

        .sidebar-nav-item.active {
            border-bottom-color: var(--color-iium-gold);
            border-left-color: transparent;
        }
    }
</style>
