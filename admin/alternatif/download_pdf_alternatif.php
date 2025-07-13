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
        // Add logo
        $logo_path = '../../images/logo.jpg';
        if (file_exists($logo_path)) {
            $this->Image($logo_path, 15, 10, 25, 25); // x, y, width, height
        }
        
        // HAPUS SetX(50) untuk alignment center yang sempurna
        // $this->SetX(50);
        
        // Title - POSISI CENTER SEJAJAR SEMPURNA
        $this->SetFont('Arial','B',16);
        $this->Cell(0,8,'LAPORAN DATA STRATEGI',0,1,'C');
        $this->SetFont('Arial','B',12);
        $this->Cell(0,6,'Sistem Pendukung Keputusan Metode Pengajaran',0,1,'C');
        $this->SetFont('Arial','B',10);
        $this->Cell(0,6,'SMKS YAPRI JAKARTA',0,1,'C');
        
        // Address
        $this->SetFont('Arial','',9);
        $this->Cell(0,5,'Jl. KH. Muhasyim IV No.7, RT.12/RW.6, Cilandak Bar., Kec. Cilandak,',0,1,'C');
        $this->Cell(0,5,'Kota Jakarta Selatan, Daerah Khusus Ibukota Jakarta 12430',0,1,'C');
        $this->Ln(5);
        
        // Line
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(5);
    }
    
    // Footer
    function Footer()
    {
        // Position at bottom right for signature section - NAIK LEBIH KE ATAS
        $this->SetY(-70); // Ubah dari -35 menjadi -50 untuk naik lebih ke atas
        
       // Jakarta date and responsible person in bottom right corner
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
         $days = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin', 
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];
         $day = date('d');
        $hari = $days[date('l')];
        $month = $months[(int)date('m')];
        $year = date('Y');
        
       // Jakarta with date in bottom right
        $this->SetFont('Arial','',10);
        $this->Cell(0,6,'Jakarta, ' . $hari . ' ' . $day . ' ' . $month . ' ' . $year,0,1,'R');
        $this->Ln(11); // Kurangi jarak dari 8 menjadi 5
        
        // Responsible person name in bottom right
        $this->SetFont('Arial','',10);
        $this->SetFont('Arial','B',10);
        $this->Cell(0,6,'(Atikah S.Pd)',0,1,'R');
          $this->Ln(18); // Kurangi jarak dari 8 menjadi 5
        
        // Line above page number
        $this->SetY(-15);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(2);
        
        // Page number
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Halaman '.$this->PageNo().' dari {nb}',0,0,'C');
    }
    
    // Helper function for Indonesian months
    function getIndonesianMonth($month_num) {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        return $months[(int)$month_num];
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
    $pdf->Cell(0,5,'SMKS YAPRI JAKARTA - 2025',0,1,'C');
    
    // HAPUS bagian ini karena sudah ada di Footer function:
    // $pdf->Ln(5); // Tambah jarak baris lebih besar
    // $pdf->SetFont('Arial','',9);
    // $pdf->SetRightMargin(10); // Tambah margin kanan
    // $pdf->Cell(0,5,'Jakarta, 13 Juli 2025',0,1,'R');
    // $pdf->Ln(2); // Tambah jarak antara Jakarta dan nama
    // $pdf->SetFont('Arial','B',9);
    // $pdf->Cell(0,5,'Atikah.S.Pd',0,1,'R');
    
    // Output PDF
    $filename = 'Laporan_Data_Strategi_' . date('Y-m-d_H-i-s') . '.pdf';
    $pdf->Output('D', $filename);
    
} catch (Exception $e) {
    // Handle errors
    header('Content-Type: text/html; charset=utf-8');
    echo '<script>alert("Error: ' . addslashes($e->getMessage()) . '"); window.history.back();</script>';
}
?>