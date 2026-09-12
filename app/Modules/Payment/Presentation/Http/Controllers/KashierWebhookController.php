<?php

namespace App\Modules\Payment\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Application\UseCases\ProcessKashierWebhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class KashierWebhookController extends Controller
{
    public function __invoke(Request $request, ProcessKashierWebhook $process): JsonResponse
    {
        $process->execute($request->all());

        return response()->json(['received' => true]);
    }
}
