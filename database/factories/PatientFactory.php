<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Patient>
 */
class PatientFactory extends Factory
{
    protected $model = Patient::class;

    private static array $firstNamesF = [
        'Fatima', 'Amina', 'Laila', 'Samira', 'Nadia', 'Zineb', 'Khadija', 'Yasmina',
        'Salma', 'Imane', 'Sanaa', 'Hanane', 'Malak', 'Inès', 'Lina', 'Rania', 'Souad', 'Farida',
    ];

    private static array $firstNamesM = [
        'Mohammed', 'Omar', 'Hassan', 'Karim', 'Youssef', 'Mehdi', 'Rachid', 'Khalid',
        'Amine', 'Bilal', 'Anas', 'Ibrahim', 'Tarik', 'Nabil', 'Said', 'Mustapha', 'Driss', 'Othman',
    ];

    private static array $lastNames = [
        'Alami', 'Benali', 'El Idrissi', 'Zouhri', 'Tazi', 'Chraibi', 'Bennis', 'Fassi',
        'Lamrani', 'Ouardi', 'Berrada', 'Moussaoui', 'Kettani', 'Tahiri', 'Sbai', 'Benjelloun', 'El Fassi', 'Bouhali',
    ];

    public function definition(): array
    {
        $isFemale = fake()->boolean(55);
        $firstNames = $isFemale ? self::$firstNamesF : self::$firstNamesM;

        return [
            'first_name' => fake()->randomElement($firstNames),
            'last_name' => fake()->randomElement(self::$lastNames),
            'birth_date' => fake()->dateTimeBetween('-70 years', '-22 years'),
            'sexe' => $isFemale ? 'F' : 'M',
            'email' => fake()->unique()->safeEmail(),
            'phone' => '06' . fake()->numerify('########'),
            'address' => fake()->optional(0.7)->address(),
            'notes' => fake()->optional(0.3)->sentence(),
            'is_active' => true,
        ];
    }
}
