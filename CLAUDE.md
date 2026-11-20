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
