---
name: db-safe-migrate
description: Audits database migrations, schemas, and ORM queries for zero-downtime safety, missing indexes, data loss risks, and N+1 bottlenecks.
---

# Database Migration & Query Safety Auditor

When invoked, inspect pending/recent database migrations (`database/migrations/`, `prisma/migrations/`, `alembic/`, etc.) and related model/controller queries before they are deployed to production.

## Audit Checklist

### 1. Migration & Schema Safety
- **Destructive Operations:** Flag any `dropColumn`, `dropTable`, `renameColumn`, or column type narrowing that could cause data loss or break currently running app instances during a rolling deploy. Recommend a safe expand-and-contract (multi-step) migration if needed.
- **Nullability & Defaults:** Flag new `NOT NULL` columns added to existing tables without a default value (which can fail on populated tables or lock large tables).
- **Foreign Keys & Cascades:** Verify foreign key constraints match parent column types (e.g., `unsignedBigInteger` / UUID) and check whether `onDelete('cascade')` vs `restrict`/`set null` is safe for financial or audit records.
- **Indexing:** Ensure indexes exist on:
  - All foreign key columns
  - Columns used in `WHERE`, `ORDER BY`, and `GROUP BY` clauses
  - Unique identifiers (e.g., `session_reference`, `phone`, `email`, transaction IDs)

### 2. Query Performance & N+1 Detection
- Scan controllers and services touching the modified tables for **N+1 query loops** (accessing relationships inside loops without eager loading like `with()` / `include` / `select_related`).
- Flag unbounded `get()` / `findAll()` queries that should be paginated (`paginate()` / `limit`).
- Check financial or balance-mutating operations (e.g., wallet deductions, recharges, payouts) to ensure row-level locking (`lockForUpdate()`) is used inside transactions to prevent race conditions.

## Output Format
Provide a concise report grouped by:
- **Critical Blockers** (must fix before running in production)
- **Performance Warnings** (missing indexes, N+1 queries, missing row locks)
- **Passed Checks**
Offer to automatically apply the recommended fixes to the migration or query files.
