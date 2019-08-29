<?php
$phpExcel = new PHPExcel();
$headers = array(
    'Content-Type: application/vnd.ms-excel'
, 'Content-Disposition: attachment;filename="print-rekap-ar-receipt.xls"'
, 'Cache-Control: max-age=0'
);
$writer = new PHPExcel_Writer_Excel5($phpExcel);
// Excel MetaData
$phpExcel->getProperties()->setCreator("Erasystem Infotama Inc (c) Budi Aditya")->setTitle("Print Laporan")->setCompany("Erasystem Infotama Inc");
$sheet = $phpExcel->getActiveSheet();
$sheet->setTitle("Rekapitulasi AR Invoice");
//helper for styling
$center = array("alignment" => array("horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
$right = array("alignment" => array("horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT));
$allBorders = array("borders" => array("allborders" => array("style" => PHPExcel_Style_Border::BORDER_THIN)));
$idrFormat = array("numberformat" => array("code" => '_([$-421]* #,##0_);_([$-421]* (#,##0);_([$-421]* "-"??_);_(@_)'));
// OK mari kita bikin ini cuma bisa di read-only
//$password = "" . time();
//$sheet->getProtection()->setSheet(true);
//$sheet->getProtection()->setPassword($password);

// FORCE Custom Margin for continous form
/*
$sheet->getPageMargins()->setTop(0)
    ->setRight(0.2)
    ->setBottom(0)
    ->setLeft(0.2)
    ->setHeader(0)
    ->setFooter(0);
*/
$row = 1;
$sheet->setCellValue("A$row",$company_name);
// Hmm Reset Pointer
$sheet->getStyle("A1");
$sheet->setShowGridlines(false);
$row++;
$sheet->setCellValue("A$row","REKAPITULASI PENERIMAAN PIUTANG");
$row++;
$sheet->setCellValue("A$row","Dari Tgl. ".date('d-m-Y',$StartDate)." - ".date('d-m-Y',$EndDate));
$row++;
$sheet->setCellValue("A$row","No.");
$sheet->setCellValue("B$row","Cabang");
$sheet->setCellValue("C$row","Tanggal");
$sheet->setCellValue("D$row","No. Receipt");
$sheet->setCellValue("E$row","Nama Customer");
$sheet->setCellValue("F$row","Cara Bayar");
$sheet->setCellValue("G$row","Kas / Bank");
$sheet->setCellValue("H$row","No. Warkat");
$sheet->setCellValue("I$row","Tgl Warkat");
$sheet->setCellValue("J$row","Penerimaan");
$sheet->setCellValue("K$row","Jumlah");
$sheet->setCellValue("L$row","Keterangan");
$sheet->setCellValue("M$row","Status");
$sheet->getStyle("A$row:M$row")->applyFromArray(array_merge($center, $allBorders));
$nmr = 0;
$str = $row;
if ($Reports != null){
    while ($rpt = $Reports->FetchAssoc()) {
        $row++;
        $nmr++;
        $sheet->setCellValue("A$row",$nmr);
        $sheet->getStyle("A$row")->applyFromArray($center);
        $sheet->setCellValue("B$row",$rpt["cabang_code"]);
        $sheet->setCellValue("C$row",date('d-m-Y',strtotime($rpt["receipt_date"])));
        $sheet->setCellValue("D$row",$rpt["receipt_no"]);
        $sheet->setCellValue("E$row",$rpt["debtor_name"].' ('.$rpt["debtor_code"].')');
        $sheet->setCellValue("F$row",$rpt["cara_bayar"]);
        $sheet->setCellValue("G$row",$rpt["bank_name"]);
        $sheet->setCellValue("H$row",$rpt["warkat_no"]);
        $sheet->setCellValue("I$row",left($rpt["warkat_date"],10));
        $sheet->setCellValue("J$row",$rpt["receipt_descs"]);
        $sheet->setCellValue("K$row",$rpt["receipt_amount"]);
        $sheet->setCellValue("L$row",$rpt["keterangan"]);
        $sheet->setCellValue("M$row",$rpt["status_desc"]);
        $sheet->getStyle("A$row:M$row")->applyFromArray(array_merge($allBorders));
    }
    $edr = $row;
    $row++;
    $sheet->setCellValue("A$row","TOTAL PENERIMAAN");
    $sheet->mergeCells("A$row:J$row");
    $sheet->getStyle("A$row")->applyFromArray($center);
    $sheet->setCellValue("K$row","=SUM(K$str:K$edr)");
    $sheet->getStyle("K$str:K$row")->applyFromArray($idrFormat);
    $sheet->getStyle("A$row:M$row")->applyFromArray(array_merge($allBorders));
    $row++;
}
// Flush to client

foreach ($headers as $header) {
    header($header);
}
// Hack agar client menutup loading dialog box... (Ada JS yang checking cookie ini pada common.js)
$writer->save("php://output");

// Garbage Collector
$phpExcel->disconnectWorksheets();
unset($phpExcel);
ob_flush();
