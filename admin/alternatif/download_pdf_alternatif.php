<?php
require_once '../../includes/auth.php';
$auth->requireLogin();
$auth->requireAdmin();

// Get database connection
$database = new Database();
$conn = $database->getConnection();

// Include FPDF library
require_once '../../vendor/fpdf/fpdf.php';

// Create PDF class
class AlternatifPDF extends FPDF
{
    // Header
    function Header()
    {
        // Logo or title
        $this->SetFont('Arial','B',16);
        $this->Cell(0,10,'LAPORAN DATA STRATEGI',0,1,'C');
        $this->SetFont('Arial','B',12);
        $this->Cell(0,8,'Sistem Pendukung Keputusan Metode Pengajaran',0,1,'C');
        $this->SetFont('Arial','B',10);
        $this->Cell(0,6,'SMKS YAPRI JAKARTA',0,1,'C');
        $this->Ln(5);
        
        // Date
        $this->SetFont('Arial','',10);
        $this->Cell(0,6,'Tanggal: ' . date('d/m/Y H:i:s'),0,1,'R');
        $this->Ln(5);
        
        // Line
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(5);
    }
    
    // Footer
    function Footer()
    {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Line
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(2);
        // Arial italic 8
        $this->SetFont('Arial','I',8);
        // Page number
        $this->Cell(0,10,'Halaman '.$this->PageNo().' dari {nb}',0,0,'C');
    }
    
    // Table header
    function TableHeader()
    {
        $this->SetFont('Arial','B',10);
        $this->SetFillColor(240,240,240);
        $this->Cell(15,8,'No',1,0,'C',true);
        $this->Cell(20,8,'ID',1,0,'C',true);
        $this->Cell(50,8,'Nama Metode',1,0,'C',true);
        $this->Cell(105,8,'Deskripsi',1,1,'C',true);
    }
    
    // Table row
    function TableRow($no, $id, $nama_metode, $deskripsi)
    {
        $this->SetFont('Arial','',9);
        
        // Calculate row height based on description length
        $nb_lines = max(1, ceil($this->GetStringWidth($deskripsi) / 100));
        $row_height = 6 * $nb_lines;
        
        // Check if we need a new page
        if($this->GetY() + $row_height > $this->PageBreakTrigger) {
            $this->AddPage();
            $this->TableHeader();
        }
        
        $x = $this->GetX();
        $y = $this->GetY();
        
        // No
        $this->Cell(15, $row_height, $no, 1, 0, 'C');
        
        // ID
        $this->Cell(20, $row_height, $id, 1, 0, 'C');
        
        // Nama Metode
        $this->Cell(50, $row_height, $nama_metode, 1, 0, 'L');
        
        // Deskripsi with text wrapping
        $this->SetXY($x + 85, $y);
        $this->MultiCell(105, 6, $deskripsi, 1, 'L');
        
        // Move to next row
        $this->SetXY($x, $y + $row_height);
    }
}

try {
    // Get all alternatif data
    $stmt = $conn->prepare("SELECT * FROM alternatif ORDER BY id ASC");
    $stmt->execute();
    $alternatifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Create PDF
    $pdf = new AlternatifPDF();
    $pdf->AliasNbPages();
    $pdf->AddPage();
    
    // Add summary information
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0,8,'RINGKASAN DATA',0,1,'L');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(0,6,'Total Strategi Metode Pengajaran: ' . count($alternatifs),0,1,'L');
    $pdf->Cell(0,6,'Tanggal Generate: ' . date('d F Y, H:i:s'),0,1,'L');
    $pdf->Ln(5);
    
    // Table header
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0,8,'DAFTAR STRATEGI METODE PENGAJARAN',0,1,'L');
    $pdf->Ln(3);
    
    $pdf->TableHeader();
    
    // Table data
    $no = 1;
    foreach ($alternatifs as $alternatif) {
        $pdf->TableRow(
            $no++,
            $alternatif['id'],
            $alternatif['nama_metode'],
            $alternatif['deskripsi']
        );
    }
    
    // Add detailed information section
    $pdf->Ln(10);
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0,8,'DETAIL INFORMASI STRATEGI',0,1,'L');
    $pdf->Ln(3);
    
    foreach ($alternatifs as $alternatif) {
        // Check if we need a new page
        if($pdf->GetY() > 250) {
            $pdf->AddPage();
        }
        
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(0,6,'ID: ' . $alternatif['id'] . ' - ' . $alternatif['nama_metode'],0,1,'L');
        $pdf->SetFont('Arial','',9);
        $pdf->MultiCell(0,5,$alternatif['deskripsi'],0,'L');
        $pdf->Ln(3);
    }
    
    // Add footer information
    $pdf->Ln(5);
    $pdf->SetFont('Arial','I',8);
    $pdf->Cell(0,5,'Laporan ini dibuat secara otomatis oleh Sistem Pendukung Keputusan',0,1,'C');
    $pdf->Cell(0,5,'SMKS YAPRI JAKARTA - ' . date('Y'),0,1,'C');
    
    // Output PDF
    $filename = 'Laporan_Data_Strategi_' . date('Y-m-d_H-i-s') . '.pdf';
    $pdf->Output('D', $filename);
    
} catch (Exception $e) {
    // Handle errors
    header('Content-Type: text/html; charset=utf-8');
    echo '<script>alert("Error: ' . addslashes($e->getMessage()) . '"); window.history.back();</script>';
}
?>