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
class SiswaPDF extends FPDF
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
        $this->Cell(0,8,'LAPORAN DATA SISWA',0,1,'C');
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
        $this->SetY(-55);
        
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
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(2);
        
        // Page number
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Halaman '.$this->PageNo().' dari {nb}',0,0,'C');
    }
    
    // Table header
    function TableHeader()
    {
        $this->SetFont('Arial','B',10);
        $this->SetFillColor(240,240,240);
        $this->Cell(20,8,'No',1,0,'C',true);
        $this->Cell(25,8,'ID Siswa',1,0,'C',true);
        $this->Cell(70,8,'Nama Siswa',1,0,'C',true);
        $this->Cell(40,8,'Tanggal Dibuat',1,0,'C',true);
        $this->Cell(35,8,'Status',1,1,'C',true);
    }
    
    // Table row
    function TableRow($no, $id, $nama, $created_at, $status)
    {
        $this->SetFont('Arial','',9);
        
        // Check if we need a new page
        if($this->GetY() + 8 > $this->PageBreakTrigger) {
            $this->AddPage();
            $this->TableHeader();
        }
        
        $this->Cell(20,8,$no,1,0,'C');
        $this->Cell(25,8,$id,1,0,'C');
        $this->Cell(70,8,$nama,1,0,'L');
        $this->Cell(40,8,$created_at,1,0,'C');
        $this->Cell(35,8,$status,1,1,'C');
    }
    
    // Summary section
    function SummarySection($total_students, $newest_student, $oldest_student)
    {
        $this->Ln(10);
        $this->SetFont('Arial','B',12);
        $this->Cell(0,8,'RINGKASAN DATA SISWA',0,1,'L');
        $this->Ln(2);
        
        $this->SetFont('Arial','',10);
        $this->Cell(0,6,'Total Siswa Terdaftar: ' . $total_students,0,1,'L');
        $this->Cell(0,6,'Siswa Terbaru: ' . $newest_student['nama'] . ' (ID: ' . $newest_student['id'] . ')',0,1,'L');
        $this->Cell(0,6,'Siswa Terlama: ' . $oldest_student['nama'] . ' (ID: ' . $oldest_student['id'] . ')',0,1,'L');
        $this->Cell(0,6,'Periode Data: ' . date('d F Y', strtotime($oldest_student['created_at'])) . ' - ' . date('d F Y', strtotime($newest_student['created_at'])),0,1,'L');
    }
    
    // Student details section
    function StudentDetailsSection($students)
    {
        $this->Ln(10);
        $this->SetFont('Arial','B',12);
        $this->Cell(0,8,'DETAIL INFORMASI SISWA',0,1,'L');
        $this->Ln(3);
        
        foreach ($students as $student) {
            // Check if we need a new page
            if($this->GetY() > 250) {
                $this->AddPage();
            }
            
            $this->SetFont('Arial','B',10);
            $this->SetFillColor(245,245,245);
            $this->Cell(0,8,'ID: ' . $student['id'] . ' - ' . $student['nama'],1,1,'L',true);
            
            $this->SetFont('Arial','',9);
            $this->Cell(40,6,'Tanggal Dibuat:',0,0,'L');
            $this->Cell(0,6,date('d F Y, H:i:s', strtotime($student['created_at'])),0,1,'L');
            
            $this->Cell(40,6,'Status:',0,0,'L');
            $this->Cell(0,6,'Aktif',0,1,'L');
            
            // Get kriteria count for this student
            $kriteria_count = $this->getKriteriaCount($student['id']);
            $this->Cell(40,6,'Jumlah Kriteria:',0,0,'L');
            $this->Cell(0,6,$kriteria_count . ' record',0,1,'L');
            
            $this->Ln(3);
        }
    }
    
    // Get kriteria count for student
    function getKriteriaCount($student_id)
    {
        global $conn;
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM kriteria WHERE id_siswa = ?");
        $stmt->execute([$student_id]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
}

try {
    // Get all student data
    $stmt = $conn->prepare("SELECT * FROM siswa ORDER BY created_at DESC");
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($students)) {
        throw new Exception('Tidak ada data siswa yang ditemukan.');
    }
    
    // Get newest and oldest student
    $newest_student = $students[0];
    $oldest_student = end($students);
    
    // Create PDF
    $pdf = new SiswaPDF();
    $pdf->AliasNbPages();
    $pdf->AddPage();
    
    // Add summary information
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0,8,'INFORMASI UMUM',0,1,'L');
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(0,6,'Total Siswa Terdaftar: ' . count($students),0,1,'L');
    $pdf->Cell(0,6,'Tanggal Generate Laporan: ' . date('d F Y, H:i:s'),0,1,'L');
    $pdf->Cell(0,6,'Sistem: Pendukung Keputusan Metode Pengajaran',0,1,'L');
    $pdf->Ln(8);
    
    // Table header
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0,8,'DAFTAR SISWA TERDAFTAR',0,1,'L');
    $pdf->Ln(3);
    
    $pdf->TableHeader();
    
    // Table data
    $no = 1;
    foreach ($students as $student) {
        $pdf->TableRow(
            $no++,
            $student['id'],
            $student['nama'],
            date('d-m-Y H:i', strtotime($student['created_at'])),
            'Aktif'
        );
    }
    
    // Add footer information
    $pdf->Ln(8);
    $pdf->SetFont('Arial','I',8);
    $pdf->Cell(0,5,'Laporan ini dibuat secara otomatis oleh Sistem Pendukung Keputusan',0,1,'C');
    $pdf->Cell(0,5,'SMKS YAPRI JAKARTA - ' . date('Y'),0,1,'C');
    
    // Output PDF
    $filename = 'Laporan_Data_Siswa_' . date('Y-m-d_H-i-s') . '.pdf';
    $pdf->Output('D', $filename);
    
} catch (Exception $e) {
    // Handle errors
    header('Content-Type: text/html; charset=utf-8');
    echo '<script>alert("Error: ' . addslashes($e->getMessage()) . '"); window.history.back();</script>';
}
?>