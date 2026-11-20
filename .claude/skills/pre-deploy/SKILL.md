---
name: pre-deploy
description: Runs a comprehensive production-readiness security, configuration, error-handling, and test audit before deploying changes.
---

# Production Readiness & Pre-Deploy Audit

When invoked before a release or deployment, audit the codebase, environment templates, routes, and git diff to ensure the application is safe for production.

## Audit Categories

### 1. Secrets & Environment Hygiene
- Compare `.env.example` against `.env` (read key names only—never print live secret values into the transcript) to ensure all newly added config variables are documented in `.env.example`.
- Scan `git diff` and tracked files for hardcoded API keys, tokens, passwords, private keys, or internal staging URLs.
- Verify production safety flags are documented properly (`APP_DEBUG=false`, `NODE_ENV=production`).

### 2. API Security & Error Sanitization
- **Auth Middleware:** List all newly added or modified routes and verify every non-public endpoint is wrapped in authentication and role-authorization middleware.
- **Rate Limiting:** Check that public/sensitive endpoints (OTP, login, registration, webhooks, payment callbacks) have throttle/rate-limit middleware applied.
- **Mass Assignment & Input Validation:** Confirm no endpoints pass raw unvalidated request payloads (`$request->all()` / `req.body`) directly into database create/update calls.
- **Error Responses:** Ensure `500` server errors return generic JSON messages without leaking SQL queries, file paths, or stack traces.
- **CORS & Upload Limits:** Verify CORS origins are not left wide open (`*`) if credentials/cookies are used, and file uploads enforce MIME type and size limits.

### 3. Documentation & Test Verification
- Check if `API_DOCUMENTATION.md` reflects the latest route/controller changes in `git status` / `git diff`.
- Run the project's test suite and linter/type-checker to confirm zero failing tests or build errors.

## Output Format
Present a clear **GO / NO-GO** readiness summary with actionable fixes for any failed checks.
