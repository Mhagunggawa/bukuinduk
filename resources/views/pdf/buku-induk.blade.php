<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hasil Prestasi Penilaian - {{ $siswa->nama_lengkap }}</title>
    <style>
        @page {
            margin: 7mm 9mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 8.2pt;
            color: #1e293b;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        /* Header Lembar Buku Induk */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0F3A5F;
            padding-bottom: 5px;
            margin-bottom: 7px;
            border-collapse: collapse;
        }
        .header-title {
            font-size: 14pt;
            font-weight: bold;
            color: #0F3A5F;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin: 0;
        }
        .header-subtitle {
            font-size: 7.5pt;
            font-weight: bold;
            color: #64748B;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 2px;
        }
        .header-jurusan {
            text-align: right;
            font-size: 7.5pt;
            line-height: 1.35;
        }
        .header-jurusan .label {
            color: #64748B;
            font-weight: bold;
        }
        .header-jurusan .val {
            color: #0F3A5F;
            font-weight: bold;
        }

        /* Layout 2 Panel Utama (Kiri 31%, Kanan 69%) */
        .layout-table {
            width: 100%;
            border-collapse: collapse;
        }
        .layout-left {
            width: 31%;
            vertical-align: top;
            padding-right: 8px;
        }
        .layout-right {
            width: 69%;
            vertical-align: top;
        }

        /* Panel Kiri */
        .left-box {
            border: 1px solid #94a3b8;
            border-radius: 4px;
            padding: 6px 8px;
        }
        .panel-banner {
            background-color: #0F3A5F;
            color: #ffffff;
            font-weight: bold;
            font-size: 8pt;
            text-align: center;
            padding: 4px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .bio-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.4pt;
        }
        .bio-table td {
            padding: 2px 1px;
            border-bottom: 0.5px solid #e2e8f0;
            vertical-align: top;
        }
        .bio-label {
            width: 37%;
            color: #475569;
            font-weight: bold;
        }
        .bio-sep {
            width: 3%;
            text-align: center;
            color: #94a3b8;
        }
        .bio-val {
            width: 60%;
            color: #0F3A5F;
            font-weight: bold;
        }
        .sub-section {
            margin-top: 5px;
            padding-top: 4px;
            border-top: 1px solid #cbd5e1;
            font-size: 7.2pt;
            line-height: 1.35;
        }
        .sub-title {
            font-weight: bold;
            color: #0F3A5F;
            text-transform: uppercase;
            margin-bottom: 2px;
            font-size: 7.4pt;
        }
        .sub-item {
            margin-left: 4px;
        }
        .sub-item .k {
            color: #475569;
        }
        .sub-item .v {
            color: #0f172a;
            font-weight: bold;
        }

        /* Pasfoto 3x4 Frame */
        .photo-wrap {
            margin-top: 6px;
            padding-top: 6px;
            border-top: 1px solid #cbd5e1;
            text-align: center;
        }
        .photo-frame {
            width: 2.6cm;
            height: 3.4cm;
            border: 1px dashed #64748b;
            margin: 0 auto;
            background-color: #f8fafc;
            text-align: center;
            overflow: hidden;
        }
        .photo-frame img {
            width: 2.6cm;
            height: 3.4cm;
            object-fit: cover;
        }
        .photo-placeholder {
            padding-top: 1.1cm;
            font-size: 7pt;
            color: #94a3b8;
            font-weight: bold;
            text-transform: uppercase;
        }
        .photo-caption {
            font-size: 6.8pt;
            color: #64748b;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 2px;
        }

        /* Panel Kanan: Tabel Matriks Nilai */
        .matrix-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.3pt;
        }
        .matrix-table th,
        .matrix-table td {
            border: 0.6px solid #64748b;
            padding: 2.2px 3px;
            text-align: center;
        }
        .matrix-table thead tr.row-tp th {
            background-color: #0F3A5F;
            color: #ffffff;
            font-weight: bold;
            font-size: 7.3pt;
            padding: 3px 2px;
        }
        .matrix-table thead tr.row-sem th {
            background-color: #134571;
            color: #ffffff;
            font-weight: bold;
            font-size: 7.3pt;
            padding: 2.5px 2px;
            width: 5.2%;
        }
        .col-no {
            width: 3.5%;
        }
        .col-mapel {
            width: 36%;
            text-align: left !important;
            padding-left: 5px !important;
        }
        .col-ket {
            width: 6.5%;
        }
        .section-row td {
            background-color: #F1F5F9;
            color: #0F3A5F;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7.3pt;
            padding: 2.8px 4px;
        }
        .text-left {
            text-align: left !important;
            padding-left: 5px !important;
        }
        .val-filled {
            font-weight: bold;
            color: #0F3A5F;
        }
        .val-empty {
            color: #94a3b8;
        }
        .rekap-row-1 td {
            background-color: #F8FAFC;
            font-weight: bold;
            color: #0F3A5F;
        }
        .rekap-row-2 td {
            background-color: #F1F5F9;
            font-weight: bold;
            color: #0F3A5F;
        }
        .status-naik {
            color: #0F3A5F;
            font-weight: bold;
            font-size: 6.8pt;
        }
        .status-tidak {
            color: #e11d48;
            font-weight: bold;
            font-size: 6.8pt;
        }
    </style>
