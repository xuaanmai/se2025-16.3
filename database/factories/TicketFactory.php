<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Ticket::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => ucfirst($this->faker->words(4, true)),
            'content' => $this->faker->paragraphs(3, true),
            'estimation' => $this->faker->randomElement([1, 2, 3, 5, 8, 13]),
            'project_id' => Project::factory(),
            'owner_id' => User::factory(),
            'responsible_id' => null,
            'status_id' => TicketStatus::inRandomOrder()->first()->id ?? TicketStatus::factory(),
            'priority_id' => TicketPriority::inRandomOrder()->first()->id ?? TicketPriority::factory(),
            'type_id' => TicketType::inRandomOrder()->first()->id ?? TicketType::factory(),
        ];
    }
}
