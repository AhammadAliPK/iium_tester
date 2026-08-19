@props(['padding' => '32px'])

<div class="iium-card" style="background: var(--color-iium-surface-raised); border: 1px solid var(--color-iium-border-subtle); border-radius: var(--radius-iium-lg); padding: {{ $padding }}; transition: all var(--iium-duration) var(--iium-ease-spring); box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);">
    {{ $slot }}
</div>
