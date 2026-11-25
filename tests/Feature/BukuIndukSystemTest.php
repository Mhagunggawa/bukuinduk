<?php

namespace Tests\Feature;

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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;
use ZipArchive;

class BukuIndukSystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $tataUsaha;
    protected MasterBidangStudi $bidang;
    protected MasterProgramKeahlian $program;
    protected MasterKonsentrasiKeahlian $jurusan;
    protected MasterTahunPelajaran $tahun;
    protected MasterMataPelajaran $mapelUmum;
    protected MasterMataPelajaran $mapelKejuruan;
    protected MasterEkstrakurikuler $ekskul;
    protected Siswa $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = User::factory()->create([
            'email' => 'admin@test.sch.id',
            'role' => 'admin',
        ]);

        $this->tataUsaha = User::factory()->create([
            'email' => 'tu@test.sch.id',
            'role' => 'tata_usaha',
        ]);

        $this->bidang = MasterBidangStudi::create([
            'kode' => 'TI',
            'nama_bidang_studi' => 'TEKNOLOGI INFORMASI',
        ]);

        $this->program = MasterProgramKeahlian::create([
            'bidang_studi_id' => $this->bidang->id,
            'kode' => 'TJKT',
            'nama_program_keahlian' => 'TEKNIK JARINGAN KOMPUTER & TELEKOMUNIKASI',
        ]);

        $this->jurusan = MasterKonsentrasiKeahlian::create([
            'program_keahlian_id' => $this->program->id,
            'kode_singkat' => 'TKJ',
            'nama_konsentrasi' => 'TEKNIK KOMPUTER DAN JARINGAN',
        ]);

        $this->tahun = MasterTahunPelajaran::create([
            'tahun_pelajaran' => '2025/2026',
        ]);

        $this->mapelUmum = MasterMataPelajaran::create([
            'kode_mapel' => 'PAI',
            'nama_mapel' => 'Pendidikan Agama Islam',
            'kelompok' => 'A_UMUM',
            'no_urut' => 1,
        ]);

        $this->mapelKejuruan = MasterMataPelajaran::create([
            'kode_mapel' => 'DASAR_KEJURUAN',
            'nama_mapel' => 'Dasar-Dasar Kejuruan',
            'kelompok' => 'B_KEJURUAN',
            'no_urut' => 2,
        ]);

        $this->ekskul = MasterEkstrakurikuler::create([
            'kode_ekskul' => 'EKSKUL_PRAMUKA',
            'nama_ekskul' => 'Pramuka',
            'no_urut' => 1,
        ]);

        $this->siswa = Siswa::create([
            'konsentrasi_keahlian_id' => $this->jurusan->id,
            'nis' => '25261001',
            'nisn' => '0081234567',
            'nama_lengkap' => 'BUDI SANTOSO',
            'jenis_kelamin' => 'L',
            'kelas_saat_ini' => 'X TKJ 1',
            'status_siswa' => 'AKTIF',
        ]);
    }

    public function test_role_access_control(): void
    {
        // 1. Tata Usaha has operational access to Siswa, Buku Induk, Import
        $this->actingAs($this->tataUsaha);
        $this->get('/dashboard')->assertOk();
        $this->get('/siswa')->assertOk();
        $this->get("/siswa/{$this->siswa->id}/buku-induk")->assertOk();
        $this->get('/import')->assertOk();

        // But Tata Usaha cannot access Master Data or User Management
        $this->get('/master-data')->assertForbidden();
        $this->get('/users')->assertForbidden();

        // 2. Admin has full access including Master Data & Users
        $this->actingAs($this->admin);
        $this->get('/master-data')->assertOk();
        $this->get('/users')->assertOk();
    }

    public function test_siswa_crud_and_validation(): void
    {
        $this->actingAs($this->tataUsaha);

        // Store new siswa
        $response = $this->post('/siswa', [
            'nis' => '25261002',
            'nisn' => '0081234568',
            'nama_lengkap' => 'ANI LESTARI',
            'jenis_kelamin' => 'P',
            'konsentrasi_keahlian_id' => $this->jurusan->id,
            'kelas_saat_ini' => 'X TKJ 1',
            'status_siswa' => 'AKTIF',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('siswa', [
            'nisn' => '0081234568',
            'nama_lengkap' => 'ANI LESTARI',
        ]);

        // Duplicate NISN should fail validation
        $responseDup = $this->post('/siswa', [
            'nis' => '25269999',
            'nisn' => '0081234568',
            'nama_lengkap' => 'DUPLIKAT',
            'jenis_kelamin' => 'L',
            'status_siswa' => 'AKTIF',
        ]);
        $responseDup->assertSessionHasErrors(['nisn']);

        // Update siswa
        $newSiswa = Siswa::where('nisn', '0081234568')->first();
        $updateResp = $this->put("/siswa/{$newSiswa->id}", [
            'nis' => '25261002',
            'nisn' => '0081234568',
            'nama_lengkap' => 'ANI LESTARI S.KOM',
            'jenis_kelamin' => 'P',
            'status_siswa' => 'AKTIF',
        ]);
        $updateResp->assertSessionHas('success');
        $this->assertDatabaseHas('siswa', ['nama_lengkap' => 'ANI LESTARI S.KOM']);

        // Delete siswa
        $delResp = $this->delete("/siswa/{$newSiswa->id}");
        $delResp->assertRedirect(route('siswa.index'));
        $this->assertDatabaseMissing('siswa', ['id' => $newSiswa->id]);
    }

    public function test_input_and_update_semester_grades(): void
    {
        $this->actingAs($this->tataUsaha);

        $response = $this->post("/siswa/{$this->siswa->id}/nilai-semester", [
            'semester_ke' => 1,
            'tahun_pelajaran_id' => $this->tahun->id,
            'nilai_akademik' => [
                ['mata_pelajaran_id' => $this->mapelUmum->id, 'nilai_angka' => 88.0, 'keterangan' => 'Tuntas'],
                ['mata_pelajaran_id' => $this->mapelKejuruan->id, 'nilai_angka' => 92.0, 'keterangan' => 'Tuntas'],
            ],
            'nilai_ekskul' => [
                ['ekstrakurikuler_id' => $this->ekskul->id, 'nilai_predikat' => 'A', 'keterangan' => 'Aktif'],
            ],
            'nilai_pkl' => '89',
            'keterangan_pkl' => 'PT Telekomunikasi',
            'ranking' => 1,
            'sakit_hari' => 2,
            'izin_hari' => 1,
            'tanpa_kabar_hari' => 0,
            'status_kenaikan' => 'NAIK_TINGKAT',
            'tingkat_tujuan' => 'XI TKJ',
        ]);

        $response->assertSessionHas('success');

        // Verify database records for first Tahun Pelajaran (2025/2026)
        $this->assertDatabaseHas('nilai_akademik', [
            'siswa_id' => $this->siswa->id,
            'tahun_pelajaran_id' => $this->tahun->id,
            'mata_pelajaran_id' => $this->mapelUmum->id,
            'semester_ke' => 1,
            'nilai_angka' => 88.0,
        ]);

        $this->assertDatabaseHas('nilai_ekstrakurikuler', [
            'siswa_id' => $this->siswa->id,
            'tahun_pelajaran_id' => $this->tahun->id,
            'ekstrakurikuler_id' => $this->ekskul->id,
            'semester_ke' => 1,
            'nilai_predikat' => 'A',
        ]);

        $this->assertDatabaseHas('rekap_semester_siswa', [
            'siswa_id' => $this->siswa->id,
            'tahun_pelajaran_id' => $this->tahun->id,
            'semester_ke' => 1,
            'jumlah_nilai' => 180.0,
            'nilai_rata_rata' => 90.0,
            'jumlah_absen_hari' => 3,
            'ranking' => 1,
        ]);

        // Input different grades for a DIFFERENT Tahun Pelajaran (2026/2027) on the same semester_ke = 1
        // Verify that both Tahun Pelajaran store their own separate grades without overwriting each other!
        $tahunBaru = MasterTahunPelajaran::create([
            'tahun_pelajaran' => '2026/2027',
        ]);

        $response2 = $this->post("/siswa/{$this->siswa->id}/nilai-semester", [
            'semester_ke' => 1,
            'tahun_pelajaran_id' => $tahunBaru->id,
            'nilai_akademik' => [
                ['mata_pelajaran_id' => $this->mapelUmum->id, 'nilai_angka' => 95.0, 'keterangan' => 'Tuntas'],
                ['mata_pelajaran_id' => $this->mapelKejuruan->id, 'nilai_angka' => 97.0, 'keterangan' => 'Tuntas'],
            ],
            'nilai_ekskul' => [
                ['ekstrakurikuler_id' => $this->ekskul->id, 'nilai_predikat' => 'B', 'keterangan' => 'Baik'],
            ],
            'nilai_pkl' => '96',
            'keterangan_pkl' => 'PT Dirgantara',
            'ranking' => 2,
            'sakit_hari' => 0,
            'izin_hari' => 0,
            'tanpa_kabar_hari' => 0,
        ]);

        $response2->assertSessionHas('success');

        // 2025/2026 grade is still 88.0, and 2026/2027 grade is 95.0
        $this->assertDatabaseHas('nilai_akademik', [
            'siswa_id' => $this->siswa->id,
            'tahun_pelajaran_id' => $this->tahun->id,
            'mata_pelajaran_id' => $this->mapelUmum->id,
            'semester_ke' => 1,
            'nilai_angka' => 88.0,
        ]);
        $this->assertDatabaseHas('nilai_akademik', [
            'siswa_id' => $this->siswa->id,
            'tahun_pelajaran_id' => $tahunBaru->id,
            'mata_pelajaran_id' => $this->mapelUmum->id,
            'semester_ke' => 1,
            'nilai_angka' => 95.0,
        ]);
    }

    public function test_download_excel_templates(): void
    {
        $this->actingAs($this->tataUsaha);

        $respIdentitas = $this->get('/import/template-identitas');
        $respIdentitas->assertOk();
        $this->assertStringContainsString('template_identitas_siswa.xlsx', $respIdentitas->headers->get('content-disposition'));

        $respNilai = $this->get('/import/template-nilai');
        $respNilai->assertOk();
        $this->assertStringContainsString('template_nilai_semester.xlsx', $respNilai->headers->get('content-disposition'));
    }

    public function test_import_identitas_excel(): void
    {
        $this->actingAs($this->tataUsaha);

        // Generate a test Excel spreadsheet in memory
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['nis', 'nisn', 'nama_lengkap', 'jenis_kelamin', 'kode_jurusan', 'kelas_saat_ini', 'tempat_lahir', 'tanggal_lahir'],
            ['25261099', '0089998881', 'SISWA IMPORT BARU', 'L', 'TKJ', 'X TKJ 2', 'Jakarta', '2008-01-15'],
        ]);

        $tempPath = tempnam(sys_get_temp_dir(), 'test_import_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $uploadedFile = new UploadedFile($tempPath, 'test_identitas.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->post('/import/identitas', [
            'file' => $uploadedFile,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('siswa', [
            'nisn' => '0089998881',
            'nama_lengkap' => 'SISWA IMPORT BARU',
            'konsentrasi_keahlian_id' => $this->jurusan->id,
        ]);

        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
    }

    public function test_upload_zip_pasfoto(): void
    {
        $this->actingAs($this->tataUsaha);

        // Create a test ZIP file containing:
        // 1) 25261001.jpg (matches by NIS and has valid image binary!)
        // 2) 99999999.jpg (unregistered NIS - must NOT be saved to storage and must be reported in unmatched_nis_list)
        // 3) 25261001.pdf (wrong file extension - must be rejected and reported in invalid_file_types)
        // 4) 25261001_palsu.jpg (fake .jpg containing plain text instead of image binary - must be rejected in invalid_file_types)
        $zipPath = tempnam(sys_get_temp_dir(), 'test_zip_') . '.zip';
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE);
        $dummyImageContent = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        $zip->addFromString('25261001.jpg', $dummyImageContent);
        $zip->addFromString('99999999.jpg', $dummyImageContent);
        $zip->addFromString('25261001.pdf', '%PDF-1.4 fake pdf content');
        $zip->addFromString('25261001_palsu.jpg', 'ini bukan file gambar melainkan teks biasa');
        $zip->close();

        $uploadedZip = new UploadedFile($zipPath, 'pasfoto.zip', 'application/zip', null, true);

        $response = $this->post('/import/zip-foto', [
            'zip_file' => $uploadedZip,
        ]);

        $response->assertSessionHas('success');
        $response->assertSessionHas('unmatched_nis_list');
        $response->assertSessionHas('invalid_file_types');

        $unmatchedList = session('unmatched_nis_list');
        $this->assertCount(1, $unmatchedList);
        $this->assertEquals('99999999', $unmatchedList[0]['identifier']);

        $invalidTypeList = session('invalid_file_types');
        $this->assertCount(2, $invalidTypeList);
        $this->assertEquals('25261001.pdf', $invalidTypeList[0]['file']);
        $this->assertEquals('25261001_palsu.jpg', $invalidTypeList[1]['file']);

        // Verify valid student photo was saved, and invalid NIS photo was NEVER saved to storage
        $this->siswa->refresh();
        $this->assertNotNull($this->siswa->pasfoto_path);
        $firstSavedPath = $this->siswa->pasfoto_path;
        $this->assertStringContainsString('0081234567', $firstSavedPath);
        Storage::disk('public')->assertExists($firstSavedPath);
        Storage::disk('public')->assertMissing('pasfoto/99999999.jpg');

        // Test filtering overwrite_existing = false (skip students who already have a photo)
        $responseSkip = $this->post('/import/zip-foto', [
            'zip_file' => new UploadedFile($zipPath, 'pasfoto.zip', 'application/zip', null, true),
            'overwrite_existing' => false,
        ]);
        $responseSkip->assertSessionHas('success');
        $this->assertStringContainsString('dilewati karena siswa sudah memiliki pasfoto sebelumnya', session('success'));

        if (file_exists($zipPath)) {
            unlink($zipPath);
        }
    }

    public function test_master_data_crud_by_admin(): void
    {
        $this->actingAs($this->admin);

        // 1. Jurusan
        $respJurusan = $this->post('/master-data/jurusan', [
            'program_keahlian_id' => $this->program->id,
            'kode_singkat' => 'RPL',
            'nama_konsentrasi' => 'REKAYASA PERANGKAT LUNAK',
        ]);
        $respJurusan->assertSessionHas('success');
        $this->assertDatabaseHas('master_konsentrasi_keahlian', ['kode_singkat' => 'RPL']);

        // 2. Mapel
        $respMapel = $this->post('/master-data/mapel', [
            'kode_mapel' => 'MTK',
            'nama_mapel' => 'Matematika',
            'kelompok' => 'B_KEJURUAN',
            'no_urut' => 10,
        ]);
        $respMapel->assertSessionHas('success');
        $this->assertDatabaseHas('master_mata_pelajaran', ['kode_mapel' => 'MTK']);

        // 3. Ekskul
        $respEkskul = $this->post('/master-data/ekskul', [
            'kode_ekskul' => 'EKSKUL_SISPALA',
            'nama_ekskul' => 'Sispala',
            'no_urut' => 2,
        ]);
        $respEkskul->assertSessionHas('success');
        $this->assertDatabaseHas('master_ekstrakurikuler', ['kode_ekskul' => 'EKSKUL_SISPALA']);

        // 4. Tahun Pelajaran
        $respTahun = $this->post('/master-data/tahun', [
            'tahun_pelajaran' => '2026/2027',
        ]);
        $respTahun->assertSessionHas('success');
        $this->assertDatabaseHas('master_tahun_pelajaran', ['tahun_pelajaran' => '2026/2027']);
    }
}
