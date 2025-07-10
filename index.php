<?php
require_once 'includes/header.php';

// Require login
$auth->requireLogin();

// Get user data
$user = $auth->getCurrentUser();
?>

<div class="bg-white shadow rounded-lg p-6">
    <h1 class="text-2xl font-bold text-gray-900 mb-4">Dashboard</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Quick Stats -->
        <div class="bg-blue-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold text-blue-700">Kriteria</h3>
            <p class="text-3xl font-bold text-blue-900">
                <?php
                $stmt = $auth->getConnection()->query("SELECT COUNT(*) as total FROM kriteria");
                echo $stmt->fetch()['total'];
                ?>
            </p>
        </div>
        
        <div class="bg-green-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold text-green-700">Alternatif</h3>
            <p class="text-3xl font-bold text-green-900">
                <?php
                $stmt = $auth->getConnection()->query("SELECT COUNT(*) as total FROM alternatif");
                echo $stmt->fetch()['total'];
                ?>
            </p>
        </div>
        
        <div class="bg-purple-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold text-purple-700">Penilaian</h3>
            <p class="text-3xl font-bold text-purple-900">
                <?php
                $stmt = $auth->getConnection()->query("SELECT COUNT(*) as total FROM penilaian_siswa");
                echo $stmt->fetch()['total'];
                ?>
            </p>
        </div>
        
        <div class="bg-yellow-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold text-yellow-700">Perhitungan</h3>
            <p class="text-3xl font-bold text-yellow-900">
                <?php
                $stmt = $auth->getConnection()->query("SELECT COUNT(DISTINCT tanggal_perhitungan) as total FROM hasil_siswa");
                echo $stmt->fetch()['total'];
                ?>
            </p>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="mt-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Grafik Hasil Perhitungan</h2>
        <div class="bg-white p-4 rounded-lg shadow">
            <div class="w-full h-[400px]">
                <canvas id="resultChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Results -->
    <div class="mt-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Hasil Perhitungan Terakhir</h2>
        <div class="bg-white border rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ranking</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metode Pengajaran</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php
                    $query = "SELECT h.ranking, a.nama_metode, h.nilai_akhir 
                             FROM hasil_siswa h 
                             JOIN alternatif a ON h.id_alternatif = a.id 
                             WHERE h.tanggal_perhitungan = (
                                 SELECT MAX(tanggal_perhitungan) FROM hasil_siswa
                             )
                             ORDER BY h.ranking ASC
                             LIMIT 5";
                    $stmt = $auth->getConnection()->query($query);
                    while ($row = $stmt->fetch()): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $row['ranking']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['nama_metode']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo number_format($row['nilai_akhir'], 4); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($auth->isAdmin()): ?>
    <!-- Quick Actions -->
    <div class="mt-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Aksi Cepat</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="/spk_mora/admin/kriteria/create.php" class="block p-6 bg-white border rounded-lg hover:bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900">Tambah Kriteria</h3>
                <p class="text-gray-600">Tambah kriteria baru untuk penilaian</p>
            </a>
            
            <a href="/spk_mora/admin/alternatif/create.php" class="block p-6 bg-white border rounded-lg hover:bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900">Tambah Alternatif</h3>
                <p class="text-gray-600">Tambah metode pengajaran baru</p>
            </a>
            
            <a href="/spk_mora/admin/penilaian/create.php" class="block p-6 bg-white border rounded-lg hover:bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900">Buat Penilaian</h3>
                <p class="text-gray-600">Buat penilaian baru</p>
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
// Get chart data
$chartQuery = "SELECT a.nama_metode, h.nilai_akhir 
           FROM hasil_siswa h 
           JOIN alternatif a ON h.id_alternatif = a.id 
           WHERE h.tanggal_perhitungan = (
               SELECT MAX(tanggal_perhitungan) FROM hasil_siswa
           )
           ORDER BY h.ranking ASC";
$chartStmt = $auth->getConnection()->query($chartQuery);
$chartData = $chartStmt->fetchAll();

$labels = array_map(function($row) { return $row['nama_metode']; }, $chartData);
$values = array_map(function($row) { return $row['nilai_akhir']; }, $chartData);
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('resultChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: 'Nilai Optimasi',
                data: <?php echo json_encode($values); ?>,
                backgroundColor: [
                    'rgba(30, 64, 175, 0.8)',  // primary color
                    'rgba(59, 130, 246, 0.8)', // blue-500
                    'rgba(96, 165, 250, 0.8)', // blue-400
                    'rgba(147, 197, 253, 0.8)', // blue-300
                    'rgba(191, 219, 254, 0.8)'  // blue-200
                ],
                borderColor: [
                    'rgb(30, 64, 175)',
                    'rgb(59, 130, 246)',
                    'rgb(96, 165, 250)',
                    'rgb(147, 197, 253)',
                    'rgb(191, 219, 254)'
                ],
                borderWidth: 1
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
                    text: 'Perbandingan Nilai Optimasi Metode Pengajaran',
                    font: {
                        size: 16
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Nilai Optimasi'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Metode Pengajaran'
                    }
                }
            }
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
