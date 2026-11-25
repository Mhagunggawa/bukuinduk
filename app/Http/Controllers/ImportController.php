<?php

namespace App\Http\Controllers;

use App\Models\MasterEkstrakurikuler;
use App\Models\MasterKonsentrasiKeahlian;
use App\Models\MasterMataPelajaran;
use App\Models\MasterTahunPelajaran;
use App\Models\NilaiAkademik;
use App\Models\NilaiEkstrakurikuler;
use App\Models\RekapSemesterSiswa;
use App\Models\Siswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class ImportController extends Controller
{
    public function index(): Response
    {
        $tahunList = MasterTahunPelajaran::orderBy('tahun_pelajaran')->get();
        $jurusanList = MasterKonsentrasiKeahlian::orderBy('nama_konsentrasi')->get();
        $siswaIdentifiers = Siswa::select('id', 'nis', 'nisn', 'nama_lengkap', 'kelas_saat_ini', 'pasfoto_path')
            ->orderBy('nama_lengkap')
            ->get();

        return Inertia::render('Import/Index', [
            'tahunList' => $tahunList,
            'jurusanList' => $jurusanList,
            'siswaIdentifiers' => $siswaIdentifiers,
        ]);
    }

    public function downloadTemplateIdentitas(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Identitas Siswa');

        $headers = [
            'nis',
            'nisn',
            'nama_lengkap',
            'jenis_kelamin',
            'kode_jurusan',
            'kelas_saat_ini',
            'tempat_lahir',
            'tanggal_lahir',
            'warga_negara',
            'agama',
            'alamat_siswa',
            'nama_orang_tua',
            'pekerjaan_orang_tua',
            'alamat_orang_tua',
            'diterima_tanggal',
            'diterima_asal_sekolah',
            'diterima_nomor_sttb',
            'diterima_tanggal_sttb',
            'keluar_tanggal',
            'keluar_alasan',
            'tamat_nomor_sttb',
            'tamat_tanggal_sttb',
            'keterangan_lain_lain',
        ];

        // Header style
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E3A8A'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];

        $col = 1;
        foreach ($headers as $header) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $sheet->getCell("{$colLetter}1")->setValue($header);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
            $col++;
        }

        $sheet->getStyle('A1:W1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Sample Row
        $sample = [
            '25261001',
            '0081234567',
            'CONTOH SISWA BARU',
            'L',
            'TKJ',
            'X TKJ 1',
            'Bandung',
            '2008-05-20',
            'WNI',
            'Islam',
            'Jl. Soekarno Hatta No. 12, Bandung',
            'Budi Santoso',
            'Wiraswasta',
            'Jl. Soekarno Hatta No. 12, Bandung',
            '2025-07-14',
            'SMP Negeri 1 Bandung',
            'DN-02/DIK/2025/0001',
            '2025-06-25',
            '',
            '',
            '',
            '',
            'Catatan siswa jika ada',
        ];

        $col = 1;
        foreach ($sample as $val) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValueExplicit(
                "{$colLetter}2",
                $val,
                DataType::TYPE_STRING
            );
            $col++;
        }

        // Sheet 2: Referensi Kode Jurusan
        $refSheet = $spreadsheet->createSheet();
        $refSheet->setTitle('Referensi Kode Jurusan');

        $refHeaders = ['KODE JURUSAN (Untuk Kolom kode_jurusan)', 'NAMA KONSENTRASI KEAHLIAN', 'PROGRAM KEAHLIAN'];
        $col = 1;
        foreach ($refHeaders as $h) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $refSheet->getCell("{$colLetter}1")->setValue($h);
            $refSheet->getColumnDimension($colLetter)->setAutoSize(true);
            $col++;
        }
        $refSheet->getStyle('A1:C1')->applyFromArray($headerStyle);
        $refSheet->getRowDimension(1)->setRowHeight(25);

        $jurusans = MasterKonsentrasiKeahlian::with('programKeahlian')->orderBy('kode_singkat')->get();
        $r = 2;
        foreach ($jurusans as $j) {
            $refSheet->setCellValueExplicit("A{$r}", $j->kode_singkat, DataType::TYPE_STRING);
            $refSheet->setCellValueExplicit("B{$r}", $j->nama_konsentrasi, DataType::TYPE_STRING);
            $refSheet->setCellValueExplicit("C{$r}", $j->programKeahlian->nama_program ?? '-', DataType::TYPE_STRING);
            $r++;
        }

        $spreadsheet->setActiveSheetIndex(0);

        $response = new StreamedResponse(function () use ($spreadsheet) {
            if (ob_get_length()) {
                ob_end_clean();
            }
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="template_identitas_siswa.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0, must-revalidate');
        $response->headers->set('Pragma', 'public');

        return $response;
    }

    public function downloadTemplateNilai(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Nilai Semester');

        $headers = [
            'nisn',
            'nama_lengkap',
            'PAI',
            'PPKN',
            'BINDO',
            'PJOK',
            'SEJARAH',
            'SENI',
            'MULOK',
            'MTK',
            'BING',
            'INFORMATIKA',
            'IPAS',
            'DASAR_KEJURUAN',
            'KONSENTRASI_KEAHLIAN',
            'PKK',
            'MAPEL_PILIHAN',
            'NILAI_PKL',
            'EKSKUL_PRAMUKA',
            'EKSKUL_SISPALA',
            'EKSKUL_OLAHRAGA',
            'RANKING',
            'SAKIT_HARI',
            'IZIN_HARI',
            'TANPA_KABAR_HARI',
            'STATUS_KENAIKAN',
        ];

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E3A8A'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];

        $col = 1;
        foreach ($headers as $header) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $sheet->getCell("{$colLetter}1")->setValue($header);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
            $col++;
        }

        $sheet->getStyle('A1:Z1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Sample Row
        $sample = [
            '0081234567',
            'CONTOH SISWA BARU (Hanya Referensi)',
            '85',
            '88',
            '86',
            '90',
            '84',
            '82',
            '87',
            '85',
            '88',
            '92',
            '85',
            '90',
            '89',
            '87',
            '86',
            '88',
            'A',
            'B',
            'A',
            '1',
            '1',
            '0',
            '0',
            'NAIK_TINGKAT',
        ];

        $col = 1;
        foreach ($sample as $val) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValueExplicit(
                "{$colLetter}2",
                $val,
                DataType::TYPE_STRING
            );
            $col++;
        }

        // Sheet 2: Panduan & Kode Mapel / Ekskul
        $refSheet = $spreadsheet->createSheet();
        $refSheet->setTitle('Panduan & Kode Mapel');

        $refHeaders = ['KATEGORI', 'KODE HEADER KOLOM', 'NAMA MATA PELAJARAN / ATRIBUT', 'PETUNJUK PENGISIAN'];
        $col = 1;
        foreach ($refHeaders as $h) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $refSheet->getCell("{$colLetter}1")->setValue($h);
            $refSheet->getColumnDimension($colLetter)->setAutoSize(true);
            $col++;
        }
        $refSheet->getStyle('A1:D1')->applyFromArray($headerStyle);
        $refSheet->getRowDimension(1)->setRowHeight(25);

        $refData = [];
        $mapels = MasterMataPelajaran::orderBy('kelompok')->orderBy('nama_mapel')->get();
        foreach ($mapels as $m) {
            $refData[] = ['Mata Pelajaran', $m->kode_mapel, $m->nama_mapel, 'Angka bulat/desimal (0 - 100)'];
        }
        $ekskuls = MasterEkstrakurikuler::orderBy('nama_ekskul')->get();
        foreach ($ekskuls as $e) {
            $refData[] = ['Ekstrakurikuler', $e->kode_ekskul, $e->nama_ekskul, 'Predikat: A (Sangat Baik), B (Baik), C (Cukup), D (Kurang)'];
        }
        $refData[] = ['Praktik Kerja Lapangan', 'NILAI_PKL', 'Nilai PKL Siswa', 'Angka (contoh: 88) atau predikat'];
        $refData[] = ['Peringkat', 'RANKING', 'Peringkat Kelas Semester Ini', 'Angka bulat (contoh: 1, 2, 3, dst)'];
        $refData[] = ['Kehadiran', 'SAKIT_HARI', 'Jumlah Hari Sakit', 'Angka bulat (contoh: 0, 1, 2)'];
        $refData[] = ['Kehadiran', 'IZIN_HARI', 'Jumlah Hari Izin', 'Angka bulat (contoh: 0, 1, 2)'];
        $refData[] = ['Kehadiran', 'TANPA_KABAR_HARI', 'Jumlah Hari Alpa / Tanpa Keterangan', 'Angka bulat (contoh: 0, 1, 2)'];
        $refData[] = ['Status Kenaikan', 'STATUS_KENAIKAN', 'Keputusan Kenaikan / Kelulusan', 'Pilihan: NAIK_TINGKAT, TINGGAL_KELAS, LULUS'];

        $r = 2;
        foreach ($refData as $item) {
            $refSheet->setCellValueExplicit("A{$r}", $item[0], DataType::TYPE_STRING);
            $refSheet->setCellValueExplicit("B{$r}", $item[1], DataType::TYPE_STRING);
            $refSheet->setCellValueExplicit("C{$r}", $item[2], DataType::TYPE_STRING);
            $refSheet->setCellValueExplicit("D{$r}", $item[3], DataType::TYPE_STRING);
            $r++;
        }

        $spreadsheet->setActiveSheetIndex(0);

        $response = new StreamedResponse(function () use ($spreadsheet) {
            if (ob_get_length()) {
                ob_end_clean();
            }
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="template_nilai_semester.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0, must-revalidate');
        $response->headers->set('Pragma', 'public');

        return $response;
    }

    public function importIdentitas(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:20480'],
        ]);

        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        if ($highestRow < 2) {
            return redirect()->back()->with('error', 'File Excel kosong atau tidak memiliki data.');
        }

        // Cache Jurusan map (kode_singkat -> id)
        $jurusanMap = MasterKonsentrasiKeahlian::pluck('id', 'kode_singkat')
            ->mapWithKeys(fn ($id, $code) => [strtoupper(trim($code)) => $id])
            ->toArray();

        $successCount = 0;
        $failedCount = 0;
        $errorLogs = [];

        $parseDate = function ($val) {
            if (empty($val)) {
                return null;
            }
            if (is_numeric($val)) {
                try {
                    return Date::excelToDateTimeObject((float) $val)->format('Y-m-d');
                } catch (\Throwable $e) {
                    return null;
                }
            }
            $clean = trim((string) $val);
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $clean)) {
                return $clean;
            }
            $time = strtotime($clean);
            return $time ? date('Y-m-d', $time) : null;
        };

        // Process in chunks of 100 rows to ensure fast memory performance
        $chunkSize = 100;
        $allRows = [];

        for ($row = 2; $row <= $highestRow; $row++) {
            $nis = trim((string) $sheet->getCell("A{$row}")->getValue());
            $nisn = trim((string) $sheet->getCell("B{$row}")->getValue());
            $nama = trim((string) $sheet->getCell("C{$row}")->getValue());

            // Skip completely empty trailing rows
            if ($nis === '' && $nisn === '' && $nama === '') {
                continue;
            }

            if ($nis === '') {
                $failedCount++;
                $errorLogs[] = [
                    'row' => $row,
                    'nisn' => $nisn ?: '-',
                    'message' => 'Kolom NIS wajib diisi.',
                ];
                continue;
            }

            if ($nisn === '') {
                $failedCount++;
                $errorLogs[] = [
                    'row' => $row,
                    'nisn' => '-',
                    'message' => 'Kolom NISN wajib diisi sebagai kunci identifikasi.',
                ];
                continue;
            }

            if ($nama === '') {
                $failedCount++;
                $errorLogs[] = [
                    'row' => $row,
                    'nisn' => $nisn,
                    'message' => 'Kolom Nama Lengkap wajib diisi.',
                ];
                continue;
            }

            $jkRaw = strtoupper(trim((string) $sheet->getCell("D{$row}")->getValue()));
            $jk = in_array($jkRaw, ['L', 'P'], true) ? $jkRaw : 'L';

            $kodeJurusan = strtoupper(trim((string) $sheet->getCell("E{$row}")->getValue()));
            $jurusanId = $jurusanMap[$kodeJurusan] ?? null;

            $data = [
                'nis' => $nis,
                'nisn' => $nisn,
                'nama_lengkap' => $nama,
                'jenis_kelamin' => $jk,
                'konsentrasi_keahlian_id' => $jurusanId,
                'kelas_saat_ini' => trim((string) $sheet->getCell("F{$row}")->getValue()) ?: null,
                'tempat_lahir' => trim((string) $sheet->getCell("G{$row}")->getValue()) ?: null,
                'tanggal_lahir' => $parseDate($sheet->getCell("H{$row}")->getValue()),
                'warga_negara' => trim((string) $sheet->getCell("I{$row}")->getValue()) ?: 'WNI',
                'agama' => trim((string) $sheet->getCell("J{$row}")->getValue()) ?: null,
                'alamat_siswa' => trim((string) $sheet->getCell("K{$row}")->getValue()) ?: null,
                'nama_orang_tua' => trim((string) $sheet->getCell("L{$row}")->getValue()) ?: null,
                'pekerjaan_orang_tua' => trim((string) $sheet->getCell("M{$row}")->getValue()) ?: null,
                'alamat_orang_tua' => trim((string) $sheet->getCell("N{$row}")->getValue()) ?: null,
                'diterima_tanggal' => $parseDate($sheet->getCell("O{$row}")->getValue()),
                'diterima_asal_sekolah' => trim((string) $sheet->getCell("P{$row}")->getValue()) ?: null,
                'diterima_nomor_sttb' => trim((string) $sheet->getCell("Q{$row}")->getValue()) ?: null,
                'diterima_tanggal_sttb' => $parseDate($sheet->getCell("R{$row}")->getValue()),
                'keluar_tanggal' => $parseDate($sheet->getCell("S{$row}")->getValue()),
                'keluar_alasan' => trim((string) $sheet->getCell("T{$row}")->getValue()) ?: null,
                'tamat_nomor_sttb' => trim((string) $sheet->getCell("U{$row}")->getValue()) ?: null,
                'tamat_tanggal_sttb' => $parseDate($sheet->getCell("V{$row}")->getValue()),
                'keterangan_lain_lain' => trim((string) $sheet->getCell("W{$row}")->getValue()) ?: null,
                'status_siswa' => 'AKTIF',
                'updated_at' => now(),
            ];

            if ($data['tamat_nomor_sttb'] || $data['tamat_tanggal_sttb']) {
                $data['status_siswa'] = 'TAMAT';
            } elseif ($data['keluar_tanggal'] || $data['keluar_alasan']) {
                $data['status_siswa'] = 'KELUAR';
            }

            $allRows[] = $data;
        }

        // Execute Batch Upsert
        foreach (array_chunk($allRows, $chunkSize) as $chunk) {
            try {
                Siswa::upsert(
                    $chunk,
                    ['nisn'],
                    [
                        'nis',
                        'nama_lengkap',
                        'jenis_kelamin',
                        'konsentrasi_keahlian_id',
                        'kelas_saat_ini',
                        'tempat_lahir',
                        'tanggal_lahir',
                        'warga_negara',
                        'agama',
                        'alamat_siswa',
                        'nama_orang_tua',
                        'pekerjaan_orang_tua',
                        'alamat_orang_tua',
                        'diterima_tanggal',
                        'diterima_asal_sekolah',
                        'diterima_nomor_sttb',
                        'diterima_tanggal_sttb',
                        'keluar_tanggal',
                        'keluar_alasan',
                        'tamat_nomor_sttb',
                        'tamat_tanggal_sttb',
                        'keterangan_lain_lain',
                        'status_siswa',
                        'updated_at',
                    ]
                );
                $successCount += count($chunk);
            } catch (\Throwable $e) {
                // If batch fails, fallback to row by row for this chunk to isolate errors
                foreach ($chunk as $singleRow) {
                    try {
                        Siswa::updateOrCreate(['nisn' => $singleRow['nisn']], $singleRow);
                        $successCount++;
                    } catch (\Throwable $rowEx) {
                        $failedCount++;
                        $errorLogs[] = [
                            'row' => '-',
                            'nisn' => $singleRow['nisn'],
                            'message' => 'Gagal menyimpan: ' . $rowEx->getMessage(),
                        ];
                    }
                }
            }
        }

        $msg = "Import Identitas Selesai. {$successCount} data berhasil diproses.";
        if ($failedCount > 0) {
            $msg .= " {$failedCount} data gagal/dilewati.";
        }

        return redirect()->back()
            ->with('success', $msg)
            ->with('import_errors', $errorLogs);
    }

    public function importNilai(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:20480'],
            'tahun_pelajaran_id' => ['required', 'exists:master_tahun_pelajaran,id'],
            'semester_ke' => ['required', 'integer', 'between:1,10'],
        ]);

        $tahunId = $request->tahun_pelajaran_id;
        $semesterKe = (int) $request->semester_ke;

        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        if ($highestRow < 2) {
            return redirect()->back()->with('error', 'File Excel kosong atau tidak memiliki data.');
        }

        // Cache mapel (kode_mapel -> id)
        $mapelMap = MasterMataPelajaran::pluck('id', 'kode_mapel')->toArray();
        // Cache ekskul (kode_ekskul -> id)
        $ekskulMap = MasterEkstrakurikuler::pluck('id', 'kode_ekskul')->toArray();

        // Cache students (nisn -> id)
        $siswaMap = Siswa::pluck('id', 'nisn')->toArray();

        $successCount = 0;
        $failedCount = 0;
        $errorLogs = [];

        // Header mapping
        $headerColMap = [];
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $val = strtoupper(trim((string) $sheet->getCell("{$colLetter}1")->getValue()));
            if ($val !== '') {
                $headerColMap[$val] = $colLetter;
            }
        }

        $allAkademik = [];
        $allEkskul = [];
        $allRekap = [];

        for ($row = 2; $row <= $highestRow; $row++) {
            $nisnCol = $headerColMap['NISN'] ?? 'A';
            $nisn = trim((string) $sheet->getCell("{$nisnCol}{$row}")->getValue());

            if ($nisn === '') {
                continue;
            }

            if (! isset($siswaMap[$nisn])) {
                $failedCount++;
                $errorLogs[] = [
                    'row' => $row,
                    'nisn' => $nisn,
                    'message' => "Siswa dengan NISN {$nisn} tidak ditemukan di database. Pastikan data identitas siswa sudah diimport terlebih dahulu.",
                ];
                continue;
            }

            $siswaId = $siswaMap[$nisn];
            $totalNilai = 0;
            $countNilai = 0;

            // Mapel columns
            $mapelKeys = [
                'PAI', 'PPKN', 'BINDO', 'PJOK', 'SEJARAH', 'SENI', 'MULOK',
                'MTK', 'BING', 'INFORMATIKA', 'IPAS', 'DASAR_KEJURUAN',
                'KONSENTRASI_KEAHLIAN', 'PKK', 'MAPEL_PILIHAN',
            ];

            foreach ($mapelKeys as $mKey) {
                if (isset($headerColMap[$mKey], $mapelMap[$mKey])) {
                    $colL = $headerColMap[$mKey];
                    $rawVal = $sheet->getCell("{$colL}{$row}")->getValue();
                    if ($rawVal !== null && $rawVal !== '') {
                        $nilai = (float) $rawVal;
                        $totalNilai += $nilai;
                        $countNilai++;

                        $allAkademik[] = [
                            'siswa_id' => $siswaId,
                            'mata_pelajaran_id' => $mapelMap[$mKey],
                            'tahun_pelajaran_id' => $tahunId,
                            'semester_ke' => $semesterKe,
                            'nilai_angka' => $nilai,
                            'keterangan' => 'Tuntas',
                            'updated_at' => now(),
                        ];
                    }
                }
            }

            // Ekskul
            $ekskulKeys = [
                'EKSKUL_PRAMUKA' => 'EKSKUL_PRAMUKA',
                'EKSKUL_SISPALA' => 'EKSKUL_SISPALA',
                'EKSKUL_OLAHRAGA' => 'EKSKUL_OLAHRAGA',
            ];
            foreach ($ekskulKeys as $colHeader => $eCode) {
                if (isset($headerColMap[$colHeader], $ekskulMap[$eCode])) {
                    $colL = $headerColMap[$colHeader];
                    $rawVal = trim((string) $sheet->getCell("{$colL}{$row}")->getValue());
                    if ($rawVal !== '') {
                        $allEkskul[] = [
                            'siswa_id' => $siswaId,
                            'ekstrakurikuler_id' => $ekskulMap[$eCode],
                            'tahun_pelajaran_id' => $tahunId,
                            'semester_ke' => $semesterKe,
                            'nilai_predikat' => $rawVal,
                            'keterangan' => null,
                            'updated_at' => now(),
                        ];
                    }
                }
            }

            // PKL
            $nilaiPkl = null;
            if (isset($headerColMap['NILAI_PKL'])) {
                $colL = $headerColMap['NILAI_PKL'];
                $rawPkl = trim((string) $sheet->getCell("{$colL}{$row}")->getValue());
                $nilaiPkl = $rawPkl !== '' ? $rawPkl : null;
            }

            // Ranking
            $ranking = null;
            if (isset($headerColMap['RANKING'])) {
                $colL = $headerColMap['RANKING'];
                $rawRank = $sheet->getCell("{$colL}{$row}")->getValue();
                $ranking = is_numeric($rawRank) ? (int) $rawRank : null;
            }

            // Absensi
            $sakit = 0;
            if (isset($headerColMap['SAKIT_HARI'])) {
                $colL = $headerColMap['SAKIT_HARI'];
                $sakit = (int) $sheet->getCell("{$colL}{$row}")->getValue();
            }
            $izin = 0;
            if (isset($headerColMap['IZIN_HARI'])) {
                $colL = $headerColMap['IZIN_HARI'];
                $izin = (int) $sheet->getCell("{$colL}{$row}")->getValue();
            }
            $alpa = 0;
            if (isset($headerColMap['TANPA_KABAR_HARI'])) {
                $colL = $headerColMap['TANPA_KABAR_HARI'];
                $alpa = (int) $sheet->getCell("{$colL}{$row}")->getValue();
            }
            $jumlahAbsen = $sakit + $izin + $alpa;

            // Status Kenaikan
            $statusKenaikan = null;
            if (isset($headerColMap['STATUS_KENAIKAN'])) {
                $colL = $headerColMap['STATUS_KENAIKAN'];
                $rawStatus = strtoupper(trim((string) $sheet->getCell("{$colL}{$row}")->getValue()));
                if (in_array($rawStatus, ['NAIK_TINGKAT', 'TIDAK_NAIK', 'LULUS'], true)) {
                    $statusKenaikan = $rawStatus;
                }
            }

            $rataRata = $countNilai > 0 ? round($totalNilai / $countNilai, 2) : null;

            $allRekap[] = [
                'siswa_id' => $siswaId,
                'tahun_pelajaran_id' => $tahunId,
                'semester_ke' => $semesterKe,
                'nilai_pkl' => $nilaiPkl,
                'keterangan_pkl' => null,
                'jumlah_nilai' => $countNilai > 0 ? $totalNilai : null,
                'nilai_rata_rata' => $rataRata,
                'ranking' => $ranking,
                'sakit_hari' => $sakit,
                'izin_hari' => $izin,
                'tanpa_kabar_hari' => $alpa,
                'jumlah_absen_hari' => $jumlahAbsen,
                'status_kenaikan' => $statusKenaikan,
                'updated_at' => now(),
            ];

            $successCount++;
        }

        // Execute batch upserts keyed per Tahun Pelajaran & Semester
        DB::transaction(function () use ($allAkademik, $allEkskul, $allRekap) {
            foreach (array_chunk($allAkademik, 200) as $chunk) {
                NilaiAkademik::upsert(
                    $chunk,
                    ['siswa_id', 'tahun_pelajaran_id', 'mata_pelajaran_id', 'semester_ke'],
                    ['nilai_angka', 'keterangan', 'updated_at']
                );
            }

            foreach (array_chunk($allEkskul, 200) as $chunk) {
                NilaiEkstrakurikuler::upsert(
                    $chunk,
                    ['siswa_id', 'tahun_pelajaran_id', 'ekstrakurikuler_id', 'semester_ke'],
                    ['nilai_predikat', 'keterangan', 'updated_at']
                );
            }

            foreach (array_chunk($allRekap, 200) as $chunk) {
                RekapSemesterSiswa::upsert(
                    $chunk,
                    ['siswa_id', 'tahun_pelajaran_id', 'semester_ke'],
                    [
                        'nilai_pkl',
                        'keterangan_pkl',
                        'jumlah_nilai',
                        'nilai_rata_rata',
                        'ranking',
                        'sakit_hari',
                        'izin_hari',
                        'tanpa_kabar_hari',
                        'jumlah_absen_hari',
                        'status_kenaikan',
                        'updated_at',
                    ]
                );
            }
        });

        $msg = "Import Nilai Semester {$semesterKe} Selesai. {$successCount} siswa berhasil diproses.";
        if ($failedCount > 0) {
            $msg .= " {$failedCount} data siswa gagal dicocokkan.";
        }

        return redirect()->back()
            ->with('success', $msg)
            ->with('import_errors', $errorLogs);
    }

    public function uploadZipFoto(Request $request): RedirectResponse
    {
        $request->validate([
            'zip_file' => ['required', 'file', 'mimes:zip', 'max:51200'],
            'overwrite_existing' => ['nullable', 'boolean'],
        ]);

        $overwriteExisting = $request->boolean('overwrite_existing', true);
        $file = $request->file('zip_file');
        $zip = new ZipArchive();

        if ($zip->open($file->getRealPath()) !== true) {
            return redirect()->back()->with('error', 'Gagal membuka file ZIP. Pastikan file tidak rusak.');
        }

        // Ensure pasfoto directory exists
        Storage::disk('public')->makeDirectory('pasfoto');

        // Build lookup maps for BOTH NIS and NISN (case-insensitive trimmed)
        $allSiswa = Siswa::select('id', 'nis', 'nisn', 'nama_lengkap', 'pasfoto_path')->get();
        $siswaByNisn = [];
        $siswaByNis = [];
        foreach ($allSiswa as $s) {
            if ($s->nisn) {
                $siswaByNisn[strtoupper(trim($s->nisn))] = $s;
            }
            if ($s->nis) {
                $siswaByNis[strtoupper(trim($s->nis))] = $s;
            }
        }

        $matchedCount = 0;
        $replacedCount = 0;
        $skippedExistingCount = 0;
        $unmatchedFiles = [];
        $invalidTypeFiles = [];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);

            // Skip directories and Mac OSX / Windows system metadata artifacts
            $basename = basename($filename);
            if (
                str_ends_with($filename, '/') ||
                str_contains($filename, '__MACOSX') ||
                str_starts_with($basename, '.') ||
                in_array(strtolower($basename), ['thumbs.db', 'desktop.ini'], true)
            ) {
                continue;
            }

            $ext = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
            $identifier = trim((string) pathinfo($basename, PATHINFO_FILENAME));

            // 1. Check if file extension is an allowed photo format
            if (! in_array($ext, $allowedExtensions, true)) {
                $extLabel = $ext !== '' ? ".{$ext}" : 'Tanpa Ekstensi';
                $invalidTypeFiles[] = [
                    'file' => $basename,
                    'identifier' => $identifier ?: '-',
                    'ext' => strtoupper($extLabel),
                    'message' => "Tipe file '{$extLabel}' pada berkas '{$basename}' keliru/tidak didukung. Gunakan format foto JPG, JPEG, PNG, atau WEBP.",
                ];

                continue;
            }

            // 2. Read stream and verify actual image binary signature / content
            $stream = $zip->getStream($filename);
            if (! $stream) {
                $invalidTypeFiles[] = [
                    'file' => $basename,
                    'identifier' => $identifier ?: '-',
                    'ext' => strtoupper(".{$ext}"),
                    'message' => "Berkas '{$basename}' tidak dapat dibaca atau rusak di dalam arsip ZIP.",
                ];

                continue;
            }

            $rawContents = stream_get_contents($stream);
            fclose($stream);

            if ($rawContents === false || strlen($rawContents) === 0 || ! $this->isValidImageBinary($rawContents)) {
                $invalidTypeFiles[] = [
                    'file' => $basename,
                    'identifier' => $identifier ?: '-',
                    'ext' => strtoupper(".{$ext}") . ' (Bukan Gambar Valid)',
                    'message' => "Isi berkas '{$basename}' keliru atau bukan file gambar yang valid (rusak/format palsu).",
                ];

                continue;
            }

            // 3. Match identifier against NIS or NISN
            $lookupKey = strtoupper($identifier);
            $matchedSiswa = $siswaByNisn[$lookupKey] ?? $siswaByNis[$lookupKey] ?? null;

            if ($matchedSiswa) {
                $hasExistingPhoto = ! empty($matchedSiswa->pasfoto_path);

                // If user chose NOT to overwrite existing photos and student already has a photo, skip it
                if ($hasExistingPhoto && ! $overwriteExisting) {
                    $skippedExistingCount++;

                    continue;
                }

                // Compress & resize image on server (fallback if not already compressed)
                [$finalBinary, $finalExt] = $this->compressPasfotoBinary($rawContents, $ext);

                $fileKey = $matchedSiswa->nisn ?: $matchedSiswa->nis;
                $targetPath = "pasfoto/{$fileKey}.{$finalExt}";

                // Remove old photo if path was different so orphan files do not accumulate
                if ($hasExistingPhoto && $matchedSiswa->pasfoto_path !== $targetPath && Storage::disk('public')->exists($matchedSiswa->pasfoto_path)) {
                    Storage::disk('public')->delete($matchedSiswa->pasfoto_path);
                }

                Storage::disk('public')->put($targetPath, $finalBinary);

                $matchedSiswa->update([
                    'pasfoto_path' => $targetPath,
                ]);
                $matchedCount++;
                if ($hasExistingPhoto) {
                    $replacedCount++;
                }
            } else {
                // NEVER save unregistered photos to storage; record them for the modal notification
                $unmatchedFiles[] = [
                    'file' => $basename,
                    'identifier' => $identifier,
                    'nisn' => $identifier,
                    'message' => "NIS / NISN '{$identifier}' (berkas: {$basename}) tidak terdaftar di data siswa.",
                ];
            }
        }

        $zip->close();

        $msg = "Upload ZIP selesai. {$matchedCount} pasfoto siswa berhasil dikompres dan dipasangkan";
        if ($replacedCount > 0) {
            $msg .= " ({$replacedCount} foto lama digantikan)";
        }
        $msg .= '.';

        if ($skippedExistingCount > 0) {
            $msg .= " {$skippedExistingCount} foto dilewati karena siswa sudah memiliki pasfoto sebelumnya.";
        }
        if (count($invalidTypeFiles) > 0) {
            $msg .= ' ' . count($invalidTypeFiles) . ' berkas ditolak karena tipe file keliru.';
        }
        if (count($unmatchedFiles) > 0) {
            $msg .= ' ' . count($unmatchedFiles) . ' foto diabaikan karena NIS/NISN tidak terdaftar.';
        }

        return redirect()->back()
            ->with('success', $msg)
            ->with('import_errors', array_merge($invalidTypeFiles, $unmatchedFiles))
            ->with('unmatched_nis_list', $unmatchedFiles)
            ->with('invalid_file_types', $invalidTypeFiles);
    }

    /**
     * Checks whether raw binary bytes represent a valid image (JPEG, PNG, WEBP, GIF).
     */
    private function isValidImageBinary(string $bytes): bool
    {
        if (strlen($bytes) < 4) {
            return false;
        }

        // Check standard magic bytes: JPEG (\xFF\xD8\xFF), PNG (\x89PNG), GIF (GIF8), WEBP (RIFF....WEBP)
        if (
            str_starts_with($bytes, "\xFF\xD8\xFF") ||
            str_starts_with($bytes, "\x89PNG") ||
            str_starts_with($bytes, 'GIF8') ||
            (strlen($bytes) >= 12 && str_starts_with($bytes, 'RIFF') && substr($bytes, 8, 4) === 'WEBP')
        ) {
            return true;
        }

        if (function_exists('imagecreatefromstring')) {
            $img = @imagecreatefromstring($bytes);
            if ($img !== false) {
                imagedestroy($img);

                return true;
            }
        }

        return false;
    }

    /**
     * Compresses and resizes a pasfoto binary to max 450x600px (3x4 ratio) at 80% JPEG quality using PHP GD.
     *
     * @return array{0: string, 1: string}
     */
    private function compressPasfotoBinary(string $rawBinary, string $originalExt): array
    {
        if (! function_exists('imagecreatefromstring') || strlen($rawBinary) < 120) {
            return [$rawBinary, $originalExt];
        }

        $srcImg = @imagecreatefromstring($rawBinary);
        if (! $srcImg) {
            return [$rawBinary, $originalExt];
        }

        $srcW = imagesx($srcImg);
        $srcH = imagesy($srcImg);

        if ($srcW <= 4 || $srcH <= 4) {
            imagedestroy($srcImg);

            return [$rawBinary, $originalExt];
        }

        $maxW = 450;
        $maxH = 600;
        $targetW = $srcW;
        $targetH = $srcH;

        if ($srcW > $maxW || $srcH > $maxH) {
            $ratio = min($maxW / $srcW, $maxH / $srcH);
            $targetW = max(1, (int) round($srcW * $ratio));
            $targetH = max(1, (int) round($srcH * $ratio));
        }

        $dstImg = imagecreatetruecolor($targetW, $targetH);
        if (! $dstImg) {
            imagedestroy($srcImg);

            return [$rawBinary, $originalExt];
        }

        // Fill white background for transparent PNG/WebP
        $white = imagecolorallocate($dstImg, 255, 255, 255);
        imagefilledrectangle($dstImg, 0, 0, $targetW, $targetH, $white);
        imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $targetW, $targetH, $srcW, $srcH);

        ob_start();
        imagejpeg($dstImg, null, 80);
        $compressedBinary = ob_get_clean();

        imagedestroy($srcImg);
        imagedestroy($dstImg);

        if ($compressedBinary && strlen($compressedBinary) > 0 && (strlen($compressedBinary) < strlen($rawBinary) || $srcW > $maxW || $srcH > $maxH)) {
            return [$compressedBinary, 'jpg'];
        }

        return [$rawBinary, $originalExt];
    }
}
