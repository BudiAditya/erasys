<?php
$phpExcel = new PHPExcel();
$headers = array(
    'Content-Type: application/vnd.ms-excel'
, 'Content-Disposition: attachment;filename="print-mutasi-stock.xls"'
, 'Cache-Control: max-age=0'
);
$writer = new PHPExcel_Writer_Excel5($phpExcel);
// Excel MetaData
$phpExcel->getProperties()->setCreator("Erasystem Infotama Inc (c) Budi Aditya")->setTitle("Print Laporan")->setCompany("Erasystem Infotama Inc");
$sheet = $phpExcel->getActiveSheet();
$sheet->setTitle("Mutasi Stock Barang Per Periode");
//helper for styling
$center = array("alignment" => array("horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
$right = array("alignment" => array("horizontal" => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT));
$allBorders = array("borders" => array("allborders" => array("style" => PHPExcel_Style_Border::BORDER_THIN)));
$idrFormat = array("numberformat" => array("code" => '_([$-421]* #,##0_);_([$-421]* (#,##0);_([$-421]* "-"??_);_(@_)'));
$row = 1;
$sheet->setCellValue("A$row",$company_name);
// Hmm Reset Pointer
$sheet->getStyle("A1");
$sheet->setShowGridlines(false);
$row++;
$sheet->setCellValue("A$row","STOCK BARANG PER PERIODE");
$row++;
$sheet->setCellValue("A$row","Cabang/Gudang: ".$userCabCode);
$row++;
$sheet->setCellValue("A$row", "Dari Tgl. " . date('d-m-Y', $startDate) . " - " . date('d-m-Y', $endDate));
$row++;
$sheet->setCellValue("A$row","No.");
$sheet->setCellValue("B$row","Kode");
$sheet->setCellValue("C$row","Nama Barang");
$sheet->setCellValue("D$row","Satuan");
$sheet->setCellValue("E$row","Awal");
$sheet->setCellValue("F$row","Masuk");
if ($rType == 1) {
    $sheet->setCellValue("J$row", "Keluar");
    $sheet->setCellValue("N$row", "Koreksi");
    $sheet->setCellValue("O$row", "Stock");
}else{
    $sheet->setCellValue("G$row", "Keluar");
    $sheet->setCellValue("H$row", "Koreksi");
    $sheet->setCellValue("I$row", "Stock");
    if ($rType == 3){
        $sheet->setCellValue("J$row", "*Hrg Beli*");
        $sheet->setCellValue("K$row", "Nilai Stock");
    }elseif ($rType == 4){
        $sheet->setCellValue("J$row", "*Hrg Jual*");
        $sheet->setCellValue("K$row", "Nilai Stock");
    }
}
$str = $row;
if ($rType == 1) {
    $row++;
    $sheet->setCellValue("F$row", "Pembelian");
    $sheet->setCellValue("G$row", "Produksi");
    $sheet->setCellValue("H$row", "Kiriman");
    $sheet->setCellValue("I$row", "Retur");
    $sheet->setCellValue("J$row", "Penjualan");
    $sheet->setCellValue("K$row", "Produksi");
    $sheet->setCellValue("L$row", "Dikirim");
    $sheet->setCellValue("M$row", "Retur");
    $sheet->mergeCells("A$str:A$row");
    $sheet->mergeCells("B$str:B$row");
    $sheet->mergeCells("C$str:C$row");
    $sheet->mergeCells("D$str:D$row");
    $sheet->mergeCells("F$str:I$str");
    $sheet->mergeCells("J$str:M$str");
    $sheet->mergeCells("N$str:N$row");
    $sheet->mergeCells("O$str:O$row");
    $sheet->getStyle("A$str:O$row")->applyFromArray(array_merge($center, $allBorders));
    $str = $row;
}elseif ($rType == 2){
    $sheet->getStyle("A$str:I$row")->applyFromArray(array_merge($center, $allBorders));
}else{
    $sheet->getStyle("A$str:K$row")->applyFromArray(array_merge($center, $allBorders));
}
$nmr = 0;
if($mstock != null) {
    while ($rpt = $mstock->FetchAssoc()) {
        $row++;
        $nmr++;
        $sheet->setCellValue("A$row",$nmr);
        $sheet->setCellValue("B$row",$rpt["item_code"],PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->setCellValue("C$row",$rpt["item_name"]);
        $sheet->setCellValue("D$row",$rpt["satuan"]);
        $sheet->setCellValue("E$row",$rpt["sAwal"]);
        if ($rType == 1) {
            $sheet->setCellValue("F$row", $rpt["sBeli"]);
            $sheet->setCellValue("G$row", $rpt["sAsyin"]);
            $sheet->setCellValue("H$row", $rpt["sXin"]);
            $sheet->setCellValue("I$row", $rpt["sRjual"]);
            $sheet->setCellValue("J$row", $rpt["sJual"]);
            $sheet->setCellValue("K$row", $rpt["sAsyout"]);
            $sheet->setCellValue("L$row", $rpt["sXout"]);
            $sheet->setCellValue("M$row", $rpt["sRbeli"]);
            $sheet->setCellValue("N$row", $rpt["sKoreksi"]);
            $sheet->setCellValue("O$row", "=((E$row+F$row+G$row+H$row+I$row)-(J$row+K$row+L$row+M$row))+N$row");
            $sheet->getStyle("A$row:O$row")->applyFromArray(array_merge($allBorders));
        }else{
            $sheet->setCellValue("F$row", $rpt["sBeli"]+$rpt["sAsyin"]+$rpt["sXin"]+$rpt["sRjual"]);
            $sheet->setCellValue("G$row", $rpt["sJual"]+$rpt["sAsyout"]+$rpt["sXout"]+$rpt["sRbeli"]);
            $sheet->setCellValue("H$row", $rpt["sKoreksi"]);
            $sheet->setCellValue("I$row", "=((E$row+F$row)-G$row)+H$row");
            if ($rType == 3) {
                $sheet->setCellValue("J$row", $rpt["hrg_beli"]);
                $sheet->setCellValue("K$row", "=I$row*J$row");
                $sheet->getStyle("A$row:K$row")->applyFromArray(array_merge($allBorders));
            }elseif ($rType == 4){
                $sheet->setCellValue("J$row", $rpt["hrg_jual"]);
                $sheet->setCellValue("K$row", "=I$row*J$row");
                $sheet->getStyle("A$row:K$row")->applyFromArray(array_merge($allBorders));
            }else{
                $sheet->getStyle("A$row:I$row")->applyFromArray(array_merge($allBorders));
            }
        }
    }
    $edr = $row;
    $row++;
    $sheet->setCellValue("A$row","TOTAL MUTASI STOCK");
    $sheet->mergeCells("A$row:D$row");
    $sheet->getStyle("A$row")->applyFromArray($center);
    $sheet->setCellValue("E$row","=SUM(E$str:E$edr)");
    $sheet->setCellValue("F$row","=SUM(F$str:F$edr)");
    $sheet->setCellValue("G$row","=SUM(G$str:G$edr)");
    $sheet->setCellValue("H$row","=SUM(H$str:H$edr)");
    $sheet->setCellValue("I$row","=SUM(I$str:I$edr)");
    if ($rType == 1) {
        $sheet->setCellValue("J$row", "=SUM(J$str:J$edr)");
        $sheet->setCellValue("K$row", "=SUM(K$str:K$edr)");
        $sheet->setCellValue("L$row", "=SUM(L$str:L$edr)");
        $sheet->setCellValue("M$row", "=SUM(M$str:M$edr)");
        $sheet->setCellValue("N$row", "=SUM(N$str:N$edr)");
        $sheet->setCellValue("O$row", "=SUM(O$str:O$edr)");
        $sheet->getStyle("E$str:O$row")->applyFromArray($idrFormat);
        $sheet->getStyle("A$row:O$row")->applyFromArray(array_merge($allBorders));
    }else{
        if ($rType > 2){
            $sheet->setCellValue("J$row", "=SUM(J$str:J$edr)");
            $sheet->setCellValue("K$row", "=SUM(K$str:K$edr)");
            $sheet->getStyle("E$str:K$row")->applyFromArray($idrFormat);
            $sheet->getStyle("A$row:K$row")->applyFromArray(array_merge($allBorders));
        }else{
            $sheet->getStyle("E$str:I$row")->applyFromArray($idrFormat);
            $sheet->getStyle("A$row:I$row")->applyFromArray(array_merge($allBorders));
        }
    }
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
