<?php

namespace Database\Factories;

use App\Models\Supervisor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupervisorFactory extends Factory
{
    protected $model = Supervisor::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->supervisor(),
            'employee_number' => fake()->unique()->numerify('EMP-#####'),
            'specialization' => fake()->randomElement(['حفظ', 'تجويد', 'القرآن الكريم', 'علوم القرآن']),
            'hire_date' => fake()->date('Y-m-d'),
        ];
    }
}
