<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TransmuteTemplatesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('transmute_templates')->delete();
        
        \DB::table('transmute_templates')->insert(array (
            0 => 
            array (
                'id' => 1,
                'user_id' => 1,
                'name' => 'DepEd Transmutation',
                'created_at' => '2025-11-16 21:24:39',
                'updated_at' => '2025-11-16 21:24:39',
            ),
        ));
        
        
    }
}