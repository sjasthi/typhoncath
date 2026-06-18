# Diagram 13 — Nonfunctional Quality / System Constraints Diagram

## Diagram type
Quality attributes diagram / nonfunctional requirements map.

## Purpose
Show how the nonfunctional requirements affect the architecture and implementation decisions.

## Source requirements translated
- Performance: page load time should be under 3 seconds and database queries should use indexing optimization.
- Security: password hashing using bcrypt, role-based access control, and session management.
- Usability: responsive Bootstrap UI and simple navigation menus.
- Maintainability: modular PHP folder structure and clean separation of concerns.
- Reliability: database backup support and error logging.

## Main quality attributes
- Performance
- Security
- Usability
- Maintainability
- Reliability

## Implementation decisions to show
- Performance -> indexed database queries, simple server-rendered pages, optimized dashboard queries
- Security -> bcrypt password hashing, RBAC, PHP sessions, protected admin/user routes
- Usability -> Bootstrap responsive layout, consistent navigation, clear forms and tables
- Maintainability -> `/modules/customer`, `/modules/rfq`, `/modules/campaign`, `/modules/inventory`, `/modules/integration`
- Reliability -> MySQL backups, error logs, safe error messages

## Mermaid starter
```mermaid
flowchart TB
    CRM[Typhon Cath CRM]
    Perf[Performance
< 3 second page loads]
    Sec[Security
bcrypt, RBAC, sessions]
    Use[Usability
Bootstrap, simple nav]
    Maint[Maintainability
modular PHP folders]
    Rel[Reliability
backups, error logging]

    CRM --> Perf
    CRM --> Sec
    CRM --> Use
    CRM --> Maint
    CRM --> Rel

    Perf --> Indexes[MySQL indexes + optimized queries]
    Sec --> Auth[Login, session checks, permission checks]
    Use --> UI[Responsive forms, tables, menus]
    Maint --> Modules[/modules/customer, rfq, campaign, inventory, integration]
    Rel --> Ops[Backup plan + log review]
```

## Draw.io notes
- Use CRM as the center node with five surrounding quality attributes.
- This is useful for a final report because it proves the design responds to NFRs, not only functional features.
