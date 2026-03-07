<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserCsvSeeder extends Seeder
{
    public function run()
    {
        $csvFile = fopen(base_path("database/seeders/users.csv"), "r");
        $firstline = true;
        $index = 1;

        while (($data = fgetcsv($csvFile, 2000, ",")) !== FALSE) {
            
            if (!$firstline) {
                if (empty(trim($data[0]))) {
                    continue;
                }

                $csvStatus = trim($data[3]);
                
               
                $dbStatus = 'active'; 
                if ($csvStatus === 'ปฏิบัติงาน') {
                    $dbStatus = 'active';
                } elseif ($csvStatus === 'ลาออก' || $csvStatus === 'พ้นสภาพ') {
                    $dbStatus = 'inactive';
                }

                User::updateOrCreate(
                    ['name' => trim($data[0])], 
                    [
                        'department' => trim($data[1]), 
                        'position'   => trim($data[2]), 
                        'status'     => $dbStatus, 
                        'email'      => 'user_' . time() . '_' . $index . '@hospital.com',
                        'password'   => Hash::make('12345678')
                    ]
                );
                $index++;
            }
            $firstline = false;
        }

        fclose($csvFile);
        $this->command->info('ข้อมูล User จากไฟล์ CSV ถูก Seed เข้าระบบเรียบร้อยแล้ว! 🎉');
    }
}