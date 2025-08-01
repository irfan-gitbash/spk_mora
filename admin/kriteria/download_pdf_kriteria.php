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
class KriteriaPDF extends FPDF
{
    // Header
    function Header()
    {
        // Add logo
        $logo_path = '../../images/logo.jpg';
        if (file_exists($logo_path)) {
            $this->Image($logo_path, 15, 10, 25, 25); // x, y, width, height
        }
        
        // Title - POSISI CENTER SEJAJAR SEMPURNA
        $this->SetFont('Arial','B',16);
        $this->Cell(0,8,'LAPORAN DATA KRITERIA',0,1,'C');
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
        // Position at bottom right for signature section
        $this->SetY(-60);
        
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
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(2);
        
        // Page number
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Halaman '.$this->PageNo().' dari {nb}',0,0,'C');
    }
    
    // Student section header
    function StudentHeader($student_name)
    {
        $this->SetFont('Arial','B',12);
        $this->SetFillColor(230,230,230);
        $this->Cell(0,8,'SISWA: ' . strtoupper($student_name),1,1,'L',true);
        $this->Ln(2);
    }
    
    // Table header
    function TableHeader()
    {
        $this->SetFont('Arial','B',8);
        $this->SetFillColor(240,240,240);
        $this->Cell(12,8,'No',1,0,'C',true);
        $this->Cell(25,8,'Strategi',1,0,'C',true);
        $this->Cell(18,8,'Grammar',1,0,'C',true);
        $this->Cell(18,8,'Speaking',1,0,'C',true);
        $this->Cell(18,8,'Motivasi',1,0,'C',true);
        $this->Cell(25,8,'Gaya Belajar',1,0,'C',true);
        $this->Cell(20,8,'Kecocokan',1,0,'C',true);
        $this->Cell(18,8,'Durasi',1,0,'C',true);
        $this->Cell(18,8,'Bobot',1,1,'C',true);
    }
    
    // Table row
    function TableRow($no, $strategi, $grammar, $speaking, $motivasi, $gaya_belajar, $kecocokan, $durasi, $bobot)
    {
        $this->SetFont('Arial','',8);
        
        // Check if we need a new page
        if($this->GetY() + 8 > $this->PageBreakTrigger) {
            $this->AddPage();
            $this->TableHeader();
        }
        
        $this->Cell(12,8,$no,1,0,'C');
        $this->Cell(25,8,$strategi,1,0,'L');
        $this->Cell(18,8,$grammar,1,0,'C');
        $this->Cell(18,8,$speaking,1,0,'C');
        $this->Cell(18,8,$motivasi,1,0,'C');
        $this->Cell(25,8,$gaya_belajar,1,0,'C');
        $this->Cell(20,8,$kecocokan,1,0,'C');
        $this->Cell(18,8,$durasi,1,0,'C');
        $this->Cell(18,8,$bobot,1,1,'C');
    }
    
    // Summary section
    function SummarySection($total_students, $total_records, $avg_grammar, $avg_speaking, $avg_motivasi)
    {
        $this->Ln(10);
        $this->SetFont('Arial','B',12);
        $this->Cell(0,8,'RINGKASAN STATISTIK',0,1,'L');
        $this->Ln(2);
        
        $this->SetFont('Arial','',10);
        $this->Cell(0,6,'Total Siswa: ' . $total_students,0,1,'L');
        $this->Cell(0,6,'Total Record Kriteria: ' . $total_records,0,1,'L');
        $this->Cell(0,6,'Rata-rata Kemampuan Grammar: ' . number_format($avg_grammar, 2),0,1,'L');
        $this->Cell(0,6,'Rata-rata Kemampuan Speaking: ' . number_format($avg_speaking, 2),0,1,'L');
        $this->Cell(0,6,'Rata-rata Motivasi Belajar: ' . number_format($avg_motivasi, 2),0,1,'L');
    }
}

