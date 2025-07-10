<?php
require_once '../../config/database.php';
require_once '../../includes/auth.php';

// Pastikan user sudah login
$auth->requireLogin();
$auth->requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validasi dan ambil data dari form
        $optimization_results = json_decode($_POST['optimization_results'], true);
        $optimization_details = json_decode($_POST['optimization_details'], true);
        $decision_matrix = json_decode($_POST['decision_matrix'], true);
        $normalized_matrix = json_decode($_POST['normalized_matrix'], true);
        $weighted_matrix = json_decode($_POST['weighted_matrix'], true);
        $kriterias = json_decode($_POST['kriterias'], true);
        $alternatifs = json_decode($_POST['alternatifs'], true);
        
        // Validasi data input
        if (!$optimization_results || !$optimization_details || !$decision_matrix || 
            !$normalized_matrix || !$weighted_matrix || !$kriterias) {
            throw new Exception('Data input tidak lengkap atau tidak valid');
        }
        
        // Ambil koneksi database
        $database = new Database();
        $pdo = $database->getConnection();
        
        // Mulai transaksi
        $pdo->beginTransaction();
        
        // Ambil informasi user
        $current_user = $auth->getCurrentUser();
        $user_id = $current_user ? $current_user['id'] : 1;
        $user_name = $current_user ? $current_user['username'] : 'admin';
        
        // Hitung statistik tambahan
        $total_alternatives = count($optimization_results);
        $total_criteria = count($kriterias);
        $benefit_criteria = array_filter($kriterias, function($k) { return $k['tipe'] === 'benefit'; });
        $cost_criteria = array_filter($kriterias, function($k) { return $k['tipe'] === 'cost'; });
        
        // Siapkan metadata tambahan
        $calculation_metadata = [
            'calculation_timestamp' => date('Y-m-d H:i:s'),
            'user_info' => [
                'id' => $user_id,
                'username' => $user_name
            ],
            'criteria_summary' => [
                'total_criteria' => $total_criteria,
                'benefit_count' => count($benefit_criteria),
                'cost_count' => count($cost_criteria),
                'criteria_weights' => array_column($kriterias, 'bobot', 'id')
            ],
            'alternatives_summary' => [
                'total_alternatives' => $total_alternatives,
                'alternative_names' => array_keys($optimization_results)
            ],
            'calculation_stats' => [
                'highest_yi' => max(array_column($optimization_results, 'yi_value')),
                'lowest_yi' => min(array_column($optimization_results, 'yi_value')),
                'average_yi' => array_sum(array_column($optimization_results, 'yi_value')) / count($optimization_results)
            ]
        ];
        
        // Gabungkan optimization_details dengan metadata
        $enhanced_optimization_details = array_merge($optimization_details, [
            'metadata' => $calculation_metadata,
            'criteria_details' => $kriterias
        ]);
        
        // 1. Simpan sesi perhitungan dengan data yang lebih lengkap
        $stmt = $pdo->prepare("
            INSERT INTO sesi_perhitungan_moora 
            (tanggal_perhitungan, jumlah_alternatif, jumlah_kriteria, 
             decision_matrix, normalized_matrix, weighted_matrix, 
             optimization_results, optimization_details, 
             created_by) 
            VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $total_alternatives,
            $total_criteria,
            json_encode($decision_matrix, JSON_PRETTY_PRINT),
            json_encode($normalized_matrix, JSON_PRETTY_PRINT),
            json_encode($weighted_matrix, JSON_PRETTY_PRINT),
            json_encode($optimization_results, JSON_PRETTY_PRINT),
            json_encode($enhanced_optimization_details, JSON_PRETTY_PRINT),
            $user_id
        ]);
        
        $sesi_id = $pdo->lastInsertId();
        
        // Log aktivitas penyimpanan
        error_log("MOORA Calculation saved - Session ID: {$sesi_id}, User: {$user_name}, Alternatives: {$total_alternatives}, Criteria: {$total_criteria}");
        
        // 2. Simpan detail hasil untuk setiap alternatif dengan validasi yang lebih ketat
        // Sesuaikan dengan 9 kriteria (K1-K9) sesuai struktur tabel
        $stmt = $pdo->prepare("
            INSERT INTO hasil_moora 
            (sesi_id, id_alternatif, nama_alternatif, 
             benefit_sum, cost_sum, yi_value, ranking, ranking_status,
             k1_original, k1_normalized, k1_weighted,
             k2_original, k2_normalized, k2_weighted,
             k3_original, k3_normalized, k3_weighted,
             k4_original, k4_normalized, k4_weighted,
             k5_original, k5_normalized, k5_weighted,
             k6_original, k6_normalized, k6_weighted,
             k7_original, k7_normalized, k7_weighted,
             k8_original, k8_normalized, k8_weighted,
             k9_original, k9_normalized, k9_weighted) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $rank = 1;
        $saved_alternatives = 0;
        
        foreach ($optimization_results as $alternative_name => $result) {
            // Validasi data hasil
            if (!isset($result['benefit_sum']) || !isset($result['cost_sum']) || !isset($result['yi_value'])) {
                throw new Exception("Data hasil tidak lengkap untuk alternatif: {$alternative_name}");
            }
            
            // Untuk alternatif yang berasal dari kombinasi siswa-strategi, gunakan nama lengkap
            $alternatif_id = null; // Bisa null karena ini adalah kombinasi dinamis
            
            // Tentukan status ranking dengan kategori yang lebih detail
            $ranking_status = '';
            if ($rank == 1) {
                $ranking_status = 'Terbaik (Optimal)';
            } elseif ($rank == 2) {
                $ranking_status = 'Sangat Baik';
            } elseif ($rank == 3) {
                $ranking_status = 'Baik';
            } elseif ($rank <= ceil($total_alternatives * 0.3)) {
                $ranking_status = 'Cukup Baik';
            } elseif ($rank <= ceil($total_alternatives * 0.7)) {
                $ranking_status = 'Sedang';
            } else {
                $ranking_status = 'Perlu Perbaikan';
            }
            
            // Ambil nilai untuk setiap kriteria (K1-K7 + K8-K9 kosong)
            $kriteria_values = [];
            for ($i = 1; $i <= 9; $i++) {
                $kriteria_id = $i;
                
                // Validasi keberadaan data dalam matriks
                $original = 0;
                $normalized = 0;
                $weighted = 0;
                
                // Hanya untuk K1-K7 yang ada data
                if ($i <= 7) {
                    if (isset($decision_matrix[$alternative_name][$kriteria_id])) {
                        $original = (float) $decision_matrix[$alternative_name][$kriteria_id];
                    }
                    
                    if (isset($normalized_matrix[$alternative_name][$kriteria_id])) {
                        $normalized = (float) $normalized_matrix[$alternative_name][$kriteria_id];
                    }
                    
                    if (isset($weighted_matrix[$alternative_name][$kriteria_id])) {
                        $weighted = (float) $weighted_matrix[$alternative_name][$kriteria_id];
                    }
                }
                
                $kriteria_values[] = $original;
                $kriteria_values[] = $normalized;
                $kriteria_values[] = $weighted;
            }
            
            // Validasi jumlah parameter sebelum eksekusi
            $params = [
                $sesi_id,
                $alternatif_id,
                $alternative_name,
                (float) $result['benefit_sum'],
                (float) $result['cost_sum'],
                (float) $result['yi_value'],
                $rank,
                $ranking_status
            ];
            
            // Tambahkan nilai kriteria K1-K9 (27 nilai: 9 kriteria x 3 nilai)
            $params = array_merge($params, $kriteria_values);
            
            // Validasi jumlah parameter (harus 35: 8 + 27)
            if (count($params) !== 35) {
                throw new Exception("Jumlah parameter tidak sesuai: " . count($params) . " (diharapkan 35)");
            }
            
            $stmt->execute($params);
            $saved_alternatives++;
            $rank++;
        }
        
        // Validasi hasil penyimpanan
        if ($saved_alternatives !== $total_alternatives) {
            throw new Exception("Tidak semua alternatif berhasil disimpan. Disimpan: {$saved_alternatives}, Total: {$total_alternatives}");
        }
        
        // Commit transaksi
        $pdo->commit();
        
        // Log sukses dengan detail
        error_log("MOORA Calculation successfully saved - Session ID: {$sesi_id}, Alternatives saved: {$saved_alternatives}, User: {$user_name}");
        
        // Redirect ke halaman hasil dengan ID sesi dan pesan sukses
        $success_message = "Hasil perhitungan MOORA berhasil disimpan dengan ID Sesi: {$sesi_id}";
        header("Location: ../../hasil.php?sesi_id=" . $sesi_id . "&success=" . urlencode($success_message));
        exit();
        
    } catch (Exception $e) {
        // Rollback jika ada error
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollback();
        }
        
        // Log error dengan detail
        $error_details = [
            'error_message' => $e->getMessage(),
            'error_file' => $e->getFile(),
            'error_line' => $e->getLine(),
            'user_id' => $user_id ?? 'unknown',
            'timestamp' => date('Y-m-d H:i:s'),
            'post_data_size' => strlen(serialize($_POST))
        ];
        
        error_log("MOORA Save Error: " . json_encode($error_details));
        
        // Redirect kembali dengan pesan error yang lebih informatif
        $error_message = "Gagal menyimpan hasil: " . $e->getMessage();
        if (strpos($e->getMessage(), 'parameter') !== false) {
            $error_message .= " (Kemungkinan masalah struktur data)";
        }
        
        header("Location: index.php?error=" . urlencode($error_message));
        exit();
    }
} else {
    // Jika bukan POST request, redirect ke index
    header("Location: index.php?error=" . urlencode("Metode request tidak valid"));
    exit();
}
?>