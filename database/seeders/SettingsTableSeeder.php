<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('settings')->delete();
        
        \DB::table('settings')->insert(array (
            0 => 
            array (
                'id' => 1,
                'key' => 'kpi_hours',
                'value' => '60',
                'created_at' => '2026-03-07 06:27:55',
                'updated_at' => '2026-03-07 06:27:55',
            ),
            1 => 
            array (
                'id' => 2,
                'key' => 'filter_start_month',
                'value' => '2026-01',
                'created_at' => '2026-03-07 07:01:56',
                'updated_at' => '2026-03-07 08:29:47',
            ),
            2 => 
            array (
                'id' => 3,
                'key' => 'filter_end_month',
                'value' => '2026-12',
                'created_at' => '2026-03-07 07:01:56',
                'updated_at' => '2026-03-07 08:29:47',
            ),
        ));
        
        
    }
}