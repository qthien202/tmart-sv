<?php

namespace App\Supports;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SERVICE_Excel
{
    public static function readExcel(Request $request)
    {
        $file = $request->file('file');
        if (!$file) {
            return response()->json([
                'message' => 'File not found'
            ], 404);
        }
        $reader = IOFactory::createReaderForFile($file);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        foreach ($rows as $key => $row) {
            if ($key == 0) {
                continue;
            }
            $rows[$key] = array_combine($rows[0], $row);
        }
        $count = count($rows);
        // Return data with count row
        return [
            'count' => $count,
            'data' => $rows
        ];

        
    }
}
