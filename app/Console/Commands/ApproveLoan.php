<?php

namespace App\Console\Commands;

use App\Models\Loan;
use App\Services\LoanService;
use Illuminate\Console\Command;

class ApproveLoan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loan:approve {loan : The id of the loan to approve}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Approve a loan and generate payments';

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
     * @param LoanService $loanService
     * @return int
     */
    public function handle(LoanService $loanService): int
    {
        try{
            $loan = Loan::find($this->argument('loan'));
            if(! $loan) {
                throw new \Exception("Loan with id: {$this->argument('loan')} not found!");
            }
            $loanService->approve($loan, now()->format('Y-m-d'));
            $this->info("Loan with id {$this->argument('loan')} successfully approved");
            return 0;
        } catch (\Exception $error) {
            $this->error($error->getMessage());
            return 1;
        }
    }
}
