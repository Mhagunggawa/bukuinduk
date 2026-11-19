Product Requirements Document (PRD)
Sistem Informasi Buku Induk Siswa (E-Buku Induk SMK)

1. Gambaran Umum Proyek (Project Overview)
   Aplikasi Buku Induk Siswa adalah sistem informasi manajemen akademik berbasis web yang dirancang untuk mendigitalkan dokumen fisik "HASIL PRESTASI PENILAIAN" (Buku Induk Siswa SMK). Sistem ini mendukung pencatatan siklus hidup data siswa mulai dari diterima, nilai akademik per semester (Semester I s.d. X), Praktik Kerja Lapangan (PKL), ekstrakurikuler, rekap absensi, status kenaikan tingkat, hingga kelulusan/tamat atau pindah sekolah.
   Sistem dirancang khusus agar mampu menangani input data massal (>600 siswa sekaligus) melalui fitur Import Excel dan Upload ZIP Pasfoto dengan cepat tanpa membebani server (anti-timeout).

2. Spesifikasi Teknis (Tech Stack)
   Backend Framework: Laravel (menggunakan Starter Kit Vue.js & Laravel Authentication bawaan).
   Frontend Framework: Vue.js 3 (Composition API) + Inertia.js.
   UI & Styling: Tailwind CSS.
   Library Pendukung (Backend):
   maatwebsite/excel (Laravel Excel) untuk proses Batch Import & Chunk Reading.
   spatie/laravel-permission atau kolom role langsung pada tabel users (admin, tata_usaha).
   barryvdh/laravel-dompdf atau CSS @media print khusus ukuran kertas F4/A3 Landscape.

3. Sistem Desain & Tema Visual (UI/UX Guidelines)
   Aplikasi menggunakan tema warna Putih dan Biru (Clean Corporate Academic) agar tampilan tabel matriks yang padat tetap nyaman dilihat di layar.
   Palet Warna (Tailwind CSS Classes)
   Background Utama: Putih Bersih (bg-white) dan Abu-abu Sangat Muda (bg-slate-50) untuk area kerja.
   Warna Primer (Biru Tua / Navy): bg-blue-900 dan text-blue-950 untuk Sidebar navigasi, Header Tabel Utama, dan judul halaman.
   Warna Aksen / Interaktif (Biru Cerah): bg-blue-600 (Hover: hover:bg-blue-700) untuk tombol aksi utama (Primary Button), tab aktif, dan progress bar.
   Warna Sekunder (Biru Muda): bg-sky-50, bg-blue-50, dan border-blue-200 untuk sub-header kelompok mata pelajaran, badge informasi, serta efek hover pada baris tabel.
   Warna Status Indikator:
   Sukses / Naik Tingkat / Aktif: bg-emerald-100 text-emerald-800
   Peringatan / Izin / Sakit: bg-amber-100 text-amber-800
   Bahaya / Tidak Naik / Keluar: bg-rose-100 text-rose-800
   Tipografi & Tata Letak
   Font: Inter atau Plus Jakarta Sans.
   Tabel Matriks Padat (Compact Table): Menggunakan ukuran teks text-xs pada sel nilai semester dengan padding rapat (px-2 py-1.5) agar seluruh kolom Semester I hingga X dapat terlihat proporsional dalam satu tampilan layar maupun saat dicetak.

4. Manajemen Role & Hak Akses Pengguna
   Sistem membatasi akses menjadi 2 Role Utama:
   Fitur / Modul
   Admin (admin)
   Tata Usaha (tata_usaha)
   Login & Pengaturan Profil
   Ya
   Ya
   Dashboard Statistik Akademik
   Ya
   Ya
   Manajemen Akun Pengguna (CRUD User)
   Ya
   Tidak
   Manajemen Data Master (Jurusan, Mapel, Tahun, Ekskul)
   Ya
   Hanya Lihat
   Direktori & Pencarian Siswa
   Ya
   Ya
   Input & Edit Biodata / Nilai Siswa (Manual)
   Ya
   Ya
   Import Massal via Excel (Identitas & Nilai)
   Ya
   Ya
   Upload Massal Pasfoto via ZIP
   Ya
   Ya
   Lihat & Cetak Lembar Buku Induk Siswa
   Ya
   Ya

