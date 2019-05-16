<?php

namespace App\Services;

use App\Exports\OrderExport;
use Maatwebsite\Excel\Facades\Excel;
use Rs\JsonLines\JsonLines;
use Spatie\ArrayToXml\ArrayToXml;

class ExportService
{
    /**
     * Exporting the data
     *
     * @param  string $fileName
     * @param  array  $data
     * @return boolean
     */
    public function export($fileName, $data)
    {
        $fileType = explode('.', $fileName)[1];

        switch ($fileType) {
            case 'xml':
                $xml = ArrayToXml::convert(['__numeric' => $data]);
                file_put_contents(storage_path('app/' . $fileName), $xml);
                break;

            case 'json':
                $json = json_encode($data);
                file_put_contents(storage_path('app/' . $fileName), $json);
                break;

            case 'jsonl':
            case 'txt':
                (new JsonLines())->enlineToFile($data, storage_path('app/' . $fileName));
                break;

            case 'xls':
            case 'xlsx':
            case 'csv':
            case 'tsv':
            case 'ods':
            case 'html':
            case 'pdf':
                $export = new OrderExport($data);
                Excel::store($export, $fileName);
                break;

            default:
                abort(500, 'Unrecognized format!');
                break;
        }
    }
}