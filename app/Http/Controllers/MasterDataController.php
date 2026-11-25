<?php

namespace App\Http\Controllers;

use App\Models\MasterBidangStudi;
use App\Models\MasterEkstrakurikuler;
use App\Models\MasterKonsentrasiKeahlian;
use App\Models\MasterMataPelajaran;
use App\Models\MasterProgramKeahlian;
use App\Models\MasterTahunPelajaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class MasterDataController extends Controller
{
    public function index(): Response
    {
        $bidangList = MasterBidangStudi::with('programKeahlian.konsentrasiKeahlian')
            ->orderBy('nama_bidang_studi')
            ->get();

        $programList = MasterProgramKeahlian::with(['bidangStudi', 'konsentrasiKeahlian'])
            ->orderBy('nama_program_keahlian')
            ->get();

        $jurusanList = MasterKonsentrasiKeahlian::with('programKeahlian.bidangStudi')
            ->orderBy('nama_konsentrasi')
            ->get();

        $mapelList = MasterMataPelajaran::orderBy('kelompok')
            ->orderBy('no_urut')
            ->get();

        $ekskulList = MasterEkstrakurikuler::orderBy('no_urut')
            ->get();

        $tahunList = MasterTahunPelajaran::withCount(['rekapSemester', 'nilaiAkademik'])
            ->orderBy('tahun_pelajaran', 'desc')
            ->get();

        return Inertia::render('MasterData/Index', [
            'bidangList' => $bidangList,
            'programList' => $programList,
            'jurusanList' => $jurusanList,
            'mapelList' => $mapelList,
            'ekskulList' => $ekskulList,
            'tahunList' => $tahunList,
        ]);
    }

    // --- Program Keahlian Induk ---
    public function storeProgram(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bidang_studi_id' => ['required', 'exists:master_bidang_studi,id'],
            'kode' => ['required', 'string', 'max:20', 'unique:master_program_keahlian,kode'],
            'nama_program_keahlian' => ['required', 'string', 'max:255'],
        ]);

        MasterProgramKeahlian::create($validated);

        return redirect()->back()->with('success', 'Program keahlian induk berhasil ditambahkan.');
    }

    public function updateProgram(Request $request, MasterProgramKeahlian $program): RedirectResponse
    {
        $validated = $request->validate([
            'bidang_studi_id' => ['required', 'exists:master_bidang_studi,id'],
            'kode' => ['required', 'string', 'max:20', Rule::unique('master_program_keahlian')->ignore($program->id)],
            'nama_program_keahlian' => ['required', 'string', 'max:255'],
        ]);

        $program->update($validated);

        return redirect()->back()->with('success', 'Program keahlian induk berhasil diperbarui.');
    }

    public function destroyProgram(MasterProgramKeahlian $program): RedirectResponse
    {
        if ($program->konsentrasiKeahlian()->count() > 0) {
            return redirect()->back()->with('error', 'Program keahlian tidak dapat dihapus karena masih menaungi konsentrasi keahlian.');
        }

        $program->delete();

        return redirect()->back()->with('success', 'Program keahlian induk berhasil dihapus.');
    }

    // --- Jurusan (Konsentrasi Keahlian) ---
    public function storeJurusan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'program_keahlian_id' => ['required', 'exists:master_program_keahlian,id'],
            'kode_singkat' => ['required', 'string', 'max:20', 'unique:master_konsentrasi_keahlian,kode_singkat'],
            'nama_konsentrasi' => ['required', 'string', 'max:255'],
        ]);

        MasterKonsentrasiKeahlian::create($validated);

        return redirect()->back()->with('success', 'Konsentrasi keahlian berhasil ditambahkan.');
    }

    public function updateJurusan(Request $request, MasterKonsentrasiKeahlian $jurusan): RedirectResponse
    {
        $validated = $request->validate([
            'program_keahlian_id' => ['required', 'exists:master_program_keahlian,id'],
            'kode_singkat' => ['required', 'string', 'max:20', Rule::unique('master_konsentrasi_keahlian')->ignore($jurusan->id)],
            'nama_konsentrasi' => ['required', 'string', 'max:255'],
        ]);

        $jurusan->update($validated);

        return redirect()->back()->with('success', 'Konsentrasi keahlian berhasil diperbarui.');
    }

    public function destroyJurusan(MasterKonsentrasiKeahlian $jurusan): RedirectResponse
    {
        $jurusan->delete();

        return redirect()->back()->with('success', 'Konsentrasi keahlian berhasil dihapus.');
    }

    // --- Mata Pelajaran ---
    public function storeMapel(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_mapel' => ['required', 'string', 'max:30', 'unique:master_mata_pelajaran,kode_mapel'],
            'nama_mapel' => ['required', 'string', 'max:255'],
            'kelompok' => ['required', Rule::in(['A_UMUM', 'B_KEJURUAN'])],
            'sub_kelompok' => ['nullable', 'string', 'max:100'],
            'no_urut' => ['required', 'integer', 'min:1'],
        ]);

        MasterMataPelajaran::create($validated);

        return redirect()->back()->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function updateMapel(Request $request, MasterMataPelajaran $mapel): RedirectResponse
    {
        $validated = $request->validate([
            'kode_mapel' => ['required', 'string', 'max:30', Rule::unique('master_mata_pelajaran')->ignore($mapel->id)],
            'nama_mapel' => ['required', 'string', 'max:255'],
            'kelompok' => ['required', Rule::in(['A_UMUM', 'B_KEJURUAN'])],
            'sub_kelompok' => ['nullable', 'string', 'max:100'],
            'no_urut' => ['required', 'integer', 'min:1'],
        ]);

        $mapel->update($validated);

        return redirect()->back()->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroyMapel(MasterMataPelajaran $mapel): RedirectResponse
    {
        $mapel->delete();

        return redirect()->back()->with('success', 'Mata pelajaran berhasil dihapus.');
    }

    // --- Ekstrakurikuler ---
    public function storeEkskul(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_ekskul' => ['required', 'string', 'max:20', 'unique:master_ekstrakurikuler,kode_ekskul'],
            'nama_ekskul' => ['required', 'string', 'max:255'],
            'no_urut' => ['required', 'integer', 'min:1'],
        ]);

        MasterEkstrakurikuler::create($validated);

        return redirect()->back()->with('success', 'Ekstrakurikuler berhasil ditambahkan.');
    }

    public function updateEkskul(Request $request, MasterEkstrakurikuler $ekskul): RedirectResponse
    {
        $validated = $request->validate([
            'kode_ekskul' => ['required', 'string', 'max:20', Rule::unique('master_ekstrakurikuler')->ignore($ekskul->id)],
            'nama_ekskul' => ['required', 'string', 'max:255'],
            'no_urut' => ['required', 'integer', 'min:1'],
        ]);

        $ekskul->update($validated);

        return redirect()->back()->with('success', 'Ekstrakurikuler berhasil diperbarui.');
    }

    public function destroyEkskul(MasterEkstrakurikuler $ekskul): RedirectResponse
    {
        $ekskul->delete();

        return redirect()->back()->with('success', 'Ekstrakurikuler berhasil dihapus.');
    }

    // --- Tahun Pelajaran ---
    public function storeTahun(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tahun_pelajaran' => ['required', 'string', 'max:20', 'unique:master_tahun_pelajaran,tahun_pelajaran'],
        ]);

        MasterTahunPelajaran::create($validated);

        return redirect()->back()->with('success', 'Tahun pelajaran berhasil ditambahkan.');
    }

    public function updateTahun(Request $request, MasterTahunPelajaran $tahun): RedirectResponse
    {
        $validated = $request->validate([
            'tahun_pelajaran' => ['required', 'string', 'max:20', Rule::unique('master_tahun_pelajaran')->ignore($tahun->id)],
        ]);

        $tahun->update($validated);

        return redirect()->back()->with('success', 'Tahun pelajaran berhasil diperbarui.');
    }

    public function destroyTahun(MasterTahunPelajaran $tahun): RedirectResponse
    {
        $tahun->delete();

        return redirect()->back()->with('success', 'Tahun pelajaran berhasil dihapus.');
    }
}
