<?php
require_once 'includes/auth.php';

$auth->requireLogin();

// Get user data
$user = $auth->getCurrentUser();
require_once 'includes/header.php';
?>

<!-- Hero Section -->
<div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl p-6 md:p-8 mb-8 shadow-lg">
    <div class="flex flex-col md:flex-row items-center justify-between">
        <div class="mb-4 md:mb-0">
            <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold mb-2">Dashboard SPK MOORA</h1>
            <p class="text-blue-100 text-sm md:text-base">Sistem Pendukung Keputusan Multi-Objective Optimization by Ratio Analysis</p>
            <p class="text-blue-200 text-xs md:text-sm mt-1">Selamat datang, <?php echo htmlspecialchars($user['username']); ?></p>
        </div>
        <div class="hidden md:block">
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4">
                <i class="fas fa-chart-line text-3xl text-blue-200"></i>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats Cards -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 mb-8">
    <!-- Kriteria Card -->
    <div class="bg-white rounded-xl p-4 md:p-6 shadow-lg hover:shadow-xl transition-shadow duration-300 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm md:text-lg font-semibold text-gray-700 mb-1">Kriteria</h3>
                <p class="text-2xl md:text-3xl font-bold text-blue-600">
                    <?php
                    $stmt = $auth->getConnection()->query("SELECT COUNT(*) as total FROM kriteria");
                    echo $stmt->fetch()['total'];
                    ?>
                </p>
                <p class="text-xs text-gray-500 mt-1">Total kriteria</p>
            </div>
            <div class="bg-blue-100 p-2 md:p-3 rounded-lg">
                <i class="fas fa-tasks text-blue-600 text-lg md:text-xl"></i>
            </div>
        </div>
    </div>
    
    <!-- Alternatif Card -->
    <div class="bg-white rounded-xl p-4 md:p-6 shadow-lg hover:shadow-xl transition-shadow duration-300 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm md:text-lg font-semibold text-gray-700 mb-1">Alternatif</h3>
                <p class="text-2xl md:text-3xl font-bold text-green-600">
                    <?php
                    $stmt = $auth->getConnection()->query("SELECT COUNT(*) as total FROM alternatif");
                    echo $stmt->fetch()['total'];
                    ?>
                </p>
                <p class="text-xs text-gray-500 mt-1">Metode pembelajaran</p>
            </div>
            <div class="bg-green-100 p-2 md:p-3 rounded-lg">
                <i class="fas fa-list-alt text-green-600 text-lg md:text-xl"></i>
            </div>
        </div>
    </div>
    
    <!-- Data Penilaian Card -->
    <div class="bg-white rounded-xl p-4 md:p-6 shadow-lg hover:shadow-xl transition-shadow duration-300 border-l-4 border-purple-500">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm md:text-lg font-semibold text-gray-700 mb-1">Penilaian</h3>
                <p class="text-2xl md:text-3xl font-bold text-purple-600">
                    <?php
                    $stmt = $auth->getConnection()->query("SELECT COUNT(*) as total FROM kriteria");
                    echo $stmt->fetch()['total'];
                    ?>
                </p>
                <p class="text-xs text-gray-500 mt-1">Data penilaian</p>
            </div>
            <div class="bg-purple-100 p-2 md:p-3 rounded-lg">
                <i class="fas fa-star-half-alt text-purple-600 text-lg md:text-xl"></i>
            </div>
        </div>
    </div>
    
    <!-- Perhitungan MOORA Card -->
    <div class="bg-white rounded-xl p-4 md:p-6 shadow-lg hover:shadow-xl transition-shadow duration-300 border-l-4 border-yellow-500">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm md:text-lg font-semibold text-gray-700 mb-1">MOORA</h3>
                <p class="text-2xl md:text-3xl font-bold text-yellow-600">
                    <?php
                    $stmt = $auth->getConnection()->query("SELECT COUNT(*) as total FROM sesi_perhitungan_moora");
                    echo $stmt->fetch()['total'];
                    ?>
                </p>
                <p class="text-xs text-gray-500 mt-1">Perhitungan</p>
            </div>
            <div class="bg-yellow-100 p-2 md:p-3 rounded-lg">
                <i class="fas fa-calculator text-yellow-600 text-lg md:text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Chart and Results Section -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 md:gap-8 mb-8">
    <!-- Chart Section -->
    <div class="xl:col-span-2">
        <div class="bg-white rounded-xl shadow-lg p-4 md:p-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4">
                <h2 class="text-lg md:text-xl font-semibold text-gray-900 mb-2 sm:mb-0">Grafik Hasil Perhitungan MOORA</h2>
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        <i class="fas fa-chart-bar mr-1"></i>
                        Analisis
                    </span>
                </div>
            </div>
            <div class="w-full h-64 md:h-80 lg:h-96">
                <canvas id="resultChart" class="w-full h-full"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <?php if ($auth->isAdmin()): ?>
    <div class="xl:col-span-1">
        <div class="bg-white rounded-xl shadow-lg p-4 md:p-6">
            <h2 class="text-lg md:text-xl font-semibold text-gray-900 mb-4">Aksi Cepat</h2>
            <div class="space-y-3">
                <a href="admin/kriteria/create.php" class="block p-4 bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-lg hover:from-blue-100 hover:to-blue-200 transition-all duration-200 group">
                    <div class="flex items-center">
                        <div class="bg-blue-500 p-2 rounded-lg mr-3 group-hover:bg-blue-600 transition-colors">
                            <i class="fas fa-plus text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 text-sm">Tambah Kriteria</h3>
                            <p class="text-gray-600 text-xs">Kriteria penilaian baru</p>
                        </div>
                    </div>
                </a>
                
                <a href="admin/alternatif/create.php" class="block p-4 bg-gradient-to-r from-green-50 to-green-100 border border-green-200 rounded-lg hover:from-green-100 hover:to-green-200 transition-all duration-200 group">
                    <div class="flex items-center">
                        <div class="bg-green-500 p-2 rounded-lg mr-3 group-hover:bg-green-600 transition-colors">
                            <i class="fas fa-plus text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 text-sm">Tambah Alternatif</h3>
                            <p class="text-gray-600 text-xs">Metode pengajaran baru</p>
                        </div>
                    </div>
                </a>
                
                <a href="admin/penilaian/index.php" class="block p-4 bg-gradient-to-r from-purple-50 to-purple-100 border border-purple-200 rounded-lg hover:from-purple-100 hover:to-purple-200 transition-all duration-200 group">
                    <div class="flex items-center">
                        <div class="bg-purple-500 p-2 rounded-lg mr-3 group-hover:bg-purple-600 transition-colors">
                            <i class="fas fa-calculator text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 text-sm">Hitung MOORA</h3>
                            <p class="text-gray-600 text-xs">Mulai perhitungan baru</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Recent Results Table -->
