<?php

namespace App\Console\Commands;

use App\TimeReport;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;


class ShowTheBestWorkers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'time:report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show the best time report of never seen';

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
     * @return int
     */
    public function handle()
    {
        $startDate = $this->ask('Enter the start date (M/d/Y):');
        $finishDate = $this->ask('Enter the finish date (M/d/Y):');

        $isPassedValidation = $this->validateData($startDate, $finishDate);

        if ($isPassedValidation) {
            $startDate = Carbon::createFromFormat("M/d/Y", $startDate)->format("Y-m-d");
            $finishDate = Carbon::createFromFormat("M/d/Y", $finishDate)->format("Y-m-d");

            $timeReports = $this->getTimeReports($startDate, $finishDate);

            $dateReports = $timeReports->groupBy('date');

            $this->processDateReports($dateReports);
        }
    }

    /**
     * Process and output date reports
     *
     * @param $dateReports
     */
    private function processDateReports($dateReports)
    {
        foreach ($dateReports as $date => $dateReport) {
            $bestDateEmployees = $dateReport->groupBy('employee_id')->sortByDesc(function ($item) {
                return $item->sum('hours');
            })->slice(0, 3);

            $this->outputBestDateEmployees($date, $bestDateEmployees);
        }
    }

    /**
     * Get time report for specific date range
     *
     * @param $startDate
     * @param $finishDate
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    private function getTimeReports($startDate, $finishDate)
    {
        return TimeReport::with('employee')
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $finishDate)
            ->orderBy('date')
            ->get();
    }

    /**
     * Validate start and finish dates
     *
     * @param $startDate
     * @param $finishDate
     * @return bool
     */
    private function validateData($startDate, $finishDate)
    {
        $validator = Validator::make([
            'startDate' => $startDate,
            'finishDate' => $finishDate
        ], [
            'startDate' => 'required|date_format:M/d/Y|before:finishDate',
            'finishDate' => 'required|date_format:M/d/Y|after_or_equal:startDate'
        ]);

        if ($validator->fails()) {
            $this->info('See error messages below:');

            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return false;
        }

        return true;
    }

    /**
     * Output in console best employees for specific date
     *
     * @param $date
     * @param $bestEmployees
     */
    private function outputBestDateEmployees($date, $bestEmployees)
    {
        $output = Carbon::createFromFormat("Y-m-d", $date)->format("l") . " | ";

        foreach ($bestEmployees as $bestEmployee) {
            $output .= $bestEmployee->first()->employee->name . " (".$bestEmployee->sum('hours'). " hours) | ";
        }

        $this->info($output);
    }
}