5. Rancangan Struktur Database (Database Schema)
   Berikut adalah rancangan tabel beserta kode migration Laravel yang siap dieksekusi:
   5.1. Skema Tabel Master & Utama (Laravel Migration)

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Master Bidang Studi
        Schema::create('master_bidang_studi', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->string('nama_bidang_studi'); // Contoh: TEKNOLOGI INFORMASI
            $table->timestamps();
        });

        // 2. Master Program Keahlian
        Schema::create('master_program_keahlian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bidang_studi_id')->constrained('master_bidang_studi')->cascadeOnDelete();
            $table->string('kode', 20)->unique();
            $table->string('nama_program_keahlian'); // Contoh: TEKNIK JARINGAN KOMPUTER dan TELEKOMUNIKASI
            $table->timestamps();
        });

        // 3. Master Konsentrasi Keahlian (Jurusan)
        Schema::create('master_konsentrasi_keahlian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_keahlian_id')->constrained('master_program_keahlian')->cascadeOnDelete();
            $table->string('kode_singkat', 20)->unique(); // Contoh: TKJ (Digunakan untuk kode di Excel)
            $table->string('nama_konsentrasi'); // Contoh: TEKNIK KOMPUTER & JARINGAN (TKJ)
            $table->timestamps();
        });

        // 4. Master Tahun Pelajaran
        Schema::create('master_tahun_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->string('tahun_pelajaran', 20)->unique(); // Contoh: 2025/2026
            $table->boolean('is_aktif')->default(false);
            $table->timestamps();
        });

        // 5. Master Mata Pelajaran
        Schema::create('master_mata_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->string('kode_mapel', 30)->unique(); // Contoh: PAI, PPKN, MTK, INFORMATIKA
            $table->string('nama_mapel');
            $table->enum('kelompok', ['A_UMUM', 'B_KEJURUAN']);
            $table->string('sub_kelompok')->nullable(); // Khusus Pendidikan Agama atau Mapel Pilihan
            $table->integer('no_urut')->default(1);
            $table->timestamps();
        });

        // 6. Master Ekstrakurikuler
        Schema::create('master_ekstrakurikuler', function (Blueprint $table) {
            $table->id();
            $table->string('kode_ekskul', 20)->unique(); // Contoh: PRAMUKA, SISPALA, OLAHRAGA
            $table->string('nama_ekskul');
            $table->integer('no_urut')->default(1);
            $table->timestamps();
        });

        // 7. Tabel Utama Siswa (Identitas & Riwayat Administrasi)
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('konsentrasi_keahlian_id')->nullable()->constrained('master_konsentrasi_keahlian')->nullOnDelete();
            
            // Identitas Utama (Varchar agar angka 0 di depan tidak hilang saat import Excel)
            $table->string('nis', 30)->unique();
            $table->string('nisn', 30)->unique();
            $table->string('nama_lengkap');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('kelas_saat_ini', 30)->nullable();
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('warga_negara', 50)->default('WNI');
            $table->string('agama', 30)->nullable();
            $table->text('alamat_siswa')->nullable();

            // Data Orang Tua
            $table->string('nama_orang_tua')->nullable();
            $table->string('pekerjaan_orang_tua')->nullable();
            $table->text('alamat_orang_tua')->nullable();

            // Diterima Menjadi Siswa
            $table->date('diterima_tanggal')->nullable();
            $table->string('diterima_asal_sekolah')->nullable();
            $table->string('diterima_nomor_sttb', 100)->nullable();
            $table->date('diterima_tanggal_sttb')->nullable();

            // Meninggalkan Sekolah (Nullable)
            $table->date('keluar_tanggal')->nullable();
            $table->text('keluar_alasan')->nullable();

            // Tamat (Nullable)
            $table->string('tamat_nomor_sttb', 100)->nullable();
            $table->date('tamat_tanggal_sttb')->nullable();
            $table->text('keterangan_lain_lain')->nullable();

            // Pasfoto 3x4cm
            $table->string('pasfoto_path')->nullable();
            $table->enum('status_siswa', ['AKTIF', 'TAMAT', 'KELUAR'])->default('AKTIF');
            $table->timestamps();
        });

        // 8. Tabel Nilai Akademik per Semester (Semester I - X)
        Schema::create('nilai_akademik', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('mata_pelajaran_id')->constrained('master_mata_pelajaran')->cascadeOnDelete();
            $table->foreignId('tahun_pelajaran_id')->nullable()->constrained('master_tahun_pelajaran')->nullOnDelete();
            $table->unsignedTinyInteger('semester_ke'); // Angka 1 sampai 10 (I s.d X)
            $table->decimal('nilai_angka', 5, 2)->nullable();
            $table->string('keterangan')->nullable();
            $table->timestamps();

            // Unique index untuk mendukung fitur Batch Upsert dari Excel
            $table->unique(['siswa_id', 'mata_pelajaran_id', 'semester_ke'], 'unik_nilai_siswa_semester');
        });

        // 9. Tabel Nilai Ekstrakurikuler per Semester
        Schema::create('nilai_ekstrakurikuler', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('ekstrakurikuler_id')->constrained('master_ekstrakurikuler')->cascadeOnDelete();
            $table->unsignedTinyInteger('semester_ke'); // 1 sampai 10
            $table->string('nilai_predikat', 50)->nullable(); // Contoh: Sangat Baik / A / 85
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['siswa_id', 'ekstrakurikuler_id', 'semester_ke'], 'unik_ekskul_siswa_semester');
        });

        // 10. Tabel Rekapitulasi Semester (PKL, Jumlah, Rata-rata, Ranking, Absensi, Kenaikan)
        Schema::create('rekap_semester_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('tahun_pelajaran_id')->nullable()->constrained('master_tahun_pelajaran')->nullOnDelete();
            $table->unsignedTinyInteger('semester_ke'); // 1 sampai 10
            
            // Praktik Kerja Lapangan (PKL)
            $table->string('nilai_pkl', 50)->nullable();
            $table->string('keterangan_pkl')->nullable();

            // Rekap Nilai
            $table->decimal('jumlah_nilai', 8, 2)->nullable();
            $table->decimal('nilai_rata_rata', 5, 2)->nullable();
            $table->integer('ranking')->nullable();

            // Absensi (Dalam Hari)
            $table->integer('sakit_hari')->default(0);
            $table->integer('izin_hari')->default(0);
            $table->integer('tanpa_kabar_hari')->default(0);
            $table->integer('jumlah_absen_hari')->default(0);

            // Status Akhir Tahun (Diisi pada semester genap: II, IV, VI, VIII, X)
            $table->enum('status_kenaikan', ['NAIK_TINGKAT', 'TIDak_NAIK', 'LULUS'])->nullable();
            $table->string('tingkat_tujuan', 50)->nullable();
            $table->timestamps();

            $table->unique(['siswa_id', 'semester_ke'], 'unik_rekap_siswa_semester');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekap_semester_siswa');
        Schema::dropIfExists('nilai_ekstrakurikuler');
        Schema::dropIfExists('nilai_akademik');
        Schema::dropIfExists('siswa');
        Schema::dropIfExists('master_ekstrakurikuler');
        Schema::dropIfExists('master_mata_pelajaran');
        Schema::dropIfExists('master_tahun_pelajaran');
        Schema::dropIfExists('master_konsentrasi_keahlian');
        Schema::dropIfExists('master_program_keahlian');
        Schema::dropIfExists('master_bidang_studi');
    }
};

