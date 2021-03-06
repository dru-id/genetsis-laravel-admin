<?php

namespace Genetsis\Admin\Commands;

use Genetsis\Admin\Database\Seeds\ManageDruidAppsSeeder;
use Genetsis\Admin\Database\Seeds\ManageUsersSeeder;
use Genetsis\Admin\Database\Seeds\RolesSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

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
            if ($this->confirm('Do you wish to create Roles?')) {
                Artisan::call('db:seed', ['--class' => RolesSeeder::class, '--force' => true]);
                $this->info('Roles created');
            }

            if ($this->confirm('Do you wish to Manage Users?')) {
                Artisan::call('db:seed', ['--class' => ManageUsersSeeder::class, '--force' => true]);
                $this->info('Permissions created');
            }
            if ($this->confirm('Do you wish to Manage Druid Apps?')) {
                Artisan::call('db:seed', ['--class' => ManageDruidAppsSeeder::class, '--force' => true]);
                $this->info('Permissions created');
            }

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

    }
}
