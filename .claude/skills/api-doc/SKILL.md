---
name: api-doc
description: Generates or incrementally updates concise, domain-grouped API and workflow documentation in Markdown, highlighting recent API changes clearly for frontend/API developers.
---

# API & Workflow Documentation Generator (Incremental Mode)

When invoked, check whether the target documentation file (`docs/API.md` by default, or the path specified by the user) already exists in the workspace.

## Execution Mode: Create vs. Incremental Update

### Case A: The documentation file ALREADY EXISTS (Default Update Mode)
Do **NOT** rewrite the entire file from scratch. Perform a surgical, incremental update:
1. **Detect Changes:** Inspect `git log -n 10 --stat`, `git diff` (staged/unstaged), or compare the existing `API_DOCUMENTATION.md` against the current routes, controllers, form requests/validators, and middleware to find **only** what was added, modified, or removed since the last doc update.
2. **Preserve Unchanged Content:** Keep all existing sections, wording, examples, and formatting for untouched endpoints completely intact.
3. **Update Affected Sections Only:**
   - Add new rows to the appropriate domain tables and add flow walkthroughs if a new stateful workflow was introduced.
   - Modify existing table rows, JSON payloads, validation rules, or status codes for changed endpoints.
   - Remove deleted endpoints (or mark them deprecated if kept as a legacy alias).
4. **Update `## Changes in this API update`:** Replace or prepend the `## Changes in this API update` section right below `## Base URL and authentication` so developers can immediately see every added, updated, and removed API from this update.
5. **Summary Output:** After editing the file, output a brief summary in chat listing which sections and endpoints were updated.

### Case B: The documentation file DOES NOT EXIST (Initial Generation)
Generate the complete `API_DOCUMENTATION.md` from scratch using the structure below (omitting `## Changes in this API update` unless the user explicitly asks to include recent commits).

---

## Tone & Formatting Rules
- **Style:** Direct, scannable, practical, and developer-focused. Avoid verbose prose or bloated per-endpoint subsections.
- **Grouping:** Group endpoints into **Markdown tables** by domain/role (e.g., Authentication, Public Content, Core User Workflows, Admin/Provider Endpoints).
- **Code Blocks:** Use `http` blocks for request examples (showing Method, Path, `Content-Type`, and compact JSON body) and `json` blocks for payload schemas.
- **Inline Payloads:** For simple endpoints, include sample JSON payloads directly inside the table's `Purpose` column (e.g., ``JSON: `{"phone":"9876543210"}` ``).

---

## Required Document Structure

### 1. Title & Overview
- `# [Project Name] API`
- One-sentence summary stating the framework (e.g., Laravel/Express/FastAPI JSON API) and linking to the exported Postman collection if present in the repo (e.g., `[postman/<Name>.postman_collection.json](./postman/...)`).

### 2. `## Base URL and authentication`
- State the base URL prefix variable (e.g., `{{baseUrl}}/api`) and local default (`http://localhost:8000`).
- Show the required auth headers in an `http` code block (`Authorization: Bearer <token>`, `Accept: application/json`).
- Explain how tokens are obtained, rate limits, and token/OTP expiration rules.
- List role-based access scopes as bullet points (`- **Role Name:** accessible modules...`).
- Summarize global HTTP status code conventions in one paragraph (validation `422`, auth `401`/`403`, not found `404`, conflict `409`, creation `201`).

### 3. `## Changes in this API update`
Make this section crystal clear for API consumers by grouping updates with bold prefixes or sub-bullets:
- **Added:** List newly created endpoints (`METHOD /path`), new request/response fields, and brief usage notes.
- **Updated:** List modified endpoints (`METHOD /path`), detailing exact payload changes, new validation rules, or updated HTTP status codes.
- **Removed / Deprecated:** List deleted endpoints or routes converted into legacy aliases and specify their replacements.

### 4. `## Authentication and account`
- Markdown table with columns: `| Method | Path | Access | Purpose |`
- Immediately follow the table with bulleted registration/input field requirements (listing **required** vs. **optional** fields, data types, and enum values).

### 5. Domain Endpoint Sections (`## [Domain / Role Name]`)
- Create separate `##` sections for each functional area or user role.
- Use a 3-column Markdown table for each section: `| Method | Path | Purpose |`
- Note pagination defaults (e.g., `(10 per page)`), query params (`?page=N`), and legacy aliases (`**Legacy alias**`).
- **Multi-step Flows & Edge Cases:** Below the table, write short step-by-step walkthroughs with `http` request snippets for any stateful or multi-step workflows. Highlight critical ID distinctions, prerequisites, idempotency rules, and error edge cases.
