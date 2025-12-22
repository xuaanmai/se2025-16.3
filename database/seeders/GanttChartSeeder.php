<?php

namespace Database\Seeders;

use App\Models\Epic;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class GanttChartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Create a dedicated Scrum project for Gantt testing
        $project = Project::factory()->create([
            'name' => 'Project Roadmap (Gantt)',
            'type' => 'scrum',
            'ticket_prefix' => 'GNT',
        ]);

        // 2. Create a couple of users and attach them with a role
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $project->users()->attach([
            $user1->id => ['role' => 'employee'],
            $user2->id => ['role' => 'employee'],
        ]);

        // 3. Create Epics with realistic timeframes
        $epic1 = Epic::factory()->create([
            'project_id' => $project->id,
            'name' => 'Q1 - Foundation & Authentication',
            'starts_at' => Carbon::now()->startOfQuarter(),
            'ends_at' => Carbon::now()->startOfQuarter()->addWeeks(5),
        ]);

        $epic2 = Epic::factory()->create([
            'project_id' => $project->id,
            'name' => 'Q1 - Core Feature Development',
            'starts_at' => Carbon::now()->startOfQuarter()->addWeeks(4),
            'ends_at' => Carbon::now()->startOfQuarter()->addWeeks(10),
        ]);

        $epic3 = Epic::factory()->create([
            'project_id' => $project->id,
            'name' => 'Q2 - Refinement & Beta Testing',
            'starts_at' => Carbon::now()->startOfQuarter()->addWeeks(10),
            'ends_at' => Carbon::now()->endOfQuarter(),
        ]);

        // 4. Create Tickets and associate them with Epics
        // Tickets for Epic 1
        Ticket::factory()->create([
            'project_id' => $project->id,
            'epic_id' => $epic1->id,
            'name' => 'Setup database schema',
            'responsible_id' => $user1->id,
        ]);
        Ticket::factory()->create([
            'project_id' => $project->id,
            'epic_id' => $epic1->id,
            'name' => 'Implement user registration',
            'responsible_id' => $user2->id,
        ]);

        // Tickets for Epic 2
        Ticket::factory()->create([
            'project_id' => $project->id,
            'epic_id' => $epic2->id,
            'name' => 'Build project dashboard',
            'responsible_id' => $user1->id,
        ]);
        Ticket::factory()->create([
            'project_id' => $project->id,
            'epic_id' => $epic2->id,
            'name' => 'Develop ticket management module',
            'responsible_id' => $user2->id,
        ]);
        Ticket::factory()->create([
            'project_id' => $project->id,
            'epic_id' => $epic2->id,
            'name' => 'Integrate Kanban board',
            'responsible_id' => $user1->id,
        ]);

        // Tickets for Epic 3
        Ticket::factory()->create([
            'project_id' => $project->id,
            'epic_id' => $epic3->id,
            'name' => 'Conduct user acceptance testing',
            'responsible_id' => $user2->id,
        ]);
    }
}
