<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Http\Controllers\BMAASDBController;
use App\Http\Controllers\RackNController;
use Illuminate\Support\Facades\Auth;
class DeployKubernetesCluster implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $request;
    protected $user;
    public $timeout = 3600;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(object $request,$user)
    {
	    $this->request = $request;
	    $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
	    //print_r($this->user);
	    //print_r($this->request);
	    //sleep(50);
	    $RackNController = new RackNController();
	    $RackNController->ProcessKubernetesOrder($this->request,$this->user);
    }
}
