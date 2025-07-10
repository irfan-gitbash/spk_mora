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
        // Logo atau gambar header (opsional)
        // $this->Image('logo.png',10,6,30);
        
        // Arial bold 15
        $this->SetFont('Arial','B',16);
        // Pindah ke kanan
        $this->Cell(80);
        // Judul
        $this->Cell(30,10,'LAPORAN HASIL PERHITUNGAN MOORA',0,0,'C');
        // Line break
        $this->Ln(8);
        
        // Arial bold 12
        $this->SetFont('Arial','B',12);
        $this->Cell(80);
        $this->Cell(30,10,'Sistem Pendukung Keputusan Pemilihan Metode Pembelajaran',0,0,'C');
        $this->Ln(6);
        
        // Arial normal 10
        $this->SetFont('Arial','',10);
        $this->Cell(80);
        $this->Cell(30,10,'SMKS YAPRI JAKARTA',0,0,'C');
        $this->Ln(15);
    }
    
    // Footer halaman
    function Footer()
    {
        // Posisi 1.5 cm dari bawah
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial','I',8);
        // Nomor halaman
        $this->Cell(0,10,'Halaman '.$this->PageNo().'/{nb} | Dicetak pada: '.date('d/m/Y H:i:s').' | SMKS YAPRI JAKARTA',0,0,'C');
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

// Detail Kriteria menggunakan definisi yang benar
$pdf->SectionTitle('Detail Kriteria Penilaian');

$header_kriteria = array('No', 'Nama Kriteria', 'Bobot', 'Tipe', 'Keterangan');
$widths_kriteria = array(15, 70, 25, 25, 55);

$data_kriteria = array();
foreach ($kriteria_definitions as $index => $k) {
    $keterangan = $k['tipe'] == 'benefit' ? 'Semakin tinggi semakin baik' : 'Semakin rendah semakin baik';
    
    $data_kriteria[] = array(
        ($index + 1),
        $k['nama'],
        number_format($k['bobot'], 3),
        strtoupper($k['tipe']),
        $keterangan
    );
}

$pdf->FancyTable($header_kriteria, $data_kriteria, $widths_kriteria);

// Interpretasi Hasil
if ($terbaik) {
    $pdf->SectionTitle('Interpretasi dan Rekomendasi');
    
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell(0,6,'Metode pembelajaran terbaik: ' . $terbaik['nama_alternatif'],0,1);
    
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell(0,6,'Nilai optimasi (Yi): ' . number_format($terbaik['yi_value'], 4),0,1);
    $pdf->Ln(3);
    
    $pdf->SetFont('Arial','',9);
    $pdf->MultiCell(0,5,'Analisis: Metode ini memiliki nilai optimasi tertinggi berdasarkan perhitungan MOORA dengan mempertimbangkan semua kriteria yang telah ditetapkan. Nilai Yi yang tinggi menunjukkan bahwa metode ini memberikan hasil terbaik dalam kombinasi benefit yang maksimal dan cost yang minimal.');
    $pdf->Ln(3);
    
    $pdf->MultiCell(0,5,'Rekomendasi: Disarankan untuk menerapkan metode pembelajaran "' . $terbaik['nama_alternatif'] . '" sebagai prioritas utama dalam proses pembelajaran di SMKS YAPRI Jakarta.');
}

// Output PDF
$filename = 'Laporan_MOORA_' . date('Y-m-d_H-i-s') . '.pdf';
$pdf->Output('D', $filename); // 'D' untuk download, 'I' untuk tampil di browser
?>