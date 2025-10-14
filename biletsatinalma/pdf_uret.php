<?php
require_once 'config.php';
require_once 'auth.php';
require_login();

require_once __DIR__ . '/fpdf/fpdf186/fpdf.php';  // fpdf klasörü doc186-html-tr içinde olmalı!

$id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];

$stmt = $db->prepare(
    "SELECT b.*, s.kalkis, s.varis, s.tarih, s.saat FROM biletler b 
     JOIN sefers s ON b.sefer_id=s.id WHERE b.id=? AND b.user_id=?"
);
$stmt->execute([$id, $user_id]);
$bilet = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bilet) die("Bilet bulunamadı!");

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(40,10,'Bilet Bilgileri');
$pdf->Ln(10);
$pdf->SetFont('Arial','',12);
$pdf->Cell(0,10,'Kalkış: '.$bilet['kalkis'],0,1);
$pdf->Cell(0,10,'Varış: '.$bilet['varis'],0,1);
$pdf->Cell(0,10,'Tarih: '.$bilet['tarih'],0,1);
$pdf->Cell(0,10,'Saat: '.$bilet['saat'],0,1);
$pdf->Cell(0,10,'Koltuk No: '.$bilet['koltuk_no'],0,1);
$pdf->Cell(0,10,'Fiyat: '.$bilet['fiyat'].'₺',0,1);
$pdf->Output('D', 'bilet.pdf');
?>