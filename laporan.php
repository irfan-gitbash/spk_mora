<?php
require_once 'includes/auth.php';
require_once 'moora/Moora.php';

$auth->requireLogin();

// Get database connection
$database = new Database();
$conn = $database->getConnection();

// Get all data for report
$kriterias = $conn->query("SELECT * FROM kriteria ORDER BY id")->fetchAll();
$alternatifs = $conn->query("SELECT * FROM alternatif ORDER BY id")->fetchAll();

// Get latest results
$stmt = $conn->prepare("
    SELECT h.*, a.nama_metode
    FROM hasil h
    JOIN alternatif a ON h.id_alternatif = a.id
    WHERE h.tanggal_perhitungan = (
        SELECT MAX(tanggal_perhitungan) FROM hasil
    )
    ORDER BY h.ranking ASC
");
$stmt->execute();
$results = $stmt->fetchAll();

// Get evaluations
$stmt = $conn->query("SELECT * FROM penilaian");
$penilaians = [];
while ($row = $stmt->fetch()) {
    $penilaians[$row['id_alternatif']][$row['id_kriteria']] = $row['nilai'];
}

require_once 'includes/header.php';
?>

<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Laporan Perhitungan MOORA</h1>
        <button onclick="window.print()" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            Cetak Laporan
        </button>
    </div>

    <!-- Report Header -->
    <div class="text-center mb-8">
        <h2 class="text-xl font-bold">LAPORAN HASIL PERHITUNGAN</h2>
        <h3 class="text-lg">Sistem Pendukung Keputusan Metode Pengajaran</h3>
        <h4>SMKS YAPRI JAKARTA</h4>
        <p class="text-gray-600">Tanggal: <?php echo date('d/m/Y'); ?></p>
    </div>

    <!-- Criteria Section -->
    <section class="mb-8">
        <h3 class="text-lg font-semibold mb-4">1. Data Kriteria</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kriteria</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bobot</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($kriterias as $i => $kriteria): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $i + 1; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo htmlspecialchars($kriteria['nama_kriteria']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo $kriteria['bobot']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo ucfirst($kriteria['tipe']); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Evaluation Matrix Section -->
    <section class="mb-8">
        <h3 class="text-lg font-semibold mb-4">2. Matriks Evaluasi</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alternatif</th>
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo htmlspecialchars($alternatif['nama_metode']); ?>
                        </td>
                        <?php foreach ($kriterias as $kriteria): ?>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo $penilaians[$alternatif['id']][$kriteria['id']] ?? '-'; ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Final Results Section -->
    <section class="mb-8">
        <h3 class="text-lg font-semibold mb-4">3. Hasil Akhir</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ranking</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metode Pengajaran</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Optimasi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($results as $result): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $result['ranking']; ?></td>
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
    </section>

    <!-- Conclusion Section -->
    <section class="mb-8">
        <h3 class="text-lg font-semibold mb-4">4. Kesimpulan</h3>
        <div class="bg-gray-50 p-4 rounded-lg">
            <p class="text-gray-800">
                Berdasarkan hasil perhitungan menggunakan metode MOORA, dapat disimpulkan bahwa:
            </p>
            <ul class="list-disc list-inside mt-2 space-y-2 text-gray-800">
                <li>
                    Metode pengajaran yang paling direkomendasikan adalah 
                    <strong><?php echo htmlspecialchars($results[0]['nama_metode']); ?></strong> 
                    dengan nilai optimasi <?php echo number_format($results[0]['nilai_akhir'], 4); ?>.
                </li>
                <?php if (count($results) > 1): ?>
                <li>
                    Diikuti oleh <strong><?php echo htmlspecialchars($results[1]['nama_metode']); ?></strong> 
                    di peringkat kedua dengan nilai <?php echo number_format($results[1]['nilai_akhir'], 4); ?>.
                </li>
                <?php endif; ?>
                <li>
                    Perhitungan ini telah mempertimbangkan semua kriteria yang telah ditentukan 
                    beserta bobotnya masing-masing.
                </li>
            </ul>
        </div>
    </section>

    <!-- Signature Section -->
    <section class="mt-12 text-center">
        <p class="mb-16">Jakarta, <?php echo date('d F Y'); ?></p>
        <p>( _________________________ )</p>
        <p>Kepala Sekolah</p>
    </section>
</div>

<!-- Print Styles -->
<style>
@media print {
    nav, button, .header-nav {
        display: none !important;
    }
    body {
        padding: 20px;
    }
    .shadow {
        box-shadow: none !important;
    }
    @page {
        size: A4;
        margin: 2cm;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
