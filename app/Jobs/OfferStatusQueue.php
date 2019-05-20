<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Http\Traits\OfferStatus;

class OfferStatusQueue implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
    use OfferStatus;
    protected $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $d = $this->data;
        return OfferStatus::retrieve($d['participant'],$d['bid_id'],$d['resource_id']);
    }
}

