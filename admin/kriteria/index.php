<?php
require_once '../../includes/auth.php';
$auth->requireLogin();
$auth->requireAdmin();

// Get database connection
$database = new Database();
$conn = $database->getConnection();

// Handle PDF export
if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
    // Get all kriteria data grouped by student
    $stmt = $conn->query("
        SELECT k.*, s.nama as nama_siswa, a.nama_metode as strategi_nama
        FROM kriteria k 
        LEFT JOIN siswa s ON k.id_siswa = s.id 
        LEFT JOIN alternatif a ON k.strategi = a.id
        ORDER BY s.nama ASC, k.id ASC
    ");
    $kriterias = $stmt->fetchAll();
    
    // Group data by student
    $grouped_data = [];
    foreach ($kriterias as $kriteria) {
        $student_name = $kriteria['nama_siswa'] ?? 'N/A';
        if (!isset($grouped_data[$student_name])) {
            $grouped_data[$student_name] = [];
        }
        $grouped_data[$student_name][] = $kriteria;
    }
    
    // Start output buffering
    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Laporan Data Kriteria</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; font-size: 12px; }
            .header { text-align: center; margin-bottom: 30px; }
            .header h1 { margin: 5px 0; font-size: 18px; }
            .header h2 { margin: 5px 0; font-size: 16px; }
            .header h3 { margin: 5px 0; font-size: 14px; }
            .student-section { margin-bottom: 40px; page-break-inside: avoid; }
            .student-name { font-weight: bold; margin-bottom: 10px; font-size: 14px; }
            table { width: 100%; border-collapse: collapse; margin: 10px 0; }
            th, td { border: 1px solid #000; padding: 8px; text-align: center; font-size: 11px; }
            th { background-color: #f0f0f0; font-weight: bold; }
            .header-cell { background-color: #e0e0e0; font-weight: bold; }
            .strategy-cell { background-color: #f8f8f8; }
            .date { text-align: right; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>LAPORAN DATA KRITERIA</h1>
            <h2>Sistem Pendukung Keputusan Metode Pengajaran</h2>
            <h3>SMKS YAPRI JAKARTA</h3>
        </div>
        
        <div class="date">
            <p>Tanggal: <?php echo date('d/m/Y'); ?></p>
        </div>
        
        <?php foreach ($grouped_data as $student_name => $student_data): ?>
        <div class="student-section">
            <div class="student-name">Nama Siswa: <?php echo htmlspecialchars($student_name); ?></div>
            
            <table>
                <thead>
                    <tr>
                        <th style="width: 8%;">No</th>
                        <th style="width: 15%;">Strategi</th>
                        <th style="width: 12%;">Kemampuan Grammar</th>
                        <th style="width: 12%;">Kemampuan Speaking</th>
                        <th style="width: 10%;">Motivasi Belajar</th>
                        <th style="width: 15%;">Gaya Belajar</th>
                        <th style="width: 12%;">Kecocokan Strategi</th>
                        <th style="width: 8%;">Durasi (menit)</th>
                        <th style="width: 8%;">Bobot Kognitif</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    foreach ($student_data as $data): 
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
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td class="strategy-cell"><?php echo htmlspecialchars($data['strategi_nama'] ?? $data['strategi'] ?? 'N/A'); ?></td>
                        <td><?php echo $data['kemampuan_grammar'] ?? 0; ?></td>
                        <td><?php echo $data['kemampuan_speaking'] ?? 0; ?></td>
                        <td><?php echo $data['motivasi_belajar'] ?? 0; ?></td>
                        <td><?php echo $gaya_belajar_display; ?></td>
                        <td><?php echo $data['kecocokan_strategi'] ?? 0; ?></td>
                        <td><?php echo $data['durasi'] ?? 0; ?></td>
                        <td><?php echo $data['bobot_kognitif'] ?? 0; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endforeach; ?>
        
        <script>
            window.onload = function() {
                window.print();
                window.onafterprint = function() {
                    window.close();
                }
            }
        </script>
    </body>
    </html>
    <?php
    $html = ob_get_clean();
    echo $html;
    exit();
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $id = $_POST['delete'];
    try {
        $stmt = $conn->prepare("DELETE FROM kriteria WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: /spk_mora/admin/kriteria/index.php?success=1");
        exit();
    } catch (PDOException $e) {
        $error = "Gagal menghapus kriteria";
    }
}

require_once '../../includes/header.php';
?>

<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Manajemen Kriteria</h1>
        <div class="flex space-x-3">
            <a href="download_pdf_kriteria.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-file-pdf mr-2"></i>Download PDF
            </a>
            <a href="create.php" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>Tambah Kriteria
            </a>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline">Data berhasil diperbarui!</span>
    </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
    </div>
    <?php endif; ?>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Siswa</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Strategi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kemampuan Grammar</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kemampuan Speaking</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motivasi Belajar</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gaya Belajar</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kecocokan Strategi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durasi (menit)</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bobot Kognitif</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php
                $stmt = $conn->query("
                    SELECT k.*, s.nama as nama_siswa 
                    FROM kriteria k 
                    LEFT JOIN siswa s ON k.id_siswa = s.id 
                    ORDER BY k.id ASC
                ");
                while ($row = $stmt->fetch()):
                ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $row['id']; ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['nama_siswa'] ?? 'N/A'); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['strategi'] ?? 'N/A'); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $row['kemampuan_grammar'] ?? 0; ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $row['kemampuan_speaking'] ?? 0; ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $row['motivasi_belajar'] ?? 0; ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            <?php echo ucfirst($row['gaya_belajar'] ?? 'N/A'); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $row['kecocokan_strategi'] ?? 0; ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $row['durasi'] ?? 0; ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $row['bobot_kognitif'] ?? 0; ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="text-primary hover:text-blue-900 mr-3">Edit</a>
                        <form action="" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kriteria ini?');">
                            <input type="hidden" name="delete" value="<?php echo $row['id']; ?>">
                            <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
