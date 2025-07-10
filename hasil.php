<?php
require_once 'config/database.php';
require_once 'includes/header.php';

// Inisialisasi koneksi database
$database = new Database();
$pdo = $database->getConnection();

// Ambil ID sesi dari parameter URL atau ambil yang terbaru
$sesi_id = isset($_GET['sesi_id']) ? (int)$_GET['sesi_id'] : null;

if (!$sesi_id) {
    // Ambil sesi terbaru jika tidak ada ID yang diberikan
    $stmt = $pdo->query("SELECT id FROM sesi_perhitungan_moora ORDER BY created_at DESC LIMIT 1");
    $latest_session = $stmt->fetch();
    $sesi_id = $latest_session ? $latest_session['id'] : null;
}

if (!$sesi_id) {
    echo '<div class="container mx-auto px-4 py-8">';
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">';
    echo 'Tidak ada hasil perhitungan MOORA yang tersimpan.';
    echo '</div>';
    echo '</div>';
    require_once 'includes/footer.php';
    exit();
}

// Ambil informasi sesi
$stmt = $pdo->prepare("SELECT * FROM sesi_perhitungan_moora WHERE id = ?");
$stmt->execute([$sesi_id]);
$sesi_info = $stmt->fetch();

// Ambil hasil perhitungan
$stmt = $pdo->prepare("
    SELECT * FROM hasil_moora 
    WHERE sesi_id = ? 
    ORDER BY ranking ASC
");
$stmt->execute([$sesi_id]);
$hasil_moora = $stmt->fetchAll();

if (!$hasil_moora) {
    echo '<div class="container mx-auto px-4 py-8">';
    echo '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">';
    echo 'Hasil perhitungan tidak ditemukan untuk sesi ini.';
    echo '</div>';
    echo '</div>';
    require_once 'includes/footer.php';
    exit();
}
?>

<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">📊 Hasil Perhitungan MOORA</h1>
        <p class="text-gray-600">Sistem Pendukung Keputusan Pemilihan Metode Pembelajaran</p>
        <?php if ($sesi_info): ?>
        <div class="mt-4 text-sm text-gray-500">
            <p>Tanggal Perhitungan: <?php echo date('d/m/Y H:i:s', strtotime($sesi_info['tanggal_perhitungan'])); ?></p>
            <p>Jumlah Alternatif: <?php echo $sesi_info['jumlah_alternatif']; ?> | Jumlah Kriteria: <?php echo $sesi_info['jumlah_kriteria']; ?></p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Hasil Perangkingan -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white p-6">
            <h2 class="text-xl font-semibold">🏆 Hasil Perangkingan Metode Pembelajaran</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peringkat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metode Pembelajaran</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">∑ Benefit</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">∑ Cost</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Optimasi (Yi)</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($hasil_moora as $hasil): 
                        $rank = $hasil['ranking'];
                        $status_class = '';
                        $status_icon = '';
                        
                        if ($rank == 1) {
                            $status_class = 'bg-yellow-100 text-yellow-800';
                            $status_icon = '🏆';
                        } elseif ($rank == 2) {
                            $status_class = 'bg-gray-100 text-gray-800';
                            $status_icon = '🥈';
                        } elseif ($rank == 3) {
                            $status_class = 'bg-orange-100 text-orange-800';
                            $status_icon = '🥉';
                        } elseif ($rank <= ceil(count($hasil_moora) * 0.5)) {
                            $status_class = 'bg-blue-100 text-blue-800';
                            $status_icon = '⭐';
                        } else {
                            $status_class = 'bg-red-100 text-red-800';
                            $status_icon = '📈';
                        }
                    ?>
                    <tr class="<?php echo $rank <= 3 ? 'bg-green-50' : ($rank <= ceil(count($hasil_moora) * 0.5) ? 'bg-blue-50' : 'bg-red-50'); ?>">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-bold rounded-full 
                                <?php echo $rank <= 3 ? 'bg-green-100 text-green-800' : ($rank <= ceil(count($hasil_moora) * 0.5) ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'); ?>">
                                #<?php echo $rank; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($hasil['nama_alternatif']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="text-sm font-medium text-green-600"><?php echo number_format($hasil['benefit_sum'], 4); ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="text-sm font-medium text-red-600"><?php echo number_format($hasil['cost_sum'], 4); ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="text-sm font-bold <?php echo $rank <= 3 ? 'text-green-600' : ($rank <= ceil(count($hasil_moora) * 0.5) ? 'text-blue-600' : 'text-red-600'); ?>">
                                <?php echo number_format($hasil['yi_value'], 4); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $status_class; ?>">
                                <?php echo $status_icon; ?> <?php echo $hasil['ranking_status']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Interpretasi Hasil -->
    <?php if (!empty($hasil_moora)): 
        $terbaik = $hasil_moora[0]; // Peringkat 1
    ?>
    <div class="bg-gradient-to-r from-green-100 to-blue-100 rounded-lg p-6 border-l-4 border-green-500">
        <h3 class="text-lg font-semibold text-green-800 mb-3">🎯 Interpretasi Hasil</h3>
        <div class="text-green-700">
            <p class="mb-2">
                <strong>Metode pembelajaran terbaik:</strong> 
                <span class="font-bold text-green-800"><?php echo htmlspecialchars($terbaik['nama_alternatif']); ?></span>
            </p>
            <p class="mb-2">
                <strong>Nilai optimasi (Yi):</strong> 
                <span class="font-bold"><?php echo number_format($terbaik['yi_value'], 4); ?></span>
            </p>
            <p class="text-sm">
                Metode ini memiliki nilai optimasi tertinggi berdasarkan perhitungan MOORA, 
                yang menunjukkan bahwa metode ini paling optimal untuk diterapkan berdasarkan 
                kriteria yang telah ditetapkan.
            </p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tombol Aksi -->
    <div class="mt-8 flex justify-center space-x-4">
        <a href="admin/penilaian/index.php" 
           class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
            🔄 Hitung Ulang
        </a>
        <a href="download_pdf.php<?php echo $sesi_id ? '?sesi_id=' . $sesi_id : ''; ?>" 
           class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
            📄 Download PDF
        </a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
