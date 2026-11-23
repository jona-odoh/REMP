# KSMCT Back-Office Permission Matrix

This document defines Role-Based Access Control (RBAC) and departmental scoping for the **Kogi State Ministry of Culture and Tourism (KSMCT)** internal back-office system.

## Roles Overview

| Role Code | Department Scope | Primary Responsibility |
| :--- | :--- | :--- |
| `SUPER_ADMIN` | All Departments | System administration, user lifecycle, RBAC, system configuration, full audit access |
| `DIRECTOR` | All Departments | Executive oversight, KPI dashboards, final approvals, reports, read-only audit access |
| `DEPT_HEAD_CULTURE` | Culture Department | Full management & approval of Cultural Assets & Intangible Heritage; read-only on other operational modules |
| `DEPT_HEAD_TOURISM` | Tourism Department | Full management & approval of Tourism Assets, Projects & Programs; read-only on other operational modules |
| `DEPT_HEAD_HOTELS` | Hotels & Hospitality | Full management & approval of Hotel Licensing, Inspections & Fees; read-only on other operational modules |
| `LICENSING_OFFICER` | Hotels & Hospitality | Manage hotel records, review applications, schedule/conduct inspections, record fees (cannot grant final license approval) |
| `DATA_ENTRY` | Assigned Department | Create and edit draft records (assets, heritage, hotels, projects); upload supporting documents; cannot approve or delete |
| `VIEWER` | Read-Only | View operational dashboards, registries, and reports; no write, delete, or admin privileges |

---

## Module & Action Permission Matrix

**Legend**: `CRUD` = Create, Read, Update, Delete | `R` = Read-only | `C/R/U` = Create, Read, Update (no delete) | `A` = Approve/Reject | `—` = No Access

| Module / Capability | SUPER_ADMIN | DIRECTOR | DEPT_HEAD_CULTURE | DEPT_HEAD_TOURISM | DEPT_HEAD_HOTELS | LICENSING_OFFICER | DATA_ENTRY | VIEWER |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| **User Management** (`users.*`) | CRUD + Deactivate / Reset / Force Logout | R | — | — | — | — | — | — |
| **Audit Trail** (`audit.*`) | R (Search/Filter) | R (Search/Filter) | — | — | — | — | — | — |
| **System Configuration** (`config.*`: LGAs, Categories, License Types, Fees, Checklists) | CRUD | R | R | R | R | R | R | R |
| **Cultural Assets** (`assets.*`) | CRUD + A | R + A | CRUD + A | CRUD + A | R | R | C/R/U | R |
| **Intangible Heritage** (`heritage.*`) | CRUD + A | R + A | CRUD + A | R | R | R | C/R/U | R |
| **Hotel Establishments** (`hotels.*`) | CRUD | R | R | R | CRUD | C/R/U | C/R/U | R |
| **License Applications** (`licenses.*`) | CRUD + A | R + A | R | R | CRUD + A | C/R/U (Review) | C/R (Draft/Submit) | R |
| **Inspections** (`inspections.*`) | CRUD | R | R | R | CRUD | CRUD | R | R |
| **License Fees** (`fees.*`) | CRUD | R | R | R | CRUD | C/R/U | R | R |
| **Projects & Milestones** (`projects.*`) | CRUD + A | R + A | R | CRUD + A | R | R | C/R/U | R |
| **KPI Dashboard & Standard Reports** (`reports.*`) | R + Export | R + Export | R + Export | R + Export | R + Export | R | R | R |
| **Document Repository** (`documents.*`) | CRUD | R | C/R/U | C/R/U | C/R/U | C/R/U | C/R | R |
| **Tasks & Internal Notes** (`tasks.*`, `notes.*`) | CRUD | CRUD | CRUD | CRUD | CRUD | C/R/U | C/R/U | R |

---

## Enforcement Architecture

1. **Permission Storage**: `spatie/laravel-permission` stores roles and granular `module.action` permissions in MySQL (`roles`, `permissions`, `model_has_roles`, `role_has_permissions`).
2. **Authorization Logic**: Every controller action and Blade UI element delegates to Laravel **Policies** (`UserPolicy`, `AuditLogPolicy`, `SystemConfigPolicy`, etc.) and **Gates**.
3. **Departmental Scoping**: Policies inspect both the user's permission and `user->department` / role scope so department heads cannot mutate or approve records belonging to another department.
4. **Super Admin Gate**: `Gate::before()` grants `SUPER_ADMIN` full access except where explicitly restricted (e.g., `SUPER_ADMIN` cannot deactivate or force-logout their own account, and nobody — including `SUPER_ADMIN` — can modify or delete `audit_logs`).
