<?php
// Start output buffering at the very beginning
ob_start();

require_once '../../includes/header.php';

// Require admin privileges
$auth->requireAdmin();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nama_siswa = $_POST['nama_siswa'];
        
        // Begin transaction
        $auth->getConnection()->beginTransaction();
        
        // Insert siswa
        $stmt = $auth->getConnection()->prepare("INSERT INTO siswa (nama) VALUES (?)");
        $stmt->execute([$nama_siswa]);
        
        // Commit transaction
        $auth->getConnection()->commit();
        
        header("Location: index.php?success=1");
        exit();
    } catch (PDOException $e) {
        $auth->getConnection()->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}
?>

<div class="bg-white shadow-md rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Tambah Data Siswa</h1>
        <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>

    <?php if (isset($error)): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p><?php echo $error; ?></p>
    </div>
    <?php endif; ?>

    <form action="create.php" method="POST" class="space-y-6">
        <div>
            <label for="nama_siswa" class="block text-sm font-medium text-gray-700 mb-2">
                Nama Siswa
            </label>
            <input type="text" id="nama_siswa" name="nama_siswa" required 
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-primary hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-save mr-2"></i>Simpan
            </button>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
<?php
// Flush the output buffer
ob_end_flush();
?>