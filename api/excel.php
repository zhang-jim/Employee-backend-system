<?php
// 引入 Composer 的自動載入檔案
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$host = 'localhost';
$username = 'backend_system';
$password = 'KzxYR6aGbPGp';
$database = 'backend_system';
$start_date = "2023-10-01";
$end_date = "2023-10-31";
$data = array();

$link = mysqli_connect($host, $username, $password, $database) or die("mysql connect error!");

// 創建一個新的 Spreadsheet 物件
$spreadsheet = new Spreadsheet();

// 選取工作表
$sheet = $spreadsheet->getActiveSheet();

// 設置工作表標題
$sheet->setCellValue('A1', 'date');
$sheet->setCellValue('B1', 'name');
$sheet->setCellValue('C1', 'onlineTime');
$sheet->setCellValue('D1', 'offlineTime');
$sheet->setCellValue('E1', 'workstatus');

// 資料列數
$sql = "SELECT `name`,`date`,`onlineTime`,`offlineTime`,`workstatus` FROM `time` WHERE `date` >= '$start_date' AND `date` <= '$end_date' ORDER BY `date` ASC";
$result = $link->query($sql);

while ($row = mysqli_fetch_assoc($result)) {
    array_push($data,array($row["date"],$row["name"],$row["onlineTime"],$row["offlineTime"],$row["workstatus"]));
};

// 寫入資料到工作表
foreach ($data as $rowIndex => $rowData) {
    $rowNumber = $rowIndex + 2; // 從第二行開始
    $sheet->setCellValue('A' . $rowNumber, $rowData[0]);
    $sheet->setCellValue('B' . $rowNumber, $rowData[1]);
    $sheet->setCellValue('C' . $rowNumber, $rowData[2]);
    $sheet->setCellValue('D' . $rowNumber, $rowData[3]);
    $sheet->setCellValue('E' . $rowNumber, $rowData[4]);
}

// 儲存 Excel 檔案
$writer = new Xlsx($spreadsheet);
$filename = 'example.xlsx'; // 檔名
$writer->save($filename);

// 下載 Excel 檔案
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
readfile($filename);
unlink($filename); // 下載完後刪除暫存的檔案