try {
    // Get all kriteria data grouped by student
    $stmt = $conn->prepare("
        SELECT k.*, s.nama as nama_siswa, a.nama_metode as strategi_nama
        FROM kriteria k 
        LEFT JOIN siswa s ON k.id_siswa = s.id 
        LEFT JOIN alternatif a ON k.strategi = a.id
        ORDER BY s.nama ASC, k.id ASC
    ");
    $stmt->execute();
    $kriterias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group data by student
    $grouped_data = [];
    $total_grammar = 0;
    $total_speaking = 0;
    $total_motivasi = 0;
    $total_records = 0;
    
    foreach ($kriterias as $kriteria) {
        $student_name = $kriteria['nama_siswa'] ?? 'N/A';
        if (!isset($grouped_data[$student_name])) {
            $grouped_data[$student_name] = [];
        }
        $grouped_data[$student_name][] = $kriteria;
        
        // Calculate totals for statistics
        $total_grammar += (float)($kriteria['kemampuan_grammar'] ?? 0);
        $total_speaking += (float)($kriteria['kemampuan_speaking'] ?? 0);
        $total_motivasi += (float)($kriteria['motivasi_belajar'] ?? 0);
        $total_records++;
    }
    
    // Calculate averages
    $avg_grammar = $total_records > 0 ? $total_grammar / $total_records : 0;
    $avg_speaking = $total_records > 0 ? $total_speaking / $total_records : 0;
    $avg_motivasi = $total_records > 0 ? $total_motivasi / $total_records : 0;
    
    // Create PDF
    $pdf = new KriteriaPDF();
    $pdf->AliasNbPages();
    $pdf->AddPage();
    
    // Add summary information
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0,8,'RINGKASAN DATA',0,1,'L');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(0,6,'Total Siswa: ' . count($grouped_data),0,1,'L');
    $pdf->Cell(0,6,'Total Record Kriteria: ' . $total_records,0,1,'L');
    $pdf->Cell(0,6,'Tanggal Generate: ' . date('d F Y, H:i:s'),0,1,'L');
    $pdf->Ln(5);
    
    // Process each student
    foreach ($grouped_data as $student_name => $student_data) {
        // Check if we need a new page for student section
        if($pdf->GetY() > 220) {
            $pdf->AddPage();
        }
        
        $pdf->StudentHeader($student_name);
        $pdf->TableHeader();
        
        $no = 1;
        foreach ($student_data as $data) {
            // Format gaya belajar
            $gaya_belajar_display = '';
            switch(strtolower($data['gaya_belajar'] ?? '')) {
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
                    $gaya_belajar_display = ucfirst($data['gaya_belajar'] ?? 'N/A');
            }
            
            $pdf->TableRow(
                $no++,
                substr($data['strategi_nama'] ?? $data['strategi'] ?? 'N/A', 0, 20),
                $data['kemampuan_grammar'] ?? 0,
                $data['kemampuan_speaking'] ?? 0,
                $data['motivasi_belajar'] ?? 0,
                $gaya_belajar_display,
                $data['kecocokan_strategi'] ?? 0,
                $data['durasi'] ?? 0,
                $data['bobot_kognitif'] ?? 0
            );
        }
        
        $pdf->Ln(8);
    }
    
    // Add summary statistics
    $pdf->SummarySection(
        count($grouped_data),
        $total_records,
        $avg_grammar,
        $avg_speaking,
        $avg_motivasi
    );
    
    // Add footer information
    $pdf->Ln(8);
    $pdf->SetFont('Arial','I',8);
    $pdf->Cell(0,5,'Laporan ini dibuat secara otomatis oleh Sistem Pendukung Keputusan',0,1,'C');
    $pdf->Cell(0,5,'SMKS YAPRI JAKARTA - 2025',0,1,'C');
    
    // Output PDF
    $filename = 'Laporan_Data_Kriteria_' . date('Y-m-d_H-i-s') . '.pdf';
    $pdf->Output('D', $filename);
    
} catch (Exception $e) {
    // Handle errors
    header('Content-Type: text/html; charset=utf-8');
    echo '<script>alert("Error: ' . addslashes($e->getMessage()) . '"); window.history.back();</script>';
}
?>