6. Spesifikasi Fitur Import Excel (>600 Siswa)
Untuk memudahkan Tata Usaha menginput ratusan siswa sekaligus, sistem menyediakan 2 Template Excel Terpisah dan 1 Fitur Upload ZIP Pasfoto.
6.1. Template 1: Import Identitas Siswa (template_identitas_siswa.xlsx)
Digunakan untuk menginput siswa baru atau memperbarui biodata siswa lama secara massal menggunakan metode Upsert berdasarkan nisn.
Urutan Header Kolom Excel:
nis (Wajib, Text)
nisn (Wajib, Text - Kunci Unik Upsert)
nama_lengkap (Wajib)
jenis_kelamin (Dropdown: L / P)
kode_jurusan (Contoh: TKJ - merujuk ke kode_singkat tabel konsentrasi keahlian)
kelas_saat_ini (Contoh: X TKJ 1)
tempat_lahir
tanggal_lahir (Format: YYYY-MM-DD)
warga_negara (Default: WNI)
agama (Dropdown: Islam, Kristen, Katolik, Buddha, Hindu, Konghucu)
alamat_siswa
nama_orang_tua
pekerjaan_orang_tua
alamat_orang_tua
diterima_tanggal (Format: YYYY-MM-DD)
diterima_asal_sekolah
diterima_nomor_sttb
diterima_tanggal_sttb (Format: YYYY-MM-DD)
keluar_tanggal (Boleh kosong)
keluar_alasan (Boleh kosong)
tamat_nomor_sttb (Boleh kosong)
tamat_tanggal_sttb (Boleh kosong)
keterangan_lain_lain (Boleh kosong)
6.2. Template 2: Import Nilai & Rekap Semester (template_nilai_semester.xlsx)
Sebelum mengunggah file ini di aplikasi, pengguna memilih Tahun Pelajaran (misal: 2025/2026) dan Semester (misal: Semester I) melalui dropdown di halaman web.
Urutan Header Kolom Excel:
nisn (Wajib - Kunci pencocokan siswa)
nama_lengkap (Hanya referensi bacaan bagi admin)
PAI (Nilai Pendidikan Agama sesuai agama siswa)
PPKN (Pendidikan Pancasila)
BINDO (Bahasa Indonesia)
PJOK (Pendidikan Jasmani, Olahraga & Kesehatan)
SEJARAH (Sejarah)
SENI (Seni Budaya)
MULOK (Muatan Lokal Potensi Daerah)
MTK (Matematika)
BING (Bahasa Inggris)
INFORMATIKA (Informatika)
IPAS (Projek Ilmu Pengetahuan Alam dan Sosial)
DASAR_KEJURUAN (Dasar-Dasar Teknik Jaringan Komputer dan Telekomunikasi)
KONSENTRASI_KEAHLIAN (Mata Pelajaran Konsentrasi Keahlian)
PKK (Projek Kreatif dan Kewirausahaan)
MAPEL_PILIHAN (Mata Pelajaran Pilihan)
NILAI_PKL (Praktik Kerja Lapangan)
EKSKUL_PRAMUKA
EKSKUL_SISPALA
EKSKUL_OLAHRAGA
RANKING
SAKIT_HARI
IZIN_HARI
TANPA_KABAR_HARI
STATUS_KENAIKAN (NAIK_TINGKAT / TIDAK_NAIK - khusus semester genap)
(Catatan: Jumlah Nilai, Nilai Rata-Rata, dan Jumlah Hari Absen dihitung otomatis oleh sistem saat proses import berlangsung).

