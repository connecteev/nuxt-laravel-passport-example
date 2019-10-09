<?php

use Illuminate\Database\Seeder;

use App\User;
use App\Tag;
use Faker\Generator as Faker;

// Call using command: php artisan db:seed --class=PassportOauthClientsSeeder
class PassportOauthClientsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(Faker $faker)
    {
        echo "Seeding data for Passport Authentication (oauth_clients table)... \n";

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // simulates the 'php artisan passport:client' command (Passport client)
        DB::insert(
            'insert into oauth_clients (
                id, 
                user_id, 
                name, 
                secret, 
                redirect, 
                personal_access_client, 
                password_client, 
                revoked,
                created_at
                ) values (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                '1',
                '1',
                'KP_passport_client',
                'INAHRlAPHYsb6Xi9hbVaGxsFETxYizFDiKLOtqpv',
                'http://localhost:8001/auth/callback',
                '0',
                '0',
                '0',
                now()
            ]
        );

        // simulates the 'php artisan passport:client --password' command (Password Grant Client)
        DB::insert(
            'insert into oauth_clients (
                id, 
                user_id, 
                name, 
                secret, 
                redirect, 
                personal_access_client, 
                password_client, 
                revoked,
                created_at
                ) values (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                '2',
                NULL,
                'KP_password_grant_client',
                'DOL7iwNKemaCKyxNrgPPOWzWgG3DmvLlRbTObAdr',
                'http://localhost:8001/auth/callback', // default is set to http://localhost
                '0',
                '1',
                '0',
                now()
            ]
        );

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
