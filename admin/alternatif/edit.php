<?php
require_once '../../includes/auth.php';
$auth->requireLogin();
$auth->requireAdmin();

// Get database connection
$database = new Database();
$conn = $database->getConnection();

$error = '';
$success = false;
$alternatif = null;

// Get alternatif by ID
if (isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM alternatif WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $alternatif = $stmt->fetch();
    
    if (!$alternatif) {
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_metode = trim($_POST['nama_metode'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    
    // Validation
    if (empty($nama_metode)) {
        $error = 'Nama metode harus diisi';
    } else {
        try {
            // Update alternative
            $stmt = $conn->prepare("UPDATE alternatif SET nama_metode = ?, deskripsi = ? WHERE id = ?");
            $stmt->execute([$nama_metode, $deskripsi, $_GET['id']]);
            
            header("Location: index.php?success=1");
            exit();
        } catch (PDOException $e) {
            $error = 'Gagal mengupdate metode: ' . $e->getMessage();
        }
    }
}

require_once '../../includes/header.php';
?>

<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Edit Metode Pengajaran</h1>
        <a href="index.php" class="text-primary hover:text-blue-700">Kembali</a>
    </div>

    <?php if ($error): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
    </div>
    <?php endif; ?>

    <form action="" method="POST" class="space-y-6">
        <div>
            <label for="nama_metode" class="block text-sm font-medium text-gray-700">Nama Metode</label>
            <input type="text" name="nama_metode" id="nama_metode" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                value="<?php echo htmlspecialchars($_POST['nama_metode'] ?? $alternatif['nama_metode']); ?>">
        </div>

        <div>
            <label for="deskripsi" class="block text-sm font-medium text-gray-700">Deskripsi</label>
            <textarea name="deskripsi" id="deskripsi" rows="4"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
            ><?php echo htmlspecialchars($_POST['deskripsi'] ?? $alternatif['deskripsi']); ?></textarea>
            <p class="mt-1 text-sm text-gray-500">Jelaskan metode pengajaran secara detail.</p>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                Update Metode
            </button>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
