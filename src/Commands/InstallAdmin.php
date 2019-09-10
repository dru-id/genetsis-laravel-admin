<?php

namespace Genetsis\Admin\Commands;

use App\User;
use Genetsis\Admin\Database\Seeds\PermissionSeeder;
use Genetsis\Admin\Database\Seeds\RolesSeeder;
use Genetsis\Admin\Database\Seeds\UsersTableSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class InstallAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'genetsis-admin:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initial setup: create Roles ';

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
            Artisan::call('db:seed', ['--class' => RolesSeeder::class, '--force' => true]);

            $this->info('Roles created');

            Artisan::call('db:seed', ['--class' => PermissionSeeder::class, '--force' => true]);
            $this->info('Permissions created');

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

    }
}
