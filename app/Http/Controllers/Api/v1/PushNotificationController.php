<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\OneSignalService;
use Illuminate\Http\Request;

class PushNotificationController extends Controller
{
    private $oneSignalService;

    public function __construct(OneSignalService $oneSignalService)
    {
        $this->oneSignalService = $oneSignalService;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $user = $request->user();
        if (!$user || empty($user->external_id)) {
            return response()->json([
                'message' => 'No external_id found for current user'
            ], 422);
        }

        $response = $this->oneSignalService->sendToSpecificUser(
            'Test',
            'Test',
            $user->external_id
        );

        return response()->json([
            'message' => 'Notification sent',
            'result' => $response
        ]);
    }
}
