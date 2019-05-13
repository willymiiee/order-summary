<?php

namespace App\Console\Commands;

use App\Exports\OrderExport;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Rs\JsonLines\JsonLines;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Export extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download & export the data from S3';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected function streamFile($callback, $headers)
    {
        $response = new StreamedResponse($callback, 200, $headers);
        $response->send();
    }

    protected function formatResult($data, $price)
    {
        $totalUnits = count_units($data['items'], 'quantity');

        return [
            'order_id' => $data['order_id'],
            'order_datetime' => Carbon::parse($data['order_date'])->toIso8601String(),
            'total_order_value' => number_format((float) $price, 2, '.', ''),
            'average_unit_price' => number_format((float) $price / $totalUnits, 2, '.', ''),
            'distinct_unit_count' => count($data['items']),
            'total_units_count' => $totalUnits,
            'customer_state' => state_code(ucwords($data['customer']['shipping_address']['state']))
        ];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $filename = $this->ask('What is your preferred output file name?');

        $this->line('Fetching file from s3...');
        $filePath = Storage::disk('s3')->url('challenge-1-in.jsonl');
        $file = download_from_s3('application/json', $filePath);

        $this->line('Converting into array...');
        $json = (new JsonLines())->deline($file);
        $datas = json_decode($json, true);
        $results = [];

        $this->line('Formatting data...');
        $bar = $this->output->createProgressBar(count($datas));
        $bar->start();

        foreach ($datas as $d) {
            $price = gross_value($d['items'], 'quantity', 'unit_price');
            $price = after_discount($price, $d['discounts']);

            if ($price == 0) {
                continue;
            }

            $results[] = $this->formatResult($d, $price);
            $bar->advance();
        }

        $bar->finish();

        $this->line('');
        $this->line('Starting export...');
        $export = new OrderExport($results);
        $this->output->success('Export successful!');
        return Excel::store($export, $filename . '.csv');
    }
}
