<?php
/** @var $delivery Delivery */ 
$userName = AclManager::GetInstance()->GetCurrentUser()->RealName;
$fontFamily = "tahoma";
$widths = $pdf->GetWidths();
$y = 0;
$y1 = 95;
$pdf->AddPage();
$pdf->SetY($y);
$pdf->SetFont($fontFamily, "B", 11);
$pdf->Cell(1,5,$delivery->CompanyName, 0, 0, "L");
$pdf->SetFont($fontFamily, "", 11);
$pdf->Cell($pdf->GetPaperWidth(), 5, "SURAT JALAN", 0, 0, "C");
$pdf->Ln(7);
$pdf->SetFont($fontFamily, "", 10);
$pdf->Cell(20, 5, "D/O No.", 0, 0, "L");
$pdf->Cell(4, 5, ":", 0, 0, "L");
$pdf->Cell(4, 5, $delivery->DoNo, 0, 0, "L");
$pdf->SetX(-80,true);
$pdf->SetFont($fontFamily, "", 10);
$pdf->Cell(20, 5, "Kepada Yth.", 0, 0, "L");
$pdf->Cell(4, 5, ":", 0, 0, "L");
$pdf->Cell(4, 5, $delivery->CustomerName, 0, 0, "L");
$pdf->Ln(5);
$pdf->SetFont($fontFamily, "", 10);
$pdf->Cell(20, 5, "Tanggal", 0, 0, "L");
$pdf->Cell(4, 5, ":", 0, 0, "L");
$pdf->Cell(4, 5, $delivery->FormatDoDate(JS_DATE), 0, 0, "L");
$pdf->SetX(-80,true);
$pdf->Cell(20, 5, "Alamat", 0, 0, "L");
$pdf->Cell(4, 5, ":", 0, 0, "L");
$pdf->Cell(4, 5, $delivery->CustomerAddress, 0, 0, "L");
$pdf->Ln(6);
$pdf->SetFont($fontFamily, "", 9);
$pdf->Cell(20, 5, "Mohon diterima dengan baik barang-barang pesanan bapak/ibu sebagai berikut:", 0, 0, "L");
$pdf->SetX(-80,true);
$pdf->Cell(20, 5, "Reff No.", 0, 0, "L");
$pdf->Cell(4, 5, ":", 0, 0, "L");
$pdf->Cell(4, 5, $delivery->DoDescs, 0, 0, "L");
$pdf->Ln(2);
$x = 6;
$y = $pdf->GetY();
//garis datar header
$pdf->SetLineWidth(0.4);
$pdf->Line($x,$y+5,$x+195,$y+5);
$pdf->SetLineWidth(0.2);
$pdf->Line($x,$y+11,$x+195,$y+11);
//haris vertical header
$pdf->Line($x,$y+5,$x,$y+81);
//qty
$pdf->Line($x+20,$y+5,$x+20,$y+75);
//uraian
$pdf->Line($x+135,$y+5,$x+135,$y+81);
$pdf->Line($x+195,$y+5,$x+195,$y+81);
//garis datar footer 1
$pdf->Line($x,$y+75,$x+195,$y+75);
//garis datar footer 2
$pdf->Line($x,$y+81,$x+195,$y+81);
$pdf->Ln(5);
//header barang
$pdf->SetFont($fontFamily, "", 10);
$pdf->Cell(17,5,"  QTY",0,0,"C");
$pdf->Cell(85,5,"   KODE DAN NAMA BARANG",0,0,"C");
$pdf->Cell(115,5,"KETERANGAN",0,0,"C");
$pdf->Ln(2);
$y = $pdf->GetY();
//detail barang
$pdf->SetX(16);
$pdf->SetY($y);
$pdf->SetFont($fontFamily, "", 9);
$qJenis = 0;
$qTotal = 0;
foreach($delivery->Details as $idx => $detail) {
	$pdf->Ln(5);
	if (right($detail->QtyDelivered,3) == '.00') {
        $pdf->Cell(22, 5, number_format($detail->QtyDelivered,0) . ' ' . strtolower($detail->SatBesar) . ' ', 0, 0, "R");
    }else{
        $pdf->Cell(22,5,number_format($detail->QtyDelivered,2) .' '.strtolower($detail->SatBesar).' ',0,0,"R");
    }
	$pdf->Cell(115, 5, $detail->ItemDescs, 0, 0, "L");
    $pdf->Cell(50, 5, $detail->ExInvoiceNo, 0, 0, "L");
	$qJenis++;
	$qTotal+= $detail->QtyDelivered;
}
$pdf->SetXY(6,$y1);
$pdf->SetFont($fontFamily, "", 9);
$pdf->Cell(5,5,'Total: '.$qTotal.' satuan *'.$qJenis.' macam*',0,0,"L");
$pdf->SetX(10);
$pdf->Write(20,'Diterima oleh,');
$pdf->SetX(60);
$pdf->Write(20,'Diserahkan oleh,');
$pdf->SetX(110);
$pdf->Write(20,'Mengetahui,');
$pdf->SetX(160);
$pdf->Write(20,'Hormat kami,');
$pdf->SetY($pdf->GetY()+17);
$pdf->SetX(10);
$pdf->Write(20,'_________________');
$pdf->SetX(60);
$pdf->Write(20,'_________________');
$pdf->SetX(110);
$pdf->Write(20,'_________________');
$pdf->SetX(160);
$pdf->Write(20,'_________________');
$pdf->Ln(5);
$pdf->SetFont($fontFamily, "", 8);
$pdf->SetX(10);
$pdf->Write(20,'Customer');
$pdf->SetX(60);
$pdf->Write(20,'Gudang');
$pdf->SetX(110);
$pdf->Write(20,'Ka. Gudang');
$pdf->SetX(160);
$pdf->Write(20,'Sales/Toko');
$pdf->Ln(13);
$pdf->SetFont($fontFamily, "i", 7);
$pdf->Cell(5,5,'Admin: '.$delivery->AdminName.' - Printed by: '.$userName.'  - Time: '.date('d-m-Y h:i:s'),0,0,"L");
