<?php
require_once '../../includes/auth.php';
$auth->requireLogin();

// Get database connection
$database = new Database();
$conn = $database->getConnection();

// Get siswa ID from URL
$id_siswa = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$id_siswa) {
    header("Location: index.php");
    exit();
}

// Get siswa data
$stmt = $conn->prepare("SELECT * FROM siswa WHERE id = ?");
$stmt->execute([$id_siswa]);
$siswa = $stmt->fetch();

if (!$siswa) {
    header("Location: index.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nama_siswa = $_POST['nama_siswa'];
        
        // Update siswa (hanya nama)
        $stmt = $conn->prepare("UPDATE siswa SET nama = ? WHERE id = ?");
        $stmt->execute([$nama_siswa, $id_siswa]);
        
        header("Location: view.php?id={$id_siswa}&success=1");
        exit();
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

require_once '../../includes/header.php';
?>

<div class="bg-white shadow-md rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Edit Data Siswa</h1>
        <a href="view.php?id=<?php echo $id_siswa; ?>" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>

    <?php if (isset($error)): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p><?php echo $error; ?></p>
    </div>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
        <p>Data siswa berhasil diperbarui!</p>
    </div>
    <?php endif; ?>

    <form action="edit.php?id=<?php echo $id_siswa; ?>" method="POST" class="space-y-6">
        <div>
            <label for="nama_siswa" class="block text-sm font-medium text-gray-700 mb-2">
                Nama Siswa
            </label>
            <input type="text" id="nama_siswa" name="nama_siswa" required 
                   value="<?php echo htmlspecialchars($siswa['nama']); ?>"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
        </div>

        <div class="flex justify-end space-x-3">
            <a href="view.php?id=<?php echo $id_siswa; ?>" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-times mr-2"></i>Batal
            </a>
            <button type="submit" class="bg-primary hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-save mr-2"></i>Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>