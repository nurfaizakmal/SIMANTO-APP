<?php

return [
    // Kunci pemulihan khusus owner. Isi OWNER_RECOVERY_KEY di .env (acak, panjang, rahasia).
    // Jika kosong, reset password owner lewat halaman Lupa Password dinonaktifkan.
    'kunci_pemulihan_owner' => env('OWNER_RECOVERY_KEY'),

    // Permintaan reset password yang tidak diproses owner akan kedaluwarsa setelah sekian hari.
    'reset_berlaku_hari' => 3,

    // Nomor WhatsApp bengkel untuk tombol "Hubungi via WhatsApp" di halaman depan.
    // Format internasional tanpa + (contoh Indonesia: 6281234567890). Isi di .env: BENGKEL_WHATSAPP=62...
    'whatsapp' => env('BENGKEL_WHATSAPP', '6281234567890'),
    'whatsapp_pesan' => env('BENGKEL_WHATSAPP_PESAN', 'Halo, saya mau bertanya tentang servis motor saya.'),

    // Motor dianggap LANGGANAN bila jumlah kunjungan (servis) dengan plat yang sama LEBIH DARI 3 kali,
    // yaitu mulai kunjungan ke-4. Ubah angka ini bila aturannya berubah (isi di .env: BENGKEL_LANGGANAN_MINIMAL=4).
    'langganan_minimal_kunjungan' => (int) env('BENGKEL_LANGGANAN_MINIMAL', 4),
];
