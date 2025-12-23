<?php

namespace App\Http\Controllers\Api\Pages;

use App\Http\Controllers\Controller;
use App\Models\Epic;
use App\Models\Project;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RoadMapController extends Controller
{
    /**
     * Get roadmap data for project with clean visual hierarchy:
     * - Main bar color = STATUS
     * - Left border = PRIORITY  
     * - Icon in popup = TYPE
     */
    public function getRoadmap(Project $project)
    {
        // === STEP 1: Authorization Check ===
        if ($project->owner_id != auth()->id() &&
            !$project->users->where('id', auth()->id())->count()) {
            Log::warning('[Roadmap] Unauthorized access attempt', [
                'project_id' => $project->id,
                'user_id' => auth()->id()
            ]);
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        Log::info('[Roadmap] Fetching roadmap for project', [
            'project_id' => $project->id,
            'project_name' => $project->name,
            'user_id' => auth()->id()
        ]);

        // === STEP 2: Query Tickets with Date Filters ===
        $tickets = $project->tickets()
            ->whereNotNull('start_date')
            ->whereNotNull('due_date')
            ->with(['status', 'priority', 'type', 'responsible'])
            ->orderBy('start_date', 'asc')
            ->get();

        Log::info('[Roadmap] Tickets query result', [
            'total_tickets_in_project' => $project->tickets()->count(),
            'tickets_with_dates' => $tickets->count(),
            'ticket_ids' => $tickets->pluck('id')->toArray()
        ]);

        // === STEP 3: Handle Empty Results ===
        if ($tickets->isEmpty()) {
            Log::warning('[Roadmap] No tickets with start/due dates found', [
                'project_id' => $project->id,
                'total_project_tickets' => $project->tickets()->count()
            ]);
            return response()->json([]);
        }

        // === STEP 4: Transform Tickets to Gantt Format ===
        $tasks = [];
        foreach ($tickets as $ticket) {
            // Get Status Information (for main bar color)
            $statusName = $ticket->status ? $ticket->status->name : 'Todo';
            $statusClass = $this->getStatusClass($statusName, $ticket->status);
            
            // Get Priority Information (for left border)
            $priorityName = $ticket->priority ? $ticket->priority->name : 'Normal';
            $priorityClass = $this->getPriorityClass($priorityName);
            
            // Get Type Information (for popup icon)
            $typeName = $ticket->type ? $ticket->type->name : 'Task';
            $typeIcon = $this->getTypeIcon($typeName);

            // Calculate Progress
            $progress = $this->calculateProgress($ticket);

            // Build Enhanced Popup HTML
            $popupHtml = $this->buildPopupHtml(
                $ticket, 
                $statusName, 
                $priorityName, 
                $typeName, 
                $typeIcon
            );

            $task = [
                'id' => (string)$ticket->id,
                'name' => $ticket->name,
                'start' => $ticket->start_date->format('Y-m-d'),
                'end' => $ticket->due_date->format('Y-m-d'),
                'progress' => $progress,
                'status_class' => $statusClass,    // ✅ Individual class
                'priority_class' => $priorityClass, // ✅ Individual class
                'custom_popup_html' => $popupHtml,
            ];

            $tasks[] = $task;

            // Log each ticket for debugging
            Log::debug('[Roadmap] Processed ticket', [
                'ticket_id' => $ticket->id,
                'ticket_name' => $ticket->name,
                'status' => $statusName,
                'priority' => $priorityName,
                'type' => $typeName,
                'status_class' => $statusClass,
                'priority_class' => $priorityClass,
                'start_date' => $ticket->start_date->format('Y-m-d'),
                'end_date' => $ticket->due_date->format('Y-m-d'),
                'progress' => $progress
            ]);
        }

        Log::info('[Roadmap] Successfully processed tickets', [
            'project_id' => $project->id,
            'total_tasks' => count($tasks),
            'date_range' => [
                'first' => $tasks[0]['start'] ?? null,
                'last' => end($tasks)['end'] ?? null
            ]
        ]);

        return response()->json($tasks);
    }

    /**
     * Get Status CSS class for main bar color
     */
    private function getStatusClass(string $statusName, $status = null): string
    {
        $nameLower = strtolower($statusName);

        // Check for "Done" or "Archived"
        if ($status && $status->is_final) {
            return 'status-done';
        }

        // Check for "In Progress"
        if (str_contains($nameLower, 'progress') || str_contains($nameLower, 'doing')) {
            return 'status-in-progress';
        }

        // Check for "Archived"
        if (str_contains($nameLower, 'archived') || str_contains($nameLower, 'archive')) {
            return 'status-archived';
        }

        // Default: Todo
        return 'status-todo';
    }

    /**
     * Get Priority CSS class for left border styling
     */
    private function getPriorityClass(string $priorityName): string
    {
        $nameLower = strtolower($priorityName);

        if (in_array($nameLower, ['high', 'critical', 'urgent', 'highest'])) {
            return 'priority-high';
        }

        if (in_array($nameLower, ['low', 'lowest', 'minor'])) {
            return 'priority-low';
        }

        // Normal priority has no special border
        return 'priority-normal';
    }

    /**
     * Get Type Icon for popup display
     */
    private function getTypeIcon(string $typeName): string
    {
        $nameLower = strtolower($typeName);

        $icons = [
            'bug' => '<svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 11H9v-2h2v2zm0-4H9V5h2v4z"/></svg>',
            'evolution' => '<svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 20 20"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/></svg>',
            'task' => '<svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>'
        ];

        return $icons[$nameLower] ?? $icons['task'];
    }

    /**
     * Calculate progress based on status
     */
    private function calculateProgress(Ticket $ticket): int
    {
        if ($ticket->status) {
            // If status is final (Done/Archived), return 100%
            if ($ticket->status->is_final) {
                return 100;
            }

            $statusName = strtolower($ticket->status->name);

            // If status is "In Progress", return 50% (or use ticket's progress field)
            if (str_contains($statusName, 'progress')) {
                return $ticket->progress ?? 50;
            }
        }

        // Default: 0% for Todo
        return 0;
    }

    /**
     * Build Enhanced Popup HTML with all details
     */
    private function buildPopupHtml(
        Ticket $ticket,
        string $statusName,
        string $priorityName,
        string $typeName,
        string $typeIcon
    ): string {
        $assignee = $ticket->responsible 
            ? htmlspecialchars($ticket->responsible->name)
            : 'Unassigned';

        return 
            '<div class="p-4 min-w-[280px]">' .
                '<div class="flex items-start gap-3 mb-3">' .
                    '<div class="flex-shrink-0 mt-0.5">' . $typeIcon . '</div>' .
                    '<div class="flex-1">' .
                        '<h4 class="font-bold text-gray-900 text-sm mb-1">' . 
                            htmlspecialchars($ticket->name) . 
                        '</h4>' .
                        '<div class="flex items-center gap-2 text-xs text-gray-500">' .
                            '<span class="font-medium text-gray-700">' . htmlspecialchars($typeName) . '</span>' .
                        '</div>' .
                    '</div>' .
                '</div>' .
                
                '<div class="space-y-2 text-xs">' .
                    '<div class="flex items-center justify-between">' .
                        '<span class="text-gray-500">Status:</span>' .
                        '<span class="font-medium text-gray-900">' . htmlspecialchars($statusName) . '</span>' .
                    '</div>' .
                    '<div class="flex items-center justify-between">' .
                        '<span class="text-gray-500">Priority:</span>' .
                        '<span class="font-medium text-gray-900">' . htmlspecialchars($priorityName) . '</span>' .
                    '</div>' .
                    '<div class="flex items-center justify-between">' .
                        '<span class="text-gray-500">Assignee:</span>' .
                        '<span class="font-medium text-gray-900">' . $assignee . '</span>' .
                    '</div>' .
                    '<div class="pt-2 mt-2 border-t border-gray-100">' .
                        '<div class="flex items-center gap-2 text-gray-500">' .
                            '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">' .
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>' .
                            '</svg>' .
                            '<span>' . 
                                $ticket->start_date->format('M d') . 
                                ' → ' . 
                                $ticket->due_date->format('M d, Y') . 
                            '</span>' .
                        '</div>' .
                    '</div>' .
                '</div>' .
            '</div>';
    }

    /**
     * Get roadmap dates for Gantt chart
     */
    public function getRoadmapDates(Project $project)
    {
        // Check access
        if ($project->owner_id != auth()->id() && 
            !$project->users->where('id', auth()->id())->count()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $firstDate = $project->epicsFirstDate;
        $lastDate = $project->epicsLastDate;

        return response()->json([
            'first_date' => $firstDate ? Carbon::parse($firstDate)->subMonths(2)->format('Y-m-d') : null,
            'last_date' => $lastDate ? Carbon::parse($lastDate)->addMonths(2)->format('Y-m-d') : null,
            'scroll_to' => $firstDate ? Carbon::parse($firstDate)->subDays(7)->format('Y-m-d') : null,
        ]);
    }
}