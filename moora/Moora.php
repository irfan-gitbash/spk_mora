<?php
class Moora {
    private $conn;
    private $alternatifs;
    private $kriterias;
    private $penilaians;
    private $normalized;
    private $weighted;
    private $optimized;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->loadData();
    }
    
    private function loadData() {
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
            
            // Load evaluations
            $stmt = $this->conn->query("SELECT * FROM penilaian");
            $this->penilaians = [];
            while ($row = $stmt->fetch()) {
                $this->penilaians[$row['id_alternatif']][$row['id_kriteria']] = $row['nilai'];
            }
            if (empty($this->penilaians)) {
                throw new Exception("No evaluations found");
            }

            error_log("Loaded data: " . 
                count($this->alternatifs) . " alternatives, " . 
                count($this->kriterias) . " criteria, " . 
                count($this->penilaians) . " evaluations");
        } catch (Exception $e) {
            error_log("Error loading data: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function calculate() {
        try {
            error_log("Starting MOORA calculation");
            $this->normalize();
            error_log("Normalization completed");
            $this->applyWeights();
            error_log("Weights applied");
            $this->optimize();
            error_log("Optimization completed");
            $this->saveResults();
            error_log("Results saved");
            $results = $this->getResults();
            error_log("Final results: " . json_encode($results));
            return $results;
        } catch (Exception $e) {
            error_log("Error in MOORA calculation: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function normalize() {
        $this->normalized = [];
        
        try {
            error_log("Starting normalization process");
            
            // Calculate denominators (square root of sum of squares)
            $denominators = [];
            foreach ($this->kriterias as $kriteria) {
                $sum = 0;
                foreach ($this->alternatifs as $alternatif) {
                    $nilai = $this->penilaians[$alternatif['id']][$kriteria['id']] ?? 0;
                    $sum += pow($nilai, 2);
                }
                $denominators[$kriteria['id']] = sqrt($sum);
                error_log("Denominator for kriteria {$kriteria['nama_kriteria']}: {$denominators[$kriteria['id']]}");
            }
            
            // Normalize each value
            foreach ($this->alternatifs as $alternatif) {
                foreach ($this->kriterias as $kriteria) {
                    $nilai = $this->penilaians[$alternatif['id']][$kriteria['id']] ?? 0;
                    $normalized_value = $denominators[$kriteria['id']] != 0 ? 
                        $nilai / $denominators[$kriteria['id']] : 0;
                    $this->normalized[$alternatif['id']][$kriteria['id']] = $normalized_value;
                    
                    error_log("Normalized value for alternatif {$alternatif['nama_metode']}, " .
                            "kriteria {$kriteria['nama_kriteria']}: " .
                            "({$nilai} / {$denominators[$kriteria['id']]}) = {$normalized_value}");
                }
            }
            
            error_log("Normalization completed successfully");
        } catch (Exception $e) {
            error_log("Error in normalization: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function applyWeights() {
        $this->weighted = [];
        
        try {
            error_log("Starting weight application process");
            
            foreach ($this->alternatifs as $alternatif) {
                error_log("Processing weights for alternatif: {$alternatif['nama_metode']}");
                
                foreach ($this->kriterias as $kriteria) {
                    $normalized_value = $this->normalized[$alternatif['id']][$kriteria['id']];
                    $weighted_value = $normalized_value * $kriteria['bobot'];
                    $this->weighted[$alternatif['id']][$kriteria['id']] = $weighted_value;
                    
                    error_log("  Kriteria {$kriteria['nama_kriteria']} (bobot: {$kriteria['bobot']}): " .
                            "{$normalized_value} * {$kriteria['bobot']} = {$weighted_value}");
                }
            }
            
            error_log("Weight application completed successfully");
        } catch (Exception $e) {
            error_log("Error in weight application: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function optimize() {
        $this->optimized = [];
        
        try {
            error_log("Starting optimization process");
            
            foreach ($this->alternatifs as $alternatif) {
                $benefit_sum = 0;
                $cost_sum = 0;
                
                error_log("Processing alternatif: {$alternatif['nama_metode']}");
                
                foreach ($this->kriterias as $kriteria) {
                    $weighted_value = $this->weighted[$alternatif['id']][$kriteria['id']];
                    
                    if ($kriteria['tipe'] === 'benefit') {
                        $benefit_sum += $weighted_value;
                        error_log("  Benefit kriteria {$kriteria['nama_kriteria']}: +{$weighted_value}");
                    } else {
                        $cost_sum += $weighted_value;
                        error_log("  Cost kriteria {$kriteria['nama_kriteria']}: -{$weighted_value}");
                    }
                }
                
                $optimization_value = $benefit_sum - $cost_sum;
                $this->optimized[$alternatif['id']] = $optimization_value;
                
                error_log("Optimization value for {$alternatif['nama_metode']}: " .
                         "benefit({$benefit_sum}) - cost({$cost_sum}) = {$optimization_value}");
            }
            
            // Sort by optimization value (descending)
            arsort($this->optimized);
            error_log("Final ranking after sorting: " . json_encode($this->optimized));
            
            error_log("Optimization completed successfully");
        } catch (Exception $e) {
            error_log("Error in optimization: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function saveResults() {
        try {
            error_log("Starting to save results");
            
            // Begin transaction
            $this->conn->beginTransaction();
            error_log("Transaction started");
            
            // Delete old results
            $this->conn->exec("DELETE FROM hasil");
            error_log("Old results deleted");
            
            // Insert new results
            $stmt = $this->conn->prepare(
                "INSERT INTO hasil (id_alternatif, nilai_akhir, ranking) VALUES (?, ?, ?)"
            );
            
            $rank = 1;
            foreach ($this->optimized as $id_alternatif => $nilai_akhir) {
                $alternatif = array_filter($this->alternatifs, function($a) use ($id_alternatif) {
                    return $a['id'] == $id_alternatif;
                });
                $alternatif = reset($alternatif);
                
                $stmt->execute([$id_alternatif, $nilai_akhir, $rank]);
                error_log("Saved rank {$rank}: {$alternatif['nama_metode']} with value {$nilai_akhir}");
                $rank++;
            }
            
            $this->conn->commit();
            error_log("Transaction committed successfully");
            
        } catch (PDOException $e) {
            error_log("Error saving results: " . $e->getMessage());
            $this->conn->rollBack();
            error_log("Transaction rolled back");
            throw $e;
        }
    }
    
    public function getResults() {
        $results = [];
        $rank = 1;
        
        foreach ($this->optimized as $id_alternatif => $nilai_akhir) {
            // Find alternative details
            $alternatif = array_filter($this->alternatifs, function($a) use ($id_alternatif) {
                return $a['id'] == $id_alternatif;
            });
            $alternatif = reset($alternatif);
            
            $results[] = [
                'rank' => $rank,
                'id_alternatif' => $id_alternatif,
                'nama_metode' => $alternatif['nama_metode'],
                'nilai_akhir' => $nilai_akhir
            ];
            
            $rank++;
        }
        
        return $results;
    }
    
    public function getNormalizedMatrix() {
        return $this->normalized;
    }
    
    public function getWeightedMatrix() {
        return $this->weighted;
    }
    
    public function getOptimizationValues() {
        return $this->optimized;
    }
}
?>
