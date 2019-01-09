<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class LoanBillCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'LoanBill:Synchronize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loan Bill Command';

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
        //
        echo 1;
    }
}
