<?php

// =====================================================
// NOTICE PDF DOWNLOAD
// =====================================================
// এই file-এর কাজ:
// 1. Notice ID গ্রহণ করা
// 2. ID অনুযায়ী PDF filename নির্ধারণ করা
// 3. PDF file আছে কিনা check করা
// 4. Browser-এ download শুরু করা
//
// এখন temporary data ব্যবহার করছি।
// পরে Database থেকে notice information নেওয়া হবে.
// =====================================================


// =====================================================
// NOTICE DATA
// -----------------------------------------------------
// আপাতত notices.php-এর temporary data-এর সাথে
// মিল রেখে data রাখা হয়েছে.
// =====================================================

$notices = [

    1 => [
        'title' => 'DUET Admission Circular',
        'pdf' => 'admission-circular.pdf'
    ],

    2 => [
        'title' => 'Admission Test Schedule',
        'pdf' => 'admission-test-schedule.pdf'
    ],

    3 => [
        'title' => 'Application Related Notice',
        'pdf' => 'application-notice.pdf'
    ]

];


// =====================================================
// GET NOTICE ID
// =====================================================

$noticeId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);


// =====================================================
// CHECK NOTICE ID
// =====================================================

if (!$noticeId || !isset($notices[$noticeId])) {

    http_response_code(404);

    exit('Notice not found.');

}


// =====================================================
// GET PDF FILE NAME
// =====================================================

$fileName = $notices[$noticeId]['pdf'];


// =====================================================
// PDF FILE PATH
// -----------------------------------------------------
// __DIR__ = actions folder
// ../       = smart-duet main folder
// =====================================================

$filePath = __DIR__ .
            '/../assets/uploads/notices/' .
            $fileName;


// =====================================================
// CHECK FILE EXISTS
// =====================================================

if (!is_file($filePath)) {

    http_response_code(404);

    exit('Notice PDF is not available.');

}


// =====================================================
// PDF DOWNLOAD HEADERS
// =====================================================

header('Content-Type: application/pdf');

header(
    'Content-Disposition: attachment; filename="' .
    basename($fileName) .
    '"'
);

header(
    'Content-Length: ' . filesize($filePath)
);

header('Cache-Control: no-cache, must-revalidate');


// =====================================================
// SEND PDF FILE TO BROWSER
// =====================================================

readfile($filePath);

exit;

?>