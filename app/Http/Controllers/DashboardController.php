<?php

namespace App\Http\Controllers;

use App\Models\MasterKonsentrasiKeahlian;
use App\Models\MasterTahunPelajaran;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $totalAktif = Siswa::where('status_siswa', 'AKTIF')->count();
        $totalTamat = Siswa::where('status_siswa', 'TAMAT')->count();
        $totalKeluar = Siswa::where('status_siswa', 'KELUAR')->count();
        $totalSemua = Siswa::count();

        // Count per konsentrasi keahlian
        $jurusans = MasterKonsentrasiKeahlian::withCount(['siswa as total_siswa' => function ($query) {
            $query->where('status_siswa', 'AKTIF');
        }])->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'kode' => $item->kode_singkat,
                'nama' => $item->nama_konsentrasi,
                'total' => $item->total_siswa,
            ];
        });

        // Recent students
        $recentSiswa = Siswa::with('konsentrasiKeahlian')
            ->latest('updated_at')
            ->take(6)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalAktif' => $totalAktif,
                'totalTamat' => $totalTamat,
                'totalKeluar' => $totalKeluar,
                'totalSemua' => $totalSemua,
            ],
            'jurusans' => $jurusans,
            'recentSiswa' => $recentSiswa,
        ]);
    }
}
