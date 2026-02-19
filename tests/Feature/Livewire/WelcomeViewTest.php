<?php
declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\WelcomeView;
use Livewire\Livewire;
use Tests\TestCase;

class WelcomeViewTest extends TestCase
{
    /** @test */
    public function it_renders_successfully()
    {
        Livewire::test(WelcomeView::class)
            ->assertStatus(200);
    }

    /** @test */
    public function it_contains_correct_tech_stack_data()
    {
        Livewire::test(WelcomeView::class)
            ->assertViewHas('stack', function ($stack) {
                return count($stack) > 0 && isset($stack[0][1]) && $stack[0][1] === 'MariaDB 10.11';
            });
    }

    /** @test */
    public function it_contains_api_entities_data()
    {
        Livewire::test(WelcomeView::class)
            ->assertViewHas('apiEntities', function ($entities) {
                return isset($entities['Autenticación y Perfil']) &&
                    count($entities['Autenticación y Perfil']) === 3;
            });
    }
}
