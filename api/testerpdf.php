<?php
require(__DIR__ . '/fpdf186/fpdf.php');

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);

$pdf->Cell(0,10,'Test PDF Greenbite',0,1,'C');
$pdf->Cell(0,10,'FPDF fonctionne correctement',0,1);

$pdf->Output();
?>