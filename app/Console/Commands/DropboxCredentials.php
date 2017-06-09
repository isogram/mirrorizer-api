<?php

namespace App\Console\Commands;

use App\Tools\Dropbox;
use Illuminate\Console\Command;

class DropboxCredentials extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dropbox:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dropbox credentials generator';

    protected $dropbox;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Dropbox $dropbox)
    {
        parent::__construct();

        $this->dropbox = $dropbox;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $this->dropbox->generateCredentials();

    }
}