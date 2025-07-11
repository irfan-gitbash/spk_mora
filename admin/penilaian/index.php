<?php
require_once '../../includes/auth.php';
require_once '../../moora/Moora.php';
$auth->requireLogin();
$auth->requireAdmin();

// Get database connection
$database = new Database();
$conn = $database->getConnection();

// Get all alternatives and criteria
$alternatifs = $conn->query("SELECT * FROM alternatif ORDER BY id")->fetchAll();

// Define criteria with proper weights based on MOORA methodology
$kriterias = [
    ['id' => 1, 'nama' => 'Kemampuan Grammar', 'bobot' => 0.15, 'tipe' => 'benefit'],
    ['id' => 2, 'nama' => 'Kemampuan Speaking', 'bobot' => 0.15, 'tipe' => 'benefit'],
    ['id' => 3, 'nama' => 'Motivasi Belajar', 'bobot' => 0.10, 'tipe' => 'benefit'],
    ['id' => 4, 'nama' => 'Gaya Belajar', 'bobot' => 0.10, 'tipe' => 'benefit'],
    ['id' => 5, 'nama' => 'Kecocokan Strategi', 'bobot' => 0.15, 'tipe' => 'benefit'],
    ['id' => 6, 'nama' => 'Durasi (menit)', 'bobot' => 0.10, 'tipe' => 'cost'],
    ['id' => 7, 'nama' => 'Bobot Kognitif', 'bobot' => 0.25, 'tipe' => 'benefit']
];

// Get kriteria data from database
$kriteria_data = [];
$stmt = $conn->query("
    SELECT k.*, s.nama as nama_siswa 
    FROM kriteria k 
    LEFT JOIN siswa s ON k.id_siswa = s.id 
    ORDER BY s.nama, k.strategi
");
while ($row = $stmt->fetch()) {
    $kriteria_data[] = $row;
}

// Handle MOORA calculation
$moora_results = [];
$decision_matrix = [];
$normalized_matrix = [];
$weighted_matrix = [];
$optimization_results = [];
$calculation_performed = false;
$denominators = [];
$sum_squares_details = [];
$normalization_details = [];
$weighting_details = [];
$optimization_details = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['calculate_moora'])) {
    try {
        // Step 1: Build Decision Matrix from kriteria data
        foreach ($kriteria_data as $data) {
            $student_name = $data['nama_siswa'];
            $strategy = $data['strategi'];
            $alternative_key = $student_name . ' - ' . $strategy;
            
            $decision_matrix[$alternative_key] = [
                1 => (float)($data['kemampuan_grammar'] ?? 0),
                2 => (float)($data['kemampuan_speaking'] ?? 0),
                3 => (float)($data['motivasi_belajar'] ?? 0),
                4 => $data['gaya_belajar'] === 'visual' ? 3 : ($data['gaya_belajar'] === 'auditori' ? 2 : 1),
                5 => (float)($data['kecocokan_strategi'] ?? 0),
                6 => (float)($data['durasi'] ?? 0),
                7 => (float)($data['bobot_kognitif'] ?? 0)
            ];
        }
        
        // Step 2: Detailed Normalization using MOORA method
        // Calculate sum of squares and denominators for each criterion
        foreach ($kriterias as $kriteria) {
            $sum_squares = 0;
            $values_for_criterion = [];
            
            foreach ($decision_matrix as $alternative => $values) {
                $value = $values[$kriteria['id']];
                $values_for_criterion[] = $value;
                $sum_squares += pow($value, 2);
            }
            
            $sum_squares_details[$kriteria['id']] = [
                'values' => $values_for_criterion,
                'sum_squares' => $sum_squares,
                'sqrt_sum_squares' => sqrt($sum_squares)
            ];
            
            $denominators[$kriteria['id']] = sqrt($sum_squares);
        }
        
        // Normalize each value with detailed calculation
        foreach ($decision_matrix as $alternative => $values) {
            foreach ($kriterias as $kriteria) {
                $original_value = $values[$kriteria['id']];
                $denominator = $denominators[$kriteria['id']];
                $normalized_value = $denominator != 0 ? $original_value / $denominator : 0;
                
                $normalized_matrix[$alternative][$kriteria['id']] = $normalized_value;
                
                $normalization_details[$alternative][$kriteria['id']] = [
                    'original' => $original_value,
                    'denominator' => $denominator,
                    'normalized' => $normalized_value,
                    'formula' => "$original_value / $denominator = $normalized_value"
                ];
            }
        }
        
        // Step 3: Apply weights to normalized matrix with detailed calculation
        foreach ($normalized_matrix as $alternative => $values) {
            foreach ($kriterias as $kriteria) {
                $normalized_value = $values[$kriteria['id']];
                $weight = $kriteria['bobot'];
                $weighted_value = $normalized_value * $weight;
                
                $weighted_matrix[$alternative][$kriteria['id']] = $weighted_value;
                
                $weighting_details[$alternative][$kriteria['id']] = [
                    'normalized' => $normalized_value,
                    'weight' => $weight,
                    'weighted' => $weighted_value,
                    'formula' => "$normalized_value × $weight = $weighted_value"
                ];
            }
        }
        
        // Step 4: Calculate optimization values with detailed breakdown
        foreach ($weighted_matrix as $alternative => $values) {
            $benefit_sum = 0;
            $cost_sum = 0;
            $benefit_details = [];
            $cost_details = [];
            
            foreach ($kriterias as $kriteria) {
                $weighted_value = $values[$kriteria['id']];
                
                if ($kriteria['tipe'] === 'benefit') {
                    $benefit_sum += $weighted_value;
                    $benefit_details[] = [
                        'kriteria' => $kriteria['nama'],
                        'value' => $weighted_value
                    ];
                } else {
                    $cost_sum += $weighted_value;
                    $cost_details[] = [
                        'kriteria' => $kriteria['nama'],
                        'value' => $weighted_value
                    ];
                }
            }
            
            $yi_value = $benefit_sum - $cost_sum;
            
            $optimization_results[$alternative] = [
                'benefit_sum' => $benefit_sum,
                'cost_sum' => $cost_sum,
                'yi_value' => $yi_value
            ];
            
            $optimization_details[$alternative] = [
                'benefit_details' => $benefit_details,
                'cost_details' => $cost_details,
                'benefit_sum' => $benefit_sum,
                'cost_sum' => $cost_sum,
                'yi_calculation' => "$benefit_sum - $cost_sum = $yi_value",
                'yi_value' => $yi_value
            ];
        }
        
        // Sort by Yi value (descending) for ranking
        uasort($optimization_results, function($a, $b) {
            return $b['yi_value'] <=> $a['yi_value'];
        });
        
        $calculation_performed = true;
        $success_message = "Perhitungan MOORA berhasil dilakukan dengan detail lengkap";
        
    } catch (Exception $e) {
        $error_message = 'Gagal melakukan perhitungan MOORA: ' . $e->getMessage();
    }
}

