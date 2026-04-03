<?php

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        $names = [
            'mobile', 'web', 'desktop', 'tablet', 'email',
            'push', 'sms', 'api', 'admin', 'user',
            'marketing', 'onboarding', 'settings', 'notification', 'analytics',
        ];

        $name = $this->faker->unique()->randomElement($names);

        return [
            'name' => $name,
            'description' => $this->faker->sentence(),
        ];
    }
}