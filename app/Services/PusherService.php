<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Pusher\Pusher;
use Pusher\PusherException;

class PusherService
{
    private ?Pusher $pusher = null;

    public function __construct()
    {
        try {
            $key    = config('services.pusher.key');
            $secret = config('services.pusher.secret');
            $appId  = config('services.pusher.app_id');

            // If credentials are not configured, skip Pusher init gracefully
            if (!$key || !$secret || !$appId) {
                Log::warning('Pusher credentials not configured. Pusher features will be disabled.');
                return;
            }

            $cluster = config('services.pusher.cluster');

            // Dynamically construct host based on cluster
            $host = config('services.pusher.host');
            if (!$host && $cluster) {
                $host = "api-{$cluster}.pusher.com";
            }

            $this->pusher = new Pusher(
                $key,
                $secret,
                $appId,
                [
                    'cluster'   => $cluster,
                    'scheme'    => config('services.pusher.scheme', 'https'),
                    'host'      => $host,
                    'port'      => config('services.pusher.port', 443),
                    'encrypted' => config('services.pusher.encrypted', true),
                ]
            );
        } catch (PusherException $e) {
            Log::error('Failed to initialize Pusher', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * @param mixed $employee
     * @param string $type
     * @param string $status
     * @param string $dateTime
     * @param string $message
     * @return void
     */
    public function pusherAttendanceResponse($employee, $type, $status, $dateTime, $message): void
    {
        if (!$this->pusher) return;
        try {
            $channel = 'attendance-channel';
            $event = 'attendance-barcode-event-' . $employee->branch_id;

            $data = [
                'message' => $message,
                'type' => $type,
                'data' => [
                    'employee' => $employee,
                    'type' => $type,
                    'status' => $status,
                    'dateTime' => $dateTime,
                ]
            ];

            $this->pusher->trigger($channel, $event, $data);

            Log::info('Pusher attendance event broadcasted', [
                'channel' => $channel,
                'event' => $event,
                'data' => $data,
            ]);
        } catch (PusherException $e) {
            Log::error('Failed to broadcast Pusher attendance event', [
                'error' => $e->getMessage(),
                'employee' => $employee,
                'type' => $type,
            ]);
            // Silent fail - should not block attendance API
        }
    }


    /**
     * Broadcast attendance event to all clients
     */
    public function broadcastAttendance($employeeName, $attendanceType, $status, $time): void
    {
        if (!$this->pusher) return;
        try {
            $channel = 'attendance-channel';
            $event = 'attendance-barcode-event';

            $data = [
                'employee_name' => $employeeName,
                'attendance_type' => $attendanceType, // 'masuk' or 'keluar'
                'status' => $status,
                'time' => $time,
                'timestamp' => now('Asia/Makassar')->toIso8601String(),
            ];

            $this->pusher->trigger($channel, $event, $data);

            Log::info('Pusher attendance event broadcasted', [
                'channel' => $channel,
                'event' => $event,
                'data' => $data,
            ]);
        } catch (PusherException $e) {
            Log::error('Failed to broadcast Pusher attendance event', [
                'error' => $e->getMessage(),
                'employee_name' => $employeeName,
                'attendance_type' => $attendanceType,
            ]);
            // Silent fail - should not block attendance API
        }
    }

    /**
     * Broadcast attendance error event
     */
    public function broadcastAttendanceError($message, $details = []): void
    {
        if (!$this->pusher) return;
        try {
            $channel = 'attendance-channel';
            $event = 'attendance-error-event';

            $data = [
                'message' => $message,
                'details' => $details,
                'timestamp' => now('Asia/Makassar')->toIso8601String(),
            ];

            $this->pusher->trigger($channel, $event, $data);

            Log::info('Pusher attendance error event broadcasted', [
                'channel' => $channel,
                'event' => $event,
                'data' => $data,
            ]);
        } catch (PusherException $e) {
            Log::error('Failed to broadcast Pusher error event', [
                'error' => $e->getMessage(),
                'message' => $message,
            ]);
        }
    }
}
