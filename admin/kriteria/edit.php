<?php
require_once '../../includes/auth.php';
$auth->requireLogin();
$auth->requireAdmin();

// Get database connection
$database = new Database();
$conn = $database->getConnection();

$error = '';
$success = false;
$kriteria = null;

// Get kriteria by ID
if (isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM kriteria WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $kriteria = $stmt->fetch();
    
    if (!$kriteria) {
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kriteria = trim($_POST['nama_kriteria'] ?? '');
    $bobot = trim($_POST['bobot'] ?? '');
    $tipe = $_POST['tipe'] ?? '';
    
    // Validation
    if (empty($nama_kriteria)) {
        $error = 'Nama kriteria harus diisi';
    } elseif (!is_numeric($bobot) || $bobot <= 0) {
        $error = 'Bobot harus berupa angka positif';
    } elseif (!in_array($tipe, ['benefit', 'cost'])) {
        $error = 'Tipe kriteria tidak valid';
    } else {
        try {
            // Check if total weights would exceed 1 (excluding current weight)
            $stmt = $conn->prepare("SELECT SUM(bobot) as total FROM kriteria WHERE id != ?");
            $stmt->execute([$_GET['id']]);
            $current_total = $stmt->fetch()['total'];
            $new_total = $current_total + $bobot;
            
            if ($new_total > 1) {
                $error = 'Total bobot tidak boleh melebihi 1. Sisa bobot yang tersedia: ' . number_format(1 - $current_total, 2);
            } else {
                // Update criteria
                $stmt = $conn->prepare("UPDATE kriteria SET nama_kriteria = ?, bobot = ?, tipe = ? WHERE id = ?");
                $stmt->execute([$nama_kriteria, $bobot, $tipe, $_GET['id']]);
                
                header("Location: index.php?success=1");
                exit();
            }
        } catch (PDOException $e) {
            $error = 'Gagal mengupdate kriteria: ' . $e->getMessage();
        }
    }
}

require_once '../../includes/header.php';
?>

<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Edit Kriteria</h1>
        <a href="index.php" class="text-primary hover:text-blue-700">Kembali</a>
    </div>

    <?php if ($error): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
    </div>
    <?php endif; ?>

    <form action="" method="POST" class="space-y-6">
        <div>
            <label for="nama_kriteria" class="block text-sm font-medium text-gray-700">Nama Kriteria</label>
            <input type="text" name="nama_kriteria" id="nama_kriteria" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                value="<?php echo htmlspecialchars($_POST['nama_kriteria'] ?? $kriteria['nama_kriteria']); ?>">
        </div>

        <div>
            <label for="bobot" class="block text-sm font-medium text-gray-700">Bobot</label>
            <input type="number" name="bobot" id="bobot" required step="0.01" min="0" max="1"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                value="<?php echo htmlspecialchars($_POST['bobot'] ?? $kriteria['bobot']); ?>">
            <p class="mt-1 text-sm text-gray-500">Masukkan nilai antara 0 dan 1. Total bobot semua kriteria tidak boleh melebihi 1.</p>
        </div>

        <div>
            <label for="tipe" class="block text-sm font-medium text-gray-700">Tipe</label>
            <select name="tipe" id="tipe" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                <option value="">Pilih Tipe</option>
                <option value="benefit" <?php echo (isset($_POST['tipe']) ? $_POST['tipe'] === 'benefit' : $kriteria['tipe'] === 'benefit') ? 'selected' : ''; ?>>Benefit</option>
                <option value="cost" <?php echo (isset($_POST['tipe']) ? $_POST['tipe'] === 'cost' : $kriteria['tipe'] === 'cost') ? 'selected' : ''; ?>>Cost</option>
            </select>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                Update Kriteria
            </button>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
