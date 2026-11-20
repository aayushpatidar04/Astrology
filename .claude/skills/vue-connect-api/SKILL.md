---
name: vue-connect-api
description: Builds and connects Vue 3 + Tailwind CSS components in resources/js/ to Laravel Sanctum APIs with loading states, 422 field validation, and responsive Tailwind UI.
---

# Vue 3 + Tailwind CSS + Laravel API Integration Skill

When invoked with a feature or Laravel API endpoint, inspect `resources/js/` and the corresponding Laravel backend files (`routes/api.php`, Controllers, FormRequests, and `API_DOCUMENTATION.md`) first to match the project's exact structure and design tokens.

## Execution Steps

1. **Inspect Existing Monolith Structure:**
   - Check `resources/js/` folders (`components/`, `pages/` or `views/`, `stores/`, `composables/`, `services/` or `api.js`).
   - Check the configured Axios/Fetch client in `resources/js/` (base URL `/api`, `Accept: application/json`, and Sanctum `Authorization: Bearer` header interceptor).
   - Check `tailwind.config.js` / `resources/css/app.css` to reuse existing project colors, fonts, and component classes.

2. **Wire the API Service / Composable / Store:**
   - Create or update clean API functions targeting `/api/...` endpoints.
   - If the endpoint accepts file uploads (e.g., `profile_image`), automatically send `FormData` with `Content-Type: multipart/form-data`.
   - Handle Laravel pagination responses (`data`, `current_page`, `last_page`, `per_page`, `total`) cleanly.

3. **Build / Update the Vue 3 + Tailwind CSS Component:**
   - Use Vue 3 Composition API (`<script setup>`) and mobile-first **Tailwind CSS** utility classes.
   - **Loading & Empty States:** Include Tailwind skeleton/spinner loaders (`animate-pulse` / `animate-spin`) while fetching, and clean empty-state cards when lists return `[]`.
   - **Form Submission & `422` Field Errors:**
     - Disable submit buttons (`disabled:opacity-50 disabled:cursor-not-allowed`) while requests are in-flight.
     - Catch HTTP `422` responses, map `error.response.data.errors` to reactive `fieldErrors`, highlight invalid inputs (`border-red-500 focus:ring-red-500`), and render error messages (`text-sm text-red-600 mt-1`) directly below each field.
   - **Auth & Business Errors (`401`, `403`, `404`, `409`):**
     - `401`: Clear stale Sanctum token and redirect to login.
     - `403` / `404` / `409` (e.g., offline status, low wallet balance): Render a clear Tailwind alert banner (`bg-red-50 border-red-200 text-red-700`).

4. **Verify Build:**
   - Run `npm run build` via Bash to verify Vite compiles all Vue templates and Tailwind classes with zero errors.
