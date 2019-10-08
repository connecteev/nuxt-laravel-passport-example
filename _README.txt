Installation steps for https://github.com/connecteev/nuxt-laravel-passport-example 
(based on https://github.com/jmschneider/nuxt-laravel-passport-example)

Note: Adopted from the two README files: 
https://github.com/jmschneider/nuxt-laravel-passport-example/blob/master/laravel/README.md
https://github.com/jmschneider/nuxt-laravel-passport-example/blob/master/nuxt/README.md


Steps to get auth working:

1. Set up .env file with DB credentials.
2. php artisan migrate:fresh --seed

3.
OneStepAtATime:laravel kunalpunjabi$ php artisan passport:client
 Which user ID should the client be assigned to?:
 > 1
 What should we name the client?:
 > KP_passport_client
 Where should we redirect the request after authorization? [http://laravel.test/auth/callback]:
 > http://localhost:8000/auth/callback
New client created successfully.
Client ID: 1
Client secret: WW0ZPJa6gTE07P00mJw0v5ASOC78GRBFY5788xpp

4.
OneStepAtATime:laravel kunalpunjabi$ php artisan passport:client --password
 What should we name the password grant client? [Passport Test Password Grant Client]:
 > KP_password_grant_client      
Password grant client created successfully.
Client ID: 2
Client Secret: EWZN9Os1W1kby2XirXzRhjizb9Kk5Nr5UdlWutYu


5. Then populate nuxt/.env with: (copy output from commands above)

LARAVEL_ENDPOINT=http://localhost:8000
PASSPORT_CLIENT_ID=1
PASSPORT_CLIENT_SECRET=WW0ZPJa6gTE07P00mJw0v5ASOC78GRBFY5788xpp
PASSPORT_PASSWORD_GRANT_ID=2
PASSPORT_PASSWORD_GRANT_SECRET=EWZN9Os1W1kby2XirXzRhjizb9Kk5Nr5UdlWutYu


6. On nuxt:
npm run dev

7. Go to http://localhost:3000
and login with credentials for user #1 (added to my seeder class)
admin@admin.com / password
