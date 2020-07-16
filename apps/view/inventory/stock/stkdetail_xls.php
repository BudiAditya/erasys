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
if ($rType < 5) {
    $sheet->setCellValue("A$row", "STOCK BARANG PER PERIODE");
    $row++;
    $sheet->setCellValue("A$row", "Cabang/Gudang: " . $userCabCode);
    $row++;
    $sheet->setCellValue("A$row", "Dari Tgl. " . date('d-m-Y', $startDate) . " - " . date('d-m-Y', $endDate));
    $row++;
    $sheet->setCellValue("A$row", "No.");
    $sheet->setCellValue("B$row", "Kode");
    $sheet->setCellValue("C$row", "Nama Barang");
    $sheet->setCellValue("D$row", "Satuan");
    $sheet->setCellValue("E$row", "Awal");
    $sheet->setCellValue("F$row", "Masuk");
    if ($rType == 1) {
        $sheet->setCellValue("J$row", "Keluar");
        $sheet->setCellValue("N$row", "Koreksi");
        $sheet->setCellValue("O$row", "Stock");
    } else {
        $sheet->setCellValue("G$row", "Keluar");
        $sheet->setCellValue("H$row", "Koreksi");
        $sheet->setCellValue("I$row", "Stock");
        if ($rType == 3) {
            $sheet->setCellValue("J$row", "*Hrg Beli*");
            $sheet->setCellValue("K$row", "Nilai Stock");
        } elseif ($rType == 4) {
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
    } elseif ($rType == 2) {
        $sheet->getStyle("A$str:I$row")->applyFromArray(array_merge($center, $allBorders));
    } else {
        $sheet->getStyle("A$str:K$row")->applyFromArray(array_merge($center, $allBorders));
    }
    $nmr = 0;
    if ($mstock != null) {
        while ($rpt = $mstock->FetchAssoc()) {
            $row++;
            $nmr++;
            $sheet->setCellValue("A$row", $nmr);
            $sheet->setCellValue("B$row", $rpt["item_code"], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue("C$row", $rpt["item_name"]);
            $sheet->setCellValue("D$row", $rpt["satuan"]);
            $sheet->setCellValue("E$row", $rpt["sAwal"]);
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
            } else {
                $sheet->setCellValue("F$row", $rpt["sBeli"] + $rpt["sAsyin"] + $rpt["sXin"] + $rpt["sRjual"]);
                $sheet->setCellValue("G$row", $rpt["sJual"] + $rpt["sAsyout"] + $rpt["sXout"] + $rpt["sRbeli"]);
                $sheet->setCellValue("H$row", $rpt["sKoreksi"]);
                $sheet->setCellValue("I$row", "=((E$row+F$row)-G$row)+H$row");
                if ($rType == 3) {
                    $sheet->setCellValue("J$row", $rpt["hrg_beli"]);
                    $sheet->setCellValue("K$row", "=I$row*J$row");
                    $sheet->getStyle("A$row:K$row")->applyFromArray(array_merge($allBorders));
                } elseif ($rType == 4) {
                    $sheet->setCellValue("J$row", $rpt["hrg_jual"]);
                    $sheet->setCellValue("K$row", "=I$row*J$row");
                    $sheet->getStyle("A$row:K$row")->applyFromArray(array_merge($allBorders));
                } else {
                    $sheet->getStyle("A$row:I$row")->applyFromArray(array_merge($allBorders));
                }
            }
        }
        $edr = $row;
        $row++;
        $sheet->setCellValue("A$row", "TOTAL MUTASI STOCK");
        $sheet->mergeCells("A$row:D$row");
        $sheet->getStyle("A$row")->applyFromArray($center);
        $sheet->setCellValue("E$row", "=SUM(E$str:E$edr)");
        $sheet->setCellValue("F$row", "=SUM(F$str:F$edr)");
        $sheet->setCellValue("G$row", "=SUM(G$str:G$edr)");
        $sheet->setCellValue("H$row", "=SUM(H$str:H$edr)");
        $sheet->setCellValue("I$row", "=SUM(I$str:I$edr)");
        if ($rType == 1) {
            $sheet->setCellValue("J$row", "=SUM(J$str:J$edr)");
            $sheet->setCellValue("K$row", "=SUM(K$str:K$edr)");
            $sheet->setCellValue("L$row", "=SUM(L$str:L$edr)");
            $sheet->setCellValue("M$row", "=SUM(M$str:M$edr)");
            $sheet->setCellValue("N$row", "=SUM(N$str:N$edr)");
            $sheet->setCellValue("O$row", "=SUM(O$str:O$edr)");
            $sheet->getStyle("E$str:O$row")->applyFromArray($idrFormat);
            $sheet->getStyle("A$row:O$row")->applyFromArray(array_merge($allBorders));
        } else {
            if ($rType > 2) {
                $sheet->setCellValue("J$row", "=SUM(J$str:J$edr)");
                $sheet->setCellValue("K$row", "=SUM(K$str:K$edr)");
                $sheet->getStyle("E$str:K$row")->applyFromArray($idrFormat);
                $sheet->getStyle("A$row:K$row")->applyFromArray(array_merge($allBorders));
            } else {
                $sheet->getStyle("E$str:I$row")->applyFromArray($idrFormat);
                $sheet->getStyle("A$row:I$row")->applyFromArray(array_merge($allBorders));
            }
        }
        $row++;
    }
}else{
    $sheet->setCellValue("A$row", "REKAPITULASI HPP");
    $row++;
    $sheet->setCellValue("A$row", "Cabang/Gudang: " . $userCabCode);
    $row++;
    $sheet->setCellValue("A$row", "Dari Tgl. " . date('d-m-Y', $startDate) . " - " . date('d-m-Y', $endDate));
    $row++;
    $str = $row;
    $sheet->setCellValue("A$row", "No.");
    $sheet->setCellValue("B$row", "Kode");
    $sheet->setCellValue("C$row", "Nama Barang");
    $sheet->setCellValue("D$row", "Satuan");
    $sheet->setCellValue("E$row", "Stok Awal");
    $sheet->setCellValue("H$row", "Barang Masuk");
    $sheet->setCellValue("W$row", "Barang Keluar");
    $sheet->setCellValue("AL$row", "Adjustment");
    $sheet->setCellValue("AO$row", "Stok Akhir");
    $sheet->mergeCells("H$row:V$row");
    $sheet->mergeCells("W$row:AK$row");
    $row++;
    $sheet->setCellValue("H$row", "Pembelian");
    $sheet->setCellValue("K$row", "Produksi");
    $sheet->setCellValue("N$row", "Transfer");
    $sheet->setCellValue("Q$row", "Retur");
    $sheet->setCellValue("T$row", "Saldo");
    $sheet->setCellValue("W$row", "Penjualan");
    $sheet->setCellValue("Z$row", "Produksi");
    $sheet->setCellValue("AC$row", "Transfer");
    $sheet->setCellValue("AF$row", "Retur");
    $sheet->setCellValue("AI$row", "Saldo");
    $sheet->mergeCells("H$row:J$row");
    $sheet->mergeCells("K$row:M$row");
    $sheet->mergeCells("N$row:P$row");
    $sheet->mergeCells("Q$row:S$row");
    $sheet->mergeCells("T$row:V$row");
    $sheet->mergeCells("W$row:Y$row");
    $sheet->mergeCells("Z$row:AB$row");
    $sheet->mergeCells("AC$row:AE$row");
    $sheet->mergeCells("AF$row:AH$row");
    $sheet->mergeCells("AI$row:AK$row");
    $sheet->mergeCells("E$str:G$row");
    $sheet->mergeCells("AL$str:AN$row");
    $sheet->mergeCells("AO$str:AQ$row");
    $row++;
    $sheet->setCellValue("E$row", "QTY");
    $sheet->setCellValue("F$row", "Harga");
    $sheet->setCellValue("G$row", "Jumlah");
    $sheet->setCellValue("H$row", "QTY");
    $sheet->setCellValue("I$row", "Harga");
    $sheet->setCellValue("J$row", "Jumlah");
    $sheet->setCellValue("K$row", "QTY");
    $sheet->setCellValue("L$row", "Harga");
    $sheet->setCellValue("M$row", "Jumlah");
    $sheet->setCellValue("N$row", "QTY");
    $sheet->setCellValue("O$row", "Harga");
    $sheet->setCellValue("P$row", "Jumlah");
    $sheet->setCellValue("Q$row", "QTY");
    $sheet->setCellValue("R$row", "Harga");
    $sheet->setCellValue("S$row", "Jumlah");
    $sheet->setCellValue("T$row", "QTY");
    $sheet->setCellValue("U$row", "Harga");
    $sheet->setCellValue("V$row", "Jumlah");
    $sheet->setCellValue("W$row", "QTY");
    $sheet->setCellValue("X$row", "Harga");
    $sheet->setCellValue("Y$row", "Jumlah");
    $sheet->setCellValue("Z$row", "QTY");
    $sheet->setCellValue("AA$row", "Harga");
    $sheet->setCellValue("AB$row", "Jumlah");
    $sheet->setCellValue("AC$row", "QTY");
    $sheet->setCellValue("AD$row", "Harga");
    $sheet->setCellValue("AE$row", "Jumlah");
    $sheet->setCellValue("AF$row", "QTY");
    $sheet->setCellValue("AG$row", "Harga");
    $sheet->setCellValue("AH$row", "Jumlah");
    $sheet->setCellValue("AI$row", "QTY");
    $sheet->setCellValue("AJ$row", "Harga");
    $sheet->setCellValue("AK$row", "Jumlah");
    $sheet->setCellValue("AL$row", "QTY");
    $sheet->setCellValue("AM$row", "Harga");
    $sheet->setCellValue("AN$row", "Jumlah");
    $sheet->setCellValue("AO$row", "QTY");
    $sheet->setCellValue("AP$row", "Harga");
    $sheet->setCellValue("AQ$row", "Jumlah");
    $sheet->mergeCells("A$str:A$row");
    $sheet->mergeCells("B$str:B$row");
    $sheet->mergeCells("C$str:C$row");
    $sheet->mergeCells("D$str:D$row");
    $sheet->getStyle("A$str:AQ$row")->applyFromArray(array_merge($center, $allBorders));
    $nmr = 0;
    $hrg = 0;
    if ($mstock != null) {
        $str = $row +1;
        $tqt = 0;
        $qty = 0;
        $jhr = 0;
        while ($rpt = $mstock->FetchAssoc()) {
            $tqt = ($rpt["sAwal"] + $rpt["sBeli"] + $rpt["sAsyin"] + $rpt["sXin"] + $rpt["sRjual"]) + ($rpt["sJual"] + $rpt["sAsyout"] + $rpt["sXout"] + $rpt["sRbeli"]) + $rpt["sKoreksi"];
            if ($tqt <> 0) {
                $row++;
                $nmr++;
                $sheet->setCellValue("A$row", $nmr);
                $sheet->setCellValue("B$row", $rpt["item_code"], PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValue("C$row", $rpt["item_name"]);
                $sheet->setCellValue("D$row", $rpt["satuan"]);
                //saldo awal
                $sheet->setCellValue("E$row", $rpt["sAwal"]);
                if ($rpt["nsAwal"] <> 0 && $rpt["sAwal"] <> 0) {
                    $hrg = round($rpt["nsAwal"] / $rpt["sAwal"], 2);
                } else {
                    $hrg = 0;
                }
                $sheet->setCellValue("F$row", $hrg);
                $sheet->setCellValue("G$row", "=Round(E$row*F$row,2)");
                //pembelian
                $sheet->setCellValue("H$row", $rpt["sBeli"]);
                if ($rpt["nsBeli"] <> 0 && $rpt["sBeli"] <> 0) {
                    $hrg = round($rpt["nsBeli"] / $rpt["sBeli"], 2);
                } else {
                    $hrg = 0;
                }
                $sheet->setCellValue("I$row", $hrg);
                $sheet->setCellValue("J$row", "=Round(H$row*I$row,2)");
                //produksi
                $sheet->setCellValue("K$row", $rpt["sAsyin"]);
                if ($rpt["nsAsyin"] <> 0 && $rpt["sAsyin"] <> 0) {
                    $hrg = round($rpt["nsAsyin"] / $rpt["sAsyin"], 2);
                } else {
                    $hrg = 0;
                }
                $sheet->setCellValue("L$row", $hrg);
                $sheet->setCellValue("M$row", "=Round(K$row*L$row,2)");
                //transfer masuk
                $sheet->setCellValue("N$row", $rpt["sXin"]);
                if ($rpt["nsXin"] <> 0 && $rpt["sXin"] <> 0) {
                    $hrg = round($rpt["nsXin"] / $rpt["sXin"], 2);
                } else {
                    $hrg = 0;
                }
                $sheet->setCellValue("O$row", $hrg);
                $sheet->setCellValue("P$row", "=Round(N$row*O$row,2)");
                //retur pembelian
                $sheet->setCellValue("Q$row", $rpt["sRbeli"]);
                if ($rpt["nsRbeli"] <> 0 && $rpt["sRbeli"] <> 0) {
                    $hrg = round($rpt["nsRbeli"] / $rpt["sRbeli"], 2);
                } else {
                    $hrg = 0;
                }
                $sheet->setCellValue("R$row", $hrg);
                $sheet->setCellValue("S$row", "=Round(Q$row*Q$row,2)");
                //saldo barang masuk
                $sheet->setCellValue("T$row", "=H$row+K$row+N$row-Q$row");
                $sheet->setCellValue("U$row", "=IF(AND(V$row<>0,T$row<>0),Round(V$row/T$row,2),0)");
                $sheet->setCellValue("V$row", "=J$row+M$row+P$row-S$row");
                //penjualan
                $sheet->setCellValue("W$row", $rpt["sJual"]);
                if ($rpt["nsJual"] <> 0 && $rpt["sJual"] <> 0) {
                    $hrg = round($rpt["nsJual"] / $rpt["sJual"], 2);
                } else {
                    $hrg = 0;
                }
                $sheet->setCellValue("X$row", $hrg);
                $sheet->setCellValue("Y$row", "=Round(W$row*X$row,2)");
                //produksi
                $sheet->setCellValue("Z$row", $rpt["sAsyout"]);
                if ($rpt["nsAsyout"] <> 0 && $rpt["sAsyout"] <> 0) {
                    $hrg = round($rpt["nsAsyout"] / $rpt["sAsyout"], 2);
                } else {
                    $hrg = 0;
                }
                $sheet->setCellValue("AA$row", $hrg);
                $sheet->setCellValue("AB$row", "=Round(Z$row*AA$row,2)");
                //transfer keluar
                $sheet->setCellValue("AC$row", $rpt["sXout"]);
                if ($rpt["nsXout"] <> 0 && $rpt["sXout"] <> 0) {
                    $hrg = round($rpt["nsXout"] / $rpt["sXout"], 2);
                } else {
                    $hrg = 0;
                }
                $sheet->setCellValue("AD$row", $hrg);
                $sheet->setCellValue("AE$row", "=Round(AC$row*AD$row,2)");
                //retur penjualan
                $sheet->setCellValue("AF$row", $rpt["sRjual"]);
                if ($rpt["nsRjual"] <> 0 && $rpt["sRbeli"] <> 0) {
                    $hrg = round($rpt["nsRjual"] / $rpt["sRjual"], 2);
                } else {
                    $hrg = 0;
                }
                $sheet->setCellValue("AG$row", $hrg);
                $sheet->setCellValue("AH$row", "=Round(AF$row*AG$row,2)");
                //saldo barang keluar
                $sheet->setCellValue("AI$row", "=W$row+Z$row+AC$row-AF$row");
                $sheet->setCellValue("AJ$row", "=IF(AND(AI$row<>0,AK$row<>0),Round(AK$row/AI$row,2),0)");
                $sheet->setCellValue("AK$row", "=Y$row+AB$row+AE$row-AH$row");
                //koreksi
                $sheet->setCellValue("AL$row", $rpt["sKoreksi"]);
                if ($rpt["sKoreksi"] <> 0 && $rpt["sKoreksi"] <> 0) {
                    $hrg = round($rpt["sKoreksi"] / $rpt["sKoreksi"], 2);
                } else {
                    $hrg = 0;
                }
                $sheet->setCellValue("AM$row", $hrg);
                $sheet->setCellValue("AN$row", "=Round(AL$row*AM$row,2)");
                //saldo akhir
                $sheet->setCellValue("AO$row", "=E$row+T$row+AI$row+AL$row");
                $sheet->setCellValue("AP$row", "=IF(AND(AO$row<>0,AQ$row<>0),Round(AQ$row/AO$row,2),0)");
                $sheet->setCellValue("AQ$row", "=G$row+V$row+AK$row+AN$row");
            }
        }
        $sheet->getStyle("E$str:AQ$row")->applyFromArray($idrFormat);
        $sheet->getStyle("A$str:AQ$row")->applyFromArray(array_merge($allBorders));
    }
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
