<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/latihan-php', function () {
    $nama = 'Auriel Lifta Ekeriana G';
    $nilai = [60, 65, 70, 55, 68];

    $hitungRataRata = function (array $data): float {
        $total = 0;
        foreach ($data as $angka) {
            $total += $angka;
        }
        return $total / count($data);
    };

    $rataRata = $hitungRataRata($nilai);

    if ($rataRata >= 75) {
        $status = 'Lulus';
    } else {
        $status = 'Perlu Perbaikan';
    }

    return view('latihan-php', compact('nama', 'nilai', 'rataRata', 'status'));
});

// ==========================================
// KODE PRAKTIKUM PERTEMUAN 3 (GET & POST)
// ==========================================

// Route GET: Menampilkan form input mahasiswa
Route::get('/form-mahasiswa', function () {
    return view('form-mahasiswa');
});

// Route POST: Menerima input, sanitasi, dan validasi server-side
Route::post('/form-mahasiswa', function (Request $request) {
    // 1. Sanitasi input sebelum divalidasi
    $dataBersih = [
        'nim'   => trim((string) $request->input('nim')),
        'nama'  => strip_tags(trim((string) $request->input('nama'))),
        'email' => filter_var((string) $request->input('email'), FILTER_SANITIZE_EMAIL),
        'usia'  => trim((string) $request->input('usia')),
    ];

    // 2. Validasi server-side
    $validator = Validator::make($dataBersih, [
        'nim'   => ['required', 'digits_between:8,12'],
        'nama'  => ['required', 'min:3', 'max:50'],
        'email' => ['required', 'email'],
        'usia'  => ['required', 'integer', 'min:17', 'max:60'],
    ], [
        'nim.required'       => 'NIM wajib diisi.',
        'nim.digits_between' => 'NIM harus berupa angka antara 8 hingga 12 digit.',
        'nama.required'      => 'Nama wajib diisi.',
        'nama.min'           => 'Nama minimal 3 karakter.',
        'nama.max'           => 'Nama maksimal 50 karakter.',
        'email.required'     => 'Email wajib diisi.',
        'email.email'        => 'Format email tidak valid.',
        'usia.required'      => 'Usia wajib diisi.',
        'usia.integer'       => 'Usia harus berupa angka.',
        'usia.min'           => 'Usia minimal 17 tahun.',
        'usia.max'           => 'Usia maksimal 60 tahun.',
    ]);

    // 3. Jika validasi gagal, kembalikan ke form beserta error & nilai lama
    if ($validator->fails()) {
        return redirect('/form-mahasiswa')
            ->withErrors($validator)
            ->withInput();
    }

    // 4. Ambil data yang lolos validasi dan tampilkan ke halaman hasil
    $data = $validator->validated();
    $data['usia'] = (int) $data['usia'];

    return view('hasil-form', ['data' => $data]);
});

// ROUTE GET: Menampilkan form tambah dan tabel data mahasiswa
Route::get('/mahasiswa', function () {
    try {
        $pdo = DB::connection()->getPdo();
        
        // 1. Mengambil data mahasiswa
        $pernyataanMahasiswa = $pdo->prepare(
            'SELECT m.nim, m.nama, m.email, m.usia, p.nama_prodi
             FROM mahasiswa AS m
             JOIN program_studi AS p ON p.id = m.program_studi_id
             ORDER BY m.nim'
        );
        $pernyataanMahasiswa->execute();
        $daftarMahasiswa = $pernyataanMahasiswa->fetchAll(\PDO::FETCH_ASSOC);
        
        // 2. Mengambil data program studi untuk pilihan di form
        $pernyataanProdi = $pdo->prepare(
            'SELECT id, nama_prodi FROM program_studi ORDER BY nama_prodi'
        );
        $pernyataanProdi->execute();
        $daftarProgramStudi = $pernyataanProdi->fetchAll(\PDO::FETCH_ASSOC);
        
        return view('mahasiswa', compact('daftarMahasiswa', 'daftarProgramStudi'));
        
    } catch (\Throwable $error) {
        report($error);
        return response('Data tidak dapat dibaca. Periksa koneksi dan query.', 500);
    }
})->name('mahasiswa.index');

// ROUTE POST: Menerima data form, validasi, dan simpan ke database
Route::post('/mahasiswa', function (Request $request) {
    // 1. Validasi Input
    $data = $request->validate([
        'nim'              => ['required', 'string', 'max:20'],
        'nama'             => ['required', 'string', 'max:100'],
        'email'            => ['required', 'email', 'max:100'],
        'usia'             => ['required', 'integer', 'min:15', 'max:100'],
        'program_studi_id' => ['required', 'integer'],
    ], [
        'nim.required'              => 'NIM wajib diisi.',
        'nama.required'             => 'Nama wajib diisi.',
        'email.required'            => 'Email wajib diisi.',
        'email.email'               => 'Format email tidak valid.',
        'usia.required'             => 'Usia wajib diisi.',
        'usia.integer'              => 'Usia harus berupa angka.',
        'program_studi_id.required' => 'Program studi wajib dipilih.',
    ]);

    try {
        $pdo = DB::connection()->getPdo();
        
        // 2. Cek apakah NIM atau Email sudah ada (Duplikat)
        $cekDuplikat = $pdo->prepare(
            'SELECT COUNT(*) FROM mahasiswa WHERE nim = :nim OR email = :email'
        );
        $cekDuplikat->execute([
            'nim'   => $data['nim'],
            'email' => $data['email'],
        ]);
        
        if ((int) $cekDuplikat->fetchColumn() > 0) {
            return back()->withInput()->with('gagal', 'NIM atau email sudah digunakan.');
        }
        
        // 3. Simpan data baru (INSERT)
        $simpan = $pdo->prepare(
            'INSERT INTO mahasiswa (nim, nama, email, usia, program_studi_id)
             VALUES (:nim, :nama, :email, :usia, :program_studi_id)'
        );
        $simpan->execute([
            'nim'              => $data['nim'],
            'nama'             => $data['nama'],
            'email'            => $data['email'],
            'usia'             => $data['usia'],
            'program_studi_id' => $data['program_studi_id'],
        ]);
        
        return redirect()->route('mahasiswa.index')->with('berhasil', 'Data mahasiswa berhasil ditambahkan.');
        
    } catch (\Throwable $error) {
        report($error);
        return back()->withInput()->with('gagal', 'Data gagal disimpan. Periksa koneksi dan data.');
    }
})->name('mahasiswa.store');