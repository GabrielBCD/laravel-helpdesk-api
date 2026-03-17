<?php

namespace Database\Factories;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = [
            Ticket::STATUS_OPEN,
            Ticket::STATUS_VERIFYING,
            Ticket::STATUS_FINISHED,
        ];

        return [
            'syndic_name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'condominium_name' => $this->faker->company(),
            'zip_code' => $this->faker->postcode(),
            'email' => $this->faker->email(),
            'status' => $this->faker->randomElement($statuses),
        ];
    }

    /**
     * Indicate that the ticket status should be open.
     */
    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Ticket::STATUS_OPEN,
        ]);
    }

    /**
     * Indicate that the ticket status should be verifying.
     */
    public function verifying(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Ticket::STATUS_VERIFYING,
        ]);
    }

    /**
     * Indicate that the ticket status should be finished.
     */
    public function finished(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Ticket::STATUS_FINISHED,
        ]);
    }
}

