<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Master Bidang Studi
        Schema::create('master_bidang_studi', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->string('nama_bidang_studi');
            $table->timestamps();
        });

        // 2. Master Program Keahlian
        Schema::create('master_program_keahlian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bidang_studi_id')->constrained('master_bidang_studi')->cascadeOnDelete();
            $table->string('kode', 20)->unique();
            $table->string('nama_program_keahlian');
            $table->timestamps();
        });

        // 3. Master Konsentrasi Keahlian (Jurusan)
        Schema::create('master_konsentrasi_keahlian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_keahlian_id')->constrained('master_program_keahlian')->cascadeOnDelete();
            $table->string('kode_singkat', 20)->unique();
            $table->string('nama_konsentrasi');
            $table->timestamps();
        });

        // 4. Master Tahun Pelajaran
        Schema::create('master_tahun_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->string('tahun_pelajaran', 20)->unique();
            $table->timestamps();
        });

        // 5. Master Mata Pelajaran
        Schema::create('master_mata_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->string('kode_mapel', 30)->unique();
            $table->string('nama_mapel');
            $table->enum('kelompok', ['A_UMUM', 'B_KEJURUAN']);
            $table->string('sub_kelompok')->nullable();
            $table->integer('no_urut')->default(1);
            $table->timestamps();
        });

        // 6. Master Ekstrakurikuler
        Schema::create('master_ekstrakurikuler', function (Blueprint $table) {
            $table->id();
            $table->string('kode_ekskul', 20)->unique();
            $table->string('nama_ekskul');
            $table->integer('no_urut')->default(1);
            $table->timestamps();
        });

        // 7. Tabel Utama Siswa (Identitas & Riwayat Administrasi)
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('konsentrasi_keahlian_id')->nullable()->constrained('master_konsentrasi_keahlian')->nullOnDelete();
            
            // Identitas Utama (Varchar agar angka 0 di depan tidak hilang)
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

        // 8. Tabel Nilai Akademik per Tahun Pelajaran & Semester (Semester I - X)
        Schema::create('nilai_akademik', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('mata_pelajaran_id')->constrained('master_mata_pelajaran')->cascadeOnDelete();
            $table->foreignId('tahun_pelajaran_id')->constrained('master_tahun_pelajaran')->cascadeOnDelete();
            $table->unsignedTinyInteger('semester_ke'); // 1 sampai 10
            $table->decimal('nilai_angka', 5, 2)->nullable();
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['siswa_id', 'tahun_pelajaran_id', 'mata_pelajaran_id', 'semester_ke'], 'unik_nilai_siswa_tp_semester');
        });

        // 9. Tabel Nilai Ekstrakurikuler per Tahun Pelajaran & Semester
        Schema::create('nilai_ekstrakurikuler', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('ekstrakurikuler_id')->constrained('master_ekstrakurikuler')->cascadeOnDelete();
            $table->foreignId('tahun_pelajaran_id')->constrained('master_tahun_pelajaran')->cascadeOnDelete();
            $table->unsignedTinyInteger('semester_ke'); // 1 sampai 10
            $table->string('nilai_predikat', 50)->nullable();
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['siswa_id', 'tahun_pelajaran_id', 'ekstrakurikuler_id', 'semester_ke'], 'unik_ekskul_siswa_tp_semester');
        });

        // 10. Tabel Rekapitulasi per Tahun Pelajaran & Semester
        Schema::create('rekap_semester_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('tahun_pelajaran_id')->constrained('master_tahun_pelajaran')->cascadeOnDelete();
            $table->unsignedTinyInteger('semester_ke'); // 1 sampai 10
            
            // Praktik Kerja Lapangan (PKL)
            $table->string('nilai_pkl', 50)->nullable();
            $table->string('keterangan_pkl')->nullable();

            // Rekap Nilai
            $table->decimal('jumlah_nilai', 8, 2)->nullable();
            $table->decimal('nilai_rata_rata', 5, 2)->nullable();
            $table->integer('ranking')->nullable();

            // Absensi (Hari)
            $table->integer('sakit_hari')->default(0);
            $table->integer('izin_hari')->default(0);
            $table->integer('tanpa_kabar_hari')->default(0);
            $table->integer('jumlah_absen_hari')->default(0);

            // Status Akhir Tahun (Semester genap: 2, 4, 6, 8, 10)
            $table->enum('status_kenaikan', ['NAIK_TINGKAT', 'TIDAK_NAIK', 'LULUS'])->nullable();
            $table->string('tingkat_tujuan', 50)->nullable();
            $table->timestamps();

            $table->unique(['siswa_id', 'tahun_pelajaran_id', 'semester_ke'], 'unik_rekap_siswa_tp_semester');
        });
    }

    /**
     * Reverse the migrations.
     */
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
