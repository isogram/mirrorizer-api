<?php

namespace App\Console\Commands;

use App\Tools\GoogleDrive;
use Illuminate\Console\Command;

class GoogleDriveCredentials extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gdrive:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Google Drive credentials generator';

    protected $gdrive;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(GoogleDrive $gdrive)
    {
        parent::__construct();

        $this->gdrive = $gdrive;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $this->gdrive->generateCredentials();

    }
}