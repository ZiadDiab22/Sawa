<?php
// database/seeders/VehicleMakeSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VehicleMake;

class VehicleMakeSeeder extends Seeder
{
    public function run(): void
    {
        $makes = [
            // Japanese
            'Toyota',
            'Honda',
            'Nissan',
            'Mazda',
            'Mitsubishi',
            'Subaru',
            'Suzuki',
            'Lexus',
            'Infiniti',

            // Korean
            'Hyundai',
            'Kia',
            'Genesis',

            // German
            'BMW',
            'Mercedes-Benz',
            'Audi',
            'Volkswagen',
            'Porsche',
            'Opel',

            // American
            'Ford',
            'Chevrolet',
            'GMC',
            'Cadillac',
            'Tesla',
            'Dodge',
            'Jeep',
            'Chrysler',

            // French
            'Peugeot',
            'Renault',
            'Citroën',

            // Italian
            'Fiat',
            'Alfa Romeo',
            'Ferrari',
            'Lamborghini',

            // British
            'Land Rover',
            'Jaguar',
            'Mini',
            'Rolls-Royce',
            'Bentley',

            // Chinese
            'Geely',
            'Chery',
            'BYD',
            'Great Wall',

            // Others
            'Volvo',
            'Skoda',
            'Seat',
            'Isuzu',
            'Daewoo',
        ];

        foreach ($makes as $make) {
            VehicleMake::firstOrCreate([
                'name' => $make
            ]);
        }
    }
}
