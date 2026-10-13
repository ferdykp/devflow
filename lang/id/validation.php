<?php
return [
    'accepted' => ':attribute harus disetujui.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'date' => ':attribute harus berupa tanggal yang valid.',
    'email' => ':attribute harus berupa alamat email yang valid.',
    'in' => 'Pilihan :attribute tidak valid.',
    'integer' => ':attribute harus berupa angka bulat.',
    'max' => ['string' => ':attribute maksimal :max karakter.', 'numeric' => ':attribute maksimal :max.'],
    'min' => ['string' => ':attribute minimal :min karakter.', 'numeric' => ':attribute minimal :min.'],
    'required' => ':attribute wajib diisi.',
    'string' => ':attribute harus berupa teks.',
    'unique' => ':attribute sudah digunakan.',
    'exists' => ':attribute yang dipilih tidak ditemukan.',
    'attributes' => [
        'name' => 'nama', 'title' => 'judul', 'email' => 'email',
        'password' => 'kata sandi', 'password_confirmation' => 'konfirmasi kata sandi',
        'workspace_id' => 'ruang kerja', 'status' => 'status',
        'description' => 'deskripsi', 'priority' => 'prioritas',
        'due_date' => 'tenggat', 'reason' => 'alasan',
    ],
];
