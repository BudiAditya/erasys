<?php
/** @var $report Delivery[] */
require_once(LIBRARY . 'tabular_pdf.php');
define('FPDF_FONTPATH','font/');

$pdf = new TabularPdf("P", "mm", "halfletter");
$pdf->SetAutoPageBreak(true, 2);
$pdf->SetMargins(5,5);
$pdf->SetDefaultAlignments(array("R", "L", "R"));

$pdf->Open();
$pdf->AddFont("helvetica");
$pdf->AddFont("helvetica", "B");
$fontFamily = "helvetica";
if ($doctype == 'do'){
	foreach ($report as $idx => $delivery) {
		require("do_print_pdf.php");
	}
	$fileName = 'ic-deliveryorder.pdf';
}elseif ($doctype == 'suratjalan') {
	foreach ($report as $idx => $delivery) {
		require("suratjalan_print_pdf.php");
	}
	$fileName = 'ic-suratjalan.pdf';
}

$pdf->Output($fileName,"D");
