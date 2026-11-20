---
name: api-doc
description: Generates or updates concise, domain-grouped API and workflow documentation in Markdown matching the project's exact house style.
---

# API & Workflow Documentation Generator

When invoked, scan the codebase's routes, controllers, form requests/validators, middleware, and recent git history (`git log` / `git diff` if updating an existing doc) to generate or update `API_DOCUMENTATION.md` (or the target file specified by the user).

## Tone & Formatting Rules
- **Style:** Direct, scannable, practical, and developer-focused. Avoid verbose prose or bloated per-endpoint subsections.
- **Grouping:** Group endpoints into **Markdown tables** by domain/role (e.g., Authentication, Public Content, Core User Workflows, Admin/Provider Endpoints).
- **Code Blocks:** Use `http` blocks for request examples (showing Method, Path, `Content-Type`, and compact JSON body) and `json` blocks for payload schemas.
- **Inline Payloads:** For simple endpoints, include sample JSON payloads directly inside the table's `Purpose` column (e.g., ``JSON: `{"phone":"9876543210"}` ``).

## Required Document Structure

Follow this exact section order and formatting:

### 1. Title & Overview
- `# [Project Name] API`
- One-sentence summary stating the framework (e.g., Laravel/Express/FastAPI JSON API) and linking to the exported Postman collection if present in the repo (e.g., `[postman/<Name>.postman_collection.json](./postman/...)`).

### 2. `## Base URL and authentication`
- State the base URL prefix variable (e.g., `{{baseUrl}}/api`) and local default (`http://localhost:8000`).
- Show the required auth headers in an `http` code block (`Authorization: Bearer <token>`, `Accept: application/json`).
- Explain how tokens are obtained, rate limits, and token/OTP expiration rules.
- List role-based access scopes as bullet points (`- **Role Name:** accessible modules...`).
- Summarize global HTTP status code conventions in one paragraph (validation `422`, auth `401`/`403`, not found `404`, conflict `409`, creation `201`).

### 3. `## Changes in this API update` *(Include when updating docs or when recent git commits exist)*
- Bullet list of concrete additions, behavior changes, status code fixes, and deprecated/legacy aliases derived from recent commits or user prompt.

### 4. `## Authentication and account`
- Markdown table with columns: `| Method | Path | Access | Purpose |`
- Immediately follow the table with bulleted registration/input field requirements (listing **required** vs. **optional** fields, data types, and enum values).

### 5. Domain Endpoint Sections (`## [Domain / Role Name]`)
- Create separate `##` sections for each functional area or user role (e.g., `## Public app content (authenticated)`, `## User directory and chat`, `## User wallet and recharge`, `## Astrologer endpoints`).
- Use a 3-column Markdown table for each section: `| Method | Path | Purpose |`
- Note pagination defaults (e.g., `(10 per page)`), query params (`?page=N`), and legacy aliases (`**Legacy alias**`).
- **Multi-step Flows & Edge Cases:** Below the table, write short step-by-step walkthroughs with `http` request snippets for any stateful or multi-step workflows (e.g., create resource -> start session -> settle/end session). Highlight critical ID distinctions (e.g., profile `id` vs `user_id`), prerequisites (online status, minimum balance), idempotency rules, and error edge cases.