require_once '../../includes/header.php';
?>

<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Perhitungan MOORA (Multi-Objective Optimization by Ratio Analysis)</h1>
    </div>

    <?php if (isset($success_message)): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline"><?php echo htmlspecialchars($success_message); ?></span>
    </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
    </div>
    <?php endif; ?>

    <!-- Data Yang Terpilih dari Manajemen Kriteria -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Data Yang Terpilih dari Halaman Manajemen Kriteria</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 border">
                <thead class="bg-blue-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">Nama Siswa</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">Strategi</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border">Kemampuan Grammar</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border">Kemampuan Speaking</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border">Motivasi Belajar</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border">Gaya Belajar</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border">Kecocokan Strategi</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border">Durasi (menit)</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border">Bobot Kognitif</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php 
                    $no = 1;
                    foreach ($kriteria_data as $data): 
                        $gaya_belajar_display = '';
                        switch(strtolower($data['gaya_belajar'] ?? '')) {
                            case 'visual':
                                $gaya_belajar_display = 'Visual';
                                break;
                            case 'auditori':
                                $gaya_belajar_display = 'Auditori';
                                break;
                            case 'kinestetik':
                                $gaya_belajar_display = 'Kinestetik';
                                break;
                            default:
                                $gaya_belajar_display = ucfirst($data['gaya_belajar'] ?? 'N/A');
                        }
                    ?>
                    <tr class="<?php echo $no % 2 == 0 ? 'bg-gray-50' : 'bg-white'; ?>">
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 border"><?php echo $no++; ?></td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border"><?php echo htmlspecialchars($data['nama_siswa'] ?? 'N/A'); ?></td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 border">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                <?php echo htmlspecialchars($data['strategi'] ?? 'N/A'); ?>
                            </span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-gray-900 border"><?php echo $data['kemampuan_grammar'] ?? 0; ?></td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-gray-900 border"><?php echo $data['kemampuan_speaking'] ?? 0; ?></td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-gray-900 border"><?php echo $data['motivasi_belajar'] ?? 0; ?></td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-gray-900 border">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                <?php echo $gaya_belajar_display; ?>
                            </span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-gray-900 border"><?php echo $data['kecocokan_strategi'] ?? 0; ?></td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-gray-900 border"><?php echo $data['durasi'] ?? 0; ?></td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-gray-900 border"><?php echo $data['bobot_kognitif'] ?? 0; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Mulai Hitung Button -->
        <div class="mt-6 text-center">
            <form method="POST" class="inline">
                <button type="submit" name="calculate_moora" value="1" 
                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg shadow-lg transform transition hover:scale-105">
                    <i class="fas fa-calculator mr-2"></i>Mulai Hitung MOORA
                </button>
            </form>
        </div>
    </div>

    <?php if ($calculation_performed): ?>
    <!-- Methodology Explanation -->
    <div class="mb-8 p-6 bg-blue-50 rounded-lg border-l-4 border-blue-400">
        <h2 class="text-xl font-semibold text-blue-900 mb-4">📚 Metodologi MOORA (Multi-Objective Optimization by Ratio Analysis)</h2>
        <div class="text-blue-800 space-y-3">
            <p><strong>MOORA</strong> adalah metode pengambilan keputusan multi-kriteria yang dikembangkan oleh Brauers dan Zavadskas. Metode ini menggunakan pendekatan rasio untuk mengoptimalkan beberapa objektif secara bersamaan.</p>
            <p><strong>Keunggulan MOORA:</strong></p>
            <ul class="list-disc list-inside ml-4 space-y-1">
                <li>Perhitungan matematis yang sederhana dan mudah dipahami</li>
                <li>Tidak terpengaruh oleh teknik pembobotan dan prosedur normalisasi</li>
                <li>Memberikan hasil yang stabil dan konsisten</li>
                <li>Dapat menangani kriteria benefit dan cost secara bersamaan</li>
            </ul>
        </div>
    </div>

    <!-- Step 1: Decision Matrix -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">📊 Langkah 1: Pembentukan Matriks Keputusan</h2>
        <p class="text-sm text-gray-600 mb-4">Matriks keputusan (X) berisi nilai evaluasi setiap alternatif terhadap setiap kriteria. Matriks ini menjadi dasar untuk perhitungan selanjutnya.</p>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 border">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">Alternatif</th>
                        <?php foreach ($kriterias as $kriteria): ?>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border">
                            <?php echo htmlspecialchars($kriteria['nama']); ?><br>
                            <span class="text-xs text-blue-600">(<?php echo $kriteria['tipe']; ?>)</span><br>
                            <span class="text-xs text-green-600">w=<?php echo $kriteria['bobot']; ?></span>
                        </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($decision_matrix as $alternative => $values): ?>
                    <tr>
                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border">
                            <?php echo htmlspecialchars($alternative); ?>
                        </td>
                        <?php foreach ($kriterias as $kriteria): ?>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-gray-900 border">
                            <?php echo number_format($values[$kriteria['id']], 2); ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Step 2: Detailed Normalization Process -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">🔢 Langkah 2: Proses Normalisasi Detail</h2>
        <p class="text-sm text-gray-600 mb-4">Normalisasi menggunakan rumus MOORA: <strong>r<sub>ij</sub> = x<sub>ij</sub> / √(∑x<sub>ij</sub>²)</strong> untuk setiap kriteria j.</p>
        
        <!-- Denominators Calculation -->
        <div class="mb-6">
            <h3 class="text-lg font-medium text-gray-700 mb-3">📐 Perhitungan Denominator (Penyebut)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($kriterias as $kriteria): ?>
                <div class="bg-gray-50 p-4 rounded-lg border">
                    <h4 class="font-medium text-gray-800 mb-2"><?php echo htmlspecialchars($kriteria['nama']); ?></h4>
                    <div class="text-sm text-gray-600 space-y-1">
                        <p><strong>Nilai-nilai:</strong> [<?php echo implode(', ', array_map(function($v) { return number_format($v, 2); }, $sum_squares_details[$kriteria['id']]['values'])); ?>]</p>
                        <p><strong>∑x²:</strong> <?php echo number_format($sum_squares_details[$kriteria['id']]['sum_squares'], 4); ?></p>
                        <p><strong>√(∑x²):</strong> <?php echo number_format($sum_squares_details[$kriteria['id']]['sqrt_sum_squares'], 4); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Normalized Matrix -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 border">
                <thead class="bg-blue-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">Alternatif</th>
                        <?php foreach ($kriterias as $kriteria): ?>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border">
                            <?php echo htmlspecialchars($kriteria['nama']); ?><br>
                            <span class="text-xs text-blue-600">÷ <?php echo number_format($denominators[$kriteria['id']], 4); ?></span>
                        </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($normalized_matrix as $alternative => $values): ?>
                    <tr>
                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border">
                            <?php echo htmlspecialchars($alternative); ?>
                        </td>
                        <?php foreach ($kriterias as $kriteria): ?>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-gray-900 border" 
                            title="<?php echo $normalization_details[$alternative][$kriteria['id']]['formula']; ?>">
                            <?php echo number_format($values[$kriteria['id']], 4); ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Step 3: Weighted Normalized Matrix -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">⚖️ Langkah 3: Matriks Normalisasi Terbobot</h2>
        <p class="text-sm text-gray-600 mb-4">Setiap nilai normalisasi dikalikan dengan bobot kriteria: <strong>v<sub>ij</sub> = w<sub>j</sub> × r<sub>ij</sub></strong></p>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 border">
                <thead class="bg-green-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">Alternatif</th>
                        <?php foreach ($kriterias as $kriteria): ?>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border">
                            <?php echo htmlspecialchars($kriteria['nama']); ?><br>
                            <span class="text-xs text-blue-600">(w=<?php echo $kriteria['bobot']; ?>)</span>
                        </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($weighted_matrix as $alternative => $values): ?>
                    <tr>
                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border">
                            <?php echo htmlspecialchars($alternative); ?>
                        </td>
                        <?php foreach ($kriterias as $kriteria): ?>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-gray-900 border"
                            title="<?php echo $weighting_details[$alternative][$kriteria['id']]['formula']; ?>">
                            <?php echo number_format($values[$kriteria['id']], 4); ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Step 4: Detailed Optimization and Ranking -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">🎯 Langkah 4: Optimasi dan Perangkingan Detail</h2>
        <p class="text-sm text-gray-600 mb-4">Perhitungan nilai optimasi: <strong>Y<sub>i</sub> = ∑(benefit criteria) - ∑(cost criteria)</strong></p>
        
        <!-- Detailed Optimization Calculation -->
        <div class="mb-6">
            <h3 class="text-lg font-medium text-gray-700 mb-3">🧮 Detail Perhitungan Optimasi</h3>
            <div class="space-y-4">
                <?php foreach ($optimization_details as $alternative => $details): ?>
                <div class="bg-gray-50 p-4 rounded-lg border">
                    <h4 class="font-medium text-gray-800 mb-2"><?php echo htmlspecialchars($alternative); ?></h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <p class="font-medium text-green-700 mb-1">Benefit Criteria:</p>
                            <?php foreach ($details['benefit_details'] as $benefit): ?>
                            <p class="text-gray-600">• <?php echo $benefit['kriteria']; ?>: <?php echo number_format($benefit['value'], 4); ?></p>
                            <?php endforeach; ?>
                            <p class="font-medium text-green-600 mt-1">Total Benefit: <?php echo number_format($details['benefit_sum'], 4); ?></p>
                        </div>
                        <div>
                            <p class="font-medium text-red-700 mb-1">Cost Criteria:</p>
                            <?php foreach ($details['cost_details'] as $cost): ?>
                            <p class="text-gray-600">• <?php echo $cost['kriteria']; ?>: <?php echo number_format($cost['value'], 4); ?></p>
                            <?php endforeach; ?>
                            <p class="font-medium text-red-600 mt-1">Total Cost: <?php echo number_format($details['cost_sum'], 4); ?></p>
                        </div>
                        <div>
                            <p class="font-medium text-blue-700 mb-1">Perhitungan Yi:</p>
                            <p class="text-gray-600"><?php echo $details['yi_calculation']; ?></p>
                            <p class="font-bold text-blue-600 mt-1">Yi = <?php echo number_format($details['yi_value'], 4); ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Final Ranking Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 border">
                <thead class="bg-yellow-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">Peringkat</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border">Alternatif</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border">∑ Benefit</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border">∑ Cost</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border">Yi (Benefit - Cost)</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border">Status Peringkat</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php 
                    $rank = 1;
                    $total_alternatives = count($optimization_results);
                    foreach ($optimization_results as $alternative => $result): 
                        // Determine ranking status based on position
                        $ranking_status = '';
                        $status_class = '';
                        $status_icon = '';
                        
                        if ($rank == 1) {
                            $ranking_status = 'Terbaik (Optimal)';
                            $status_class = 'bg-yellow-100 text-yellow-800';
                            $status_icon = '🏆';
                        } elseif ($rank == 2) {
                            $ranking_status = 'Sangat Baik';
                            $status_class = 'bg-gray-100 text-gray-800';
                            $status_icon = '🥈';
                        } elseif ($rank == 3) {
                            $ranking_status = 'Baik';
                            $status_class = 'bg-orange-100 text-orange-800';
                            $status_icon = '🥉';
                        } elseif ($rank <= ceil($total_alternatives * 0.5)) {
                            $ranking_status = 'Cukup Baik';
                            $status_class = 'bg-blue-100 text-blue-800';
                            $status_icon = '⭐';
                        } else {
                            $ranking_status = 'Perlu Perbaikan';
                            $status_class = 'bg-red-100 text-red-800';
                            $status_icon = '📈';
                        }
                    ?>
                    <tr class="<?php echo $rank <= 3 ? 'bg-green-50' : ($rank <= ceil($total_alternatives * 0.5) ? 'bg-blue-50' : 'bg-red-50'); ?>">
                        <td class="px-4 py-4 whitespace-nowrap border">
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-bold rounded-full 
                                <?php echo $rank <= 3 ? 'bg-green-100 text-green-800' : ($rank <= ceil($total_alternatives * 0.5) ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'); ?>">
                                #<?php echo $rank; ?>
                            </span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border">
                            <?php echo htmlspecialchars($alternative); ?>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-green-600 font-medium border">
                            <?php echo number_format($result['benefit_sum'], 4); ?>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center text-red-600 font-medium border">
                            <?php echo number_format($result['cost_sum'], 4); ?>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center font-bold border
                            <?php echo $rank <= 3 ? 'text-green-600' : ($rank <= ceil($total_alternatives * 0.5) ? 'text-blue-600' : 'text-red-600'); ?>">
                            <?php echo number_format($result['yi_value'], 4); ?>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-center border">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $status_class; ?>">
                                <?php echo $status_icon; ?> <?php echo $ranking_status; ?>
                            </span>
                        </td>
                    </tr>
                    <?php 
                    $rank++;
                    endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Tombol Simpan Hasil -->
        <div class="mt-6 flex justify-center">
            <form method="POST" action="save_moora_results.php" class="inline">
                <input type="hidden" name="optimization_results" value="<?php echo htmlspecialchars(json_encode($optimization_results)); ?>">
                <input type="hidden" name="optimization_details" value="<?php echo htmlspecialchars(json_encode($optimization_details)); ?>">
                <input type="hidden" name="decision_matrix" value="<?php echo htmlspecialchars(json_encode($decision_matrix)); ?>">
                <input type="hidden" name="normalized_matrix" value="<?php echo htmlspecialchars(json_encode($normalized_matrix)); ?>">
                <input type="hidden" name="weighted_matrix" value="<?php echo htmlspecialchars(json_encode($weighted_matrix)); ?>">
                <input type="hidden" name="kriterias" value="<?php echo htmlspecialchars(json_encode($kriterias)); ?>">
                <input type="hidden" name="alternatifs" value="<?php echo htmlspecialchars(json_encode($alternatifs)); ?>">
                
                <button type="submit" 
                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg transform transition hover:scale-105 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                    </svg>
                    💾 Simpan Hasil ke Database
                </button>
            </form>
        </div>
    </div>

    <!-- Enhanced Explanation -->
    <div class="p-6 bg-blue-50 rounded-lg border-l-4 border-blue-400">
        <h2 class="text-lg font-semibold text-blue-900 mb-4">📖 Penjelasan Lengkap Perhitungan MOORA</h2>
        <div class="text-blue-800 space-y-3">
            <div>
                <p><strong>🔸 Langkah 1 - Pembentukan Matriks Keputusan:</strong></p>
                <p class="ml-4">Matriks keputusan X berisi nilai evaluasi setiap alternatif (i) terhadap setiap kriteria (j). Matriks ini menjadi input dasar untuk perhitungan MOORA.</p>
            </div>
            
            <div>
                <p><strong>🔸 Langkah 2 - Normalisasi:</strong></p>
                <p class="ml-4">Menggunakan rumus: <code>r<sub>ij</sub> = x<sub>ij</sub> / √(∑<sub>i=1</sub><sup>m</sup> x<sub>ij</sub>²)</code></p>
                <p class="ml-4">Normalisasi ini memastikan semua kriteria memiliki skala yang sebanding dan menghilangkan pengaruh satuan pengukuran yang berbeda.</p>
            </div>
            
            <div>
                <p><strong>🔸 Langkah 3 - Pembobotan:</strong></p>
                <p class="ml-4">Setiap nilai normalisasi dikalikan dengan bobot kriteria: <code>v<sub>ij</sub> = w<sub>j</sub> × r<sub>ij</sub></code></p>
                <p class="ml-4">Pembobotan memberikan tingkat kepentingan yang berbeda untuk setiap kriteria sesuai dengan preferensi pengambil keputusan.</p>
            </div>
            
            <div>
                <p><strong>🔸 Langkah 4 - Optimasi dan Perangkingan:</strong></p>
                <p class="ml-4">Perhitungan nilai optimasi: <code>Y<sub>i</sub> = ∑<sub>j=1</sub><sup>g</sup> v<sub>ij</sub> - ∑<sub>j=g+1</sub><sup>n</sup> v<sub>ij</sub></code></p>
                <p class="ml-4">Di mana g adalah jumlah kriteria benefit, dan (n-g) adalah jumlah kriteria cost.</p>
                <p class="ml-4"><strong>Interpretasi Peringkat:</strong></p>
                <ul class="ml-8 list-disc space-y-1">
                    <li><strong>Peringkat 1:</strong> Alternatif terbaik dengan nilai Yi tertinggi</li>
                    <li><strong>Peringkat 2-3:</strong> Alternatif dengan performa sangat baik hingga baik</li>
                    <li><strong>Peringkat Tengah:</strong> Alternatif dengan performa cukup baik</li>
                    <li><strong>Peringkat Bawah:</strong> Alternatif yang memerlukan perbaikan</li>
                </ul>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal Edit Form -->
<div id="editModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Edit Penilaian</h3>
            <form id="penilaianForm" method="POST" class="space-y-4">
                <input type="hidden" name="id_alternatif" id="id_alternatif">
                
                <?php foreach ($kriterias as $kriteria): ?>
                <div>
                    <label for="nilai_<?php echo $kriteria['id']; ?>" class="block text-sm font-medium text-gray-700">
                        <?php echo htmlspecialchars($kriteria['nama']); ?>
                    </label>
                    <input type="number" 
                           name="nilai[<?php echo $kriteria['id']; ?>]" 
                           id="nilai_<?php echo $kriteria['id']; ?>"
                           step="0.01"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm"
                           required>
                </div>
                <?php endforeach; ?>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeEditForm()"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">
                        Batal
                    </button>
                    <button type="submit"
                            class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showEditForm(alternatifId) {
    document.getElementById('id_alternatif').value = alternatifId;
    
    // Fetch existing values
    <?php foreach ($alternatifs as $alternatif): ?>
    if (alternatifId === <?php echo $alternatif['id']; ?>) {
        <?php foreach ($kriterias as $kriteria): ?>
        document.getElementById('nilai_<?php echo $kriteria['id']; ?>').value = 
            '<?php echo isset($evaluations[$alternatif['id']][$kriteria['id']]) ? $evaluations[$alternatif['id']][$kriteria['id']] : ''; ?>';
        <?php endforeach; ?>
    }
    <?php endforeach; ?>
    
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditForm() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>

<?php require_once '../../includes/footer.php'; ?>
