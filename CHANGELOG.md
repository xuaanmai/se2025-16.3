# Changelog (Final Fixes)

This file documents the final, effective changes made to resolve all reported issues.

## Core Permission Logic (Frontend)

- **Switched to Owner-Based Permissions:** The primary permission model for management tasks was changed. Instead of checking for roles or specific permissions, the UI now checks if the current user's ID matches the `owner_id` of the project. This was implemented in:
  - `resources/js/components/Projects/ProjectTickets.vue` (for the "Create Ticket" button).
  - `resources/js/components/Projects/ProjectMembers.vue` (for all member management controls).

## Member Management Tab

- **Fixed Empty Member List:** Changed component rendering logic in `ProjectDetail.vue` (using `v-show`) to ensure the member list displays reliably.
- **Enabled Management Controls:** The "Add Member" form, role change dropdowns, and "Remove" buttons are now correctly displayed to the project owner.

## Ticket Creation & Editing

- **Added "Create Ticket" Button:** A "Create Ticket" button was added to the "Tickets" tab within the Project Detail page.
- **Improved "Create Ticket" Modal (`TicketFormModal.vue`):**
  - **Context-Aware Project Field:** The "Project" dropdown is now hidden when creating a ticket from within a project.
  - **Default Status:** New tickets now correctly default to the "Todo" status. The Status dropdown is hidden on creation and only appears in edit mode.
  - **Default Owner:** The `owner_id` is now correctly set to the current user upon creation, fixing a validation error.
  - **Project Selection:** Fixed a bug where creating a ticket from the global view would fail if no projects existed. It now correctly requires a user selection.
  - **Improved Dropdown UX:**
    - The "Assignee" dropdown now groups users by their project role.
    - The "Type" and "Priority" dropdowns are now sorted alphabetically.
- **Fixed Form Crashing/Freezing:**
  - Resolved multiple crashes caused by race conditions and inconsistent API data structures when loading dropdown data (`Type`, `Priority`, `Status`, `Assignee`).
  - The form now defensively waits for all necessary data to be loaded before initializing.

## Backend API Fixes

- **Fixed 500 Errors on Kanban Board (`BoardController.php`):**
  - Patched methods that could crash if a ticket was associated with a deleted status.
  - Removed an unreliable permission check that could cause errors.
- **Fixed 500 Errors on Data Endpoints (`ProjectController.php`, `UserController.php`):**
  - Fixed a server crash that occurred when the frontend requested all items from an endpoint (using `per_page=-1`).