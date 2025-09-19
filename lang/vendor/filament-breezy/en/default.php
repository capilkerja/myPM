<?php

return [
    'password_confirm' => [
        'heading' => 'Konfirm Password',
        'description' => 'Silahkan konfirmasi password anda untuk melanjutkan.',
        'current_password' => 'Password saat ini',
    ],
    'two_factor' => [
        'heading' => 'Autentikasi Dua Langkah',
        'description' => 'Masukkan kode verifikasi dari aplikasi autentikator Anda.',
        'code_placeholder' => 'XXX-XXX',
        'recovery' => [
            'heading' => 'Autentikasi Dua Langkah',
            'description' => 'Untuk mengakses akun, masukkan salah satu kode pemulihan yang telah Anda simpan.',
        ],
        'recovery_code_placeholder' => 'abcdef-98765',
        'recovery_code_text' => 'Perangkat Hilang?',
        'recovery_code_link' => 'Gunakan Kode Pemulihan',
        'back_to_login_link' => 'Kembal ke halaman login',
    ],
    'profile' => [
        'account' => 'Account',
        'profile' => 'Profile',
        'my_profile' => 'Profil Saya',
        'subheading' => 'Anda bisa mengatur profil anda di sini.',
        'personal_info' => [
            'heading' => 'Informasi Pribadi',
            'subheading' => 'Atur Informasi Pribadi Anda.',
            'submit' => [
                'label' => 'Perbarui',
            ],
            'notify' => 'Profil berhasil diperbarui.',
        ],
        'password' => [
            'heading' => 'Password',
            'subheading' => 'Wajib setidaknya 8 karakter.',
            'submit' => [
                'label' => 'Perbarui',
            ],
            'notify' => 'Password berhasil diperbarui!',
        ],
        '2fa' => [
            'title' => 'Autentikasi Dua Langkah',
            'description' => 'Atur Autentikasi Dua Langkah untuk melindungi akun Anda. (Disarankan)',
            'actions' => [
                'enable' => 'Aktifkan',
                'regenerate_codes' => 'Buat Ulang Kode Pemulihan',
                'disable' => 'Non-aktifkan',
                'confirm_finish' => 'Ok & selesai',
                'cancel_setup' => 'Batalkan',
            ],
            'setup_key' => 'Setup key',
            'must_enable' => 'Anda harus mengaktifkan autentikasi dua langkah untuk menggunakan fitur ini.',
            'not_enabled' => [
                'title' => 'Anda belum mengaktifkan autentikasi dua langkah.',
                'description' => 'Saat autentikasi dua langkah diaktifkan, Anda akan diminta memasukkan token keamanan yang dibuat secara acak setiap kali login. Anda bisa mendapatkan token ini dari aplikasi autentikator di ponsel Anda, seperti Google Authenticator, Microsoft Authenticator, dan lainnya',
            ],
            'finish_enabling' => [
                'title' => 'Menyelesaikan autentikasi dua langkah.',
                'description' => "Selesaikan pengaturan dengan memindai Kode QR di bawah, atau masukkan kunci penyiapan ke aplikasi autentikator Anda. Setelah itu, masukkan kode OTP yang ditampilkan.",
            ],
            'enabled' => [
                'notify' => 'Autentikasi dua langkah berhasil diaktifkan.',
                'title' => 'Anda telah mengaktifkan autentikasi dua langkah!',
                'description' => 'Autentikasi dua langkah telah aktif. Fitur ini membantu akun Anda menjadi lebih aman. T',
                'store_codes' => 'TKode-kode ini bisa digunakan untuk memulihkan akses akun Anda jika perangkat Anda hilang.
Peringatan! Kode ini hanya akan ditampilkan satu kali saja. Pastikan Anda menyimpannya di tempat yang aman.',
            ],
            'disabling' => [
                'notify' => 'Autentikasi dua langkah berhasil dinon-aktifkan.',
            ],
            'regenerate_codes' => [
                'notify' => 'Kode pemulihan berhasil diperbarui.',
            ],
            'confirmation' => [
                'success_notification' => 'Kode Sesuai. Autentikasi dua langkah berhasil diaktifkan.',
                'invalid_code' => 'Kode OTP yang Anda masukkan salah.',
            ],
        ],
        'sanctum' => [
            'title' => 'API Tokens',
            'description' => 'Manage API tokens that allow third-party services to access this application on your behalf.',
            'create' => [
                'notify' => 'Token created successfully!',
                'message' => 'Your token is only shown once upon creation. If you lose your token, you will need to delete it and create a new one.',
                'submit' => [
                    'label' => 'Create',
                ],
            ],
            'update' => [
                'notify' => 'Token updated successfully!',
                'submit' => [
                    'label' => 'Update',
                ],
            ],
            'copied' => [
                'label' => 'I have copied my token',
            ],
        ],
        'browser_sessions' => [
            'heading' => 'Sesi Browser',
            'subheading' => 'Atur Sesi Browser Anda.',
            'label' => 'Sesi Browser',
            'content' => 'Anda bisa keluar dari semua perangkat lain jika diperlukan. Di bawah ini adalah daftar sesi terakhir Anda (daftar mungkin tidak lengkap). Jika Anda curiga ada masalah keamanan, segera perbarui kata sandi Anda.',
            'device' => 'Perangkat Ini',
            'last_active' => 'Terakhir Aktif',
            'logout_other_sessions' => 'Keluar dari sesi browser lain',
            'logout_heading' => 'Keluar dari sesi browser lain',
            'logout_description' => 'Masukkan kata sandi Anda untuk keluar dari semua perangkat lain.',
            'logout_action' => 'Keluar dari Sesi Browser Lain',
            'incorrect_password' => 'Password yang anda masukan salah. Silahkan coba lagi',
            'logout_success' => 'Semua sesi browser lain telah berhasil dikeluarkan',
        ],
    ],
    'clipboard' => [
        'link' => 'Copy to clipboard',
        'tooltip' => 'Copied!',
    ],
    'fields' => [
        'avatar' => 'Foto Profil',
        'email' => 'Email',
        'login' => 'Login',
        'name' => 'Nama',
        'password' => 'Password',
        'password_confirm' => 'Password Konfirmasi',
        'new_password' => 'Password Baru',
        'new_password_confirmation' => 'Konfirmasi Password Baru',
        'token_name' => 'Token name',
        'token_expiry' => 'Token expiry',
        'abilities' => 'Abilities',
        '2fa_code' => 'Code',
        '2fa_recovery_code' => 'Recovery Code',
        'created' => 'Created',
        'expires' => 'Expires',
    ],
    'or' => 'Or',
    'cancel' => 'Cancel',
];
