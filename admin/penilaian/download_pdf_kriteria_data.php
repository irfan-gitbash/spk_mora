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
        $this->SetFont('Arial','B',16);
        $this->Cell(0,10,'Data Yang Terpilih dari Halaman Manajemen Kriteria',0,1,'C');
        $this->Cell(0,10,'Sistem Pendukung Keputusan MOORA',0,1,'C');
        $this->Ln(10);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Halaman '.$this->PageNo().'/{nb}',0,0,'C');
    }

    function BasicTable($header, $data)
    {
        // Header
        $this->SetFont('Arial','B',8);
        $this->SetFillColor(200,220,255);
        
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

// Add summary information
$pdf->Ln(10);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(0,10,'Ringkasan Data:',0,1,'L');
$pdf->SetFont('Arial','',9);
$pdf->Cell(0,6,'Total Data: ' . count($kriteria_data) . ' record',0,1,'L');
$pdf->Cell(0,6,'Tanggal Generate: ' . date('d/m/Y H:i:s'),0,1,'L');

$pdf->Output('D', 'Data_Kriteria_Manajemen_' . date('Y-m-d_H-i-s') . '.pdf');
?>