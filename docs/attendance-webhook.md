# Attendance Webhook

Inbound push endpoint. The external attendance/device panel calls this whenever
someone punches in or out, and the ERP updates `employee_attendances`
automatically — no cron polling needed.

Employees are addressed by **`au_user_id`** (the payroll/device id on
`admin_user`). The ERP translates it to the internal `au_id` that
`employee_attendances.user_id` actually stores, and fills `company_id` /
`branch_id` from that employee's record.

---

## Endpoint

| | |
|---|---|
| Push | `POST https://erp2.iglweb.com/api/webhook/attendance` |
| Health check | `GET https://erp2.iglweb.com/api/webhook/attendance/ping` |
| Content type | `application/json` |
| Rate limit | 300 requests/minute per IP |
| Max events per request | 500 |

> **Use `https://`, never `http://`.** This project's `.htaccess` places its
> force-HTTPS redirect *after* the Laravel front-controller rule, so a plain
> `http://` request is answered with `301 Location: https://<host>/index.php` —
> the `/api/webhook/attendance` path is stripped and, because it is a 301, the
> POST body is dropped. The request silently lands on the dashboard instead of
> the webhook. Always configure the panel with the `https://` URL.

### Authentication

Send the shared secret on every request, either way:

```
X-Webhook-Secret: <secret>
```
```
Authorization: Bearer <secret>
```

The secret lives in `.env` as `ATTENDANCE_WEBHOOK_SECRET` (already generated —
copy the value from there into the sending panel). Responses are `401` when it
is missing or wrong, and `503` if the server has no secret configured.

**Optional hardening** — both off by default:

- `ATTENDANCE_WEBHOOK_SIGNING_SECRET` — once set, the panel must also send
  `X-Webhook-Signature: sha256=<hmac_sha256(raw_request_body, signing_secret)>`.
  A bare hex digest without the `sha256=` prefix is accepted too.
- `ATTENDANCE_WEBHOOK_IPS` — comma-separated IP allow list. Anything else gets
  `403`.

---

## Request body

### Single punch

```json
{
  "au_user_id": "445909",
  "check_in": "2026-10-01 09:02:13",
  "check_out": "2026-10-01 18:20:40",
  "device_ip": "27.147.177.102"
}
```

Send `check_in` alone on punch-in, `check_out` alone on punch-out, or both.

### Batch

```json
{
  "events": [
    { "au_user_id": "445909", "check_in": "2026-10-01 09:02:13" },
    { "au_user_id": "445943", "check_out": "2026-10-01 18:05:00" }
  ]
}
```

A bare JSON array also works, as do the keys `user_records`, `records`, `data`
and `attendance` in place of `events`.

### Accepted field names

Every field has aliases, so the existing panel can post its own naming
(including the `testzk` shape already used by `php artisan api:hit-and-save`)
without changes:

| Meaning | Accepted keys |
|---|---|
| Employee | `au_user_id`, `user_id`, `employee_id`, `emp_id` |
| Work date | `work_date`, `date`, `attendance_date` |
| Check-in | `check_in`, `checkin_timestamp`, `checkin_time`, `check_in_time`, `in_time`, `punch_in` |
| Check-out | `check_out`, `checkout_timestamp`, `checkout_time`, `check_out_time`, `out_time`, `punch_out` |
| Device IP | `device_ip`, `user_ip`, `ip`, `ip_address` |

Times may be a full datetime (`2026-10-01 09:02:13`) or a bare time
(`09:02:13`), in which case `work_date` must be present. Times are interpreted
in the app timezone, `Asia/Dhaka`. Empty strings, `null` and `0000-00-00…`
count as "no punch".

---

## Response

`200` when everything applied, `207` if some events failed, `401`/`403`/`503`
on auth problems, `422` on an unusable payload.

```json
{
  "success": true,
  "request_id": "35c38280-1e4f-434f-a847-13a29d6ec867",
  "message": "Attendance synced.",
  "summary": { "received": 2, "created": 1, "updated": 1, "unchanged": 0, "skipped": 0, "failed": 0 },
  "results": [
    {
      "au_user_id": "445909",
      "result": "created",
      "message": null,
      "attendance_id": 41512,
      "check_in": "2026-10-01 09:02:13",
      "check_out": null
    }
  ]
}
```

