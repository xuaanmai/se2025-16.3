<?php

namespace App\Http\Controllers\Api\Resources;

use App\Exports\TicketHoursExport;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketHour;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class TicketController extends Controller
{
    /**
     * Kiểm tra xem user hiện tại có phải admin không
     * Admin = có role "Admin"
     */
    protected function isAdmin(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        
        return $user->hasRole('Admin');
    }

    public function index(Request $request)
    {
        $userId = auth()->id();
        $query = Ticket::with(['owner', 'responsible', 'status', 'project', 'type', 'priority']);

        // Nếu không phải admin, chỉ lấy tickets mà user có quyền truy cập
        if (!$this->isAdmin()) {
            $query->where(function ($query) use ($userId) {
                $query->where('owner_id', $userId)
                    ->orWhere('responsible_id', $userId)
                    ->orWhereHas('project', function ($query) use ($userId) {
                        $query->where('owner_id', $userId)
                            ->orWhereHas('users', function ($query) use ($userId) {
                                $query->where('users.id', $userId);
                            });
                    });
            });
        }

        // Filter by project
        if ($request->has('project_id')) {
            $projectId = $request->project_id;
            
            // Kiểm tra quyền truy cập project nếu không phải admin
            if (!$this->isAdmin()) {
                $hasAccess = Project::where('id', $projectId)
                    ->where(function ($q) use ($userId) {
                        $q->where('owner_id', $userId)
                            ->orWhereHas('users', function ($q) use ($userId) {
                                $q->where('users.id', $userId);
                            });
                    })
                    ->exists();
                
                if (!$hasAccess) {
                    return response()->json([
                        'message' => 'You do not have permission to access this project.'
                    ], 403);
                }
            }
            
            $query->where('project_id', $projectId);
        }

        // Filter by status
        if ($request->has('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        // Filter by priority
        if ($request->has('priority_id')) {
            $query->where('priority_id', $request->priority_id);
        }

        // Search
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $tickets = $query->orderBy('order')->paginate($perPage);

        return response()->json($tickets);
    }

    public function show(Ticket $ticket)
    {
        // Kiểm tra quyền truy cập nếu không phải admin
        if (!$this->isAdmin()) {
            $userId = auth()->id();
            $hasAccess = $ticket->owner_id === $userId
                || $ticket->responsible_id === $userId
                || $ticket->project->owner_id === $userId
                || $ticket->project->users()->where('users.id', $userId)->exists();
            
            if (!$hasAccess) {
                return response()->json([
                    'message' => 'You do not have permission to view this ticket.'
                ], 403);
            }
        }

        $ticket->load([
            'owner', 'responsible', 'status', 'project', 
            'type', 'priority', 'comments', 'activities', 
            'hours', 'subscribers', 'epic', 'sprint'
        ]);
        return response()->json($ticket);
    }

    public function store(Request $request)
    {
        $userId = auth()->id();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'project_id' => 'required|exists:projects,id',
            'owner_id' => 'required|exists:users,id',
            'responsible_id' => 'nullable|exists:users,id',
            'status_id' => 'required|exists:ticket_statuses,id',
            'type_id' => 'nullable|exists:ticket_types,id',
            'priority_id' => 'nullable|exists:ticket_priorities,id',
            'estimation' => 'nullable|numeric',
            'epic_id' => 'nullable|exists:epics,id',
            'sprint_id' => 'nullable|exists:sprints,id',
        ]);

        // Kiểm tra quyền truy cập project nếu không phải admin
        if (!$this->isAdmin()) {
            $projectId = $validated['project_id'];
            $hasAccess = Project::where('id', $projectId)
                ->where(function ($q) use ($userId) {
                    $q->where('owner_id', $userId)
                        ->orWhereHas('users', function ($q) use ($userId) {
                            $q->where('users.id', $userId);
                        });
                })
                ->exists();
            
            if (!$hasAccess) {
                return response()->json([
                    'message' => 'You do not have permission to create tickets in this project.'
                ], 403);
            }
        }

        // Đảm bảo content luôn là empty string nếu null (vì Laravel middleware ConvertEmptyStringsToNull)
        $validated['content'] = $validated['content'] ?? '';

        $ticket = Ticket::create($validated);
        $ticket->load(['owner', 'responsible', 'status', 'project', 'type', 'priority']);

        return response()->json($ticket, 201);
    }

    public function update(Request $request, Ticket $ticket)
    {
        // Kiểm tra quyền chỉnh sửa nếu không phải admin
        if (!$this->isAdmin()) {
            $userId = auth()->id();
            $hasAccess = $ticket->owner_id === $userId
                || $ticket->responsible_id === $userId
                || $ticket->project->owner_id === $userId
                || $ticket->project->users()->where('users.id', $userId)->exists();
            
            if (!$hasAccess) {
                return response()->json([
                    'message' => 'You do not have permission to update this ticket.'
                ], 403);
            }
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'project_id' => 'sometimes|required|exists:projects,id',
            'owner_id' => 'sometimes|required|exists:users,id',
            'responsible_id' => 'nullable|exists:users,id',
            'status_id' => 'sometimes|required|exists:ticket_statuses,id',
            'type_id' => 'nullable|exists:ticket_types,id',
            'priority_id' => 'nullable|exists:ticket_priorities,id',
            'estimation' => 'nullable|numeric',
            'epic_id' => 'nullable|exists:epics,id',
            'sprint_id' => 'nullable|exists:sprints,id',
            'order' => 'nullable|integer',
        ]);

        // Đảm bảo content luôn là empty string nếu null (khi content được gửi trong request)
        if (isset($validated['content']) && $validated['content'] === null) {
            $validated['content'] = '';
        }

        $ticket->update($validated);
        $ticket->load(['owner', 'responsible', 'status', 'project', 'type', 'priority']);

        return response()->json($ticket);
    }

    public function destroy(Ticket $ticket)
    {
        // Kiểm tra quyền xóa - chỉ owner hoặc admin mới được xóa
        if (!$this->isAdmin()) {
            $userId = auth()->id();
            if ($ticket->owner_id !== $userId) {
                return response()->json([
                    'message' => 'You do not have permission to delete this ticket. Only the owner can delete it.'
                ], 403);
            }
        }

        $ticket->delete();
        return response()->json(['message' => 'Ticket deleted successfully']);
    }

    /**
     * Get ticket subscribers
     */
    public function getSubscribers(Ticket $ticket)
    {
        $subscribers = $ticket->subscribers;
        return response()->json($subscribers);
    }

    /**
     * Subscribe user to ticket
     */
    public function subscribe(Ticket $ticket, User $user)
    {
        if ($ticket->subscribers()->where('users.id', $user->id)->exists()) {
            return response()->json([
                'message' => 'User is already subscribed to this ticket.',
                'errors' => ['user_id' => ['User is already subscribed to this ticket.']]
            ], 422);
        }

        $ticket->subscribers()->attach($user->id);

        return response()->json([
            'message' => 'User subscribed successfully',
            'user' => $user
        ], 201);
    }

    /**
     * Unsubscribe user from ticket
     */
    public function unsubscribe(Ticket $ticket, User $user)
    {
        if (!$ticket->subscribers()->where('users.id', $user->id)->exists()) {
            return response()->json([
                'message' => 'User is not subscribed to this ticket.',
                'errors' => ['user_id' => ['User is not subscribed to this ticket.']]
            ], 404);
        }

        $ticket->subscribers()->detach($user->id);

        return response()->json([
            'message' => 'User unsubscribed successfully'
        ]);
    }

    /**
     * Get ticket hours
     */
    public function getHours(Ticket $ticket)
    {
        $hours = $ticket->hours()->with(['user', 'activity'])->orderBy('created_at', 'desc')->get();
        return response()->json($hours);
    }

    /**
     * Log hours for ticket
     */
    public function logHours(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'value' => 'required|numeric|min:0',
            'comment' => 'nullable|string',
            'activity_id' => 'nullable|exists:activities,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $validated['ticket_id'] = $ticket->id;
        if (!isset($validated['user_id'])) {
            $validated['user_id'] = auth()->id();
        }

        $hour = TicketHour::create($validated);
        $hour->load(['user', 'activity']);

        return response()->json($hour, 201);
    }

    /**
     * Export ticket hours as CSV
     */
    public function exportHours(Ticket $ticket)
    {
        $export = new TicketHoursExport($ticket);
        $filename = 'time_' . str_replace('-', '_', $ticket->code) . '.csv';

        return Excel::download($export, $filename, \Maatwebsite\Excel\Excel::CSV, [
            'Content-Type' => 'text/csv'
        ]);
    }
}


