<?php
require_once 'includes/auth.php';
require_once 'moora/Moora.php';

$auth->requireLogin();

// Get database connection
$database = new Database();
$conn = $database->getConnection();

$error = '';
$results = null;

// Handle calculation request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        error_log("Starting MOORA calculation from hasil.php");
        $moora = new Moora($conn);
        $results = $moora->calculate();
        error_log("MOORA calculation completed. Results: " . json_encode($results));
    } catch (Exception $e) {
        error_log("Error in MOORA calculation: " . $e->getMessage());
        $error = 'Gagal melakukan perhitungan: ' . $e->getMessage();
    }
}

// Get latest results if not calculating
if (!$results) {
    try {
        error_log("Fetching latest results from database");
        $stmt = $conn->prepare("
            SELECT h.ranking, h.nilai_akhir, a.id as id_alternatif, a.nama_metode
            FROM hasil h
            JOIN alternatif a ON h.id_alternatif = a.id
            WHERE h.tanggal_perhitungan = (
                SELECT MAX(tanggal_perhitungan) FROM hasil
            )
            ORDER BY h.ranking ASC
        ");
        $stmt->execute();
        $results = $stmt->fetchAll();
        error_log("Latest results fetched: " . json_encode($results));
    } catch (PDOException $e) {
        error_log("Error fetching results: " . $e->getMessage());
        $error = 'Gagal mengambil hasil perhitungan: ' . $e->getMessage();
    }
}

require_once 'includes/header.php';
?>

<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Hasil Perhitungan MOORA</h1>
        <form method="POST" class="inline">
            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                Hitung Ulang
            </button>
        </form>
    </div>

    <?php if ($error): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
    </div>
    <?php endif; ?>

    <?php if (empty($results)): ?>
    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline">Belum ada hasil perhitungan.</span>
    </div>
    <?php else: ?>
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

    <!-- Explanation -->
    <div class="mt-8 p-4 bg-blue-50 rounded-lg">
        <h2 class="text-lg font-semibold text-blue-900 mb-2">Interpretasi Hasil</h2>
        <p class="text-blue-800">
            Hasil perhitungan menggunakan metode MOORA menunjukkan bahwa:
        </p>
        <ul class="list-disc list-inside mt-2 space-y-2 text-blue-800">
            <li>
                <strong><?php echo htmlspecialchars($results[0]['nama_metode']); ?></strong> 
                merupakan metode pengajaran yang paling direkomendasikan dengan nilai optimasi 
                <?php echo number_format($results[0]['nilai_akhir'], 4); ?>.
            </li>
            <?php if (count($results) > 1): ?>
            <li>
                Diikuti oleh <strong><?php echo htmlspecialchars($results[1]['nama_metode']); ?></strong> 
                di peringkat kedua dengan nilai <?php echo number_format($results[1]['nilai_akhir'], 4); ?>.
            </li>
            <?php endif; ?>
            <li>
                Perhitungan ini mempertimbangkan semua kriteria yang telah ditentukan beserta bobotnya.
            </li>
        </ul>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
