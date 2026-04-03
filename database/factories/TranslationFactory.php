<?php

namespace Database\Factories;

use App\Models\Locale;
use App\Models\Translation;
use Illuminate\Database\Eloquent\Factories\Factory;

class TranslationFactory extends Factory
{
    protected $model = Translation::class;

    public function definition(): array
    {
        return [
            'locale_id' => Locale::query()->inRandomOrder()->first()->id ?? 1,
            'key' => $this->faker->unique()->numerify('key_##########'),
            'content' => $this->faker->sentence(),
        ];
    }

    public function configure()
    {
        return $this->onStart(function () {
            $locales = Locale::all();
            if ($locales->isEmpty()) {
                Locale::factory()->count(10)->create();
            }
        });
    }
}