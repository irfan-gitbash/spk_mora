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

// Get all alternatives and criteria
$alternatifs = $conn->query("SELECT * FROM alternatif ORDER BY id")->fetchAll();
$kriterias = $conn->query("SELECT * FROM kriteria ORDER BY id")->fetchAll();

// Get penilaian data for this siswa
$stmt = $conn->prepare("SELECT * FROM penilaian_siswa WHERE id_siswa = ?");
$stmt->execute([$id_siswa]);
$penilaians = [];
while ($row = $stmt->fetch()) {
    $penilaians[$row['id_alternatif']][$row['id_kriteria']] = [
        'nilai' => $row['nilai'],
        'gaya_belajar' => $row['gaya_belajar']
    ];
}

// Get hasil perhitungan for this siswa
$stmt = $conn->prepare("
    SELECT h.ranking, h.nilai_akhir, a.id as id_alternatif, a.nama_metode
    FROM hasil_siswa h
    JOIN alternatif a ON h.id_alternatif = a.id
    WHERE h.id_siswa = ?
    ORDER BY h.ranking ASC
");
$stmt->execute([$id_siswa]);
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

    <!-- Penilaian Table -->
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Data Penilaian</h2>
    <div class="overflow-x-auto mb-8">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Strategi</th>
                    <?php foreach ($kriterias as $kriteria): ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <?php echo htmlspecialchars($kriteria['nama_kriteria']); ?>
                    </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($alternatifs as $alternatif): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        <?php echo htmlspecialchars($alternatif['nama_metode']); ?>
                    </td>
                    <?php foreach ($kriterias as $kriteria): ?>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?php 
                        if (isset($penilaians[$alternatif['id']][$kriteria['id']])) {
                            if ($kriteria['nama_kriteria'] == 'Gaya belajar' && !empty($penilaians[$alternatif['id']][$kriteria['id']]['gaya_belajar'])) {
                                echo htmlspecialchars($penilaians[$alternatif['id']][$kriteria['id']]['gaya_belajar']);
                            } else {
                                echo $penilaians[$alternatif['id']][$kriteria['id']]['nilai'];
                            }
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Hasil Perhitungan -->
    <?php if (!empty($hasil)): ?>
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Hasil Perhitungan MOORA</h2>
    <div class="overflow-x-auto mb-8">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ranking</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Strategi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Optimasi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($hasil as $result): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            <?php echo $result['ranking'] <= 3 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                            <?php echo $result['ranking']; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?php echo htmlspecialchars($result['nama_metode']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?php echo number_format($result['nilai_akhir'], 4); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Rekomendasi -->
    <div class="p-4 bg-blue-50 rounded-lg">
        <h2 class="text-lg font-semibold text-blue-900 mb-2">Rekomendasi Strategi Pembelajaran</h2>
        <p class="text-blue-800">
            Berdasarkan perhitungan metode MOORA, strategi pembelajaran yang paling direkomendasikan untuk
            <strong><?php echo htmlspecialchars($siswa['nama']); ?></strong> adalah:
        </p>
        <div class="mt-4 p-4 bg-white rounded-lg border border-blue-200">
            <h3 class="text-xl font-bold text-blue-900"><?php echo htmlspecialchars($hasil[0]['nama_metode']); ?></h3>
            <p class="mt-2 text-blue-800">
                Dengan nilai optimasi: <strong><?php echo number_format($hasil[0]['nilai_akhir'], 4); ?></strong>
            </p>
        </div>
    </div>
    <?php else: ?>
    <div class="p-4 bg-yellow-50 rounded-lg">
        <p class="text-yellow-800">
            Belum ada hasil perhitungan untuk siswa ini. Silakan lakukan perhitungan terlebih dahulu.
        </p>
        <form method="POST" action="calculate.php" class="mt-4">
            <input type="hidden" name="id_siswa" value="<?php echo $id_siswa; ?>">
            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                Hitung Rekomendasi
            </button>
        </form>
    </div>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>