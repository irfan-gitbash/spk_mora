<?php
require_once '../../includes/auth.php';
$auth->requireLogin();
$auth->requireAdmin();

// Get database connection
$database = new Database();
$conn = $database->getConnection();

$error = '';
$success = false;

// Fetch siswa data for dropdown
$stmt_siswa = $conn->query("SELECT * FROM siswa ORDER BY nama ASC");
$siswa_list = $stmt_siswa->fetchAll();

// Handle form submission
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Begin transaction
        $conn->beginTransaction();
        
        // Get selected student ID
        $id_siswa = isset($_POST['id_siswa']) ? intval($_POST['id_siswa']) : 0;
        
        if (!$id_siswa) {
            throw new Exception("Pilih siswa terlebih dahulu");
        }
        
        // Data untuk setiap strategi
        $strategies = [
            ['id' => 12, 'name' => 'Demonstran'],
            ['id' => 13, 'name' => 'Diskusi'], 
            ['id' => 14, 'name' => 'Praktikkum']
        ];
        
        foreach ($strategies as $strategy) {
            $strategy_id = $strategy['id'];
            $strategy_name = $strategy['name'];
            
            // Ambil nilai dari form
            $grammar = $_POST['grammar_' . $strategy_id] ?? 0;
            $speaking = $_POST['speaking_' . $strategy_id] ?? 0;
            $motivasi = $_POST['motivasi_' . $strategy_id] ?? 0;
            $gaya = $_POST['gaya_' . $strategy_id] ?? '';
            $kecocokan = $_POST['kecocokan_' . $strategy_id] ?? 0;
            $durasi = $_POST['durasi_' . $strategy_id] ?? 0;
            $kognitif = $_POST['kognitif_' . $strategy_id] ?? 0;
            
            // Cek apakah data sudah ada
            $check_stmt = $conn->prepare("SELECT id FROM kriteria WHERE id_siswa = ? AND strategi = ?");
            $check_stmt->execute([$id_siswa, $strategy_name]);
            
            if ($check_stmt->fetch()) {
                // Update data yang sudah ada
                $stmt = $conn->prepare("
                    UPDATE kriteria SET 
                        kemampuan_grammar = ?, 
                        kemampuan_speaking = ?, 
                        motivasi_belajar = ?, 
                        gaya_belajar = ?, 
                        kecocokan_strategi = ?, 
                        durasi = ?, 
                        bobot_kognitif = ?
                    WHERE id_siswa = ? AND strategi = ?
                ");
                $stmt->execute([
                    $grammar, $speaking, $motivasi, $gaya, 
                    $kecocokan, $durasi, $kognitif, 
                    $id_siswa, $strategy_name
                ]);
            } else {
                // Insert data baru
                $stmt = $conn->prepare("
                    INSERT INTO kriteria (
                        id_siswa, strategi, kemampuan_grammar, kemampuan_speaking, 
                        motivasi_belajar, gaya_belajar, kecocokan_strategi, 
                        durasi, bobot_kognitif
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $id_siswa, $strategy_name, $grammar, $speaking, 
                    $motivasi, $gaya, $kecocokan, $durasi, $kognitif
                ]);
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        // Redirect dengan pesan sukses
        header("Location: index.php?success=1");
        exit();
        
    } catch (PDOException $e) {
        $conn->rollBack();
        $error = 'Gagal menambahkan kriteria: ' . $e->getMessage();
    } catch (Exception $e) {
        $conn->rollBack();
        $error = $e->getMessage();
    }
}


require_once '../../includes/header.php';
?>

<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Tambah Kriteria</h1>
        <a href="index.php" class="text-primary hover:text-blue-700">Kembali</a>
    </div>

    <?php if ($error): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
    </div>
    <?php endif; ?>

    <form action="" method="POST" class="space-y-6">
        <!-- Data Siswa -->
        <div>
            <label for="id_siswa" class="block text-sm font-medium text-gray-700 mb-2">Pilih SISWA</label>
            <select id="id_siswa" name="id_siswa" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" required>
                <option value="">-- Pilih SISWA --</option>
                <?php foreach ($siswa_list as $siswa): ?>
                <option value="<?php echo $siswa['id']; ?>"><?php echo htmlspecialchars($siswa['nama']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>


        <!-- Kriteria Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">no</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Strategi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kemampuan grammar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kemampuan Speaking</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motivasi Belajar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gaya belajar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kecocokan strategi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durasi (menit)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bobot kognitif</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!-- Demonstran -->
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">1</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Demonstran</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="grammar_12" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="100">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="speaking_12" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="100">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="motivasi_12" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="10">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <select name="gaya_12" class="rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                                <option value="">Pilih</option>
                                <option value="Visual">Visual</option>
                                <option value="Auditori">Auditori</option>
                                <option value="Kinestetik">Kinestetik</option>
                            </select>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="kecocokan_12" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="100">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="durasi_12" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="100">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="kognitif_12" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="100">
                        </td>
                    </tr>
                    <!-- Diskusi -->
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Diskusi</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="grammar_13" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="100">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="speaking_13" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="100">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="motivasi_13" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="10">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <select name="gaya_13" class="rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                                <option value="">Pilih</option>
                                <option value="Visual">Visual</option>
                                <option value="Auditori">Auditori</option>
                                <option value="Kinestetik">Kinestetik</option>
                            </select>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="kecocokan_13" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="100">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="durasi_13" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="100">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="kognitif_13" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="100">
                        </td>
                    </tr>
                    <!-- Praktikkum -->
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">3</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Praktikkum</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="grammar_14" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="100">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="speaking_14" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="100">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="motivasi_14" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="10">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <select name="gaya_14" class="rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                                <option value="">Pilih</option>
                                <option value="Visual">Visual</option>
                                <option value="Auditori">Auditori</option>
                                <option value="Kinestetik">Kinestetik</option>
                            </select>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="kecocokan_14" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="100">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="durasi_14" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="100">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="kognitif_14" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="100">
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                Simpan Kriteria
            </button>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
