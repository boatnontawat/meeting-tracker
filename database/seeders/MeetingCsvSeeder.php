<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\MeetingRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class MeetingCsvSeeder extends Seeder
{
    public function run()
    {
        $csvFile = fopen(base_path("database/seeders/details.csv"), "r");
        $firstline = true;

        while (($data = fgetcsv($csvFile, 4000, ",")) !== FALSE) {
            
            
            if (!$firstline) {
                
                
                $userName = trim($data[2]);
                if (empty($userName)) {
                    continue;
                }

                $userStatus = trim($data[12]) === 'ลาออก' || trim($data[12]) === 'พ้นสภาพ' ? 'inactive' : 'active';
                $user = User::firstOrCreate(
                    ['name' => $userName],
                    [
                        'department' => trim($data[3]),
                        'position'   => trim($data[4]),
                        'status'     => $userStatus,
                        'email'      => 'legacy_user_' . uniqid() . '@hospital.com',
                        'password'   => Hash::make('12345678')
                    ]
                );
                try {
                    $startCarbon = Carbon::createFromFormat('d/m/Y', trim($data[5]))->startOfDay(); 
                    $endCarbon = Carbon::createFromFormat('d/m/Y', trim($data[6]))->endOfDay();   
                } catch (\Exception $e) {
                    $startCarbon = Carbon::now()->startOfDay();
                    $endCarbon = Carbon::now()->endOfDay();
                }

                $ymRaw = trim($data[13]);
                $ymParts = explode('-', $ymRaw);
                $ymFormatted = count($ymParts) == 2 
                    ? sprintf('%04d-%02d', $ymParts[0], $ymParts[1]) 
                    : $startCarbon->format('Y-m');

                $meetingType = trim($data[9]);
                if (stripos($meetingType, 'online') !== false) {
                    $meetingType = 'Online';
                }

                MeetingRecord::create([
                    'user_id'      => $user->id,
                    'start_time'   => $startCarbon->toDateTimeString(),
                    'end_time'     => $endCarbon->toDateTimeString(),
                    'total_hours'  => (float) trim($data[7]), 
                    'topic'        => trim($data[8]),         
                    'organizer'    => trim($data[10]),        
                    'location'     => trim($data[11]),        
                    'month_year'   => $ymFormatted,           
                ]);
            }
            $firstline = false;
        }

        fclose($csvFile);
        $this->command->info('✅ นำเข้าข้อมูลประวัติการประชุมจากระบบเก่า (details.csv) เรียบร้อยแล้ว!');
    }
}