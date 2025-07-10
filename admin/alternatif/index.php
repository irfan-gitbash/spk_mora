<?php
require_once '../../includes/auth.php';
$auth->requireLogin();
$auth->requireAdmin();

// Get database connection
$database = new Database();
$conn = $database->getConnection();

// Handle PDF export
if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
    // Get all alternatif data
    $stmt = $conn->query("SELECT * FROM alternatif ORDER BY id ASC");
    $alternatifs = $stmt->fetchAll();
    
    // Start output buffering
    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Laporan Data Strategi</title>
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
            .description { max-width: 300px; word-wrap: break-word; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>LAPORAN DATA STRATEGI</h1>
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
                    <th style="width: 15%;">ID</th>
                    <th style="width: 30%;">Nama Metode</th>
                    <th style="width: 45%;">Deskripsi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                foreach ($alternatifs as $alternatif): 
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($alternatif['id']); ?></td>
                    <td><?php echo htmlspecialchars($alternatif['nama_metode']); ?></td>
                    <td class="description"><?php echo nl2br(htmlspecialchars($alternatif['deskripsi'])); ?></td>
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
        $stmt = $conn->prepare("DELETE FROM alternatif WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: /spk_mora/admin/alternatif/index.php?success=1");
        exit();
    } catch (PDOException $e) {
        $error = "Gagal menghapus alternatif";
    }
}

require_once '../../includes/header.php';
?>

<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Strategi</h1>
        <div class="flex space-x-3">
            <a href="download_pdf_alternatif.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-file-pdf mr-2"></i>Download PDF
            </a>
            <a href="create.php" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>Tambah Metode
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Metode</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php
                $stmt = $conn->query("SELECT * FROM alternatif ORDER BY id ASC");
                while ($row = $stmt->fetch()):
                ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $row['id']; ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['nama_metode']); ?></td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        <?php echo nl2br(htmlspecialchars($row['deskripsi'])); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="text-primary hover:text-blue-900 mr-3">Edit</a>
                        <form action="" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus metode ini?');">
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
