<?php

namespace App\Http\Controllers\Api\Pages;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BoardController extends Controller
{
    /**
     * Get projects accessible for board selection
     */
    public function getProjects(Request $request)
    {
        $query = Project::where(function ($query) {
            $query->where('owner_id', auth()->id())
                ->orWhereHas('users', function ($query) {
                    $query->where('users.id', auth()->id());
                });
        });

        // Search
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $projects = $query->select('id', 'name', 'type', 'cover')
            ->orderBy('name')
            ->get();

        return response()->json($projects);
    }

    /**
     * Get Kanban/Scrum board statuses
     */
    public function getStatuses(Project $project)
    {
        $userId = auth()->id();
        
        // Check access - Fix N+1: Dùng whereHas thay vì load users
        if ($project->owner_id != $userId && 
            !$project->users()->where('users.id', $userId)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = TicketStatus::query();
        
        if ($project->status_type === 'custom') {
            $query->where('project_id', $project->id);
        } else {
            $query->whereNull('project_id');
        }

        // Fix N+1: Pre-load ticket counts với withCount
        /** @var User|null $user */
        $user = auth()->user();
        $canCreateTicket = $user ? Gate::forUser($user)->allows('Create ticket') : false;
        
        $statuses = $query->orderBy('order')
            ->withCount([
                'tickets' => function ($q) use ($project) {
                    $q->where('project_id', $project->id);
                }
            ])
            ->get()
            ->map(function ($status) use ($canCreateTicket) {
                return [
                    'id' => $status->id,
                    'title' => $status->name,
                    'color' => $status->color,
                    'size' => $status->tickets_count,  // Dùng count đã load
                    'add_ticket' => $status->is_default && $canCreateTicket,
                    'order' => $status->order,
                ];
            });

        return response()->json($statuses);
    }

    /**
     * Get Kanban board tickets
     */
    public function getKanbanTickets(Request $request, Project $project)
    {
        $userId = auth()->id();
        
        // Check access - Fix N+1: Dùng whereHas thay vì load users
        if ($project->owner_id != $userId && 
            !$project->users()->where('users.id', $userId)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check project type
        if ($project->type === 'scrum') {
            return response()->json([
                'message' => 'This project uses Scrum. Use /api/projects/{project}/scrum/tickets endpoint.',
                'errors' => ['type' => ['Project type is scrum, not kanban']]
            ], 422);
        }

        $query = Ticket::with(['project', 'owner', 'responsible', 'status', 'type', 'priority', 'epic', 'relations'])
            ->where('project_id', $project->id);

        // Filter by users (owners/responsibles)
        if ($request->has('users') && is_array($request->users) && count($request->users) > 0) {
            $query->where(function ($q) use ($request) {
                $q->whereIn('owner_id', $request->users)
                  ->orWhereIn('responsible_id', $request->users);
            });
        }

        // Filter by types
        if ($request->has('types') && is_array($request->types) && count($request->types) > 0) {
            $query->whereIn('type_id', $request->types);
        }

        // Filter by priorities
        if ($request->has('priorities') && is_array($request->priorities) && count($request->priorities) > 0) {
            $query->whereIn('priority_id', $request->priorities);
        }

        // Filter not affected tickets
        if ($request->boolean('includeNotAffectedTickets')) {
            $query->whereNull('responsible_id');
        }

        // Only show tickets user has access to - Optimize: Pre-fetch project IDs
        $projectIds = Project::where('owner_id', $userId)
            ->orWhereHas('users', function ($q) use ($userId) {
                $q->where('users.id', $userId);
            })
            ->pluck('id');
        
        $query->where(function ($q) use ($userId, $projectIds) {
            $q->where('owner_id', $userId)
              ->orWhere('responsible_id', $userId)
              ->orWhereIn('project_id', $projectIds);
        });

        $tickets = $query->orderBy('order')->get()
            ->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'code' => $ticket->code,
                    'title' => $ticket->name,
                    'owner' => $ticket->owner,
                    'type' => $ticket->type,
                    'responsible' => $ticket->responsible,
                    'project' => $ticket->project,
                    'status' => $ticket->status->id ?? null,
                    'priority' => $ticket->priority,
                    'epic' => $ticket->epic,
                    'relations' => $ticket->relations,
                    'totalLoggedHours' => $ticket->totalLoggedSeconds ? $ticket->totalLoggedHours : null,
                ];
            });

