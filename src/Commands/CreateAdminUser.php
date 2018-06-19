<?php

namespace Genetsis\Admin\Commands;

use Genetsis\Admin\Database\Seeds\UsersTableSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'genetsis-admin:create-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initial setup: publish resource and create an admin user ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {

            if ($this->confirm('Do you wish to create the Default Admin User?')) {
                Artisan::call('db:seed', ['--class' => UsersTableSeeder::class]);
            } else {
                $email = $this->ask('What is Admin User email?');
                $name = $this->ask('What is Admin User Full name?', false);
                $password = $this->secret('What is password?');

                $rules = array(
                    'email' => 'required|email',
                    'password'  => 'required',
                );
                $validator = \Validator::make(compact('email', 'name', 'password'), $rules);
                if ($validator->fails()) {
                    throw new \Exception($validator->messages());
                }

//                $this->info($email);
//                $this->info($name);
//                $this->info(bcrypt($password));

                DB::table('users')->insert([
                    'name' => $name,
                    'email' => $email,
                    'password' => bcrypt($password),
                ]);
            }

            $this->info('Admin User created');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

    }
}