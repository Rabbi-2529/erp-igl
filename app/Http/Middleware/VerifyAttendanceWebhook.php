<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Authenticates the incoming attendance webhook from the external panel.
 *
 * Three independent checks, all optional-by-config except the shared secret:
 *   1. shared secret  - X-Webhook-Secret header, or "Authorization: Bearer <secret>"
 *   2. HMAC signature - X-Webhook-Signature: sha256=<hmac_sha256(raw body, signing_secret)>
 *   3. IP allow list  - ATTENDANCE_WEBHOOK_IPS
 */
class VerifyAttendanceWebhook
{
    public function handle(Request $request, Closure $next)
    {
        $secret = (string) config('attendance_webhook.secret');

        if ($secret === '') {
            return $this->deny($request, 'Webhook secret is not configured on the server.', 503);
        }

        if (! hash_equals($secret, $this->presentedSecret($request))) {
            return $this->deny($request, 'Invalid or missing webhook secret.', 401);
        }

        $signingSecret = (string) config('attendance_webhook.signing_secret');

        if ($signingSecret !== '' && ! $this->signatureMatches($request, $signingSecret)) {
            return $this->deny($request, 'Invalid webhook signature.', 401);
        }

        $allowedIps = (array) config('attendance_webhook.allowed_ips');

        if ($allowedIps !== [] && ! in_array($request->ip(), $allowedIps, true)) {
            return $this->deny($request, 'Source IP is not allowed.', 403);
        }

        return $next($request);
    }

    /**
     * Pull the secret out of either supported header.
     */
    private function presentedSecret(Request $request): string
    {
        $header = (string) $request->header('X-Webhook-Secret', '');

        if ($header !== '') {
            return $header;
        }

        return (string) $request->bearerToken();
    }

    private function signatureMatches(Request $request, string $signingSecret): bool
    {
        $presented = (string) $request->header('X-Webhook-Signature', '');

        if ($presented === '') {
            return false;
        }

        // Accept both "sha256=<hex>" and a bare hex digest.
        if (str_starts_with($presented, 'sha256=')) {
            $presented = substr($presented, 7);
        }

        $expected = hash_hmac('sha256', $request->getContent(), $signingSecret);

        return hash_equals($expected, $presented);
    }

    private function deny(Request $request, string $message, int $status)
    {
        Log::channel('attendance_webhook')->warning('Rejected attendance webhook request', [
            'reason' => $message,
            'status' => $status,
            'ip'     => $request->ip(),
            'agent'  => $request->userAgent(),
        ]);

        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }
}