</head>
<body>
    <!-- Header Lembar Hasil Prestasi Penilaian -->
    <table class="header-table">
        <tr>
            <td style="width: 50%; vertical-align: bottom;">
                <div class="header-title">HASIL PRESTASI PENILAIAN</div>
                <div class="header-subtitle">BUKU INDUK SISWA SEKOLAH MENENGAH KEJURUAN (SMK)</div>
            </td>
            <td style="width: 50%; vertical-align: bottom;" class="header-jurusan">
                <div>
                    <span class="label">BIDANG STUDI KEAHLIAN:</span>
                    <span class="val">{{ $siswa->konsentrasiKeahlian?->programKeahlian?->bidangStudi?->nama_bidang_studi ?? 'TEKNOLOGI INFORMASI' }}</span>
                </div>
                <div>
                    <span class="label">PROGRAM KEAHLIAN:</span>
                    <span class="val">{{ $siswa->konsentrasiKeahlian?->programKeahlian?->nama_program_keahlian ?? 'TEKNIK JARINGAN KOMPUTER & TELEKOMUNIKASI' }}</span>
                </div>
                <div>
                    <span class="label">KONSENTRASI KEAHLIAN:</span>
                    <span class="val">{{ $siswa->konsentrasiKeahlian?->nama_konsentrasi ?? 'TEKNIK KOMPUTER DAN JARINGAN (TKJ)' }}</span>
                </div>
            </td>
        </tr>
    </table>

    <!-- Konten 2 Panel Berdampingan -->
    <table class="layout-table">
        <tr>
            <!-- PANEL KIRI: IDENTITAS SISWA -->
            <td class="layout-left">
                <div class="left-box">
                    <div class="panel-banner">I. IDENTITAS SISWA</div>

                    <table class="bio-table">
                        <tr>
                            <td class="bio-label">1. NIS</td>
                            <td class="bio-sep">:</td>
                            <td class="bio-val">{{ $siswa->nis }}</td>
                        </tr>
                        <tr>
                            <td class="bio-label">2. NISN</td>
                            <td class="bio-sep">:</td>
                            <td class="bio-val">{{ $siswa->nisn }}</td>
                        </tr>
                        <tr>
                            <td class="bio-label">3. Jenis Kelamin</td>
                            <td class="bio-sep">:</td>
                            <td class="bio-val">{{ $siswa->jenis_kelamin === 'L' ? 'LAKI-LAKI' : 'PEREMPUAN' }}</td>
                        </tr>
                        <tr>
                            <td class="bio-label">4. Nama Lengkap</td>
                            <td class="bio-sep">:</td>
                            <td class="bio-val" style="text-transform: uppercase;">{{ $siswa->nama_lengkap }}</td>
                        </tr>
                        <tr>
                            <td class="bio-label">5. Kelas Saat Ini</td>
                            <td class="bio-sep">:</td>
                            <td class="bio-val">{{ $siswa->kelas_saat_ini ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="bio-label">6. Tempat, Tgl Lahir</td>
                            <td class="bio-sep">:</td>
                            <td style="color: #1e293b;">{{ $siswa->tempat_lahir ?: '-' }}, {{ $formattedDates['tanggal_lahir'] }}</td>
                        </tr>
                        <tr>
                            <td class="bio-label">7. Warga Negara</td>
                            <td class="bio-sep">:</td>
                            <td style="color: #1e293b;">{{ $siswa->warga_negara ?: 'WNI' }}</td>
                        </tr>
                        <tr>
                            <td class="bio-label">8. Agama</td>
                            <td class="bio-sep">:</td>
                            <td style="color: #1e293b;">{{ $siswa->agama ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="bio-label">9. Alamat Siswa</td>
                            <td class="bio-sep">:</td>
                            <td style="color: #1e293b;">{{ $siswa->alamat_siswa ?: '-' }}</td>
                        </tr>
                    </table>

                    <!-- 10. Data Orang Tua -->
                    <div class="sub-section">
                        <div class="sub-title">10. DATA ORANG TUA / WALI</div>
                        <div class="sub-item"><span class="k">Nama Orang Tua:</span> <span class="v">{{ $siswa->nama_orang_tua ?: '-' }}</span></div>
                        <div class="sub-item"><span class="k">a. Pekerjaan:</span> <span>{{ $siswa->pekerjaan_orang_tua ?: '-' }}</span></div>
                        <div class="sub-item"><span class="k">b. Alamat Rumah:</span> <span>{{ $siswa->alamat_orang_tua ?: '-' }}</span></div>
                    </div>

                    <!-- 11. Diterima Menjadi Siswa -->
                    <div class="sub-section">
                        <div class="sub-title">11. DITERIMA MENJADI SISWA</div>
                        <div class="sub-item"><span class="k">a. Mulai Tanggal:</span> <span class="v">{{ $formattedDates['diterima_tanggal'] }}</span></div>
                        <div class="sub-item"><span class="k">b. Asal Sekolah:</span> <span class="v">{{ $siswa->diterima_asal_sekolah ?: '-' }}</span></div>
                        <div class="sub-item"><span class="k">c. Nomor STTB/Ijazah:</span> <span>{{ $siswa->diterima_nomor_sttb ?: '-' }}</span></div>
                        <div class="sub-item"><span class="k">d. Tanggal STTB/Ijazah:</span> <span>{{ $formattedDates['diterima_tanggal_sttb'] }}</span></div>
                    </div>

                    <!-- 12. Meninggalkan Sekolah -->
                    <div class="sub-section">
                        <div class="sub-title">12. MENINGGALKAN SEKOLAH</div>
                        <div class="sub-item"><span class="k">a. Tanggal:</span> <span>{{ $formattedDates['keluar_tanggal'] }}</span></div>
                        <div class="sub-item"><span class="k">b. Alasan:</span> <span>{{ $siswa->keluar_alasan ?: '-' }}</span></div>
                    </div>

                    <!-- 13. Tamat Belajar / Lulus -->
                    <div class="sub-section">
                        <div class="sub-title">13. TAMAT BELAJAR / LULUS</div>
                        <div class="sub-item"><span class="k">a. Nomor STTB/Ijazah:</span> <span class="v">{{ $siswa->tamat_nomor_sttb ?: '-' }}</span></div>
                        <div class="sub-item"><span class="k">b. Tanggal STTB/Ijazah:</span> <span>{{ $formattedDates['tamat_tanggal_sttb'] }}</span></div>
                        <div class="sub-item"><span class="k">Keterangan Lain-Lain:</span> <span>{{ $siswa->keterangan_lain_lain ?: '-' }}</span></div>
                    </div>

                    <!-- Bingkai Pasfoto 3x4 -->
                    <div class="photo-wrap">
                        <div class="photo-frame">
                            @if($pasfotoBase64)
                                <img src="{{ $pasfotoBase64 }}" alt="Pasfoto" />
                            @else
                                <div class="photo-placeholder">
                                    PASPHOTO<br/>3 x 4 cm
                                </div>
                            @endif
                        </div>
                        <div class="photo-caption">Pasfoto Siswa 3x4</div>
                    </div>
                </div>
            </td>

            <!-- PANEL KANAN: MATRIKS NILAI SEMESTER I - X -->
            <td class="layout-right">
                <table class="matrix-table">
                    <thead>
                        <tr class="row-tp">
                            <th rowspan="2" class="col-no">NO</th>
                            <th rowspan="2" class="col-mapel">PROGRAM PENDIDIKAN DAN PELATIHAN</th>
                            @foreach($gradeYears as $tpYear)
                                <th colspan="2">{{ $tpYear }}</th>
                            @endforeach
                            <th rowspan="2" class="col-ket">KET</th>
                        </tr>
                        <tr class="row-sem">
                            @foreach(['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'] as $rom)
                                <th>{{ $rom }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <!-- SEKSI A: MATA PELAJARAN UMUM -->
                        <tr class="section-row">
                            <td>A</td>
                            <td colspan="12" class="text-left">KELOMPOK MATA PELAJARAN UMUM</td>
                        </tr>
                        @foreach($mapelUmum as $idx => $mapel)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td class="text-left">
                                    {{ $mapel->nama_mapel }}
                                    @if($mapel->kode_mapel === 'PAI')
                                        <span style="font-size: 6.5pt; color: #64748b; font-style: italic;">({{ $siswa->agama ?: 'Islam' }})</span>
                                    @endif
                                </td>
                                @for($sem = 1; $sem <= 10; $sem++)
                                    @php $val = $academicMap["{$mapel->id}_{$sem}"] ?? null; @endphp
                                    <td class="{{ $val !== null ? 'val-filled' : 'val-empty' }}">
                                        {{ $val !== null ? $val : '-' }}
                                    </td>
                                @endfor
                                <td class="val-empty">-</td>
                            </tr>
                        @endforeach

                        <!-- SEKSI B: MATA PELAJARAN KEJURUAN -->
                        <tr class="section-row">
                            <td>B</td>
                            <td colspan="12" class="text-left">KELOMPOK MATA PELAJARAN KEJURUAN</td>
                        </tr>
                        @foreach($mapelKejuruan as $idx => $mapel)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td class="text-left">{{ $mapel->nama_mapel }}</td>
                                @for($sem = 1; $sem <= 10; $sem++)
                                    @php $val = $academicMap["{$mapel->id}_{$sem}"] ?? null; @endphp
                                    <td class="{{ $val !== null ? 'val-filled' : 'val-empty' }}">
                                        {{ $val !== null ? $val : '-' }}
                                    </td>
                                @endfor
                                <td class="val-empty">-</td>
                            </tr>
                        @endforeach

                        <!-- SEKSI C: PKL & EKSTRAKURIKULER -->
                        <tr class="section-row">
                            <td>C</td>
                            <td colspan="12" class="text-left">PRAKTIK KERJA LAPANGAN (PKL) & KEGIATAN EKSTRA KURIKULER</td>
                        </tr>
                        <tr>
                            <td>1</td>
                            <td class="text-left" style="font-weight: bold;">Praktik Kerja Lapangan (PKL)</td>
                            @for($sem = 1; $sem <= 10; $sem++)
                                @php $pkl = $rekapMap[$sem]->nilai_pkl ?? null; @endphp
                                <td class="{{ $pkl ? 'val-filled' : 'val-empty' }}">{{ $pkl ?: '-' }}</td>
                            @endfor
                            <td class="val-empty">-</td>
                        </tr>
                        @foreach($ekskulList as $eIdx => $ekskul)
                            <tr>
                                <td>{{ $eIdx + 2 }}</td>
                                <td class="text-left">{{ $ekskul->nama_ekskul }}</td>
                                @for($sem = 1; $sem <= 10; $sem++)
                                    @php $eVal = $ekskulMap["{$ekskul->id}_{$sem}"] ?? null; @endphp
                                    <td class="{{ $eVal ? 'val-filled' : 'val-empty' }}">{{ $eVal ?: '-' }}</td>
                                @endfor
                                <td class="val-empty">-</td>
                            </tr>
                        @endforeach

                        <!-- REKAPITULASI BAWAH -->
                        <tr class="rekap-row-1">
                            <td colspan="2" class="text-left">JUMLAH NILAI</td>
                            @for($sem = 1; $sem <= 10; $sem++)
                                <td>{{ $rekapMap[$sem]->jumlah_nilai ?? '-' }}</td>
                            @endfor
                            <td class="val-empty">-</td>
                        </tr>
                        <tr class="rekap-row-2">
                            <td colspan="2" class="text-left">NILAI RATA-RATA</td>
                            @for($sem = 1; $sem <= 10; $sem++)
                                <td>{{ $rekapMap[$sem]->nilai_rata_rata ?? '-' }}</td>
                            @endfor
                            <td class="val-empty">-</td>
                        </tr>
                        <tr>
                            <td colspan="2" class="text-left" style="font-weight: bold;">RANKING</td>
                            @for($sem = 1; $sem <= 10; $sem++)
                                <td class="val-filled">{{ $rekapMap[$sem]->ranking ?? '-' }}</td>
                            @endfor
                            <td class="val-empty">-</td>
                        </tr>
                        <tr>
                            <td colspan="2" class="text-left">Ketidakhadiran: SAKIT (HARI)</td>
                            @for($sem = 1; $sem <= 10; $sem++)
                                <td>{{ $rekapMap[$sem]->sakit_hari ?? 0 }}</td>
                            @endfor
                            <td class="val-empty">-</td>
                        </tr>
                        <tr>
                            <td colspan="2" class="text-left">Ketidakhadiran: IZIN (HARI)</td>
                            @for($sem = 1; $sem <= 10; $sem++)
                                <td>{{ $rekapMap[$sem]->izin_hari ?? 0 }}</td>
                            @endfor
                            <td class="val-empty">-</td>
                        </tr>
                        <tr>
                            <td colspan="2" class="text-left">Ketidakhadiran: TANPA KABAR (HARI)</td>
                            @for($sem = 1; $sem <= 10; $sem++)
                                <td>{{ $rekapMap[$sem]->tanpa_kabar_hari ?? 0 }}</td>
                            @endfor
                            <td class="val-empty">-</td>
                        </tr>
                        <tr class="rekap-row-1">
                            <td colspan="2" class="text-left">JUMLAH KETIDAKHADIRAN (HARI)</td>
                            @for($sem = 1; $sem <= 10; $sem++)
                                <td>{{ $rekapMap[$sem]->jumlah_absen_hari ?? 0 }}</td>
                            @endfor
                            <td class="val-empty">-</td>
                        </tr>

                        <!-- STATUS KENAIKAN / KELULUSAN (Per 2 Semester) -->
                        <tr class="rekap-row-2">
                            <td colspan="2" class="text-left">STATUS KENAIKAN / KELULUSAN</td>
                            @foreach([2 => 'XI', 4 => 'XII', 6 => 'XIII', 8 => 'XIV', 10 => 'LULUS'] as $evenSem => $defaultTarget)
                                @php
                                    $rEven = $rekapMap[$evenSem] ?? null;
                                    $st = $rEven?->status_kenaikan;
                                @endphp
                                <td colspan="2">
                                    @if($st === 'LULUS')
                                        <span class="status-naik">LULUS TAMAT</span>
                                    @elseif($st === 'NAIK_TINGKAT')
                                        <span class="status-naik">NAIK KE {{ $rEven->tingkat_tujuan ?: $defaultTarget }}</span>
                                    @elseif($st === 'TIDAK_NAIK')
                                        <span class="status-tidak">TIDAK NAIK</span>
                                    @else
                                        <span class="val-empty">-</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="val-empty">-</td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
