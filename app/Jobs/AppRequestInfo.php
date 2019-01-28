<?php

namespace App\Jobs;

use App\Models\ActiveRecord\ARPFAppRequestLog;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

/**
 * app接口请求访问日志记录
 * Class AppRequestInfo
 * @package App\Jobs
 */
class AppRequestInfo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $info = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $info = [])
    {
        $this->info = $info;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!empty($this->info)) {
            ARPFAppRequestLog::addInfo($this->info);
        } else {
            Log::error("消息内容异常：", $this->info);
        }
    }
}
