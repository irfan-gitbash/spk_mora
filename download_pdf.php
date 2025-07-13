<?php
require_once 'config/database.php';
require_once 'vendor/fpdf/fpdf.php';

// Inisialisasi koneksi database
$database = new Database();
$pdo = $database->getConnection();

// Ambil ID sesi dari parameter URL
$sesi_id = isset($_GET['sesi_id']) ? (int)$_GET['sesi_id'] : null;

if (!$sesi_id) {
    $stmt = $pdo->query("SELECT id FROM sesi_perhitungan_moora ORDER BY created_at DESC LIMIT 1");
    $latest_session = $stmt->fetch();
    $sesi_id = $latest_session ? $latest_session['id'] : null;
}

if (!$sesi_id) {
    die('Tidak ada hasil perhitungan MOORA yang tersimpan.');
}

// Ambil informasi sesi
$stmt = $pdo->prepare("SELECT * FROM sesi_perhitungan_moora WHERE id = ?");
$stmt->execute([$sesi_id]);
$sesi_info = $stmt->fetch();

// Ambil hasil perhitungan
$stmt = $pdo->prepare("
    SELECT * FROM hasil_moora 
    WHERE sesi_id = ? 
    ORDER BY ranking ASC
");
$stmt->execute([$sesi_id]);
$hasil_moora = $stmt->fetchAll();

if (!$hasil_moora) {
    die('Hasil perhitungan tidak ditemukan untuk sesi ini.');
}

// Definisi kriteria berdasarkan struktur yang benar
$kriteria_definitions = [
    ['nama' => 'Kemampuan Grammar', 'bobot' => 0.15, 'tipe' => 'benefit'],
    ['nama' => 'Kemampuan Speaking', 'bobot' => 0.15, 'tipe' => 'benefit'],
    ['nama' => 'Motivasi Belajar', 'bobot' => 0.10, 'tipe' => 'benefit'],
    ['nama' => 'Gaya Belajar', 'bobot' => 0.10, 'tipe' => 'benefit'],
    ['nama' => 'Kecocokan Strategi', 'bobot' => 0.15, 'tipe' => 'benefit'],
    ['nama' => 'Durasi (menit)', 'bobot' => 0.10, 'tipe' => 'cost'],
    ['nama' => 'Bobot Kognitif', 'bobot' => 0.25, 'tipe' => 'benefit']
];

// Ambil data alternatif untuk detail
$stmt = $pdo->query("SELECT * FROM alternatif ORDER BY id");
$alternatif = $stmt->fetchAll();

// Buat PDF menggunakan FPDF
class PDF extends FPDF
{
    // Header halaman
    function Header()
    {
        // Add logo
        $logo_path = 'images/logo.jpg';
        if (file_exists($logo_path)) {
            $this->Image($logo_path, 15, 10, 25, 25); // x, y, width, height
        }
        
        // Title - POSISI CENTER SEJAJAR SEMPURNA
        $this->SetFont('Arial','B',16);
        $this->Cell(0,8,'LAPORAN HASIL PERHITUNGAN MOORA',0,1,'C');
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
    
    // Footer halaman
    function Footer()
    {
        // Position at bottom right for signature section
        $this->SetY(-50);
        
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
        $this->SetFont('Arial','B',10);
        $this->Cell(0,6,'(Atikah S.Pd)',0,1,'R');
        $this->Ln(18);
        
        // Line above page number
        $this->SetY(-15);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(2);
        
        // Page number
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Halaman '.$this->PageNo().'/{nb}',0,0,'C');
    }
    
    // Fungsi untuk membuat tabel dengan border
    function FancyTable($header, $data, $widths)
    {
        // Warna, ketebalan garis dan font
        $this->SetFillColor(37, 99, 235); // Biru
        $this->SetTextColor(255);
        $this->SetDrawColor(128,0,0);
        $this->SetLineWidth(.3);
        $this->SetFont('','B');
        
        // Header
        for($i=0;$i<count($header);$i++)
            $this->Cell($widths[$i],7,$header[$i],1,0,'C',true);
        $this->Ln();
        
        // Kembalikan warna dan font
        $this->SetFillColor(224,235,255);
        $this->SetTextColor(0);
        $this->SetFont('');
        
        // Data
        $fill = false;
        foreach($data as $row)
        {
            for($i=0;$i<count($row);$i++)
            {
                $align = ($i == 0 || $i == count($row)-1) ? 'C' : 'L';
                if($i >= 2 && $i <= count($row)-2) $align = 'C'; // Kolom angka di tengah
                $this->Cell($widths[$i],6,$row[$i],'LR',0,$align,$fill);
            }
            $this->Ln();
            $fill = !$fill;
        }
        // Garis penutup
        $this->Cell(array_sum($widths),0,'','T');
        $this->Ln(10);
    }
    
    // Fungsi untuk teks dengan background
    function SectionTitle($title)
    {
        $this->SetFillColor(239, 246, 255);
        $this->SetTextColor(30, 64, 175);
        $this->SetFont('Arial','B',12);
        $this->Cell(0,8,$title,1,1,'L',true);
        $this->SetTextColor(0);
        $this->Ln(3);
    }
}

// Buat instance PDF
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',10);

// Informasi Sesi
if ($sesi_info) {
    $pdf->SectionTitle('Informasi Perhitungan');
    
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell(50,6,'Tanggal Perhitungan:',0,0);
    $pdf->SetFont('Arial','',9);
    $pdf->Cell(0,6,date('d/m/Y H:i:s', strtotime($sesi_info['tanggal_perhitungan'])),0,1);
    
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell(50,6,'ID Sesi:',0,0);
    $pdf->SetFont('Arial','',9);
    $pdf->Cell(0,6,$sesi_info['id'],0,1);
    
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell(50,6,'Jumlah Alternatif:',0,0);
    $pdf->SetFont('Arial','',9);
    $pdf->Cell(0,6,$sesi_info['jumlah_alternatif'] . ' metode',0,1);
    
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell(50,6,'Jumlah Kriteria:',0,0);
    $pdf->SetFont('Arial','',9);
    $pdf->Cell(0,6,$sesi_info['jumlah_kriteria'] . ' kriteria',0,1);
    
    $pdf->Ln(10);
}

// Statistik Ringkasan
$total_alternatif = count($hasil_moora);
$terbaik = $hasil_moora[0] ?? null;
$rata_yi = $total_alternatif > 0 ? array_sum(array_column($hasil_moora, 'yi_value')) / $total_alternatif : 0;
$max_yi = $total_alternatif > 0 ? max(array_column($hasil_moora, 'yi_value')) : 0;

$pdf->SectionTitle('Statistik Ringkasan');

// Buat tabel statistik dalam 2 kolom
$pdf->SetFont('Arial','B',9);
$pdf->Cell(95,6,'Total Alternatif: ' . $total_alternatif,1,0,'C');
$pdf->Cell(95,6,'Nilai Yi Tertinggi: ' . number_format($max_yi, 4),1,1,'C');
$pdf->Cell(95,6,'Rata-rata Yi: ' . number_format($rata_yi, 4),1,0,'C');
$pdf->Cell(95,6,'Jumlah Kriteria: ' . count($kriteria_definitions),1,1,'C');
$pdf->Ln(10);

// Tabel Hasil Perangkingan
$pdf->SectionTitle('Hasil Perangkingan Metode Pembelajaran');

$header = array('RANK', 'METODE PEMBELAJARAN', 'BENEFIT', 'COST', 'NILAI Yi', 'STATUS');
$widths = array(20, 60, 25, 25, 25, 35);

$data = array();
foreach ($hasil_moora as $hasil) {
    $rank = $hasil['ranking'];
    $status_text = '';
    
    if ($rank == 1) {
        $status_text = 'Terbaik';
    } elseif ($rank == 2) {
        $status_text = 'Sangat Baik';
    } elseif ($rank == 3) {
        $status_text = 'Baik';
    } elseif ($rank <= ceil(count($hasil_moora) * 0.5)) {
        $status_text = 'Sedang';
    } else {
        $status_text = 'Perlu Perbaikan';
    }
    
    $data[] = array(
        '#' . $rank,
        $hasil['nama_alternatif'],
        number_format($hasil['benefit_sum'], 4),
        number_format($hasil['cost_sum'], 4),
        number_format($hasil['yi_value'], 4),
        $status_text
    );
}

$pdf->FancyTable($header, $data, $widths);

// Output PDF
$filename = 'Laporan_MOORA_' . date('Y-m-d_H-i-s') . '.pdf';
$pdf->Output('D', $filename); // 'D' untuk download, 'I' untuk tampil di browser
?>