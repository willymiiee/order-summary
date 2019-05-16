<?php

namespace App\Console\Commands;

use App\Services\DownloadService;
use App\Services\ProcessingService;
use App\Services\ExportService;
use Illuminate\Console\Command;
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

    protected $downloadService, $processingService, $exportService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->downloadService = new DownloadService;
        $this->processingService = new ProcessingService;
        $this->exportService = new ExportService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $input = $this->ask('What is your input file name?');
        $output = $this->ask('What is your preferred output file name?');

        $this->line('Fetching file from s3...');
        try {
            $jsonl = $this->downloadService->downloadFile('s3', 'application/json', $input);
        } catch (\Throwable $th) {
            $this->error('Download failed!');
            return;
        }

        $this->line('Processing the data...');
        $datas = $this->processingService->processData($jsonl);

        $this->line('Exporting...');

        try {
            $this->exportService->export($output, $datas);
            $this->output->success('Export successful!');
        } catch (\Throwable $th) {
            $this->error('Export failed! ' . $th->getMessage());
        }
    }
}