7. Spesifikasi Halaman & Komponen UI (Untuk Antigravity)
7.1. Layout Navigasi Utama (AuthenticatedLayout.vue)
Sidebar Kiri (bg-blue-900 text-white w-64):
Logo & Nama Aplikasi di bagian atas.
Menu Navigasi:
Dashboard (Ikon Chart/Home)
Direktori Siswa (Ikon Users)
Import Data Massal (Ikon FileSpreadsheet)
Data Master (Hanya tampil untuk Role admin):
Bidang & Program Keahlian
Mata Pelajaran & Ekskul
Tahun Pelajaran
Manajemen Pengguna (Hanya tampil untuk Role admin)
Topbar (bg-white border-b border-slate-200 h-16):
Menampilkan Breadcrumb halaman aktif di sebelah kiri.
Menampilkan Badge Tahun Pelajaran Aktif, Nama User, Badge Role (Admin / Tata Usaha), dan dropdown Logout di sebelah kanan.
7.2. Halaman Dashboard (Dashboard.vue)
4 Kartu Ringkasan (Stat Cards): Total Siswa Aktif, Total Siswa per Jurusan, Siswa Tamat/Alumni, dan Siswa Pindah/Keluar dengan ikon berlatar biru muda (bg-blue-50 text-blue-600).
Pintasan Aksi Cepat (Quick Actions): Tombol langsung ke "Import Siswa Baru (Excel)", "Import Nilai Semester", dan "Cari & Cetak Buku Induk".
7.3. Halaman Direktori Siswa (Siswa/Index.vue)
Baris Kontrol Atas:
Input pencarian real-time (cari berdasarkan NIS, NISN, atau Nama Siswa).
Filter Dropdown: Konsentrasi Keahlian, Kelas, dan Status Siswa (Aktif, Tamat, Keluar).
Tombol "+ Tambah Siswa" (bg-blue-600 text-white) dan tombol "Import Excel" (bg-white border-blue-600 text-blue-600).
Tabel Data Siswa:
Menampilkan Pasfoto mini, NIS / NISN, Nama Lengkap, L/P, Kelas, Jurusan, Status, dan Tombol Aksi: "Buka Buku Induk" (Ikon Buku) & "Edit".
7.4. Halaman Detail Buku Induk Siswa (Siswa/ShowBukuInduk.vue) — Halaman Inti
Halaman ini mereplikasi persis tata letak lembar fisik "HASIL PRESTASI PENILAIAN" dalam tampilan kanvas putih berbingkai tegas:
Action Bar di Atas Kanvas (Disembunyikan saat Print):
Tombol "Kembali ke Direktori", Tombol "Edit Identitas", Tombol "Input/Edit Nilai Semester", dan Tombol "Cetak Buku Induk (PDF / Print)" (bg-blue-600 text-white).
Header Lembar Buku Induk:
Kiri: Judul besar HASIL PRESTASI PENILAIAN dengan garis bawah tebal.
Kanan: Informasi BIDANG STUDI, PROGRAM KEAHLIAN, dan KONSENTRASI KEAHLIAN.
Struktur Konten Utama (Grid 2 Panel Berdampingan):
Panel Kiri (Lebar 32% - IDENTITAS SISWA):
Menampilkan seluruh poin biodata berurutan dari atas ke bawah: NIS, NISN, LAKI-LAKI / PEREMPUAN, NAMA, KELAS, TEMPAT / TANGGAL LAHIR, WARGA NEGARA, AGAMA, ALAMAT SISWA.
Sub-bagian NAMA ORANG TUA (a. Pekerjaan, b. Alamat Rumah).
Sub-bagian DITERIMA MENJADI SISWA (a. Mulai Tanggal, b. Asal Sekolah, c. Nomor STTB, d. Tanggal STTB).
Sub-bagian MENINGGALKAN SEKOLAH (a. Tanggal, b. Alasan).
Sub-bagian TAMAT (a. Nomor STTB, b. Tanggal STTB, KETERANGAN LAIN-LAIN).
Kotak Bingkai PasPhoto ukuran proporsional $3 \times 4\text{ cm}$ di bagian kiri bawah.
Panel Kanan (Lebar 68% - PROGRAM PENDIDIKAN DAN PELATIHAN):
Header Kolom Bertingkat:
Baris 1: 10 Kolom THN PEL. (Mulai 2025/2026 s.d. 2034/2035) + 1 Kolom KETERANGAN (Vertikal/Miring).
Baris 2: NILAI SEMESTER I s.d. NILAI SEMESTER X.
Seksi A. KELOMPOK MATA PELAJARAN UMUM:
Nomor 1 menampilkan grup Pendidikan Agama (Islam, Kristen, Katolik, Buddha, Hindu).
Nomor 2 s.d. 7 menampilkan Pendidikan Pancasila, Bahasa Indonesia, PJOK, Sejarah, Seni Budaya, dan Muatan Lokal.
Seksi B. KELOMPOK MATA PELAJARAN KEJURUAN:
Nomor 1 s.d. 7 menampilkan Matematika, Bahasa Inggris, Informatika, Projek IPAS, Dasar-Dasar Kejuruan, Konsentrasi Keahlian, Projek Kreatif & Kewirausahaan, serta Mata Pelajaran Pilihan.
Seksi PRAKTIK KERJA LAPANGAN (PKL) & KEGIATAN EKSTRA KURIKULER:
Baris Pramuka, SISPALA, dan Olahraga.
Seksi Rekapitulasi Bawah:
Baris JUMLAH NILAI (Latar aksen hijau lembut / biru muda).
Baris NILAI RATA-RATA (Latar aksen ungu/biru lembut).
Baris RANKING.
Grup Baris ABSENSI: SAKIT (.... HARI), IZIN (.... HARI), TANPA KABAR (.... HARI), JUMLAH (.... HARI).
Baris Status Akhir Tahun (Digabung per 2 semester / 1 tahun pelajaran): NAIK TINGKAT ....... dan TIDAK NAIK ........
7.5. Halaman Import Massal (Import/Index.vue)
Menggunakan komponen 3 Tab Navigasi (bg-blue-50 dengan tab aktif bg-blue-600 text-white):
Tab 1: Import Identitas Siswa (Excel):
Tombol unduh Template_Identitas_Siswa.xlsx.
Dropzone area upload file .xlsx / .csv.
Indikator Progress Bar dan Tabel Hasil Validasi Baris (menampilkan baris ke berapa yang gagal beserta alasannya).
Tab 2: Import Nilai & Absensi Semester (Excel):
Pilihan Dropdown Wajib: Pilih Tahun Pelajaran dan Pilih Semester (I - X).
Tombol unduh Template_Nilai_Semester.xlsx.
Dropzone area upload file Excel nilai.
Tab 3: Upload Massal Pasfoto 3x4 (ZIP):
Panduan singkat: "Masukkan seluruh file foto siswa (JPG/PNG) dengan nama file sesuai NISN (contoh: 0081234567.jpg) ke dalam satu file .zip."
Dropzone upload file .zip dan ringkasan jumlah foto yang berhasil dipasangkan ke data siswa.
