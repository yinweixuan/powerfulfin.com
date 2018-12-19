<?php

namespace App\Console\Commands;

use App\Components\PFException;
use App\Components\ViewUtil;
use App\Models\Message\MsgInit;
use App\Models\Message\MsgSMS;
use Illuminate\Console\Command;

class MsgCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'MsgCommand:sms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'SMS message send Queue';

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
        ViewUtil::setBeginTime();
        for ($i = 0; $i < 1000; $i++) {
            try {
                $result = MsgInit::sendMsg();
                if ($result == null) {
                    sleep(2);
                }

                $endTime = ViewUtil::setEndTime();
                if ($endTime > 60 * 1000) {
                    exit();
                }
            } catch (PFException $exception) {
                continue;
            }
        }
    }
}
