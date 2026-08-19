@props(['maxWidth' => '1200px', 'padding' => '40px'])

<div class="iium-container" style="max-width: {{ $maxWidth }}; margin: 0 auto; padding: 0 {{ $padding }};">
    {{ $slot }}
</div>
