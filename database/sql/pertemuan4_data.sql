USE praktikum_web_2401020071;

-- 1. Input Data Program Studi
INSERT INTO program_studi (nama_prodi) VALUES 
('Teknik Elektro'), 
('Teknik Kelautan');

-- 2. Input Data Mahasiswa
INSERT INTO mahasiswa 
(nim, nama, email, usia, program_studi_id) 
VALUES 
('2401020111', 'Budi Santoso', 'budi.s@studentumrah.ac.id', 20, 1),
('2401020222', 'Rina Melati', 'rina.m@studentumrah.ac.id', 19, 1),
('2401020333', 'Doni Damara', 'doni.d@studentumrah.ac.id', 21, 2),
('2401020999', 'Data Sementara', 'hapus.aku@studentumrah.ac.id', 18, 2);

-- 3. Mengubah (UPDATE) satu data email mahasiswa
UPDATE mahasiswa 
SET email = 'budi.santoso.update@studentumrah.ac.id' 
WHERE nim = '2401020111';

-- 4. Menghapus (DELETE) data sementara
DELETE FROM mahasiswa 
WHERE nim = '2401020999';

-- 5. Menampilkan hasil akhir dengan SELECT JOIN
SELECT m.nim, m.nama, m.email, m.usia, p.nama_prodi 
FROM mahasiswa AS m 
JOIN program_studi AS p 
ON p.id = m.program_studi_id 
ORDER BY m.nim;