<?php
require_once '../../includes/auth.php';
require_once '../../moora/Moora.php';

$auth->requireLogin();

// Get database connection
$database = new Database();
$conn = $database->getConnection();

// Get siswa ID from POST
$id_siswa = isset($_POST['id_siswa']) ? intval($_POST['id_siswa']) : 0;

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

try {
    // Create a modified MOORA calculator for a specific student
    class MooraSiswa extends Moora {
        private $id_siswa;
        
        public function __construct($conn, $id_siswa) {
            $this->conn = $conn;
            $this->id_siswa = $id_siswa;
            $this->loadData();
        }
        
        protected function loadData() {
            try {
                // Load alternatives
                $stmt = $this->conn->query("SELECT * FROM alternatif");
                $this->alternatifs = $stmt->fetchAll();
                if (empty($this->alternatifs)) {
                    throw new Exception("No alternatives found");
                }
                
                // Load criteria
                $stmt = $this->conn->query("SELECT * FROM kriteria");
                $this->kriterias = $stmt->fetchAll();
                if (empty($this->kriterias)) {
                    throw new Exception("No criteria found");
                }
                
                // Load evaluations for this student
                $stmt = $this->conn->prepare("SELECT * FROM penilaian_siswa WHERE id_siswa = ?");
                $stmt->execute([$this->id_siswa]);
                $this->penilaians = [];
                while ($row = $stmt->fetch()) {
                    $this->penilaians[$row['id_alternatif']][$row['id_kriteria']] = $row['nilai'];
                }
                if (empty($this->penilaians)) {
                    throw new Exception("No evaluations found for this student");
                }
            } catch (Exception $e) {
                error_log("Error loading data: " . $e->getMessage());
                throw $e;
            }
        }
        
        protected function saveResults() {
            try {
                // Begin transaction
                $this->conn->beginTransaction();
                
                // Delete old results for this student
                $stmt = $this->conn->prepare("DELETE FROM hasil_siswa WHERE id_siswa = ?");
                $stmt->execute([$this->id_siswa]);
                
                // Insert new results
                $stmt = $this->conn->prepare(
                    "INSERT INTO hasil_siswa (id_siswa, id_alternatif, nilai_akhir, ranking) VALUES (?, ?, ?, ?)"
                );
                
                $rank = 1;
                foreach ($this->optimized as $id_alternatif => $nilai_akhir) {
                    $stmt->execute([$this->id_siswa, $id_alternatif, $nilai_akhir, $rank]);
                    $rank++;
                }
                
                // Commit transaction
                $this->conn->commit();
            } catch (Exception $e) {
                $this->conn->rollBack();
                error_log("Error saving results: " . $e->getMessage());
                throw $e;
            }
        }
    }
    
    // Calculate MOORA for this student
    $moora = new MooraSiswa($conn, $id_siswa);
    $results = $moora->calculate();
    
    // Redirect to view page
    header("Location: view.php?id={$id_siswa}&calculated=1");
    exit();
} catch (Exception $e) {
    // Handle error
    header("Location: view.php?id={$id_siswa}&error=" . urlencode($e->getMessage()));
    exit();
}