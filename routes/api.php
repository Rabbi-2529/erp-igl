<?php

use App\Http\Controllers\Api\AttendanceWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Attendance webhook
|--------------------------------------------------------------------------
|
| Inbound push from the external attendance/device panel. Employees are
| addressed by admin_user.au_user_id. Authenticated by the shared secret in
| the attendance.webhook middleware, and given its own rate limit instead of
| the general 60/min API limit.
|
| Controllers are referenced with the class-array syntax on purpose: this file
| is also loaded inside a namespaced route group, which would otherwise
| prefix a string action twice.
|
*/
Route::prefix('webhook/attendance')
    ->middleware(['attendance.webhook', 'throttle:attendance-webhook'])
    ->withoutMiddleware('throttle:api')
    ->group(function () {
        Route::post('/', [AttendanceWebhookController::class, 'store'])
            ->name('webhook.attendance.store');

        Route::get('ping', [AttendanceWebhookController::class, 'ping'])
            ->name('webhook.attendance.ping');
    });
