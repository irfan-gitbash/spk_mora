USE spk_mora;

-- Insert sample kriteria
INSERT INTO kriteria (nama_kriteria, bobot, tipe) VALUES
('Efektivitas Pembelajaran', 0.30, 'benefit'),
('Tingkat Partisipasi Siswa', 0.25, 'benefit'),
('Kemudahan Implementasi', 0.20, 'benefit'),
('Biaya Pelaksanaan', 0.15, 'cost'),
('Waktu Persiapan', 0.10, 'cost');

-- Insert sample alternatif (metode pengajaran)
INSERT INTO alternatif (nama_metode, deskripsi) VALUES
('Project Based Learning', 'Pembelajaran berbasis proyek yang melibatkan siswa dalam pemecahan masalah'),
('Flipped Classroom', 'Metode pembelajaran terbalik dimana siswa mempelajari materi di rumah'),
('Collaborative Learning', 'Pembelajaran kolaboratif yang menekankan kerja sama antar siswa'),
('Direct Instruction', 'Metode pengajaran langsung dengan penjelasan step-by-step'),
('Inquiry Based Learning', 'Pembelajaran berbasis penyelidikan yang mendorong siswa untuk menemukan solusi');

-- Insert sample penilaian
INSERT INTO penilaian (id_alternatif, id_kriteria, nilai) VALUES
-- Project Based Learning
(1, 1, 85), -- Efektivitas
(1, 2, 90), -- Partisipasi
(1, 3, 75), -- Kemudahan
(1, 4, 70), -- Biaya
(1, 5, 80), -- Waktu

-- Flipped Classroom
(2, 1, 80),
(2, 2, 75),
(2, 3, 85),
(2, 4, 60),
(2, 5, 65),

-- Collaborative Learning
(3, 1, 88),
(3, 2, 92),
(3, 3, 80),
(3, 4, 65),
(3, 5, 70),

-- Direct Instruction
(4, 1, 70),
(4, 2, 65),
(4, 3, 90),
(4, 4, 50),
(4, 5, 55),

-- Inquiry Based Learning
(5, 1, 90),
(5, 2, 85),
(5, 3, 70),
(5, 4, 75),
(5, 5, 85);
