<?php

use Illuminate\Database\Seeder;

class AccountTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('accounts')->insert([
            [
                'account_name'      => 'Cash', //account id : 1
                'description'       => 'Cash account',
                'type'              => 1,
                'relation'          => 'real',
                'financial_status'  => 'none',
                'opening_balance'   => '0',
                'status'            => 1
            ],
            [
                'account_name'      => 'Sales', //account id : 2
                'description'       => 'Sales account',
                'type'              => 2,
                'relation'          => 'nominal',
                'financial_status'  => 'none',
                'opening_balance'   => '0',
                'status'            => 1  
            ],
            [
                'account_name'      => 'Purchases', //account id : 3
                'description'       => 'Purchases account',
                'type'              => 2,
                'relation'          => 'nominal',
                'financial_status'  => 'none',
                'opening_balance'   => '0',
                'status'            => 1  
            ],
            [
                'account_name'      => 'Labour Wage', //account id : 4
                'description'       => 'Labour wage account',
                'type'              => 2,
                'relation'          => 'nominal',
                'financial_status'  => 'none',
                'opening_balance'   => '0',
                'status'            => 1  
            ],
            [
                'account_name'      => 'Employee Salary', //account id : 5
                'description'       => 'Employee salary account',
                'type'              => 2,
                'relation'          => 'nominal',
                'financial_status'  => 'none',
                'opening_balance'   => '0',
                'status'            => 1  
            ],
            [
                'account_name'      => 'Account Opening Balance', //account id : 6
                'description'       => 'Account opening Balance account',
                'type'              => 2,
                'relation'          => 'nominal',
                'financial_status'  => 'none',
                'opening_balance'   => '0',
                'status'            => 1  
            ],
            [
                'account_name'      => 'temp_1', //account id : 7
                'description'       => 'temp_1 account',
                'type'              => 2,
                'relation'          => 'nominal',
                'financial_status'  => 'none',
                'opening_balance'   => '0',
                'status'            => 0  
            ],
            [
                'account_name'      => 'temp_2', //account id : 8
                'description'       => 'temp_2 account',
                'type'              => 2,
                'relation'          => 'nominal',
                'financial_status'  => 'none',
                'opening_balance'   => '0',
                'status'            => 0  
            ],
            [
                'account_name'      => 'temp_3', //account id : 9
                'description'       => 'temp_3',
                'type'              => 2,
                'relation'          => 'nominal',
                'financial_status'  => 'none',
                'opening_balance'   => '0',
                'status'            => 0  
            ],
            [
                'account_name'      => 'temp_4', //account id : 10
                'description'       => 'temp_4 account',
                'type'              => 2,
                'relation'          => 'nominal',
                'financial_status'  => 'none',
                'opening_balance'   => '0',
                'status'            => 0  
            ]
        ]);

        DB::table('account_details')->insert([
            [
                'account_id'    => '01',
                'name'          => 'Cash account',
                'address'       => '',
                'image'         => '/images/real.jpg',
                'status'        => 1
            ],
            [
                'account_id'    => '02',
                'name'          => 'Sales account',
                'address'       => '',
                'image'         => '/images/real.jpg',
                'status'        => 1
            ],
            [
                'account_id'    => '03',
                'name'          => 'Purchases account',
                'address'       => '',
                'image'         => '/images/real.jpg',
                'status'        => 1  
            ],
            [
                'account_id'    => '04',
                'name'          => 'Labour wage account',
                'address'       => '',
                'image'         => '/images/real.jpg',
                'status'        => 1  
            ],
            [
                'account_id'    => '05',
                'name'          => 'Employee Salary',
                'address'       => '',
                'image'         => '/images/real.jpg',
                'status'        => 1  
            ],
            [
                'account_id'    => '06',
                'name'          => 'Account Opening Balance account',
                'address'       => '',
                'image'         => '/images/real.jpg',
                'status'        => 1  
            ],
            [
                'account_id'    => '07',
                'name'          => 'temp_1 account',
                'address'       => '',
                'image'         => '/images/real.jpg',
                'status'        => 0
            ],
            [
                'account_id'    => '08',
                'name'          => 'temp_2 account',
                'address'       => '',
                'image'         => '/images/real.jpg',
                'status'        => 0
            ],
            [
                'account_id'    => '09',
                'name'          => 'temp_3 account',
                'address'       => '',
                'image'         => '/images/real.jpg',
                'status'        => 0
            ],
            [
                'account_id'    => '10',
                'name'          => 'temp_4 account',
                'address'       => '',
                'image'         => '/images/real.jpg',
                'status'        => 0
            ]
        ]);
    }
}
