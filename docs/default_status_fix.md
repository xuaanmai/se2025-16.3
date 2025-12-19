# Default Status Fix Plan

This document outlines the plan to ensure new tickets always have a default status, handled primarily by the backend.

## Problem

- When creating a new ticket, the `status_id` field is sometimes missing or empty, leading to a `422 Unprocessable Content` validation error from the backend.
- This occurs because the frontend's logic to find a default status (e.g., "Todo") might fail if the status doesn't exist, is named differently, or if there's a data loading issue.

## Solution Strategy

The most robust solution is to make the backend responsible for assigning a default status if one is not explicitly provided by the frontend. This ensures data integrity regardless of frontend state.

## Backend Changes (`app/Http/Controllers/Api/Resources/TicketController.php`)

### 1. Modify `store` method validation

- **Current:** `'status_id' => 'required|exists:ticket_statuses,id'`
- **Change to:** `'status_id' => 'nullable|exists:ticket_statuses,id'`

This allows the frontend to omit `status_id` if it doesn't have a value, without immediately failing validation.

### 2. Add default status assignment logic in `store` method

After validation, before creating the `Ticket` model, add logic to assign a default `status_id` if it's missing from the request.

- **Logic:**
  - Check if `status_id` is present in the validated data.
  - If not, query the `TicketStatus` model to find the default status. The default status is typically the one with `is_default = true` and `project_id = null`.
  - If a default status is found, assign its ID to the `$validated` array.
  - If no default status is found (which indicates a data setup issue), a fallback mechanism might be needed (e.g., assign the ID of the first available status, or throw a specific error if no default can be determined). For now, we'll assume a default exists.

## Frontend Changes (`resources/js/components/Tickets/TicketFormModal.vue`)

### 1. Simplify `initializeForm` logic

Since the backend will now handle the default status assignment, the frontend's `initializeForm` function can be simplified.

- **Current:** `status_id: referentialStore.ticketStatuses.find(s => s.name === 'Todo')?.id || '',`
- **Change to:** `status_id: '',` (or simply omit it from `defaultValues` if it's always `null` for new tickets).

The frontend will no longer attempt to find and assign a default `status_id` for new tickets. It will rely entirely on the backend.

### 2. Hide Status Dropdown on Create (already implemented)

The `Status` dropdown is already hidden (`v-if="isEditMode"`) when creating a new ticket. This aligns with the new backend-driven default assignment.

## Rationale

- **Robustness:** Ensures a valid `status_id` is always set, even if frontend data is incomplete or inconsistent.
- **Separation of Concerns:** Backend is responsible for data integrity and business rules (what constitutes a default status). Frontend is responsible for UI/UX.
- **Simplicity:** Simplifies frontend logic by removing complex default-finding.
