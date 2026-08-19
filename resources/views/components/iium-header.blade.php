@props(['sticky' => true, 'blurred' => true])

<header @if($sticky) style="position: sticky; top: 0; background: var(--color-iium-deep); backdrop-filter: blur(10px); z-index: 100; border-bottom: 1px solid var(--color-iium-border-subtle);" @else style="background: var(--color-iium-deep); border-bottom: 1px solid var(--color-iium-border-subtle);" @endif>
    <div class="iium-container">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 24px 0;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <h1 style="font-family: var(--font-heading); font-size: 20px; margin: 0;">
                    IIUM <span style="color: var(--color-iium-gold)">DIGITAL</span>
                </h1>
            </div>
            <div style="display: flex; gap: 16px; align-items: center;">
                @auth
                    <span style="font-size: 13px; color: var(--color-iium-text-secondary);">
                        {{ auth()->user()->name }}
                    </span>
                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                        @csrf
                        <button type="submit" style="background: none; border: 1px solid var(--color-iium-border-subtle); border-radius: var(--radius-iium-sm); padding: 8px 16px; cursor: pointer; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-iium-text-primary);">
                            Logout
                        </button>
                    </form>
                @endauth
                <x-iium-theme-toggle />
            </div>
        </div>
    </div>
</header>
