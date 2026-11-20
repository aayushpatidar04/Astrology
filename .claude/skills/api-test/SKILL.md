---
name: api-test
description: Writes and runs automated integration/feature tests covering happy paths, auth boundaries, validation errors, and idempotency for REST endpoints.
---

# Automated API & Edge-Case Test Generator

When invoked (for specific endpoints, recent git changes, or a controller), detect the project's test framework (e.g., PHPUnit/Pest, Jest/Supertest, Vitest, Pytest) and write comprehensive feature/integration tests.

## Required Test Coverage Matrix

For every target endpoint, generate test cases covering:

1. **Happy Path (`200` / `201`):**
   - Valid authenticated request returns the expected JSON structure, HTTP status code, and persists/updates the expected database state.
   - Pagination, filtering, and sorting parameters work as documented.

2. **Authentication Boundaries (`401`):**
   - Unauthenticated requests (missing or invalid Bearer token) are rejected with `401` on all protected routes.

3. **Role & Ownership Authorization (`403` / `404`):**
   - Wrong role access (e.g., a standard User hitting an Astrologer/Admin endpoint) is blocked.
   - **IDOR Check:** Authenticated User A attempting to read, modify, or delete User B's resource ID is strictly rejected.

4. **Validation & Boundary Edge Cases (`422`):**
   - Missing required fields, wrong data types, out-of-range numbers (e.g., negative amounts/seconds), invalid enums, and strings exceeding max length return `422` with field-level error keys.

5. **State Conflicts & Idempotency (`409` / `200`):**
   - Business rule conflicts (e.g., insufficient wallet balance, offline target, duplicate active session) return the documented error status.
   - Repeating idempotent requests (e.g., duplicate creation or settlement with the same reference ID) does not double-charge or create duplicate records.

## Execution
- Write the test files following the repository's `tests/` directory conventions.
- Execute the test runner via Bash (`php artisan test`, `npm test`, or `pytest`) and fix any failing assertions or bugs uncovered in the endpoint code until all tests pass.
