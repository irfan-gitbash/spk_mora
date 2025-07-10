<?php
require_once '../../includes/header.php';

// Require admin privileges
$auth->requireAdmin();

// Get siswa ID from URL
$id_siswa = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$id_siswa) {
    header("Location: index.php");
    exit();
}

// Get siswa data
$stmt = $auth->getConnection()->prepare("SELECT * FROM siswa WHERE id = ?");
$stmt->execute([$id_siswa]);
$siswa = $stmt->fetch();

if (!$siswa) {
    header("Location: index.php");
    exit();
}

// Get list of alternatif and kriteria
$stmt_alternatif = $auth->getConnection()->query("SELECT * FROM alternatif ORDER BY nama_metode");
$alternatifs = $stmt_alternatif->fetchAll();

$stmt_kriteria = $auth->getConnection()->query("SELECT * FROM kriteria ORDER BY nama_kriteria");
$kriterias = $stmt_kriteria->fetchAll();

// Get existing penilaian data
$stmt = $auth->getConnection()->prepare("SELECT * FROM penilaian_siswa WHERE id_siswa = ?");
$stmt->execute([$id_siswa]);
$penilaians = [];
while ($row = $stmt->fetch()) {
    $penilaians[$row['id_alternatif']][$row['id_kriteria']] = [
        'nilai' => $row['nilai'],
        'gaya_belajar' => $row['gaya_belajar']
    ];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nama_siswa = $_POST['nama_siswa'];
        
        // Begin transaction
        $auth->getConnection()->beginTransaction();
        
        // Update siswa
        $stmt = $auth->getConnection()->prepare("UPDATE siswa SET nama = ? WHERE id = ?");
        $stmt->execute([$nama_siswa, $id_siswa]);
        
        // Delete existing penilaian
        $stmt = $auth->getConnection()->prepare("DELETE FROM penilaian_siswa WHERE id_siswa = ?");
        $stmt->execute([$id_siswa]);
        
        // Insert updated penilaian
        $stmt = $auth->getConnection()->prepare("INSERT INTO penilaian_siswa (id_siswa, id_alternatif, id_kriteria, nilai, gaya_belajar) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($alternatifs as $alternatif) {
            $id_alternatif = $alternatif['id'];
            
            // Process each kriteria
            foreach ($kriterias as $kriteria) {
                $id_kriteria = $kriteria['id'];
                $nilai_key = "nilai_{$id_alternatif}_{$id_kriteria}";
                $gaya_belajar = null;
                
                // Handle gaya belajar separately
                if ($kriteria['nama_kriteria'] == 'Gaya belajar') {
                    $gaya_belajar_key = "gaya_belajar_{$id_alternatif}";
                    $gaya_belajar = $_POST[$gaya_belajar_key] ?? null;
                    $nilai = 0; // Default value
                } else {
                    $nilai = $_POST[$nilai_key] ?? 0;
                }
                
                $stmt->execute([$id_siswa, $id_alternatif, $id_kriteria, $nilai, $gaya_belajar]);
            }
        }
        
        // Commit transaction
        $auth->getConnection()->commit();
        
        header("Location: view.php?id={$id_siswa}&success=1");
        exit();
    } catch (PDOException $e) {
        $auth->getConnection()->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}
?>

<div class="bg-white shadow-md rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Edit Data Siswa</h1>
        <a href="view.php?id=<?php echo $id_siswa; ?>" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>

    <?php if (isset($error)): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p><?php echo $error; ?></p>
    </div>
    <?php endif; ?>

    <form action="edit.php?id=<?php echo $id_siswa; ?>" method="POST" class="space-y-6">
        <div>
            <label for="nama_siswa" class="block text-sm font-medium text-gray-700 mb-2">
                Nama Siswa
            </label>
            <input type="text" id="nama_siswa" name="nama_siswa" required 
                   value="<?php echo htmlspecialchars($siswa['nama']); ?>"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
        </div>

        <?php foreach ($alternatifs as $alternatif): ?>
        <div class="border p-4 rounded-lg">
            <h2 class="text-lg font-medium text-gray-900 mb-4"><?php echo htmlspecialchars($alternatif['nama_metode']); ?></h2>
            
            <div class="space-y-4">
                <?php foreach ($kriterias as $kriteria): ?>
                <div>
                    <label for="nilai_<?php echo $alternatif['id']; ?>_<?php echo $kriteria['id']; ?>" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php echo htmlspecialchars($kriteria['nama_kriteria']); ?>
                        <span class="text-sm text-gray-500">
                            (<?php echo $kriteria['tipe'] === 'benefit' ? 'Benefit' : 'Cost'; ?>)
                        </span>
                    </label>
                    
                    <?php if ($kriteria['nama_kriteria'] == 'Gaya belajar'): ?>
                    <select id="gaya_belajar_<?php echo $alternatif['id']; ?>" 
                            name="gaya_belajar_<?php echo $alternatif['id']; ?>" 
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary rounded-md">
                        <option value="">Pilih Gaya Belajar</option>
                        <option value="Visual" <?php echo isset($penilaians[$alternatif['id']][$kriteria['id']]) && $penilaians[$alternatif['id']][$kriteria['id']]['gaya_belajar'] == 'Visual' ? 'selected' : ''; ?>>Visual</option>
                        <option value="Auditori" <?php echo isset($penilaians[$alternatif['id']][$kriteria['id']]) && $penilaians[$alternatif['id']][$kriteria['id']]['gaya_belajar'] == 'Auditori' ? 'selected' : ''; ?>>Auditori</option>
                        <option value="Kinestetik" <?php echo isset($penilaians[$alternatif['id']][$kriteria['id']]) && $penilaians[$alternatif['id']][$kriteria['id']]['gaya_belajar'] == 'Kinestetik' ? 'selected' : ''; ?>>Kinestetik</option>
                    </select>
                    <?php else: ?>
                    <input type="number" 
                           id="nilai_<?php echo $alternatif['id']; ?>_<?php echo $kriteria['id']; ?>" 
                           name="nilai_<?php echo $alternatif['id']; ?>_<?php echo $kriteria['id']; ?>" 
                           required
                           step="0.01"
                           min="0"
                           max="100"
                           value="<?php echo isset($penilaians[$alternatif['id']][$kriteria['id']]) ? $penilaians[$alternatif['id']][$kriteria['id']]['nilai'] : ''; ?>"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="flex justify-end">
            <button type="submit" class="bg-primary hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-save mr-2"></i>Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>