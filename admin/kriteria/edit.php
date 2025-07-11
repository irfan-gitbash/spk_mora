<?php
require_once '../../includes/auth.php';
$auth->requireLogin();
$auth->requireAdmin();

// Get database connection
$database = new Database();
$conn = $database->getConnection();

$error = '';
$success = false;

// Get kriteria data by ID
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$kriteria_id = $_GET['id'];

// Fetch existing kriteria data
$stmt = $conn->prepare("SELECT * FROM kriteria WHERE id = ?");
$stmt->execute([$kriteria_id]);
$existing_kriteria = $stmt->fetch();

if (!$existing_kriteria) {
    header("Location: index.php");
    exit();
}

// Get student data for dropdown
$siswa_list = $conn->query("SELECT id, nama FROM siswa ORDER BY nama")->fetchAll();

// Get all kriteria data for this student to populate the form
$stmt = $conn->prepare("SELECT * FROM kriteria WHERE id_siswa = ?");
$stmt->execute([$existing_kriteria['id_siswa']]);
$student_kriteria = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize data by strategy
$kriteria_by_strategy = [];
foreach ($student_kriteria as $k) {
    $kriteria_by_strategy[$k['strategi']] = $k;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_siswa = $_POST['id_siswa'] ?? '';
    
    if (empty($id_siswa)) {
        $error = 'Pilih siswa terlebih dahulu';
    } else {
        try {
            $conn->beginTransaction();
            
            // Define strategies
            $strategies = [
                '12' => 'Demonstran',
                '13' => 'Diskusi', 
                '14' => 'Praktikkum'
            ];
            
            foreach ($strategies as $strategy_id => $strategy_name) {
                $grammar = $_POST["grammar_{$strategy_id}"] ?? 0;
                $speaking = $_POST["speaking_{$strategy_id}"] ?? 0;
                $motivasi = $_POST["motivasi_{$strategy_id}"] ?? 0;
                $gaya = $_POST["gaya_{$strategy_id}"] ?? '';
                $kecocokan = $_POST["kecocokan_{$strategy_id}"] ?? 0;
                $durasi = $_POST["durasi_{$strategy_id}"] ?? 0;
                $kognitif = $_POST["kognitif_{$strategy_id}"] ?? 0;
                
                // Check if data exists for this student and strategy
                $check_stmt = $conn->prepare("SELECT id FROM kriteria WHERE id_siswa = ? AND strategi = ?");
                $check_stmt->execute([$id_siswa, $strategy_name]);
                
                if ($check_stmt->fetch()) {
                    // Update existing data
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
                    // Insert new data
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
            
            $conn->commit();
            header("Location: index.php?success=1");
            exit();
            
        } catch (PDOException $e) {
            $conn->rollBack();
            $error = 'Gagal mengupdate kriteria: ' . $e->getMessage();
        } catch (Exception $e) {
            $conn->rollBack();
            $error = $e->getMessage();
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
        <!-- Data Siswa -->
        <div>
            <label for="id_siswa" class="block text-sm font-medium text-gray-700 mb-2">Pilih SISWA</label>
            <select id="id_siswa" name="id_siswa" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" required>
                <option value="">-- Pilih SISWA --</option>
                <?php foreach ($siswa_list as $siswa): ?>
                <option value="<?php echo $siswa['id']; ?>" <?php echo ($siswa['id'] == $existing_kriteria['id_siswa']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($siswa['nama']); ?>
                </option>
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
                            <input type="number" name="grammar_12" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="100" 
                                value="<?php echo isset($kriteria_by_strategy['Demonstran']) ? htmlspecialchars($kriteria_by_strategy['Demonstran']['kemampuan_grammar']) : ''; ?>">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="speaking_12" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="100" 
                                value="<?php echo isset($kriteria_by_strategy['Demonstran']) ? htmlspecialchars($kriteria_by_strategy['Demonstran']['kemampuan_speaking']) : ''; ?>">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="motivasi_12" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="10" 
                                value="<?php echo isset($kriteria_by_strategy['Demonstran']) ? htmlspecialchars($kriteria_by_strategy['Demonstran']['motivasi_belajar']) : ''; ?>">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <select name="gaya_12" class="rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                                <option value="">Pilih</option>
                                <option value="Visual" <?php echo (isset($kriteria_by_strategy['Demonstran']) && $kriteria_by_strategy['Demonstran']['gaya_belajar'] == 'Visual') ? 'selected' : ''; ?>>Visual</option>
                                <option value="Auditori" <?php echo (isset($kriteria_by_strategy['Demonstran']) && $kriteria_by_strategy['Demonstran']['gaya_belajar'] == 'Auditori') ? 'selected' : ''; ?>>Auditori</option>
                                <option value="Kinestetik" <?php echo (isset($kriteria_by_strategy['Demonstran']) && $kriteria_by_strategy['Demonstran']['gaya_belajar'] == 'Kinestetik') ? 'selected' : ''; ?>>Kinestetik</option>
                            </select>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="kecocokan_12" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="100" 
                                value="<?php echo isset($kriteria_by_strategy['Demonstran']) ? htmlspecialchars($kriteria_by_strategy['Demonstran']['kecocokan_strategi']) : ''; ?>">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="durasi_12" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="100" 
                                value="<?php echo isset($kriteria_by_strategy['Demonstran']) ? htmlspecialchars($kriteria_by_strategy['Demonstran']['durasi']) : ''; ?>">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="kognitif_12" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="100" 
                                value="<?php echo isset($kriteria_by_strategy['Demonstran']) ? htmlspecialchars($kriteria_by_strategy['Demonstran']['bobot_kognitif']) : ''; ?>">
                        </td>
                    </tr>
                    <!-- Diskusi -->
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Diskusi</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="grammar_13" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="100" 
                                value="<?php echo isset($kriteria_by_strategy['Diskusi']) ? htmlspecialchars($kriteria_by_strategy['Diskusi']['kemampuan_grammar']) : ''; ?>">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="speaking_13" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="100" 
                                value="<?php echo isset($kriteria_by_strategy['Diskusi']) ? htmlspecialchars($kriteria_by_strategy['Diskusi']['kemampuan_speaking']) : ''; ?>">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="motivasi_13" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="10" 
                                value="<?php echo isset($kriteria_by_strategy['Diskusi']) ? htmlspecialchars($kriteria_by_strategy['Diskusi']['motivasi_belajar']) : ''; ?>">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <select name="gaya_13" class="rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                                <option value="">Pilih</option>
                                <option value="Visual" <?php echo (isset($kriteria_by_strategy['Diskusi']) && $kriteria_by_strategy['Diskusi']['gaya_belajar'] == 'Visual') ? 'selected' : ''; ?>>Visual</option>
                                <option value="Auditori" <?php echo (isset($kriteria_by_strategy['Diskusi']) && $kriteria_by_strategy['Diskusi']['gaya_belajar'] == 'Auditori') ? 'selected' : ''; ?>>Auditori</option>
                                <option value="Kinestetik" <?php echo (isset($kriteria_by_strategy['Diskusi']) && $kriteria_by_strategy['Diskusi']['gaya_belajar'] == 'Kinestetik') ? 'selected' : ''; ?>>Kinestetik</option>
                            </select>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="kecocokan_13" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="100" 
                                value="<?php echo isset($kriteria_by_strategy['Diskusi']) ? htmlspecialchars($kriteria_by_strategy['Diskusi']['kecocokan_strategi']) : ''; ?>">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="durasi_13" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="100" 
                                value="<?php echo isset($kriteria_by_strategy['Diskusi']) ? htmlspecialchars($kriteria_by_strategy['Diskusi']['durasi']) : ''; ?>">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="kognitif_13" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="100" 
                                value="<?php echo isset($kriteria_by_strategy['Diskusi']) ? htmlspecialchars($kriteria_by_strategy['Diskusi']['bobot_kognitif']) : ''; ?>">
                        </td>
                    </tr>
                    <!-- Praktikkum -->
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">3</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Praktikkum</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="grammar_14" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="100" 
                                value="<?php echo isset($kriteria_by_strategy['Praktikkum']) ? htmlspecialchars($kriteria_by_strategy['Praktikkum']['kemampuan_grammar']) : ''; ?>">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="speaking_14" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="100" 
                                value="<?php echo isset($kriteria_by_strategy['Praktikkum']) ? htmlspecialchars($kriteria_by_strategy['Praktikkum']['kemampuan_speaking']) : ''; ?>">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="motivasi_14" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="10" 
                                value="<?php echo isset($kriteria_by_strategy['Praktikkum']) ? htmlspecialchars($kriteria_by_strategy['Praktikkum']['motivasi_belajar']) : ''; ?>">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <select name="gaya_14" class="rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                                <option value="">Pilih</option>
                                <option value="Visual" <?php echo (isset($kriteria_by_strategy['Praktikkum']) && $kriteria_by_strategy['Praktikkum']['gaya_belajar'] == 'Visual') ? 'selected' : ''; ?>>Visual</option>
                                <option value="Auditori" <?php echo (isset($kriteria_by_strategy['Praktikkum']) && $kriteria_by_strategy['Praktikkum']['gaya_belajar'] == 'Auditori') ? 'selected' : ''; ?>>Auditori</option>
                                <option value="Kinestetik" <?php echo (isset($kriteria_by_strategy['Praktikkum']) && $kriteria_by_strategy['Praktikkum']['gaya_belajar'] == 'Kinestetik') ? 'selected' : ''; ?>>Kinestetik</option>
                            </select>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="kecocokan_14" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="100" 
                                value="<?php echo isset($kriteria_by_strategy['Praktikkum']) ? htmlspecialchars($kriteria_by_strategy['Praktikkum']['kecocokan_strategi']) : ''; ?>">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="durasi_14" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="100" 
                                value="<?php echo isset($kriteria_by_strategy['Praktikkum']) ? htmlspecialchars($kriteria_by_strategy['Praktikkum']['durasi']) : ''; ?>">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <input type="number" name="kognitif_14" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" min="0" max="100" 
                                value="<?php echo isset($kriteria_by_strategy['Praktikkum']) ? htmlspecialchars($kriteria_by_strategy['Praktikkum']['bobot_kognitif']) : ''; ?>">
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                Update Kriteria
            </button>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
