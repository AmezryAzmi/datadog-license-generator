# Datadog License Monitoring Generator

Stateless Laravel application that generates Datadog Dashboard and Monitor JSON from license entitlements. It does not store customer data and does not call the Datadog API.

## License logic

### Infrastructure
- Infra Host is the base license and requires PRO or Enterprise.
- PRO: Container = Infra Host x5, Custom Metrics = Infra Host x100, Custom Events = Infra Host x500.
- Enterprise: Container = Infra Host x10, Custom Metrics = Infra Host x200, Custom Events = Infra Host x1000.
- Container add-on is added to the derived Container entitlement.
- NDM and CNM are independent inputs.

### APM
- APM Host is the base license and requires PRO or Enterprise.
- Both plans: Ingested Spans = APM Host x150 GB; Indexed Spans = APM Host x1 Million.
- Enterprise additionally: Profiler Host = APM Host; Profiler Container = APM Host x4.
- Ingested Spans add-on is entered in GB.
- Indexed Spans add-on is entered in Million.

### RUM
Choose exactly one plan:
- RUM Session: RUM Session + RUM Session Replay.
- RUM Without Limit: RUM Investigate + RUM Measure + RUM Session Replay.

### Other groups
- DBM: DBM Host.
- Synthetic: API Test, Browser Test.
- Logs Management: Logs Ingested, Logs Indexed, Cloud SIEM.

## Dashboard behavior
- `child_org_name` filters are removed from generated dashboard queries.
- Only selected/derived license widgets are included.
- Each license has a summary widget and a breakdown widget/timeseries. New licenses not present in the supplied dashboard template are generated from the standard widget prototype.
- Timeseries entitlement markers are set to the license entitlement.
- `white_on_green` and `white_on_red` conditional formats are both set to the same entitlement value.
- GB is converted to decimal bytes and Million is converted to raw counts for metrics/thresholds.

## Run

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan serve --host=0.0.0.0 --port=8000
```

Open `http://127.0.0.1:8000` or use your VM port-forwarding address.

## Deploy to Vercel

This project uses the community-maintained [`vercel-php` runtime](https://github.com/vercel-community/php) to run Laravel as a Vercel Function.

1. Import this GitHub repository into Vercel and keep the project root set to the repository root.
2. Add these Environment Variables in the Vercel project settings:
   - `APP_KEY` set to a stable key generated with `php artisan key:generate --show`.
   - `APP_URL` set to the production deployment URL.
3. Deploy. Vercel reads `vercel.json`, installs Composer dependencies, and routes application requests through `api/index.php`.

`vercel.json` sets production mode, cookie-backed sessions, in-memory cache, and synchronous queues. Cookie sessions avoid depending on persistent storage between serverless invocations. Laravel's writable runtime storage is redirected to the function's temporary directory, so anything written there is ephemeral. Do not commit `.env` or place secrets in the repository; `.env.example` contains only placeholders.

## Recent dashboard/output behavior

- Estimated Usage Summary widgets are ordered as: Infra Host, Container, CNM, NDM, Custom Metrics, Custom Events, APM Host, APM Ingested Spans, APM Indexed Spans, APM Profiler Host, APM Profiler Container, DBM Host, RUM Session, RUM Measure, RUM Investigate, RUM Session Replay, Logs Ingested, Logs Indexed, Cloud SIEM, Synthetic API Test, Synthetic Browser Test.
- Every Summary widget uses a `2 x 2` layout.
- Breakdown count widgets use `3 x 2`; Current Month vs Prior Month timeseries use `9 x 2`.
- Infra Host breakdown uses three widgets: two `3 x 2` widgets and one `6 x 2` Current Month vs Prior Month timeseries.
- Monitor JSON is displayed separately for every selected/derived license, with individual downloads and one ZIP download containing one JSON file per monitor.
- The validation error banner is intentionally hidden from the main generator page.
