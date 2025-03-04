<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
// Normal Group

use Illuminate\Http\Request;

$api->group(['prefix' => 'api/normal', 'middleware' => ['cors'], 'namespace' => 'V1\Normal\Controllers'], function ($api) {
    $api->get('/', function () {
        return ['status' => 'NORMAL API OK!'];
    });
    $api->get('/file/{fileName}', function ($fileName, Request $request) {
        $fileName = str_replace('-', '.', $fileName);
        // Read file image from storage
        $by = $request->get('by', '');
        $path = storage_path('uploads/' . (!empty($by) ? $by . '/' : '') . $fileName);

        if (!file_exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found',
            ]);
        }

        $file = file_get_contents($path);
        $type = mime_content_type($path);

        //Resize image
        if ((isset($_GET['w']) && isset($_GET['h'])) || isset($_GET['s'])) {

            $s = $_GET['s'] ?? null;
            $w = $_GET['w'] ?? null;
            $h = $_GET['h'] ?? null;

            // tạo bản sao của ảnh
            $srcImage = imagecreatefromjpeg($path);

            if (isset($s)) {
                // tính toán kích thước mới của ảnh dựa trên chiều rộng mới
                $w = $s;
                $ratio = $s / imagesx($srcImage);
                $h = $ratio * imagesy($srcImage);
            }
            // tạo bản sao mới của ảnh với kích thước mới
            $dstImage = imagecreatetruecolor($w, $h);
            imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $w, $h, imagesx($srcImage), imagesy($srcImage));

            // lưu ảnh mới vào đường dẫn khác
            if (isset($by)) {
                if (!file_exists(storage_path("uploads/$by/resize"))) {
                    mkdir(storage_path("uploads/$by/resize"), 0777, true);
                }
                imagejpeg($dstImage, storage_path("uploads/$by/resize") . '/' . $w . 'x' . $h . '-' . $fileName, 100);
                $path = storage_path("uploads/$by/resize") . '/' .  $w . 'x' . $h . '-' . $fileName;
            } else {
                if (!file_exists(storage_path('uploads/resize'))) {
                    mkdir(storage_path('uploads/resize'), 0777, true);
                }
                imagejpeg($dstImage, storage_path('uploads/resize') . '/' . $w . 'x' . $h . '-' . $fileName, 100);
                $path = storage_path('uploads/resize') . '/' .  $w . 'x' . $h . '-' . $fileName;
            }
            // giải phóng bộ nhớ
            imagedestroy($srcImage);
            imagedestroy($dstImage);
        }
        $file = file_get_contents($path);
        $type = mime_content_type($path);
        // Return response
        return response($file, 200)->header("Content-Type", $type)->header("Accept-Ranges", "bytes")->header("Content-Length", filesize($path));
    });
    //Việt Nam List District
    require __DIR__ . '/vietnam_list_district.php';
    //Excel
    require __DIR__ . '/excel.php';

    //Banner
    require __DIR__ . '/banner.php';
    //Category
    require __DIR__ . '/category.php';

    // Session
    require __DIR__ . '/session.php';
    // Cart
    require __DIR__ . '/cart.php';
    // Shipping company
    require __DIR__ . '/shipping_company.php';

    require __DIR__ . '/vnpay.php';
});
