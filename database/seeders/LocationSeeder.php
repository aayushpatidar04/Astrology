<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            ['city_name' => 'Chhoti Sadri', 'state' => 'Rajasthan', 'latitude' => 24.381500, 'longitude' => 74.701200],
            ['city_name' => 'New Delhi', 'state' => 'Delhi', 'latitude' => 28.613900, 'longitude' => 77.209000],
            ['city_name' => 'Mumbai', 'state' => 'Maharashtra', 'latitude' => 18.975000, 'longitude' => 72.825800],
            ['city_name' => 'Jaipur', 'state' => 'Rajasthan', 'latitude' => 26.912400, 'longitude' => 75.787300],
            ['city_name' => 'Ujjain', 'state' => 'Madhya Pradesh', 'latitude' => 23.176500, 'longitude' => 75.788500],
            ['city_name' => 'Bengaluru', 'state' => 'Karnataka', 'latitude' => 12.971600, 'longitude' => 77.594600],
            ['city_name' => 'Kolkata', 'state' => 'West Bengal', 'latitude' => 22.572600, 'longitude' => 88.363900],
            ['city_name' => 'Chennai', 'state' => 'Tamil Nadu', 'latitude' => 13.082700, 'longitude' => 80.270700],
            ['city_name' => 'Hyderabad', 'state' => 'Telangana', 'latitude' => 17.385000, 'longitude' => 78.486700],
            ['city_name' => 'Ahmedabad', 'state' => 'Gujarat', 'latitude' => 23.022500, 'longitude' => 72.571400],
            ['city_name' => 'Pune', 'state' => 'Maharashtra', 'latitude' => 18.520400, 'longitude' => 73.856700],
            ['city_name' => 'Varanasi', 'state' => 'Uttar Pradesh', 'latitude' => 25.317600, 'longitude' => 82.973900],
            ['city_name' => 'Prayagraj', 'state' => 'Uttar Pradesh', 'latitude' => 25.435800, 'longitude' => 81.846300],
            ['city_name' => 'Patna', 'state' => 'Bihar', 'latitude' => 25.594100, 'longitude' => 85.137600],
            ['city_name' => 'Bhopal', 'state' => 'Madhya Pradesh', 'latitude' => 23.259900, 'longitude' => 77.412600],
            ['city_name' => 'Indore', 'state' => 'Madhya Pradesh', 'latitude' => 22.719600, 'longitude' => 75.857700],
            ['city_name' => 'Lucknow', 'state' => 'Uttar Pradesh', 'latitude' => 26.846700, 'longitude' => 80.946200],
            ['city_name' => 'Chandigarh', 'state' => 'Punjab', 'latitude' => 30.733300, 'longitude' => 76.779400],
            ['city_name' => 'Kochi', 'state' => 'Kerala', 'latitude' => 9.931200, 'longitude' => 76.267300],
            ['city_name' => 'Udaipur', 'state' => 'Rajasthan', 'latitude' => 24.585400, 'longitude' => 73.712500],
            ['city_name' => 'Jodhpur', 'state' => 'Rajasthan', 'latitude' => 26.238900, 'longitude' => 73.024300],
            ['city_name' => 'Kota', 'state' => 'Rajasthan', 'latitude' => 25.213800, 'longitude' => 75.864800],
            ['city_name' => 'Ajmer', 'state' => 'Rajasthan', 'latitude' => 26.449900, 'longitude' => 74.639900],
        ];

        foreach ($locations as $loc) {
            Location::updateOrCreate(
                ['city_name' => $loc['city_name']],
                $loc
            );
        }
    }
}
