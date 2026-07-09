<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class ExampleTest extends TestCase
{
    public function test_welcome_page_is_available(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSeeText('Базовый каркас Laravel-проекта');
    }
}

