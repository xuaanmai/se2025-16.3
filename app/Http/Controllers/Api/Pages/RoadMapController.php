<?php

namespace App\Http\Controllers\Api\Pages;

use App\Http\Controllers\Controller;
use App\Models\Epic;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RoadMapController extends Controller
{
    /**
     * Get roadmap data for project
     */
    public function getRoadmap(Project $project)
    {
        // Check access
        if ($project->owner_id != auth()->id() && 
            !$project->users->where('id', auth()->id)->count()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $epics = $project->epics()
            ->with(['tickets', 'sprint', 'parent'])
            ->orderBy('starts_at')
            ->get()
            ->map(function ($epic) {
                return [
                    'id' => $epic->id,
                    'name' => $epic->name,
                    'starts_at' => $epic->starts_at,
                    'ends_at' => $epic->ends_at,
                    'parent_id' => $epic->parent_id,
                    'tickets' => $epic->tickets->map(function ($ticket) {
                        return [
                            'id' => $ticket->id,
                            'code' => $ticket->code,
                            'name' => $ticket->name,
                            'status' => $ticket->status,
                            'priority' => $ticket->priority,
                        ];
                    }),
                    'sprint' => $epic->sprint ? [
                        'id' => $epic->sprint->id,
                        'name' => $epic->sprint->name,
                    ] : null,
                ];
            });

        return response()->json([
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
            ],
            'epics' => $epics,
        ]);
    }

    /**
     * Get roadmap dates for Gantt chart
     */
    public function getRoadmapDates(Project $project)
    {
        // Check access
        if ($project->owner_id != auth()->id() && 
            !$project->users->where('id', auth()->id)->count()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $firstDate = $project->epicsFirstDate;
        $lastDate = $project->epicsLastDate;

        return response()->json([
            'first_date' => $firstDate ? Carbon::parse($firstDate)->subYear()->format('Y-m-d') : null,
            'last_date' => $lastDate ? Carbon::parse($lastDate)->addYear()->format('Y-m-d') : null,
            'scroll_to' => $firstDate ? Carbon::parse($firstDate)->subDays(5)->format('Y-m-d') : null,
        ]);
    }

    /**
     * Get Gantt chart data for project
     */
    public function getGanttData(Project $project)
    {
        // Check access
        if ($project->owner_id != auth()->id() &&
            !$project->users->where('id', auth()->id)->count()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $tasks = $project->tickets()
            ->with(['status', 'relations'])
            ->get()
            ->map(function ($ticket) {
                $endDate = $ticket->due_date ? $ticket->due_date->toDateString() : Carbon::parse($ticket->created_at)->addDays(3)->toDateString();

                // Get dependencies
                $dependencies = $ticket->relations
                    ->where('type', 'blocks')
                    ->pluck('related_ticket_id')
                    ->map(function ($id) {
                        return 'task_' . $id;
                    })
                    ->implode(',');
                
                // Determine progress based on status
                $progress = 0;
                if ($ticket->status) {
                    if ($ticket->status->is_closed) {
                        $progress = 100;
                    } elseif (str_contains(strtolower($ticket->status->name), 'progress')) {
                        $progress = 50;
                    }
                }

                return [
                    'id' => 'task_' . $ticket->id,
                    'name' => $ticket->name,
                    'start' => $ticket->created_at->toDateString(),
                    'end' => $endDate,
                    'progress' => $progress,
                    'dependencies' => $dependencies,
                    'custom_class' => 'gantt-bar-' . str_replace(' ', '-', strtolower($ticket->status->name ?? 'default'))
                ];
            });

        return response()->json($tasks);
    }
}



