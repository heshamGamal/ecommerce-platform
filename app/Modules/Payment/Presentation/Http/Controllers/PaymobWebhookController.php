<?php

namespace App\Modules\Payment\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Application\UseCases\ProcessPaymobWebhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PaymobWebhookController extends Controller
{
    public function __invoke(Request $request, ProcessPaymobWebhook $process): JsonResponse
    {
        $process->execute($request->all(), (string) $request->query('hmac', $request->header('X-Paymob-Hmac', '')));

        return response()->json(['received' => true]);
    }
}
