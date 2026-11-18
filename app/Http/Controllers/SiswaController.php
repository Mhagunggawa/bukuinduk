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
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SiswaController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Siswa::with('konsentrasiKeahlian');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%")
                    ->orWhere('nama_lengkap', 'like', "%{$search}%");
            });
        }

        if ($request->filled('jurusan_id')) {
            $query->where('konsentrasi_keahlian_id', $request->jurusan_id);
        }

        if ($request->filled('kelas')) {
            $query->where('kelas_saat_ini', $request->kelas);
        }

        if ($request->filled('status')) {
            $query->where('status_siswa', $request->status);
        }

        $siswa = $query->orderBy('nama_lengkap', 'asc')->paginate(15)->withQueryString();

        $jurusanList = MasterKonsentrasiKeahlian::orderBy('nama_konsentrasi')->get();
        $kelasList = Siswa::whereNotNull('kelas_saat_ini')
            ->distinct()
            ->orderBy('kelas_saat_ini')
            ->pluck('kelas_saat_ini');

        return Inertia::render('Siswa/Index', [
            'siswa' => $siswa,
            'jurusanList' => $jurusanList,
            'kelasList' => $kelasList,
            'filters' => [
                'search' => $request->search ?? '',
                'jurusan_id' => $request->jurusan_id ?? '',
                'kelas' => $request->kelas ?? '',
                'status' => $request->status ?? '',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nis' => ['required', 'string', 'max:30', 'unique:siswa,nis'],
            'nisn' => ['required', 'string', 'max:30', 'unique:siswa,nisn'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'konsentrasi_keahlian_id' => ['nullable', 'exists:master_konsentrasi_keahlian,id'],
            'kelas_saat_ini' => ['nullable', 'string', 'max:30'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'warga_negara' => ['nullable', 'string', 'max:50'],
            'agama' => ['nullable', 'string', 'max:30'],
            'alamat_siswa' => ['nullable', 'string'],
            'nama_orang_tua' => ['nullable', 'string', 'max:255'],
            'pekerjaan_orang_tua' => ['nullable', 'string', 'max:255'],
            'alamat_orang_tua' => ['nullable', 'string'],
            'diterima_tanggal' => ['nullable', 'date'],
            'diterima_asal_sekolah' => ['nullable', 'string', 'max:255'],
            'diterima_nomor_sttb' => ['nullable', 'string', 'max:100'],
            'diterima_tanggal_sttb' => ['nullable', 'date'],
            'keluar_tanggal' => ['nullable', 'date'],
            'keluar_alasan' => ['nullable', 'string'],
            'tamat_nomor_sttb' => ['nullable', 'string', 'max:100'],
            'tamat_tanggal_sttb' => ['nullable', 'date'],
            'keterangan_lain_lain' => ['nullable', 'string'],
            'status_siswa' => ['required', Rule::in(['AKTIF', 'TAMAT', 'KELUAR'])],
            'pasfoto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('pasfoto')) {
            $path = $request->file('pasfoto')->store('pasfoto', 'public');
            $validated['pasfoto_path'] = $path;
        }

        Siswa::create($validated);

        return redirect()->back()->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function update(Request $request, Siswa $siswa): RedirectResponse
    {
        $validated = $request->validate([
            'nis' => ['required', 'string', 'max:30', Rule::unique('siswa')->ignore($siswa->id)],
            'nisn' => ['required', 'string', 'max:30', Rule::unique('siswa')->ignore($siswa->id)],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'konsentrasi_keahlian_id' => ['nullable', 'exists:master_konsentrasi_keahlian,id'],
            'kelas_saat_ini' => ['nullable', 'string', 'max:30'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'warga_negara' => ['nullable', 'string', 'max:50'],
            'agama' => ['nullable', 'string', 'max:30'],
            'alamat_siswa' => ['nullable', 'string'],
            'nama_orang_tua' => ['nullable', 'string', 'max:255'],
            'pekerjaan_orang_tua' => ['nullable', 'string', 'max:255'],
            'alamat_orang_tua' => ['nullable', 'string'],
            'diterima_tanggal' => ['nullable', 'date'],
            'diterima_asal_sekolah' => ['nullable', 'string', 'max:255'],
            'diterima_nomor_sttb' => ['nullable', 'string', 'max:100'],
            'diterima_tanggal_sttb' => ['nullable', 'date'],
            'keluar_tanggal' => ['nullable', 'date'],
            'keluar_alasan' => ['nullable', 'string'],
            'tamat_nomor_sttb' => ['nullable', 'string', 'max:100'],
            'tamat_tanggal_sttb' => ['nullable', 'date'],
            'keterangan_lain_lain' => ['nullable', 'string'],
            'status_siswa' => ['required', Rule::in(['AKTIF', 'TAMAT', 'KELUAR'])],
            'pasfoto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('pasfoto')) {
            if ($siswa->pasfoto_path && Storage::disk('public')->exists($siswa->pasfoto_path)) {
                Storage::disk('public')->delete($siswa->pasfoto_path);
            }
            $path = $request->file('pasfoto')->store('pasfoto', 'public');
            $validated['pasfoto_path'] = $path;
        }

        $siswa->update($validated);

        return redirect()->back()->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa): RedirectResponse
    {
        if ($siswa->pasfoto_path && Storage::disk('public')->exists($siswa->pasfoto_path)) {
            Storage::disk('public')->delete($siswa->pasfoto_path);
        }

        $siswa->delete();

        return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil dihapus.');
    }

    public function showBukuInduk(Siswa $siswa): Response
    {
        $siswa->load([
            'konsentrasiKeahlian.programKeahlian.bidangStudi',
            'nilaiAkademik.mataPelajaran',
            'nilaiAkademik.tahunPelajaran',
            'nilaiEkstrakurikuler.ekstrakurikuler',
            'rekapSemester.tahunPelajaran',
        ]);

        $mapelUmum = MasterMataPelajaran::where('kelompok', 'A_UMUM')
            ->orderBy('no_urut')
            ->get();

        $mapelKejuruan = MasterMataPelajaran::where('kelompok', 'B_KEJURUAN')
            ->orderBy('no_urut')
            ->get();

        $ekskulList = MasterEkstrakurikuler::orderBy('no_urut')->get();
        $tahunList = MasterTahunPelajaran::orderBy('tahun_pelajaran')->get();
        $jurusanList = MasterKonsentrasiKeahlian::orderBy('nama_konsentrasi')->get();

        return Inertia::render('Siswa/ShowBukuInduk', [
            'siswa' => $siswa,
            'mapelUmum' => $mapelUmum,
            'mapelKejuruan' => $mapelKejuruan,
            'ekskulList' => $ekskulList,
            'tahunList' => $tahunList,
            'jurusanList' => $jurusanList,
        ]);
    }

    public function updateNilaiSemester(Request $request, Siswa $siswa): RedirectResponse
    {
        $validated = $request->validate([
            'semester_ke' => ['required', 'integer', 'between:1,10'],
            'tahun_pelajaran_id' => ['nullable', 'exists:master_tahun_pelajaran,id'],
            'nilai_akademik' => ['array'],
            'nilai_akademik.*.mata_pelajaran_id' => ['required', 'exists:master_mata_pelajaran,id'],
            'nilai_akademik.*.nilai_angka' => ['nullable', 'numeric', 'between:0,100'],
            'nilai_akademik.*.keterangan' => ['nullable', 'string', 'max:255'],
            'nilai_ekskul' => ['array'],
            'nilai_ekskul.*.ekstrakurikuler_id' => ['required', 'exists:master_ekstrakurikuler,id'],
            'nilai_ekskul.*.nilai_predikat' => ['nullable', 'string', 'max:50'],
            'nilai_ekskul.*.keterangan' => ['nullable', 'string', 'max:255'],
            'nilai_pkl' => ['nullable', 'string', 'max:50'],
            'keterangan_pkl' => ['nullable', 'string', 'max:255'],
            'ranking' => ['nullable', 'integer', 'min:1'],
            'sakit_hari' => ['nullable', 'integer', 'min:0'],
            'izin_hari' => ['nullable', 'integer', 'min:0'],
            'tanpa_kabar_hari' => ['nullable', 'integer', 'min:0'],
            'status_kenaikan' => ['nullable', Rule::in(['NAIK_TINGKAT', 'TIDAK_NAIK', 'LULUS'])],
            'tingkat_tujuan' => ['nullable', 'string', 'max:50'],
        ]);

        $semesterKe = $validated['semester_ke'];
        $tahunId = $validated['tahun_pelajaran_id'] ?? null;

        DB::transaction(function () use ($siswa, $semesterKe, $tahunId, $validated) {
            $totalNilai = 0;
            $countNilai = 0;

            if (! empty($validated['nilai_akademik'])) {
                foreach ($validated['nilai_akademik'] as $item) {
                    $nilai = $item['nilai_angka'] !== null && $item['nilai_angka'] !== '' ? (float) $item['nilai_angka'] : null;

                    NilaiAkademik::updateOrCreate(
                        [
                            'siswa_id' => $siswa->id,
                            'mata_pelajaran_id' => $item['mata_pelajaran_id'],
                            'semester_ke' => $semesterKe,
                        ],
                        [
                            'tahun_pelajaran_id' => $tahunId,
                            'nilai_angka' => $nilai,
                            'keterangan' => $item['keterangan'] ?? null,
                        ]
                    );

                    if ($nilai !== null) {
                        $totalNilai += $nilai;
                        $countNilai++;
                    }
                }
            }

            if (! empty($validated['nilai_ekskul'])) {
                foreach ($validated['nilai_ekskul'] as $item) {
                    NilaiEkstrakurikuler::updateOrCreate(
                        [
                            'siswa_id' => $siswa->id,
                            'ekstrakurikuler_id' => $item['ekstrakurikuler_id'],
                            'semester_ke' => $semesterKe,
                        ],
                        [
                            'nilai_predikat' => $item['nilai_predikat'] ?? null,
                            'keterangan' => $item['keterangan'] ?? null,
                        ]
                    );
                }
            }

            $sakit = (int) ($validated['sakit_hari'] ?? 0);
            $izin = (int) ($validated['izin_hari'] ?? 0);
            $alpa = (int) ($validated['tanpa_kabar_hari'] ?? 0);
            $jumlahAbsen = $sakit + $izin + $alpa;
            $rataRata = $countNilai > 0 ? round($totalNilai / $countNilai, 2) : null;

            RekapSemesterSiswa::updateOrCreate(
                [
                    'siswa_id' => $siswa->id,
                    'semester_ke' => $semesterKe,
                ],
                [
                    'tahun_pelajaran_id' => $tahunId,
                    'nilai_pkl' => $validated['nilai_pkl'] ?? null,
                    'keterangan_pkl' => $validated['keterangan_pkl'] ?? null,
                    'jumlah_nilai' => $countNilai > 0 ? $totalNilai : null,
                    'nilai_rata_rata' => $rataRata,
                    'ranking' => $validated['ranking'] ?? null,
                    'sakit_hari' => $sakit,
                    'izin_hari' => $izin,
                    'tanpa_kabar_hari' => $alpa,
                    'jumlah_absen_hari' => $jumlahAbsen,
                    'status_kenaikan' => $validated['status_kenaikan'] ?? null,
                    'tingkat_tujuan' => $validated['tingkat_tujuan'] ?? null,
                ]
            );
        });

        return redirect()->back()->with('success', "Nilai Semester {$semesterKe} berhasil disimpan.");
    }
}
