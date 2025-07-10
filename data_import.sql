-- Insert Alternatif (Strategies)
INSERT INTO alternatif (nama_metode, deskripsi) VALUES
('Demonstran', 'Demonstrasi method'),
('Diskusi', 'Diskusi method'),
('Praktikkum', 'Praktikum method');

-- Insert Kriteria
INSERT INTO kriteria (nama_kriteria, bobot, tipe) VALUES
('Kemampuan grammar', 0.15, 'benefit'),
('Kemampuan Speaking', 0.15, 'benefit'),
('Motivasi Belajar', 0.10, 'benefit'),
('Gaya belajar', 0.10, 'benefit'),
('Kecocokan strategi', 0.15, 'benefit'),
('Durasi (menit)', 0.10, 'cost'),
('Bobot kognitif', 0.25, 'benefit');

-- Insert Penilaian (Evaluations) for each student and strategy with correct alternatif and kriteria IDs
-- Student 1: Nayla
INSERT INTO penilaian (id_alternatif, id_kriteria, nilai) VALUES
(12, 20, 85), (12, 21, 89), (12, 22, 8), (12, 23, 90), (12, 24, 90), (12, 25, 40), (12, 26, 88),
(13, 20, 80), (13, 21, 83), (13, 22, 8), (13, 23, 85), (13, 24, 85), (13, 25, 40), (13, 26, 82),
(14, 20, 88), (14, 21, 89), (14, 22, 8), (14, 23, 80), (14, 24, 80), (14, 25, 40), (14, 26, 85);

-- Student 2: Jihan
INSERT INTO penilaian (id_alternatif, id_kriteria, nilai) VALUES
(12, 20, 75), (12, 21, 80), (12, 22, 8), (12, 23, 80), (12, 24, 80), (12, 25, 40), (12, 26, 78),
(13, 20, 75), (13, 21, 79), (13, 22, 8), (13, 23, 79), (13, 24, 79), (13, 25, 40), (13, 26, 77),
(14, 20, 78), (14, 21, 80), (14, 22, 8), (14, 23, 80), (14, 24, 80), (14, 25, 40), (14, 26, 79);

-- Student 3: Ayu
INSERT INTO penilaian (id_alternatif, id_kriteria, nilai) VALUES
(12, 20, 80), (12, 21, 85), (12, 22, 8), (12, 23, 82), (12, 24, 82), (12, 25, 40), (12, 26, 82),
(13, 20, 79), (13, 21, 80), (13, 22, 8), (13, 23, 80), (13, 24, 80), (13, 25, 40), (13, 26, 79),
(14, 20, 80), (14, 21, 85), (14, 22, 8), (14, 23, 82), (14, 24, 82), (14, 25, 40), (14, 26, 82);

-- Student 4: Afifah
INSERT INTO penilaian (id_alternatif, id_kriteria, nilai) VALUES
(12, 20, 80), (12, 21, 85), (12, 22, 8), (12, 23, 80), (12, 24, 80), (12, 25, 40), (12, 26, 81),
(13, 20, 75), (13, 21, 75), (13, 22, 8), (13, 23, 75), (13, 24, 75), (13, 25, 40), (13, 26, 75),
(14, 20, 80), (14, 21, 82), (14, 22, 8), (14, 23, 80), (14, 24, 80), (14, 25, 40), (14, 26, 81);

-- Student 5: Fitri
INSERT INTO penilaian (id_alternatif, id_kriteria, nilai) VALUES
(12, 20, 88), (12, 21, 80), (12, 22, 8), (12, 23, 82), (12, 24, 82), (12, 25, 40), (12, 26, 83),
(13, 20, 85), (13, 21, 81), (13, 22, 8), (13, 23, 80), (13, 24, 80), (13, 25, 40), (13, 26, 80),
(14, 20, 80), (14, 21, 88), (14, 22, 8), (14, 23, 84), (14, 24, 84), (14, 25, 40), (14, 26, 84);

-- Student 6: Zakry
INSERT INTO penilaian (id_alternatif, id_kriteria, nilai) VALUES
(12, 20, 75), (12, 21, 75), (12, 22, 7), (12, 23, 75), (12, 24, 75), (12, 25, 40), (12, 26, 70),
(13, 20, 75), (13, 21, 75), (13, 22, 7), (13, 23, 75), (13, 24, 75), (13, 25, 40), (13, 26, 70),
(14, 20, 77), (14, 21, 77), (14, 22, 7), (14, 23, 77), (14, 24, 77), (14, 25, 40), (14, 26, 70);

-- Student 7: Alfa Risky
INSERT INTO penilaian (id_alternatif, id_kriteria, nilai) VALUES
(12, 20, 76), (12, 21, 78), (12, 22, 7), (12, 23, 77), (12, 24, 77), (12, 25, 40), (12, 26, 75),
(13, 20, 75), (13, 21, 75), (13, 22, 7), (13, 23, 75), (13, 24, 75), (13, 25, 40), (13, 26, 75),
(14, 20, 80), (14, 21, 79), (14, 22, 7), (14, 23, 79), (14, 24, 79), (14, 25, 40), (14, 26, 76);

-- Student 8: Andina
INSERT INTO penilaian (id_alternatif, id_kriteria, nilai) VALUES
(12, 20, 89), (12, 21, 89), (12, 22, 8), (12, 23, 89), (12, 24, 89), (12, 25, 40), (12, 26, 80),
(13, 20, 90), (13, 21, 89), (13, 22, 8), (13, 23, 89), (13, 24, 89), (13, 25, 40), (13, 26, 88),
(14, 20, 88), (14, 21, 88), (14, 22, 8), (14, 23, 88), (14, 24, 88), (14, 25, 40), (14, 26, 88);

-- Student 9: Andinda
INSERT INTO penilaian (id_alternatif, id_kriteria, nilai) VALUES
(12, 20, 95), (12, 21, 95), (12, 22, 9), (12, 23, 97), (12, 24, 97), (12, 25, 40), (12, 26, 90),
(13, 20, 96), (13, 21, 96), (13, 22, 9), (13, 23, 97), (13, 24, 97), (13, 25, 40), (13, 26, 90),
(14, 20, 98), (14, 21, 98), (14, 22, 9), (14, 23, 98), (14, 24, 98), (14, 25, 40), (14, 26, 90);

-- Student 10: Irsyad
INSERT INTO penilaian (id_alternatif, id_kriteria, nilai) VALUES
(12, 20, 80), (12, 21, 85), (12, 22, 8), (12, 23, 85), (12, 24, 85), (12, 25, 40), (12, 26, 80),
(13, 20, 85), (13, 21, 80), (13, 22, 8), (13, 23, 83), (13, 24, 83), (13, 25, 40), (13, 26, 80),
(14, 20, 85), (14, 21, 80), (14, 22, 8), (14, 23, 83), (14, 24, 83), (14, 25, 40), (14, 26, 80);