Per-event `result` values:

| Value | Meaning |
|---|---|
| `created` | New attendance row for that work date |
| `updated` | Existing row changed |
| `unchanged` | Nothing to write — a safe duplicate push, or the date is already complete (see `message`) |
| `skipped` | Not applicable — see `message` (unknown `au_user_id`, no times, no matching check-in, check-out before check-in) |
| `failed` | Unexpected error; retry this event |

Retry on `5xx`, on a transport error, or for individual `failed` events.
`skipped` events will never succeed unchanged — fix the data instead.

---

## Write behaviour

One row per employee per work date, matched on
`company_id` + `user_id` + `DATE(check_in)`.

- **Once a date has both a check-in and a check-out stored, that row is
  final.** Anything the panel sends for it afterwards is ignored and reported
  as `unchanged` — not even `updated_at` moves. Set
  `ATTENDANCE_WEBHOOK_LOCK_COMPLETED=false` to turn this off.
- While a row is still open (check-in but no check-out yet), the **earliest**
  punch wins as `check_in` and the first valid `check_out` completes it.
  Re-sending the same punches changes nothing, so the panel can push freely
  and retry safely.
- A check-out with no row for that date falls back to the previous day's row if
  it is still open — this handles shifts that end after midnight.
- `in_click` is set to `1` on check-in, `out_click` to `2` on check-out, and
  `user_ip` / `user_ip_checkout` to the payload's `device_ip` (falling back to
  `ATTENDANCE_WEBHOOK_DEVICE_IP`) so webhook punches stay distinguishable from
  in-browser ones.
- `total_hour` is always set to the hours actually worked. `incomplete_*` and
  `over_*` are recalculated — and the unused group zeroed — on every check-out,
  so a corrected punch never leaves stale figures behind.

### Status codes written to `employee_attendances.status`

These match the browser check-in/check-out flow in `AttendanceController`, so
webhook rows render identically in the existing reports:

| Code | Meaning |
|---|---|
| 1 | Late check-in |
| 3 | On-time check-in |
| 4 | Incomplete shift |
| 5 | Overtime |
| 6 | Exact shift |

The shift is resolved per employee: their own `au_shifts` row, then their
branch's row in `payroll_shift_management`, then `admin_user.au_work_shift`,
then `ATTENDANCE_WEBHOOK_DEFAULT_SHIFT`. Required minutes come from
`admin_user.au_working_hour`, then `payroll_shift_management.shift_hour`, then
the shift window length, then `ATTENDANCE_WEBHOOK_WORKING_MINUTES` (540).
Late grace is `payroll_shift_management.late_entry` in minutes.

---

## Troubleshooting

Every request, response, rejection and failure is logged to
`storage/logs/attendance-webhook-YYYY-MM-DD.log` (kept 30 days), keyed by the
`request_id` returned to the caller.

Test connectivity and credentials without writing anything:

```bash
curl -i -H "X-Webhook-Secret: <secret>" https://erp2.iglweb.com/api/webhook/attendance/ping
```

Send a punch:

```bash
curl -i -X POST https://erp2.iglweb.com/api/webhook/attendance \
  -H "Content-Type: application/json" \
  -H "X-Webhook-Secret: <secret>" \
  -d '{"au_user_id":"445909","check_in":"2026-10-01 09:02:13"}'
```

---

## Files

| File | Role |
|---|---|
| `routes/api.php` | Route registration |
| `app/Http/Middleware/VerifyAttendanceWebhook.php` | Secret, HMAC and IP checks |
| `app/Http/Controllers/Api/AttendanceWebhookController.php` | Payload shaping, per-event results |
| `app/Services/AttendanceWebhookService.php` | `au_user_id` resolution and the upsert |
| `config/attendance_webhook.php` | All settings |

## Relationship to the existing cron

`app/Console/Commands/HitApiAndSaveToDatabase.php` (`php artisan
api:hit-and-save`) still *pulls* today's attendance from
`https://testzk.iglweb.com/api`. The webhook is the push equivalent and the two
are safe to run side by side — both converge on the same earliest-in /
latest-out row. Once the panel reliably pushes, the cron can be kept purely as
a daily backfill.
