<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Tests\TestCase;

class IiumShellTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_is_disabled(): void
    {
        $response = $this->get('/register');

        $response->assertNotFound();
    }

    public function test_unsanctioned_fortify_features_are_disabled(): void
    {
        $features = config('fortify.features');

        $this->assertNotContains(Features::registration(), $features);
        $this->assertNotContains(Features::emailVerification(), $features);
        $this->assertNotContains(Features::twoFactorAuthentication(), $features);
        $this->assertNotContains('passkeys', $features);
    }

    public function test_theme_source_defines_iium_tokens_and_dark_overrides(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString('--color-iium-turquoise: #00928F', $css);
        $this->assertStringContainsString('--color-iium-gold: #D59F0F', $css);
        $this->assertStringContainsString("[data-theme='dark']", $css);
        $this->assertStringContainsString('--color-iium-deep: #030F0D', $css);
    }

    public function test_shell_contains_iium_theme_mechanisms(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('iium-theme-toggle', false)
            ->assertSee("localStorage.setItem('iium-theme'", false)
            ->assertSee("localStorage.getItem('iium-theme'", false)
            ->assertSee('data-theme', false);
    }

    public function test_theme_restores_before_first_paint(): void
    {
        $user = User::factory()->create();

        $html = $this->actingAs($user)->get(route('dashboard'))->getContent();

        $scriptPos = strpos($html, "localStorage.getItem('iium-theme'");
        $bodyPos = strpos($html, '<body');

        $this->assertNotFalse($scriptPos, 'Theme restore script missing from page');
        $this->assertNotFalse($bodyPos);
        $this->assertLessThan(
            $bodyPos,
            $scriptPos,
            'Theme restore script must run in <head> before <body> paints'
        );
    }
}
