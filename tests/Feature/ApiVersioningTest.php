<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

final class ApiVersioningTest extends TestCase
{
    public function test_v1_exposes_the_same_route_surface_with_versioned_names(): void
    {
        $legacy = [];
        $versioned = [];

        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();
            $name = $route->getName();
            if ($name === null || ! str_starts_with($uri, 'api/')) {
                continue;
            }
            $signature = implode('|', $route->methods()).'|'.preg_replace('#^api/v1/#', 'api/', $uri);
            if (str_starts_with($name, 'v1.')) {
                $versioned[$signature] = $name;
            } elseif (! str_starts_with($name, 'v1.')) {
                $legacy[$signature] = $name;
            }
        }

        $this->assertCount(count($legacy), $versioned);
        $this->assertSame(array_keys($legacy), array_keys($versioned));
        $this->assertSame('v1.customer.checkout', $versioned['POST|api/customer/checkout']);
        $this->assertSame('v1.webhooks.paymob', $versioned['POST|api/webhooks/paymob']);
    }
}