<div class="bg-white rounded-xl shadow-lg overflow-hidden">
    <div class="px-4 md:px-6 py-4 border-b border-gray-200">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between">
            <h2 class="text-lg md:text-xl font-semibold text-gray-900 mb-2 sm:mb-0">Hasil Perhitungan MOORA Terakhir</h2>
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <i class="fas fa-trophy mr-1"></i>
                    Top 5
                </span>
            </div>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ranking</th>
                    <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metode Pembelajaran</th>
                    <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Nilai Optimasi (Yi)</th>
                    <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php
                $query = "SELECT h.ranking, h.nama_alternatif, h.yi_value, h.ranking_status
                         FROM hasil_moora h 
                         WHERE h.sesi_id = (
                             SELECT MAX(id) FROM sesi_perhitungan_moora
                         )
                         ORDER BY h.ranking ASC
                         LIMIT 5";
                $stmt = $auth->getConnection()->query($query);
                $results = $stmt->fetchAll();
                
                if (empty($results)): ?>
                <tr>
                    <td colspan="4" class="px-4 md:px-6 py-8 text-center">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-chart-line text-4xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500 mb-2">Belum ada hasil perhitungan MOORA</p>
                            <a href="admin/penilaian/index.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-calculator mr-2"></i>
                                Mulai Perhitungan
                            </a>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($results as $row): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <?php if ($row['ranking'] == 1): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-crown mr-1"></i>
                                        #<?php echo $row['ranking']; ?>
                                    </span>
                                <?php elseif ($row['ranking'] <= 3): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                        <i class="fas fa-medal mr-1"></i>
                                        #<?php echo $row['ranking']; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                        #<?php echo $row['ranking']; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-4 md:px-6 py-4">
                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['nama_alternatif']); ?></div>
                        </td>
                        <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 hidden sm:table-cell">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <?php echo number_format($row['yi_value'], 4); ?>
                            </span>
                        </td>
                        <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                            <?php if ($row['ranking'] == 1): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-star mr-1"></i>
                                    <?php echo $row['ranking_status']; ?>
                                </span>
                            <?php elseif ($row['ranking'] <= 3): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                    <?php echo $row['ranking_status']; ?>
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                    <?php echo $row['ranking_status']; ?>
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// Get chart data untuk grafik
$chartQuery = "SELECT h.nama_alternatif, h.yi_value 
           FROM hasil_moora h 
           WHERE h.sesi_id = (
               SELECT MAX(id) FROM sesi_perhitungan_moora
           )
           ORDER BY h.ranking ASC
           LIMIT 10";
