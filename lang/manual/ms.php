<?php
/**
 * Kandungan manual pengguna — Bahasa Melayu
 */
return [
    'page_title' => 'Manual Pengguna',
    'subtitle' => 'Pratonton ciri & panduan operasi',
    'back_home' => 'Kembali ke Laman Utama',
    'back_dashboard' => 'Kembali ke Papan Pemuka',
    'toc_title' => 'Kandungan',
    'updated' => 'Manual ini mengikuti bahasa antara muka semasa anda',

    'sections' => [
        [
            'id' => 'overview',
            'title' => '1. Pratonton Ciri',
            'intro' => 'Sistem Chat ialah platform masa nyata untuk sembang peribadi, kumpulan, forum, serta panggilan suara/video. Bahasa: Cina, English, Bahasa Melayu.',
            'preview_items' => [
                ['icon' => '💬', 'name' => 'Sembang Peribadi & Kumpulan', 'desc' => 'Hantar teks, imej, video, fail dan nota suara. Petik, majukan, pin dan favorit mesej.'],
                ['icon' => '👥', 'name' => 'Rakan & Permintaan', 'desc' => 'Tambah rakan mengikut nama pengguna; terima atau tolak permintaan rakan dan jemputan forum.'],
                ['icon' => '🏢', 'name' => 'Pengurusan Kumpulan', 'desc' => 'Cipta kumpulan, jemput rakan, tetapkan admin, atau bubar kumpulan.'],
                ['icon' => '📢', 'name' => 'Plaza Forum', 'desc' => 'Cipta atau sertai forum; siarkan, balas, dan urus ahli serta permintaan sertai.'],
                ['icon' => '📞', 'name' => 'Panggilan Suara / Video', 'desc' => 'Panggilan satu-lawan-satu atau kumpulan dengan kawalan senyap dan kamera.'],
                ['icon' => '⭐', 'name' => 'Favorit & Profil', 'desc' => 'Simpan mesej penting; urus avatar, akaun dan kata laluan.'],
            ],
        ],
        [
            'id' => 'start',
            'title' => '2. Bermula',
            'blocks' => [
                [
                    'heading' => 'Daftar',
                    'steps' => [
                        'Di laman welcome, klik Daftar.',
                        'Masukkan nama pengguna (min. 3 aksara), e-mel, kata laluan (min. 6 aksara) dan sahkan kata laluan.',
                        'Selepas berjaya, kembali ke Log Masuk dan gunakan akaun baharu.',
                    ],
                ],
                [
                    'heading' => 'Log masuk',
                    'steps' => [
                        'Masukkan nama pengguna atau e-mel, serta kata laluan.',
                        'Klik Log Masuk untuk membuka Papan Pemuka.',
                    ],
                ],
                [
                    'heading' => 'Tukar bahasa',
                    'steps' => [
                        'Gunakan penukar bahasa di laman welcome, log masuk atau daftar.',
                        'Selepas log masuk: avatar → Tetapan → pilih Cina / English / Bahasa Melayu.',
                        'Pilihan disimpan serta-merta dan manual ini ikut bertukar.',
                    ],
                ],
                [
                    'heading' => 'Log keluar',
                    'steps' => [
                        'Avatar → Log Keluar → sahkan. Anda kembali ke laman welcome dan offline.',
                    ],
                ],
            ],
        ],
        [
            'id' => 'dashboard',
            'title' => '3. Gambaran Papan Pemuka',
            'intro' => 'Bar sisi kiri ialah hab anda; kawasan utama dibuka apabila anda masuk sembang atau forum.',
            'blocks' => [
                [
                    'heading' => 'Tab bar sisi',
                    'list' => [
                        'Sembang — bilik peribadi dan kumpulan, carian, lencana belum dibaca, pin.',
                        'Rakan — senarai rakan dan status dalam talian; tambah rakan.',
                        'Kumpulan — senarai kumpulan; cipta atau buka tetapan.',
                        'Forum — forum yang disertai; cipta atau sertai.',
                        'Permintaan — permintaan rakan dan jemputan forum; terima atau tolak.',
                    ],
                ],
                [
                    'heading' => 'Menu avatar',
                    'list' => [
                        'Profil — avatar, nama pengguna, e-mel, kata laluan.',
                        'Favorit — mesej dan media tersimpan.',
                        'Tetapan — bahasa sahaja.',
                        'Pengguna Disekat — urus pengguna dise kat.',
                        'Log Keluar.',
                    ],
                ],
                [
                    'heading' => 'Menu baris sembang (⋮)',
                    'list' => [
                        'Pin / Nyahpin',
                        'Padam Sembang (buang dari senarai)',
                        'Butiran (maklumat bilik)',
                    ],
                ],
                [
                    'heading' => 'Tips mudah alih',
                    'tips' => [
                        'Buka bar sisi dengan butang menu.',
                        'Leret untuk buka atau tutup bar sisi.',
                    ],
                ],
            ],
        ],
        [
            'id' => 'friends',
            'title' => '4. Rakan',
            'blocks' => [
                [
                    'heading' => 'Tambah rakan',
                    'steps' => [
                        'Buka Rakan → Tambah Rakan.',
                        'Masukkan nama pengguna; permintaan dihantar.',
                        'Mereka terima di Permintaan untuk menjadi rakan.',
                    ],
                ],
                [
                    'heading' => 'Mulakan sembang peribadi',
                    'steps' => [
                        'Klik rakan dalam senarai untuk membuka atau mencipta bilik peribadi.',
                    ],
                ],
                [
                    'heading' => 'Nama panggilan, sekat, padam',
                    'steps' => [
                        'Dalam bilik peribadi, buka Butiran (ℹ️).',
                        'Tetapkan/edit nama panggilan, sekat, atau padam rakan.',
                        'Nyahsekat bila-bila masa di Pengguna Disekat.',
                    ],
                ],
            ],
        ],
        [
            'id' => 'chat',
            'title' => '5. Sembang Peribadi & Tindakan Mesej',
            'intro' => 'Bilik menyokong pelbagai jenis mesej dan bar tindakan gelembung.',
            'blocks' => [
                [
                    'heading' => 'Hantar mesej',
                    'list' => [
                        'Teks — taip dan Hantar (atau Enter).',
                        'Fail / imej / video — lampirkan fail (kolaj berbilang disokong).',
                        'Suara — tahan butang rakam, lepaskan untuk hantar.',
                    ],
                ],
                [
                    'heading' => 'Bar gelembung (hover atau tekan lama)',
                    'list' => [
                        'Favorit — simpan ke Favorit.',
                        'Pin / Nyahpin — pin mesej penting di atas.',
                        'Petik — balas mesej.',
                        'Majukan / Kongsi — hantar kepada rakan atau kumpulan.',
                        'Edit — mesej teks sendiri sahaja.',
                        'Tarik balik — teks sendiri dalam kira-kira 2 minit.',
                        'Padam — buang paparan mesej di sisi anda.',
                    ],
                ],
                [
                    'heading' => 'Butiran bilik',
                    'list' => [
                        'Lihat maklumat bilik dan kosongkan sejarah sembang.',
                        'Dalam sembang peribadi: nama panggilan, sekat, atau padam rakan.',
                    ],
                ],
            ],
        ],
        [
            'id' => 'groups',
            'title' => '6. Kumpulan',
            'blocks' => [
                [
                    'heading' => 'Cipta & masuk',
                    'steps' => [
                        'Tab Kumpulan → Cipta Kumpulan → masukkan nama.',
                        'Buka kumpulan untuk bersembang; fungsi mesej sama seperti peribadi dan menunjukkan nama penghantar.',
                    ],
                ],
                [
                    'heading' => 'Tetapan kumpulan mengikut peranan',
                    'table' => [
                        'headers' => ['Tindakan', 'Pemilik', 'Admin', 'Ahli'],
                        'rows' => [
                            ['Edit nama / avatar', '✓', '✓', '—'],
                            ['Jemput / keluarkan ahli', '✓', '✓', '—'],
                            ['Naik / turun pangkat admin', '✓', '✓', '—'],
                            ['Kosongkan sejarah kumpulan', '✓', '—', '—'],
                            ['Bubar kumpulan', '✓', '—', '—'],
                            ['Sembang & panggilan', '✓', '✓', '✓'],
                        ],
                    ],
                    'tips' => [
                        'Lencana: Pemilik 👑, Admin ⚡, Ahli 👤.',
                    ],
                ],
            ],
        ],
        [
            'id' => 'calls',
            'title' => '7. Panggilan Suara & Video',
            'blocks' => [
                [
                    'heading' => 'Mulakan panggilan',
                    'steps' => [
                        'Bilik peribadi: gunakan Panggilan Suara atau Panggilan Video di pengepala.',
                        'Bilik kumpulan: mulakan panggilan suara atau video kumpulan dengan cara yang sama.',
                    ],
                ],
                [
                    'heading' => 'Jawab & kawalan',
                    'list' => [
                        'Panggilan masuk: Jawab atau Tolak.',
                        'Semasa panggilan: senyap, togol video, tutup.',
                        'Benarkan kebenaran kamera/mikrofon dalam pelayar.',
                        'HTTPS dan konfigurasi STUN/TURN disyorkan untuk kestabilan.',
                    ],
                ],
            ],
        ],
        [
            'id' => 'forums',
            'title' => '8. Forum',
            'blocks' => [
                [
                    'heading' => 'Cipta & sertai',
                    'steps' => [
                        'Forum → Cipta Forum: nama dan penerangan pilihan.',
                        'Sertai Forum membuka plaza; tapis Semua / Disertai / Tersedia / Menunggu.',
                        'Forum awam boleh diserti terus; yang peribadi perlukan jemputan atau kelulusan.',
                    ],
                ],
                [
                    'heading' => 'Siaran & interaksi',
                    'list' => [
                        'Gunakan Siaran Pantas atau layari siaran terkini.',
                        'Buka siaran untuk lihat media dan balas; pengarang boleh edit atau padam siaran sendiri.',
                        'Siaran dipin ditanda dengan jelas.',
                    ],
                ],
                [
                    'heading' => 'Tetapan forum (pencipta / admin)',
                    'list' => [
                        'Edit nama, penerangan, had ahli, awam/peribadi.',
                        'Tukar avatar; jemput rakan; lulus atau tolak permintaan sertai.',
                        'Naik/turun pangkat admin; keluarkan ahli.',
                        'Ahli boleh tinggalkan; pencipta mesti Padam Forum (tidak boleh tinggalkan terus).',
                    ],
                ],
            ],
        ],
        [
            'id' => 'favorites',
            'title' => '9. Favorit',
            'blocks' => [
                [
                    'heading' => 'Menggunakan favorit',
                    'steps' => [
                        'Ketik Favorit pada gelembung mesej.',
                        'Menu avatar → Favorit untuk melihatnya.',
                        'Tapis Semua / Imej / Video / Suara / Fail; pratonton atau padam.',
                    ],
                ],
            ],
        ],
        [
            'id' => 'profile',
            'title' => '10. Profil & Pengguna Disekat',
            'blocks' => [
                [
                    'heading' => 'Profil',
                    'list' => [
                        'Klik avatar untuk muat naik baharu.',
                        'Edit nama pengguna dan e-mel.',
                        'Tukar kata laluan dengan kata laluan semasa, baharu, dan pengesahan.',
                        'Lihat status, masa daftar, ID pengguna, dan log masuk terakhir.',
                    ],
                ],
                [
                    'heading' => 'Pengguna dise kat',
                    'steps' => [
                        'Avatar → Pengguna Disekat.',
                        'Klik Nyahsekat untuk pulihkan.',
                    ],
                ],
            ],
        ],
        [
            'id' => 'faq',
            'title' => '11. Soalan Lazim',
            'faq' => [
                [
                    'q' => 'Mesej tidak muncul secara langsung?',
                    'a' => 'Bilik dimuat semula melalui polling. Semak rangkaian atau muat semula; bar sisi dikemas kini kira-kira setiap 2 saat.',
                ],
                [
                    'q' => 'Tidak boleh tarik balik mesej?',
                    'a' => 'Hanya mesej teks sendiri dalam kira-kira 2 minit boleh ditarik balik. Selepas itu, guna Padam.',
                ],
                [
                    'q' => 'Panggilan tidak bersambung?',
                    'a' => 'Benarkan kamera/mikrofon, utamakan HTTPS, dan pastikan STUN/TURN dikonfigurasi pada pelayan.',
                ],
                [
                    'q' => 'Lupa kata laluan?',
                    'a' => 'Tetapan semula kendiri belum tersedia. Minta pentadbir sistem menetapkan semula akaun anda.',
                ],
            ],
        ],
    ],
];
