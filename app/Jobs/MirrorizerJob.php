<?php

namespace App\Jobs;

use App\Models\Upload;

class MirrorizerJob extends Job
{

    protected $upload;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Upload $upload)
    {
        $this->upload = $upload;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        
    }
}