$chartStmt = $auth->getConnection()->query($chartQuery);
$chartData = $chartStmt->fetchAll();

$labels = array_map(function($row) { return $row['nama_alternatif']; }, $chartData);
$values = array_map(function($row) { return $row['yi_value']; }, $chartData);
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('resultChart').getContext('2d');
    
    <?php if (!empty($chartData)): ?>
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: 'Nilai Optimasi (Yi)',
                data: <?php echo json_encode($values); ?>,
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',   // blue-500
                    'rgba(16, 185, 129, 0.8)',   // emerald-500
                    'rgba(245, 158, 11, 0.8)',   // amber-500
                    'rgba(139, 92, 246, 0.8)',   // violet-500
                    'rgba(236, 72, 153, 0.8)',   // pink-500
                    'rgba(34, 197, 94, 0.8)',    // green-500
                    'rgba(239, 68, 68, 0.8)',    // red-500
                    'rgba(168, 85, 247, 0.8)',   // purple-500
                    'rgba(14, 165, 233, 0.8)',   // sky-500
                    'rgba(132, 204, 22, 0.8)'    // lime-500
                ],
                borderColor: [
                    'rgb(59, 130, 246)',
                    'rgb(16, 185, 129)',
                    'rgb(245, 158, 11)',
                    'rgb(139, 92, 246)',
                    'rgb(236, 72, 153)',
                    'rgb(34, 197, 94)',
                    'rgb(239, 68, 68)',
                    'rgb(168, 85, 247)',
                    'rgb(14, 165, 233)',
                    'rgb(132, 204, 22)'
                ],
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Perbandingan Nilai Optimasi Metode Pembelajaran (MOORA)',
                    font: {
                        size: window.innerWidth < 768 ? 12 : 16,
                        weight: 'bold'
                    },
                    color: '#374151'
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#374151',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Nilai Optimasi (Yi)',
                        font: {
                            size: window.innerWidth < 768 ? 10 : 12
                        },
                        color: '#6B7280'
                    },
                    grid: {
                        color: 'rgba(156, 163, 175, 0.3)'
                    },
                    ticks: {
                        font: {
                            size: window.innerWidth < 768 ? 9 : 11
                        },
                        color: '#6B7280'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Metode Pembelajaran',
                        font: {
                            size: window.innerWidth < 768 ? 10 : 12
                        },
                        color: '#6B7280'
                    },
                    grid: {
                        display: false
                    },
                    ticks: {
                        maxRotation: window.innerWidth < 768 ? 45 : 0,
                        minRotation: 0,
                        font: {
                            size: window.innerWidth < 768 ? 9 : 11
                        },
                        color: '#6B7280'
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            },
            animation: {
                duration: 1000,
                easing: 'easeInOutQuart'
            }
        }
    });
    <?php else: ?>
    // Display message when no data available
    const canvas = document.getElementById('resultChart');
    const ctx = canvas.getContext('2d');
    
    // Clear canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    // Draw no data message
    ctx.fillStyle = '#9CA3AF';
    ctx.font = '16px Inter, system-ui, sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText('Belum ada data untuk ditampilkan', canvas.width / 2, canvas.height / 2 - 10);
    ctx.font = '14px Inter, system-ui, sans-serif';
    ctx.fillText('Mulai perhitungan MOORA untuk melihat grafik', canvas.width / 2, canvas.height / 2 + 15);
    <?php endif; ?>
});

// Handle responsive chart resize
window.addEventListener('resize', function() {
    // Chart.js automatically handles resize, but we can add custom logic here if needed
});
</script>

<?php require_once 'includes/footer.php'; ?>
