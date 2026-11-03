# Relay Laravel API (v1)

Laravel REST API starter aligned to the Relay React frontend contract. This is a backend implementation package; install dependencies and run the automated tests in a PHP/Laravel environment before deploying.

## Stack
- PHP 8.3+, Laravel 12, MySQL 8 or PostgreSQL
- Laravel Sanctum SPA cookie authentication
- PHPUnit feature tests
- Versioned endpoints under `/api/v1`

## Install
```bash
composer install
cp .env.example .env
php artisan key:generate
# Configure DB_*, SESSION_DOMAIN, SANCTUM_STATEFUL_DOMAINS, and FRONTEND_URL in .env
php artisan migrate
php artisan serve
```

For the existing Vite frontend:
```env
VITE_USE_MOCK_API=false
VITE_API_BASE_URL=http://localhost:8000
```

Configure Sanctum SPA cookies and CORS for the frontend host. In `config/cors.php`, enable credentials and allow the frontend origin. Set `SANCTUM_STATEFUL_DOMAINS` to include `localhost:5173` for local development. Never use wildcard origins with credentials in production.

## API conventions
- Single resource: `{ "data": { ... } }`
- Collection: `{ "data": [ ... ] }`
- Paginated collection: `{ "data": [...], "meta": { "current_page", "last_page", "per_page", "total" } }`
- Validation: Laravel's `{ "message": "...", "errors": { "field": ["..."] } }` with HTTP 422.
- Money is stored as a decimal amount plus ISO currency code; no implicit FX conversion.
- Country codes: `GH`, `NG`, `KE`; currencies: `GHS`, `NGN`, `KES`.

## Implemented routes
- `GET /api/v1/health`
- `POST /api/v1/register`, `POST /api/v1/login`, `POST /api/v1/logout`, `GET /api/v1/me`
- `GET/PATCH /api/v1/profile`
- `GET /api/v1/creators`, `GET /api/v1/creators/{slug}` (brand/admin only)
- `GET/POST /api/v1/brand/campaigns`, `GET/PATCH /api/v1/brand/campaigns/{campaign}`
- `GET/POST /api/v1/brand/campaigns/{campaign}/invitations`
- `GET /api/v1/creator/invitations`, `POST /api/v1/creator/invitations/{invitation}/respond`
- `GET /api/v1/brand/negotiations`, `POST /api/v1/brand/negotiations/{invitation}/offers`
- `POST /api/v1/creator/invitations/{invitation}/offers`
- `POST /api/v1/brand/negotiations/{invitation}/respond`
- `GET /api/v1/admin/export/leads`, `GET /api/v1/admin/export/creators`

## Security notes
- Authorization is enforced server-side, not by frontend route guards.
- Public directory payloads are explicitly allow-listed; email, phone, and private profile fields are not serialized.
- Negotiation threads are visible only to the brand that created the invitation, the invited creator, and admins.
- Offer turn order and maximum rounds are checked server-side.
- Creator commission is not deducted or calculated.
- Do not commit `.env` or real credentials.
