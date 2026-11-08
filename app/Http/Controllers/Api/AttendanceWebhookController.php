<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AttendanceWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Receives attendance punches pushed by the external device panel.
 *
 * Authentication is handled by the attendance.webhook middleware; this
 * controller only shapes the payload, delegates to the sync service and
 * reports a per-event result so the sender can retry selectively.
 */
class AttendanceWebhookController extends Controller
{
    public function __construct(private AttendanceWebhookService $sync)
    {
    }

    /**
     * POST /api/webhook/attendance
     */
    public function store(Request $request): JsonResponse
    {
        $requestId = (string) Str::uuid();
        $events    = $this->extractEvents($request);
        $maxEvents = (int) config('attendance_webhook.max_events');

        if ($events === []) {
            return $this->respond($requestId, [], 'No attendance events found in the payload.', 422);
        }

        if (count($events) > $maxEvents) {
            return $this->respond(
                $requestId,
                [],
                "Too many events in one request; the maximum is {$maxEvents}.",
                422
            );
        }

        Log::channel('attendance_webhook')->info('Attendance webhook received', [
            'request_id' => $requestId,
            'ip'         => $request->ip(),
            'events'     => count($events),
            'payload'    => $events,
        ]);

        $results = [];

        foreach ($events as $event) {
            if (! is_array($event)) {
                $results[] = [
                    'au_user_id'    => '',
                    'result'        => 'skipped',
                    'message'       => 'Event is not an object.',
                    'attendance_id' => null,
                    'check_in'      => null,
                    'check_out'     => null,
                ];

                continue;
            }

            try {
                $results[] = $this->sync->syncEvent($event);
            } catch (\Throwable $e) {
                Log::channel('attendance_webhook')->error('Attendance event failed', [
                    'request_id' => $requestId,
                    'event'      => $event,
                    'error'      => $e->getMessage(),
                    'trace'      => $e->getTraceAsString(),
                ]);

                $results[] = [
                    'au_user_id'    => (string) ($event['au_user_id'] ?? $event['user_id'] ?? ''),
                    'result'        => 'failed',
                    'message'       => $e->getMessage(),
                    'attendance_id' => null,
                    'check_in'      => null,
                    'check_out'     => null,
                ];
            }
        }

        return $this->respond($requestId, $results);
    }

    /**
     * GET /api/webhook/attendance/ping
     *
     * Lets the panel verify URL + credentials without writing anything.
     */
    public function ping(Request $request): JsonResponse
    {
        return response()->json([
            'success'   => true,
            'message'   => 'Attendance webhook is reachable and the credentials are valid.',
            'server_time' => now()->toDateTimeString(),
            'timezone'  => config('app.timezone'),
        ]);
    }

    /**
     * Accepts a batch ({"events": [...]} / {"user_records": [...]} / a bare
     * array) or a single flat punch object.
     *
     * @return array<int, mixed>
     */
    private function extractEvents(Request $request): array
    {
        $payload = $request->json()->all();

        if (! is_array($payload) || $payload === []) {
            $payload = $request->all();
        }

        if (! is_array($payload) || $payload === []) {
            return [];
        }

        foreach (['events', 'user_records', 'records', 'data', 'attendance'] as $key) {
            if (isset($payload[$key]) && is_array($payload[$key])) {
                return array_values($payload[$key]);
            }
        }

        // A bare list of events.
        if (array_keys($payload) === range(0, count($payload) - 1)) {
            return array_values($payload);
        }

        // A single flat event.
        return [$payload];
    }

    /**
     * @param  array<int, array>  $results
     */
    private function respond(string $requestId, array $results, ?string $message = null, int $status = 200): JsonResponse
    {
        $counts = [
            'created'   => 0,
            'updated'   => 0,
            'unchanged' => 0,
            'skipped'   => 0,
            'failed'    => 0,
        ];

        foreach ($results as $result) {
            $key = $result['result'] ?? 'failed';
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }

        $body = [
            'success'    => $status === 200 && $counts['failed'] === 0,
            'request_id' => $requestId,
            'message'    => $message ?? 'Attendance synced.',
            'summary'    => array_merge(['received' => count($results)], $counts),
            'results'    => $results,
        ];

        if ($status === 200 && $counts['failed'] > 0) {
            // Signal a partial failure so the sender retries instead of
            // treating the push as fully delivered.
            $status = 207;
            $body['message'] = 'Some attendance events could not be applied.';
        }

        Log::channel('attendance_webhook')->info('Attendance webhook responded', [
            'request_id' => $requestId,
            'status'     => $status,
            'summary'    => $body['summary'],
        ]);

        return response()->json($body, $status);
    }
}
