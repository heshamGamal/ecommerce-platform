<?php

namespace App\Modules\Shipping\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shipping\Application\UseCases\ProcessBostaWebhook;
use App\Modules\Shipping\Infrastructure\Configuration\ShippingProviderSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class BostaWebhookController extends Controller
{
    public function __invoke(Request $request, ProcessBostaWebhook $process, ShippingProviderSettings $settings): JsonResponse
    {
        $header = (string) $settings->value('bosta', 'webhook_auth_header', config('services.bosta.webhook_auth_header', 'Authorization'));
        $expected = (string) $settings->value('bosta', 'webhook_auth_value', config('services.bosta.webhook_auth_value'));
        if ($expected === '' || ! hash_equals($expected, (string) $request->header($header, ''))) {
            return response()->json(['message' => 'Invalid Bosta webhook credentials.'], 401);
        }

        $process->execute($request->all());
        return response()->json(['received' => true]);
    }
}
