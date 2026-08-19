<button
    class="iium-theme-toggle"
    onclick="window.toggleIIUMTheme()"
    aria-label="Toggle theme"
    style="background: transparent; border: 1px solid var(--color-iium-border-subtle); border-radius: var(--radius-iium-sm); padding: 8px 12px; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 200ms ease; color: var(--color-iium-text-primary); font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;"
    onmouseover="this.style.background='var(--color-iium-glass)'; this.style.borderColor='var(--color-iium-border-active)'"
    onmouseout="this.style.background='transparent'; this.style.borderColor='var(--color-iium-border-subtle)'"
>
    <span class="theme-toggle-text">Dark</span>
    <svg class="icon-moon theme-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; display: block;">
        <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
    </svg>
    <svg class="icon-sun theme-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; display: none;">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
    </svg>
</button>

<script>
    function toggleIIUMTheme() {
        const html = document.documentElement;
        const currentTheme = html.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        html.setAttribute('data-theme', newTheme);
        localStorage.setItem('iium-theme', newTheme);

        // Update button text and icons
        const buttonText = document.querySelector('.theme-toggle-text');
        const moonIcon = document.querySelector('.icon-moon');
        const sunIcon = document.querySelector('.icon-sun');

        if (buttonText) {
            buttonText.textContent = newTheme === 'light' ? 'Dark' : 'Light';
        }

        if (moonIcon && sunIcon) {
            moonIcon.style.display = newTheme === 'light' ? 'block' : 'none';
            sunIcon.style.display = newTheme === 'light' ? 'none' : 'block';
        }
    }

    // Load saved theme preference on page load (before first paint)
    (function() {
        const savedTheme = localStorage.getItem('iium-theme');
        if (savedTheme) {
            document.documentElement.setAttribute('data-theme', savedTheme);

            // Update button text and icons after DOM loads
            document.addEventListener('DOMContentLoaded', function() {
                const buttonText = document.querySelector('.theme-toggle-text');
                const moonIcon = document.querySelector('.icon-moon');
                const sunIcon = document.querySelector('.icon-sun');

                if (buttonText) {
                    buttonText.textContent = savedTheme === 'light' ? 'Dark' : 'Light';
                }

                if (moonIcon && sunIcon) {
                    moonIcon.style.display = savedTheme === 'light' ? 'block' : 'none';
                    sunIcon.style.display = savedTheme === 'light' ? 'none' : 'block';
                }
            });
        }
    })();
</script>
