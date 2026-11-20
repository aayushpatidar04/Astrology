---
name: new-endpoint
description: Scaffolds a complete, production-ready REST API endpoint (migration, model, validation, controller/service, route, tests) and syncs API docs and Postman.
---

# Full-Stack REST Endpoint Scaffolder

When invoked with a feature or endpoint requirement, inspect the existing codebase first to match its exact architecture, folder structure, naming conventions, and authentication middleware.

## Execution Steps

1. **Analyze Existing Patterns:**
   - Check `routes/`, controllers, services/repositories, models, and request validators to match the project's exact coding style and response envelope format.
   - Check how role-based access control (RBAC) and ownership scoping are enforced.

2. **Database & Model Layer (If needed):**
   - Create a clean database migration with appropriate column types, foreign keys, `nullable` constraints, and indexes on queried/foreign columns.
   - Update or create the ORM model with proper `$fillable`/`guarded` properties, relationships, and type casts.

3. **Validation & Security:**
   - Create dedicated input validation rules (e.g., Laravel FormRequest, Zod/Joi schema, or Pydantic model).
   - Ensure strict type validation, string length limits, and enum checks so invalid inputs cleanly return HTTP `422`.
   - Enforce authentication (`401`) and resource ownership/role checks (`403` or `404` as appropriate so unauthorized users cannot access another user's ID).

4. **Controller / Service & Route Registration:**
   - Implement clean, thin controller methods delegating complex business logic to service classes where appropriate.
   - Wrap multi-step database writes in database transactions (`DB::transaction` / atomic transactions).
   - Return standardized HTTP status codes (`200` OK, `201` Created, `404` Not Found, `409` Conflict, `422` Validation Error) without leaking raw exception traces on `500` errors.
   - Register the route under the appropriate middleware group and prefix.

5. **Sync Documentation & Postman:**
   - Incrementally update `API_DOCUMENTATION.md` (adding the new endpoint to the right domain table and updating `## Changes in this API update`).
   - If Postman MCP tools are active, add the new request with realistic sample payloads to the connected Postman collection.

6. **Summary:**
   - List all files created/modified and show a ready-to-test `http` request snippet.
