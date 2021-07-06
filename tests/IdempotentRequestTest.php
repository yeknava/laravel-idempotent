<?php

namespace Yeknava\LaravelIdempotent;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase;

class IdempotentRequestTest extends TestCase
{
    public function testValidIdempotentRoutes()
    {
        Route::post('/', function () {
            return request()->name;
        })->middleware(Idempotent::class);

        $response = $this->post(
            '/',
            [
                'name' => 'laravel',
            ],
            [
                'Accept' => 'application/json',
                'Idempotency-Key' => Str::uuid(),
            ]
        );

        $this->assertSame('laravel', $response->content());
        $response->assertStatus(200);
    }

    public function testDuplicateIdempotentRoutes()
    {
        Route::post('/', function () {
            return request()->name;
        })->middleware(Idempotent::class);

        $uuid = Str::uuid();

        $response = $this->post(
            '/',
            [
                'name' => 'laravel',
            ],
            [
                'Accept' => 'application/json',
                'Idempotency-Key' => $uuid,
            ]
        );

        $this->assertSame('laravel', $response->content());

        $response = $this->post(
            '/',
            [
                'name' => 'php',
            ],
            [
                'Accept' => 'application/json',
                'Idempotency-Key' => $uuid,
            ]
        );

        $response->assertStatus(422);

        $response = $this->post(
            '/',
            [
                'name' => 'laravel',
            ],
            [
                'Accept' => 'application/json',
                'Idempotency-Key' => $uuid,
            ]
        );

        $this->assertSame('laravel', $response->content());
        $response->assertStatus(200);
    }

    public function testEmptyIdempotencyKey()
    {
        Route::post('/', function () {
            return request()->name;
        })->middleware(Idempotent::class);

        $response = $this->post(
            '/',
            [
                'name' => 'laravel',
            ],
            [
                'Accept' => 'application/json',
            ]
        );

        $response->assertStatus(400);
    }

    public function testCharacterLimitIdempotencyKey()
    {
        Route::post('/', function () {
            return request()->name;
        })->middleware(Idempotent::class);

        $response = $this->post(
            '/',
            [
                'name' => 'laravel',
            ],
            [
                'Accept' => 'application/json',
                'Idempotency-Key' => Str::random(39),
            ]
        );

        $response->assertStatus(422);
    }
}
