<?php

namespace App\Console\Commands;

use App\Http\Controllers\V1\Normal\Models\Session;
use App\OCProduct;
use App\OCProductEcom;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeleteExpiredSession extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:Delete-Expired-Session';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

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
     * @throws \Exception
     */
    public function handle()
    {
        $now = Carbon::now();
        $seventDaysAgo = $now->subDays(7);
        $result = Session::where("created_at","<=",$seventDaysAgo)->delete();
        if ($result>0) {
            Log::info("Đã xóa ".$result." session");
        }
    }
}