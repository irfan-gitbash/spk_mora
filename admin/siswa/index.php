<?php
require_once '../../includes/auth.php';
$auth->requireLogin();
$auth->requireAdmin();

// Get database connection
$database = new Database();
$conn = $database->getConnection();

// Handle PDF export
if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
    // Get all student data
    $stmt = $conn->query("SELECT * FROM siswa ORDER BY id ASC");
    $students = $stmt->fetchAll();
    
    // Start output buffering
    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Laporan Data Siswa</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .header h1 { margin: 5px 0; font-size: 18px; }
            .header h2 { margin: 5px 0; font-size: 16px; }
            .header h3 { margin: 5px 0; font-size: 14px; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { border: 1px solid #000; padding: 8px; text-align: left; }
            th { background-color: #f0f0f0; font-weight: bold; }
            .date { text-align: right; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>LAPORAN DATA SISWA</h1>
            <h2>Sistem Pendukung Keputusan Metode Pengajaran</h2>
            <h3>SMKS YAPRI JAKARTA</h3>
        </div>
        
        <div class="date">
            <p>Tanggal: <?php echo date('d/m/Y'); ?></p>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">No</th>
                    <th style="width: 15%;">ID Siswa</th>
                    <th style="width: 45%;">Nama Siswa</th>
                    <th style="width: 30%;">Tanggal Dibuat</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                foreach ($students as $student): 
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($student['id']); ?></td>
                    <td><?php echo htmlspecialchars($student['nama']); ?></td>
                    <td><?php echo date('d-m-Y H:i', strtotime($student['created_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
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
        $stmt = $conn->prepare("DELETE FROM siswa WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: /spk_mora/admin/siswa/index.php?success=1");
        exit();
    } catch (PDOException $e) {
        $error = "Gagal menghapus data siswa";
    }
}

require_once '../../includes/header.php';
?>

<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Manajemen Data Siswa</h1>
        <div class="flex space-x-3">
            <a href="download_pdf_siswa.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-file-pdf mr-2"></i>Download PDF
            </a>
            <a href="create.php" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>Tambah Siswa
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Dibuat</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php
                $stmt = $conn->query("SELECT * FROM siswa ORDER BY id ASC");
                while ($row = $stmt->fetch()):
                ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $row['id']; ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['nama']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('d-m-Y H:i', strtotime($row['created_at'])); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="text-primary hover:text-blue-900 mr-3">Edit</a>
                        <a href="view.php?id=<?php echo $row['id']; ?>" class="text-green-600 hover:text-green-900 mr-3">Lihat</a>
                        <form action="" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data siswa ini?');">
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