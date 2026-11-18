<?php

namespace Database\Seeders;

use App\Models\MasterBidangStudi;
use App\Models\MasterEkstrakurikuler;
use App\Models\MasterKonsentrasiKeahlian;
use App\Models\MasterMataPelajaran;
use App\Models\MasterProgramKeahlian;
use App\Models\MasterTahunPelajaran;
use App\Models\NilaiAkademik;
use App\Models\NilaiEkstrakurikuler;
use App\Models\RekapSemesterSiswa;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users (Admin & Tata Usaha)
        $this->call(AdminUserSeeder::class);

        // 2. Master Bidang Studi & Program Keahlian & Konsentrasi
        $bidangTI = MasterBidangStudi::firstOrCreate(
            ['kode' => 'TI'],
            ['nama_bidang_studi' => 'TEKNOLOGI INFORMASI']
        );

        $bidangTM = MasterBidangStudi::firstOrCreate(
            ['kode' => 'TM'],
            ['nama_bidang_studi' => 'TEKNOLOGI MANUFAKTUR & REKAYASA']
        );

        $progTJKT = MasterProgramKeahlian::firstOrCreate(
            ['kode' => 'TJKT'],
            [
                'bidang_studi_id' => $bidangTI->id,
                'nama_program_keahlian' => 'TEKNIK JARINGAN KOMPUTER DAN TELEKOMUNIKASI',
            ]
        );

        $progPPLG = MasterProgramKeahlian::firstOrCreate(
            ['kode' => 'PPLG'],
            [
                'bidang_studi_id' => $bidangTI->id,
                'nama_program_keahlian' => 'PENGEMBANGAN PERANGKAT LUNAK DAN GIM',
            ]
        );

        $progDKV = MasterProgramKeahlian::firstOrCreate(
            ['kode' => 'DKV'],
            [
                'bidang_studi_id' => $bidangTI->id,
                'nama_program_keahlian' => 'DESAIN KOMUNIKASI VISUAL',
            ]
        );

        $jurTKJ = MasterKonsentrasiKeahlian::firstOrCreate(
            ['kode_singkat' => 'TKJ'],
            [
                'program_keahlian_id' => $progTJKT->id,
                'nama_konsentrasi' => 'TEKNIK KOMPUTER & JARINGAN (TKJ)',
            ]
        );

        $jurRPL = MasterKonsentrasiKeahlian::firstOrCreate(
            ['kode_singkat' => 'RPL'],
            [
                'program_keahlian_id' => $progPPLG->id,
                'nama_konsentrasi' => 'REKAYASA PERANGKAT LUNAK (RPL)',
            ]
        );

        $jurDKV = MasterKonsentrasiKeahlian::firstOrCreate(
            ['kode_singkat' => 'DKV'],
            [
                'program_keahlian_id' => $progDKV->id,
                'nama_konsentrasi' => 'DESAIN KOMUNIKASI VISUAL (DKV)',
            ]
        );

        // 3. Master Tahun Pelajaran
        $tpList = [
            ['tahun_pelajaran' => '2023/2024', 'is_aktif' => false],
            ['tahun_pelajaran' => '2024/2025', 'is_aktif' => false],
            ['tahun_pelajaran' => '2025/2026', 'is_aktif' => true],
            ['tahun_pelajaran' => '2026/2027', 'is_aktif' => false],
        ];
        $tpModels = [];
        foreach ($tpList as $tp) {
            $tpModels[$tp['tahun_pelajaran']] = MasterTahunPelajaran::firstOrCreate(
                ['tahun_pelajaran' => $tp['tahun_pelajaran']],
                ['is_aktif' => $tp['is_aktif']]
            );
        }

        // 4. Master Mata Pelajaran
        $mapelList = [
            // Kelompok A_UMUM
            ['kode_mapel' => 'PAI', 'nama_mapel' => 'Pendidikan Agama dan Budi Pekerti', 'kelompok' => 'A_UMUM', 'sub_kelompok' => 'Pendidikan Agama', 'no_urut' => 1],
            ['kode_mapel' => 'PPKN', 'nama_mapel' => 'Pendidikan Pancasila', 'kelompok' => 'A_UMUM', 'sub_kelompok' => null, 'no_urut' => 2],
            ['kode_mapel' => 'BINDO', 'nama_mapel' => 'Bahasa Indonesia', 'kelompok' => 'A_UMUM', 'sub_kelompok' => null, 'no_urut' => 3],
            ['kode_mapel' => 'PJOK', 'nama_mapel' => 'Pendidikan Jasmani, Olahraga & Kesehatan', 'kelompok' => 'A_UMUM', 'sub_kelompok' => null, 'no_urut' => 4],
            ['kode_mapel' => 'SEJARAH', 'nama_mapel' => 'Sejarah', 'kelompok' => 'A_UMUM', 'sub_kelompok' => null, 'no_urut' => 5],
            ['kode_mapel' => 'SENI', 'nama_mapel' => 'Seni Budaya', 'kelompok' => 'A_UMUM', 'sub_kelompok' => null, 'no_urut' => 6],
            ['kode_mapel' => 'MULOK', 'nama_mapel' => 'Muatan Lokal Potensi Daerah', 'kelompok' => 'A_UMUM', 'sub_kelompok' => null, 'no_urut' => 7],
            // Kelompok B_KEJURUAN
            ['kode_mapel' => 'MTK', 'nama_mapel' => 'Matematika', 'kelompok' => 'B_KEJURUAN', 'sub_kelompok' => null, 'no_urut' => 8],
            ['kode_mapel' => 'BING', 'nama_mapel' => 'Bahasa Inggris', 'kelompok' => 'B_KEJURUAN', 'sub_kelompok' => null, 'no_urut' => 9],
            ['kode_mapel' => 'INFORMATIKA', 'nama_mapel' => 'Informatika', 'kelompok' => 'B_KEJURUAN', 'sub_kelompok' => null, 'no_urut' => 10],
            ['kode_mapel' => 'IPAS', 'nama_mapel' => 'Projek Ilmu Pengetahuan Alam dan Sosial', 'kelompok' => 'B_KEJURUAN', 'sub_kelompok' => null, 'no_urut' => 11],
            ['kode_mapel' => 'DASAR_KEJURUAN', 'nama_mapel' => 'Dasar-Dasar Kejuruan', 'kelompok' => 'B_KEJURUAN', 'sub_kelompok' => null, 'no_urut' => 12],
            ['kode_mapel' => 'KONSENTRASI_KEAHLIAN', 'nama_mapel' => 'Mata Pelajaran Konsentrasi Keahlian', 'kelompok' => 'B_KEJURUAN', 'sub_kelompok' => null, 'no_urut' => 13],
            ['kode_mapel' => 'PKK', 'nama_mapel' => 'Projek Kreatif dan Kewirausahaan', 'kelompok' => 'B_KEJURUAN', 'sub_kelompok' => null, 'no_urut' => 14],
            ['kode_mapel' => 'MAPEL_PILIHAN', 'nama_mapel' => 'Mata Pelajaran Pilihan', 'kelompok' => 'B_KEJURUAN', 'sub_kelompok' => 'Pilihan', 'no_urut' => 15],
        ];

        $mapelModels = [];
        foreach ($mapelList as $m) {
            $mapelModels[$m['kode_mapel']] = MasterMataPelajaran::firstOrCreate(
                ['kode_mapel' => $m['kode_mapel']],
                $m
            );
        }

        // 5. Master Ekstrakurikuler
        $ekskulList = [
            ['kode_ekskul' => 'EKSKUL_PRAMUKA', 'nama_ekskul' => 'Pramuka', 'no_urut' => 1],
            ['kode_ekskul' => 'EKSKUL_SISPALA', 'nama_ekskul' => 'Sispala (Pencinta Alam)', 'no_urut' => 2],
            ['kode_ekskul' => 'EKSKUL_OLAHRAGA', 'nama_ekskul' => 'Olahraga (Futsal / Basket)', 'no_urut' => 3],
        ];

        $ekskulModels = [];
        foreach ($ekskulList as $e) {
            $ekskulModels[$e['kode_ekskul']] = MasterEkstrakurikuler::firstOrCreate(
                ['kode_ekskul' => $e['kode_ekskul']],
                $e
            );
        }

        // 6. Sample Siswa
        $siswaData = [
            [
                'konsentrasi_keahlian_id' => $jurTKJ->id,
                'nis' => '24251001',
                'nisn' => '0081234567',
                'nama_lengkap' => 'AHMAD FAUZI PRATAMA',
                'jenis_kelamin' => 'L',
                'kelas_saat_ini' => 'X TKJ 1',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '2008-04-15',
                'warga_negara' => 'WNI',
                'agama' => 'Islam',
                'alamat_siswa' => 'Jl. Merdeka No. 45, RT 02/05, Kec. Coblong, Kota Bandung',
                'nama_orang_tua' => 'Bambang Pratama',
                'pekerjaan_orang_tua' => 'Wiraswasta',
                'alamat_orang_tua' => 'Jl. Merdeka No. 45, RT 02/05, Kec. Coblong, Kota Bandung',
                'diterima_tanggal' => '2024-07-15',
                'diterima_asal_sekolah' => 'SMP Negeri 1 Bandung',
                'diterima_nomor_sttb' => 'DN-02/DIK/2024/00129',
                'diterima_tanggal_sttb' => '2024-06-20',
                'status_siswa' => 'AKTIF',
            ],
            [
                'konsentrasi_keahlian_id' => $jurTKJ->id,
                'nis' => '24251002',
                'nisn' => '0081234568',
                'nama_lengkap' => 'SITI NURHALIZA',
                'jenis_kelamin' => 'P',
                'kelas_saat_ini' => 'X TKJ 1',
                'tempat_lahir' => 'Cimahi',
                'tanggal_lahir' => '2008-08-22',
                'warga_negara' => 'WNI',
                'agama' => 'Islam',
                'alamat_siswa' => 'Jl. Cihanjuang No. 12, Cimahi Utara',
                'nama_orang_tua' => 'H. Suryana',
                'pekerjaan_orang_tua' => 'Pegawai Negeri Sipil',
                'alamat_orang_tua' => 'Jl. Cihanjuang No. 12, Cimahi Utara',
                'diterima_tanggal' => '2024-07-15',
                'diterima_asal_sekolah' => 'SMP Negeri 2 Cimahi',
                'diterima_nomor_sttb' => 'DN-02/DIK/2024/00130',
                'diterima_tanggal_sttb' => '2024-06-20',
                'status_siswa' => 'AKTIF',
            ],
            [
                'konsentrasi_keahlian_id' => $jurRPL->id,
                'nis' => '23241015',
                'nisn' => '0071234569',
                'nama_lengkap' => 'DIMAS ARYA RAMADHAN',
                'jenis_kelamin' => 'L',
                'kelas_saat_ini' => 'XI RPL 2',
                'tempat_lahir' => 'Jakarta',
                'tanggal_lahir' => '2007-10-10',
                'warga_negara' => 'WNI',
                'agama' => 'Islam',
                'alamat_siswa' => 'Komp. Antapani Mas Blok C4 No. 8, Bandung',
                'nama_orang_tua' => 'Gunawan Wibisono',
                'pekerjaan_orang_tua' => 'Karyawan Swasta',
                'alamat_orang_tua' => 'Komp. Antapani Mas Blok C4 No. 8, Bandung',
                'diterima_tanggal' => '2023-07-17',
                'diterima_asal_sekolah' => 'SMP Negeri 5 Bandung',
                'diterima_nomor_sttb' => 'DN-02/DIK/2023/00889',
                'diterima_tanggal_sttb' => '2023-06-25',
                'status_siswa' => 'AKTIF',
            ],
            [
                'konsentrasi_keahlian_id' => $jurDKV->id,
                'nis' => '22231088',
                'nisn' => '0061234570',
                'nama_lengkap' => 'CLARA ANGELINA',
                'jenis_kelamin' => 'P',
                'kelas_saat_ini' => 'XII DKV 1',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '2006-12-05',
                'warga_negara' => 'WNI',
                'agama' => 'Kristen',
                'alamat_siswa' => 'Jl. Pasirkaliki No. 102, Bandung',
                'nama_orang_tua' => 'Hendrik Tanuwidjaja',
                'pekerjaan_orang_tua' => 'Pengusaha',
                'alamat_orang_tua' => 'Jl. Pasirkaliki No. 102, Bandung',
                'diterima_tanggal' => '2022-07-18',
                'diterima_asal_sekolah' => 'SMP BPK Penabur Bandung',
                'diterima_nomor_sttb' => 'DN-02/DIK/2022/00551',
                'diterima_tanggal_sttb' => '2022-06-22',
                'status_siswa' => 'TAMAT',
                'tamat_nomor_sttb' => 'SMK-02/2025/000987',
                'tamat_tanggal_sttb' => '2025-05-15',
                'keterangan_lain_lain' => 'Lulus dengan predikat Sangat Memuaskan',
            ],
            [
                'konsentrasi_keahlian_id' => $jurTKJ->id,
                'nis' => '23241099',
                'nisn' => '0071234571',
                'nama_lengkap' => 'RENDY SAPUTRA',
                'jenis_kelamin' => 'L',
                'kelas_saat_ini' => 'XI TKJ 2',
                'tempat_lahir' => 'Tasikmalaya',
                'tanggal_lahir' => '2007-03-12',
                'warga_negara' => 'WNI',
                'agama' => 'Islam',
                'alamat_siswa' => 'Jl. Cikutra Barat No. 80, Bandung',
                'nama_orang_tua' => 'Hendra Saputra',
                'pekerjaan_orang_tua' => 'Buruh',
                'alamat_orang_tua' => 'Jl. Cikutra Barat No. 80, Bandung',
                'diterima_tanggal' => '2023-07-17',
                'diterima_asal_sekolah' => 'SMP Negeri 1 Tasikmalaya',
                'diterima_nomor_sttb' => 'DN-02/DIK/2023/00741',
                'diterima_tanggal_sttb' => '2023-06-25',
                'status_siswa' => 'KELUAR',
                'keluar_tanggal' => '2024-01-10',
                'keluar_alasan' => 'Pindah domisili mengikuti orang tua ke Yogyakarta',
            ],
        ];

        $firstSiswa = null;
        foreach ($siswaData as $index => $s) {
            $siswa = Siswa::updateOrCreate(['nisn' => $s['nisn']], $s);
            if ($index === 0) {
                $firstSiswa = $siswa;
            }
        }

        // 7. Seed Sample Grades for First Siswa (Semester 1 & 2)
        if ($firstSiswa) {
            $tpAktif = $tpModels['2025/2026'] ?? null;
            // Semester 1
            $sem1Scores = [
                'PAI' => 88.0,
                'PPKN' => 85.0,
                'BINDO' => 86.0,
                'PJOK' => 90.0,
                'SEJARAH' => 84.0,
                'SENI' => 82.0,
                'MULOK' => 87.0,
                'MTK' => 83.0,
                'BING' => 89.0,
                'INFORMATIKA' => 92.0,
                'IPAS' => 85.0,
                'DASAR_KEJURUAN' => 91.0,
                'KONSENTRASI_KEAHLIAN' => 90.0,
                'PKK' => 88.0,
                'MAPEL_PILIHAN' => 87.0,
            ];

            $total1 = 0;
            $count1 = 0;
            foreach ($sem1Scores as $k => $score) {
                if (isset($mapelModels[$k])) {
                    NilaiAkademik::updateOrCreate(
                        [
                            'siswa_id' => $firstSiswa->id,
                            'mata_pelajaran_id' => $mapelModels[$k]->id,
                            'semester_ke' => 1,
                        ],
                        [
                            'tahun_pelajaran_id' => $tpAktif?->id,
                            'nilai_angka' => $score,
                            'keterangan' => 'Tuntas',
                        ]
                    );
                    $total1 += $score;
                    $count1++;
                }
            }

            // Ekskul Sem 1
            NilaiEkstrakurikuler::updateOrCreate(
                ['siswa_id' => $firstSiswa->id, 'ekstrakurikuler_id' => $ekskulModels['EKSKUL_PRAMUKA']->id, 'semester_ke' => 1],
                ['nilai_predikat' => 'A', 'keterangan' => 'Sangat Aktif']
            );
            NilaiEkstrakurikuler::updateOrCreate(
                ['siswa_id' => $firstSiswa->id, 'ekstrakurikuler_id' => $ekskulModels['EKSKUL_OLAHRAGA']->id, 'semester_ke' => 1],
                ['nilai_predikat' => 'B', 'keterangan' => 'Baik']
            );

            // Rekap Sem 1
            RekapSemesterSiswa::updateOrCreate(
                ['siswa_id' => $firstSiswa->id, 'semester_ke' => 1],
                [
                    'tahun_pelajaran_id' => $tpAktif?->id,
                    'nilai_pkl' => null,
                    'keterangan_pkl' => null,
                    'jumlah_nilai' => $total1,
                    'nilai_rata_rata' => round($total1 / max(1, $count1), 2),
                    'ranking' => 3,
                    'sakit_hari' => 2,
                    'izin_hari' => 1,
                    'tanpa_kabar_hari' => 0,
                    'jumlah_absen_hari' => 3,
                    'status_kenaikan' => null,
                ]
            );

            // Semester 2
            $sem2Scores = [
                'PAI' => 90.0,
                'PPKN' => 87.0,
                'BINDO' => 88.0,
                'PJOK' => 91.0,
                'SEJARAH' => 86.0,
                'SENI' => 85.0,
                'MULOK' => 89.0,
                'MTK' => 86.0,
                'BING' => 91.0,
                'INFORMATIKA' => 95.0,
                'IPAS' => 88.0,
                'DASAR_KEJURUAN' => 93.0,
                'KONSENTRASI_KEAHLIAN' => 92.0,
                'PKK' => 90.0,
                'MAPEL_PILIHAN' => 89.0,
            ];
            $total2 = 0;
            $count2 = 0;
            foreach ($sem2Scores as $k => $score) {
                if (isset($mapelModels[$k])) {
                    NilaiAkademik::updateOrCreate(
                        [
                            'siswa_id' => $firstSiswa->id,
                            'mata_pelajaran_id' => $mapelModels[$k]->id,
                            'semester_ke' => 2,
                        ],
                        [
                            'tahun_pelajaran_id' => $tpAktif?->id,
                            'nilai_angka' => $score,
                            'keterangan' => 'Tuntas',
                        ]
                    );
                    $total2 += $score;
                    $count2++;
                }
            }

            NilaiEkstrakurikuler::updateOrCreate(
                ['siswa_id' => $firstSiswa->id, 'ekstrakurikuler_id' => $ekskulModels['EKSKUL_PRAMUKA']->id, 'semester_ke' => 2],
                ['nilai_predikat' => 'A', 'keterangan' => 'Sangat Aktif']
            );

            RekapSemesterSiswa::updateOrCreate(
                ['siswa_id' => $firstSiswa->id, 'semester_ke' => 2],
                [
                    'tahun_pelajaran_id' => $tpAktif?->id,
                    'nilai_pkl' => null,
                    'keterangan_pkl' => null,
                    'jumlah_nilai' => $total2,
                    'nilai_rata_rata' => round($total2 / max(1, $count2), 2),
                    'ranking' => 2,
                    'sakit_hari' => 1,
                    'izin_hari' => 0,
                    'tanpa_kabar_hari' => 0,
                    'jumlah_absen_hari' => 1,
                    'status_kenaikan' => 'NAIK_TINGKAT',
                    'tingkat_tujuan' => 'XI TKJ',
                ]
            );
        }
    }
}