        return response()->json($tickets);
    }

    /**
     * Get Scrum board sprint info
     */
    public function getScrumSprint(Project $project)
    {
        $userId = auth()->id();
        
        // Check access - Fix N+1: Dùng whereHas thay vì load users
        if ($project->owner_id != $userId && 
            !$project->users()->where('users.id', $userId)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check project type
        if ($project->type !== 'scrum') {
            return response()->json([
                'message' => 'This project uses Kanban. Use /api/projects/{project}/kanban/tickets endpoint.',
                'errors' => ['type' => ['Project type is kanban, not scrum']]
            ], 422);
        }

        $currentSprint = $project->currentSprint;
        $nextSprint = $project->nextSprint;

        if (!$currentSprint) {
            return response()->json([
                'message' => 'No active sprint found',
                'current_sprint' => null,
                'next_sprint' => $nextSprint ? [
                    'id' => $nextSprint->id,
                    'name' => $nextSprint->name,
                    'starts_at' => $nextSprint->starts_at,
                    'ends_at' => $nextSprint->ends_at,
                ] : null,
            ]);
        }

        return response()->json([
            'current_sprint' => [
                'id' => $currentSprint->id,
                'name' => $currentSprint->name,
                'starts_at' => $currentSprint->starts_at,
                'ends_at' => $currentSprint->ends_at,
                'started_at' => $currentSprint->started_at,
                'remaining' => $currentSprint->remaining,
            ],
            'next_sprint' => $nextSprint ? [
                'id' => $nextSprint->id,
                'name' => $nextSprint->name,
                'starts_at' => $nextSprint->starts_at,
                'ends_at' => $nextSprint->ends_at,
            ] : null,
        ]);
    }

    /**
     * Get Scrum board tickets
     */
    public function getScrumTickets(Request $request, Project $project)
    {
        $userId = auth()->id();
        
        // Check access - Fix N+1: Dùng whereHas thay vì load users
        if ($project->owner_id != $userId && 
            !$project->users()->where('users.id', $userId)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check project type
        if ($project->type !== 'scrum') {
            return response()->json([
                'message' => 'This project uses Kanban. Use /api/projects/{project}/kanban/tickets endpoint.',
                'errors' => ['type' => ['Project type is kanban, not scrum']]
            ], 422);
        }

        $currentSprint = $project->currentSprint;
        if (!$currentSprint) {
            return response()->json([
                'message' => 'No active sprint found',
                'tickets' => []
            ]);
        }

        $query = Ticket::with(['project', 'owner', 'responsible', 'status', 'type', 'priority', 'epic', 'relations'])
            ->where('project_id', $project->id)
            ->where('sprint_id', $currentSprint->id);

        // Apply same filters as Kanban
        if ($request->has('users') && is_array($request->users) && count($request->users) > 0) {
            $query->where(function ($q) use ($request) {
                $q->whereIn('owner_id', $request->users)
                  ->orWhereIn('responsible_id', $request->users);
            });
        }

        if ($request->has('types') && is_array($request->types) && count($request->types) > 0) {
            $query->whereIn('type_id', $request->types);
        }

        if ($request->has('priorities') && is_array($request->priorities) && count($request->priorities) > 0) {
            $query->whereIn('priority_id', $request->priorities);
        }

        if ($request->boolean('includeNotAffectedTickets')) {
            $query->whereNull('responsible_id');
        }

        // Optimize: Pre-fetch project IDs
        $projectIds = Project::where('owner_id', $userId)
            ->orWhereHas('users', function ($q) use ($userId) {
                $q->where('users.id', $userId);
            })
            ->pluck('id');
        
        $query->where(function ($q) use ($userId, $projectIds) {
            $q->where('owner_id', $userId)
              ->orWhere('responsible_id', $userId)
              ->orWhereIn('project_id', $projectIds);
        });

        $tickets = $query->orderBy('order')->get()
            ->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'code' => $ticket->code,
                    'title' => $ticket->name,
                    'owner' => $ticket->owner,
                    'type' => $ticket->type,
                    'responsible' => $ticket->responsible,
                    'project' => $ticket->project,
                    'status' => $ticket->status->id ?? null,
                    'priority' => $ticket->priority,
                    'epic' => $ticket->epic,
                    'relations' => $ticket->relations,
                    'totalLoggedHours' => $ticket->totalLoggedSeconds ? $ticket->totalLoggedHours : null,
                ];
            });

        return response()->json($tickets);
    }

    /**
     * Move ticket (update status and order)
     */
    public function moveTicket(Request $request, Ticket $ticket)
    {
        $userId = auth()->id();
        
        // Check access - Fix N+1: Dùng whereHas thay vì load users
        $project = $ticket->project;
        if ($project->owner_id != $userId && 
            !$project->users()->where('users.id', $userId)->exists()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status_id' => 'required|exists:ticket_statuses,id',
            'order' => 'nullable|integer|min:0',
        ]);

        $ticket->status_id = $validated['status_id'];
        if (isset($validated['order'])) {
            $ticket->order = $validated['order'];
        }
        $ticket->save();

        $ticket->load(['project', 'owner', 'responsible', 'status', 'type', 'priority', 'epic', 'relations']);

        return response()->json([
            'message' => 'Ticket moved successfully',
            'ticket' => [
                'id' => $ticket->id,
                'code' => $ticket->code,
                'title' => $ticket->name,
                'owner' => $ticket->owner,
                'type' => $ticket->type,
                'responsible' => $ticket->responsible,
                'project' => $ticket->project,
                'status' => $ticket->status->id ?? null,
                'priority' => $ticket->priority,
                'epic' => $ticket->epic,
                'relations' => $ticket->relations,
                'totalLoggedHours' => $ticket->totalLoggedSeconds ? $ticket->totalLoggedHours : null,
            ]
        ]);
    }
}



