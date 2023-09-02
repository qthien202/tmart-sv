<?php

namespace App\Http\Controllers\V1\Auth\Traits;


trait ReportTrait
{
    protected function writeExcelIssueUserReport($fileName, $dir, $data)
    {
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        \Excel::create($fileName, function ($writer) use ($data) {

            $writer->sheet('Report', function ($sheet) use ($data) {
                $sheet->loadView('report_issue_user', $data);
            });
        })->store('xlsx', $dir);

        $fileExported = $fileName . ".xlsx";
        header('Access-Control-Allow-Origin: *');
        readfile("$dir/$fileExported");
    }
}