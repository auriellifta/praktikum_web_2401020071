<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

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