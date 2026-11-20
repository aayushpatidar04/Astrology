# Project Stack & Conventions (Laravel + Vue 3 + Tailwind CSS)

## Architecture & Directory Layout
- **Backend:** Laravel JSON API (`routes/api.php`), Laravel Sanctum (`Authorization: Bearer <token>`), Eloquent ORM
- **Frontend:** Vue 3 (`<script setup>`) + Tailwind CSS inside `resources/js/`, bundled with Vite (`npm run build`)
- **API Docs:** `API_DOCUMENTATION.md`
- **Postman Collection:** `[Your Postman Collection Name]`

## Laravel Backend Rules
- **Validation:** Use dedicated `FormRequest` classes (`app/Http/Requests/`) for all write endpoints so validation errors consistently return HTTP `422` with `{ message, errors }`.
- **Responses & Status Codes:** Return clean JSON responses with standard status codes: `200` OK, `201` Created, `401` Unauthenticated, `403` Forbidden, `404` Not Found, `409` Conflict, `422` Validation Error. Never leak exception traces on `500` responses.
- **Database Safety:** Wrap multi-step writes and wallet/financial mutations in `DB::transaction()` with `->lockForUpdate()`. Use eager loading (`->with(...)`) to avoid N+1 queries.
- **Testing:** Run `php artisan test` after backend changes.

## Vue 3 + Tailwind Frontend Rules (`resources/js/`)
- Always use Vue 3 `<script setup>` and Tailwind CSS utility classes (avoid custom `<style>` blocks unless strictly necessary).
- Map Laravel `422` `errors` objects to form inputs, disable buttons while submitting, and provide clean loading/empty states.
- Run `npm run build` after modifying Vue or CSS files to confirm Vite builds cleanly.

## Postman Collection Sync Rules

Whenever I create, modify, or delete an API route, controller, or request/response schema in this codebase, automatically keep my Postman collection in sync using the Postman MCP tools:

- **Target Workspace:** backup intouch's Workspace
- **Target Collection:** Astrology API
- **Base URL Variable:** `{{base_url}}` (use this instead of hardcoding `localhost`)

### Sync Behavior
1. **New Endpoints:** Create a corresponding request inside the appropriate folder in the target collection. Include realistic sample JSON payloads, query parameters, path variables, and headers (e.g., `Authorization: Bearer {{token}}`) inferred from the route definition and validation schemas.
2. **Modified Endpoints:** Locate the existing request in the collection and update its HTTP method, path, headers, query parameters, or request body to match the updated code.
3. **Deleted Endpoints:** Find the corresponding request in the target collection and delete it.
4. **Confirmation:** After syncing, briefly list which Postman requests were added, updated, or removed.
5. Incrementally update `docs/API.md` using `/api-doc`.