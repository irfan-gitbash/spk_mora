<?php
require_once '../../includes/auth.php';
require_once '../../vendor/fpdf/fpdf.php';
$auth->requireLogin();
$auth->requireAdmin();

// Get database connection
$database = new Database();
$conn = $database->getConnection();

// Get kriteria data from database
$kriteria_data = [];
$stmt = $conn->query("
    SELECT k.*, s.nama as nama_siswa 
    FROM kriteria k 
    LEFT JOIN siswa s ON k.id_siswa = s.id 
    ORDER BY s.nama, k.strategi
");
while ($row = $stmt->fetch()) {
    $kriteria_data[] = $row;
}

class PDF extends FPDF
{
    function Header()
    {
        // Add logo
        $logo_path = '../../images/logo.jpg';
        if (file_exists($logo_path)) {
            $this->Image($logo_path, 15, 10, 25, 25); // x, y, width, height
        }
        
        // Title - POSISI CENTER SEJAJAR SEMPURNA
        $this->SetFont('Arial','B',16);
        $this->Cell(0,8,'DATA YANG TERPILIH DARI HALAMAN MANAJEMEN KRITERIA',0,1,'C');
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
        $this->Line(10, $this->GetY(), 287, $this->GetY()); // Adjusted for landscape
        $this->Ln(5);
    }

    function Footer()
    {
        // Position at bottom right for signature section
        $this->SetY(-45);
        
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
        $this->Ln(11);
        
        // Responsible person name in bottom right
        $this->SetFont('Arial','',10);
        $this->SetFont('Arial','B',10);
        $this->Cell(0,6,'(Atikah S.Pd)',0,1,'R');
        $this->Ln(18);
        
        // Line above page number
        $this->SetY(-15);
        $this->Line(10, $this->GetY(), 287, $this->GetY()); // Adjusted for landscape
        $this->Ln(2);
        
        // Page number
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Halaman '.$this->PageNo().'/{nb}',0,0,'C');
    }

    function BasicTable($header, $data)
    {
        // Header
        $this->SetFont('Arial','B',8);
        $this->SetFillColor(240,240,240);
        
        // Column widths
        $w = array(10, 25, 25, 20, 20, 18, 18, 22, 15, 15);
        
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],7,$header[$i],1,0,'C',true);
        $this->Ln();

        // Data
        $this->SetFont('Arial','',7);
        $this->SetFillColor(245,245,245);
        $fill = false;
        $no = 1;
        
        foreach($data as $row)
        {
            $gaya_belajar_display = '';
            switch(strtolower($row['gaya_belajar'] ?? '')) {
                case 'visual':
                    $gaya_belajar_display = 'Visual';
                    break;
                case 'auditori':
                    $gaya_belajar_display = 'Auditori';
                    break;
                case 'kinestetik':
                    $gaya_belajar_display = 'Kinestetik';
                    break;
                default:
                    $gaya_belajar_display = ucfirst($row['gaya_belajar'] ?? 'N/A');
            }
            
            $this->Cell($w[0],6,$no,1,0,'C',$fill);
            $this->Cell($w[1],6,substr($row['nama_siswa'] ?? 'N/A', 0, 12),1,0,'L',$fill);
            $this->Cell($w[2],6,substr($row['strategi'] ?? 'N/A', 0, 12),1,0,'L',$fill);
            $this->Cell($w[3],6,$row['kemampuan_grammar'] ?? 0,1,0,'C',$fill);
            $this->Cell($w[4],6,$row['kemampuan_speaking'] ?? 0,1,0,'C',$fill);
            $this->Cell($w[5],6,$row['motivasi_belajar'] ?? 0,1,0,'C',$fill);
            $this->Cell($w[6],6,$gaya_belajar_display,1,0,'C',$fill);
            $this->Cell($w[7],6,$row['kecocokan_strategi'] ?? 0,1,0,'C',$fill);
            $this->Cell($w[8],6,$row['durasi'] ?? 0,1,0,'C',$fill);
            $this->Cell($w[9],6,$row['bobot_kognitif'] ?? 0,1,0,'C',$fill);
            $this->Ln();
            $fill = !$fill;
            $no++;
        }
    }
}

// Create PDF
$pdf = new PDF('L', 'mm', 'A4'); // Landscape orientation
$pdf->AliasNbPages();
$pdf->AddPage();

$header = array('No', 'Nama Siswa', 'Strategi', 'Grammar', 'Speaking', 'Motivasi', 'Gaya Belajar', 'Kecocokan', 'Durasi', 'Bobot');

$pdf->BasicTable($header, $kriteria_data);

// Add footer information
$pdf->Ln(8);
$pdf->SetFont('Arial','I',8);
$pdf->Cell(0,5,'Laporan ini dibuat secara otomatis oleh Sistem Pendukung Keputusan',0,1,'C');
$pdf->Cell(0,5,'SMKS YAPRI JAKARTA - ' . date('Y'),0,1,'C');

$pdf->Output('D', 'Data_Kriteria_Manajemen_' . date('Y-m-d_H-i-s') . '.pdf');
?>