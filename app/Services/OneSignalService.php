<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\LogActivity;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class OneSignalService
{
    /**
     * Send a push notification to a specific OneSignal subscription id.
     *
     * You can optionally pass two callables to persist data after the request:
     *  - $persistNotification: function(array $data): void
     *  - $persistLog: function(array $data): void
     * The service will pass a standardized payload to these handlers so the caller can
     * decide how to store the data (e.g., using Eloquent models Notification, LogActivity, etc.).
     *
     * @param string        $headingMsg
     * @param string        $contentMsg
     * @param string        $oneSignalId
     * @param array|null    $additionalData       optional data to send with notification (notification_id, model_type, etc.)
     * @param callable|null $persistNotification  optional function(array $data): void
     * @param callable|null $persistLog           optional function(array $data): void
     * @return mixed string|array
     */
    public function sendToSpecificUser(
        string $headingMsg,
        string $contentMsg,
        string $oneSignalId,
        ?array $additionalData = null,
        ?callable $persistNotification = null,
        ?callable $persistLog = null
    ) {
        $oneSignalAppId = env('ONESIGNAL_APP_ID');
        $oneSignalRestApiKey = env('ONESIGNAL_REST_API_KEY');
        // Validate OneSignal credentials
        if (empty($oneSignalAppId) || empty($oneSignalRestApiKey)) {
            Log::error('[OneSignalService] OneSignal credentials not configured. Please set ONESIGNAL_APP_ID and ONESIGNAL_REST_API_KEY in .env file');
            return [
                'error' => 'OneSignal credentials not configured. Please set ONESIGNAL_APP_ID and ONESIGNAL_REST_API_KEY in .env file'
            ];
        }

        try {
            // Log info before sending
            Log::info("[OneSignalService] OneSignal API request initiated: {$headingMsg}");

            $fields = [
                'app_id' => $oneSignalAppId,
                'target_channel' => 'push',
                'contents' => ['en' => $contentMsg],
                'headings' => ['en' => $headingMsg],
                'include_subscription_ids' => [$oneSignalId],
            ];

            // Add additional data if provided (for deep linking in mobile app)
            if (!empty($additionalData)) {
                $fields['data'] = $additionalData;
                Log::info("[OneSignalService] OneSignal additional data attached", ['data' => $additionalData]);
            }

            $payload = json_encode($fields);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.onesignal.com/notifications?c=push');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json; charset=utf-8',
                'Authorization: Basic ' . $oneSignalRestApiKey,
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($response === false) {
                $err = curl_error($ch);
                curl_close($ch);

                // Log curl error
                Log::error("[OneSignalService] OneSignal API curl error: {$err}");

                return ['error' => $err];
            }
            curl_close($ch);

            $responseData = json_decode($response, true);

            // Check for API errors (including authentication errors)
            if (isset($responseData['errors']) || $httpCode >= 400) {
                $errorMessage = $responseData['errors'][0] ?? 'Unknown API error';
                Log::error("[OneSignalService] OneSignal API error response (HTTP {$httpCode}): {$errorMessage}");

                return [
                    'error' => $errorMessage,
                    'errors' => $responseData['errors'] ?? [],
                    'http_code' => $httpCode,
                ];
            }

            // Log response even when successful
            Log::info("[OneSignalService] OneSignal API response (HTTP {$httpCode})", [
                'response' => $responseData,
            ]);

            // Log success
            $recipients = $responseData['recipients'] ?? 0;
            Log::info("[OneSignalService] OneSignal API request successful: {$headingMsg} (Recipients: {$recipients})");

            // NOTE: Notification creation is handled by sendAndStore() method
            // Do NOT create notification here to avoid duplicates

            // Call callback if provided (for backward compatibility)
            if (is_callable($persistNotification)) {
                try {
                    $persistNotification([
                        'one_signal_response' => $response,
                        'data' => $response,
                    ]);
                } catch (\Throwable $e) {
                    Log::error("[OneSignalService] Error in persistNotification callback: {$e->getMessage()}");
                }
            }

            // Insert ke table log activity (callback)
            if (is_callable($persistLog)) {
                try {
                    $persistLog([
                        'data' => $response,
                    ]);
                } catch (\Throwable $e) {
                    Log::error("[OneSignalService] Error in persistLog callback: {$e->getMessage()}");
                }
            }

            return $response;
        } catch (\Throwable $e) {
            // Log exception
            Log::error("[OneSignalService] OneSignal API exception: {$e->getMessage()}");

            report($e);
            return ['error' => 'Could not send notification'];
        }
    }

    /**
     * Send push notification and store notification record with activity log.
     *
     * @param string $headingMsg Notification title/heading
     * @param string $contentMsg Notification content/message
     * @param string $oneSignalId OneSignal user ID
     * @param int|null $userId Database user ID (optional)
     * @param string|null $modelType Related model type for polymorphic relation (optional)
     * @param int|null $modelId Related model ID for polymorphic relation (optional)
     * @param string|null $category Notification category (optional)
     * @param int $status Notification status (0 = unread, default)
     * @param array|null $customAdditionalData Custom additional data to store in notification (optional)
     * @return mixed string|array
     */
    public function sendAndStore(
        string $headingMsg,
        string $contentMsg,
        string $oneSignalId,
        ?int $userId = null,
        ?string $modelType = null,
        ?int $modelId = null,
        ?string $category = null,
        int $status = 0,
        ?array $customAdditionalData = null
    ) {
        // Check for recent notification (within 5 seconds) to avoid duplicates
        // This handles cases where notification is created before calling this method (e.g., SendChecklistReminder)
        $notification = null;
        $additionalData = [];
        $reusedExisting = false;

        try {
            // Look for recent notification with same user_id, category, title, model_id, and has additional_data
            $recentNotification = Notification::where('user_id', $userId)
                ->where('category', $category)
                ->where('title', $headingMsg)
                ->where('model_id', $modelId) // Add model_id to make it more specific
                ->whereNotNull('additional_data')
                ->where('created_at', '>=', now()->subSeconds(5))
                ->orderBy('created_at', 'desc')
                ->first();

            if ($recentNotification) {
                // Reuse existing notification to avoid duplicate
                $notification = $recentNotification;
                $reusedExisting = true;

                // Use the existing additional_data (which may have richer data like pending_count, checklist_ids)
                $additionalData = $notification->additional_data ?? [];

                // Ensure notification_id is set
                if (!isset($additionalData['notification_id'])) {
                    $additionalData['notification_id'] = $notification->id;
                }

                Log::info("[OneSignalService] Reusing existing notification to avoid duplicate", [
                    'notification_id' => $notification->id,
                    'user_id' => $userId,
                    'category' => $category,
                    'title' => $headingMsg,
                    'model_id' => $modelId,
                    'additional_data' => $additionalData,
                    'created_at' => $notification->created_at,
                ]);
            } else {
                // No recent notification found, create new one
                // Build additional data for mobile app deep linking with notification_id
                $additionalData = [
                    'notification_id' => null, // Will be set after creation
                    'model_type' => $modelType,
                    'model_id' => $modelId,
                    'category' => $category,
                ];

                // Merge with custom additional data if provided (takes precedence)
                if (!empty($customAdditionalData)) {
                    $additionalData = array_merge($additionalData, $customAdditionalData);
                }

                $notification = Notification::create([
                    'user_id' => $userId,
                    'title' => $headingMsg,
                    'model_type' => $modelType,
                    'model_id' => $modelId,
                    'category' => $category,
                    'pesan' => $contentMsg,
                    'status' => $status,
                    'additional_data' => $additionalData,
                ]);

                // Update notification_id in additional_data
                $additionalData['notification_id'] = $notification->id;
                $notification->additional_data = $additionalData;
                $notification->save();

                Log::info("[OneSignalService] New notification created for sendAndStore with additional_data", [
                    'notification_id' => $notification->id,
                    'user_id' => $userId,
                    'additional_data' => $additionalData,
                    'custom_additional_data_provided' => !empty($customAdditionalData),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error("[OneSignalService] Failed to create/find notification before push: {$e->getMessage()}");

            // Still build additionalData for push even if DB operation failed
            $additionalData = [
                'notification_id' => null,
                'model_type' => $modelType,
                'model_id' => $modelId,
                'category' => $category,
            ];

            // Merge with custom additional data if provided
            if (!empty($customAdditionalData)) {
                $additionalData = array_merge($additionalData, $customAdditionalData);
            }
        }

        // Log explicit confirmation about duplicate prevention
        Log::info("[OneSignalService] OneSignal push will be sent with additional_data", [
            'notification_id' => $notification ? $notification->id : null,
            'reused_existing' => $reusedExisting,
            'user_id' => $userId,
            'category' => $category,
            'additional_data' => $additionalData,
            'additional_data_keys' => array_keys($additionalData),
        ]);

        // Send push notification with additional data
        return $this->sendToSpecificUser(
            $headingMsg,
            $contentMsg,
            $oneSignalId,
            $additionalData,
            null, // persistNotification callback not needed, already created above
            $this->createLogActivityCallback($headingMsg, $userId, $modelType, $modelId)
        );
    }

    /**
     * Create callback function to persist activity log.
     *
     * @param string $headingMsg
     * @param int|null $userId
     * @param string|null $modelType
     * @param int|null $modelId
     * @return callable
     */
    private function createLogActivityCallback(string $headingMsg, ?int $userId, ?string $modelType = null, ?int $modelId = null): callable
    {
        return function (array $data) use ($headingMsg, $userId, $modelType, $modelId) {
            // Skip if no user ID provided (required field in database)
            if ($userId === null) {
                Log::warning("[OneSignalService] Skipping log activity creation - no user_id provided for: {$headingMsg}");
                return;
            }

            try {
                LogActivity::create([
                    'users_id' => $userId,
                    'model_type' => $modelType ?? 'App\\Models\\Notification',
                    'model_id' => $modelId ?? 0,
                    'description' => "OneSignal push notification '{$headingMsg}' dikirim via API",
                ]);
            } catch (\Throwable $e) {
                Log::error("[OneSignalService] Failed to create log activity in sendAndStore: {$e->getMessage()}");
            }
        };
    }

}
