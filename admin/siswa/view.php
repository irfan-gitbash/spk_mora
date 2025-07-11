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

// Get all siswa data
$stmt = $conn->prepare("SELECT * FROM siswa ORDER BY id ASC");
$stmt->execute();
$hasil = $stmt->fetchAll();

require_once '../../includes/header.php';
?>

<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Detail Siswa: <?php echo htmlspecialchars($siswa['nama']); ?></h1>
        <div>
            <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded mr-2">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
            <a href="edit.php?id=<?php echo $id_siswa; ?>" class="bg-primary hover:bg-blue-700 text-white px-4 py-2 rounded">
                <i class="fas fa-edit mr-2"></i>Edit
            </a>
        </div>
    </div>

    <!-- Data Siswa -->
    <?php if (!empty($hasil)): ?>
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Daftar Nama Siswa</h2>
    <div class="overflow-x-auto mb-8">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Siswa</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($hasil as $result): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?php echo isset($result['nama']) ? htmlspecialchars($result['nama']) : '-'; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Informasi Siswa yang Dipilih -->
    <div class="p-4 bg-blue-50 rounded-lg">
        <h2 class="text-lg font-semibold text-blue-900 mb-2">Informasi Siswa</h2>
        <p class="text-blue-800">
            Data siswa yang sedang dilihat:
            <strong><?php echo htmlspecialchars($siswa['nama']); ?></strong>
        </p>
        <div class="mt-4 p-4 bg-white rounded-lg border border-blue-200">
            <h3 class="text-xl font-bold text-blue-900"><?php echo isset($siswa['nama']) ? htmlspecialchars($siswa['nama']) : 'Nama tidak tersedia'; ?></h3>
        </div>
    </div>
    <?php else: ?>
    <div class="p-4 bg-yellow-50 rounded-lg">
        <p class="text-yellow-800">
            Tidak ada data siswa yang ditemukan.
        </p>
    </div>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>