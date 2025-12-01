<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pemilihan - {{ $pemilihan->nama_pemilihan }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #1f2937;
        }
        .header .subtitle {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: #6b7280;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
        }
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        .stat-item {
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 11px;
            color: #6b7280;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th {
            background-color: #f8fafc;
            border: 1px solid #e5e7eb;
            padding: 8px 12px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }
        .table td {
            border: 1px solid #e5e7eb;
            padding: 8px 12px;
            font-size: 11px;
        }
        .table tr.terpilih {
            background-color: #f0fdf4;
        }
        .peringkat {
            width: 40px;
            text-align: center;
            font-weight: bold;
        }
        .foto {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #e5e7eb;
        }
        .progress-bar {
            width: 100%;
            height: 8px;
            background-color: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background-color: #3b82f6;
        }
        .terpilih-badge {
            background-color: #10b981;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
        }
        .formatur-section {
            background-color: #f0fdf4;
            border: 1px solid #10b981;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .formatur-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            padding: 8px;
            background-color: white;
            border-radius: 5px;
            border: 1px solid #d1fae5;
        }
        .formatur-rank {
            width: 25px;
            height: 25px;
            background-color: #10b981;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 12px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>HASIL PEMILIHAN {{ strtoupper($pemilihan->nama_pemilihan) }}</h1>
        <p class="subtitle">Laporan Resmi Hasil Akhir Pemilihan</p>
        <p>Dicetak pada: {{ $tanggal_cetak }}</p>
    </div>

    <!-- Statistik Utama -->
    <div class="section">
        <div class="section-title">📊 STATISTIK PEMILIHAN</div>
        <div class="stat-grid">
            <div class="stat-item" style="background-color: #eff6ff;">
                <div class="stat-value">{{ $statistik['total_peserta'] }}</div>
                <div class="stat-label">TOTAL PESERTA</div>
            </div>
            <div class="stat-item" style="background-color: #f0fdf4;">
                <div class="stat-value">{{ $statistik['memilih'] }}</div>
                <div class="stat-label">SUDAH MEMILIH</div>
            </div>
            <div class="stat-item" style="background-color: #fef3c7;">
                <div class="stat-value">{{ $statistik['tidak_memilih'] }}</div>
                <div class="stat-label">TIDAK MEMILIH</div>
            </div>
            <div class="stat-item" style="background-color: #fef2f2;">
                <div class="stat-value">{{ $statistik['belum_memilih'] }}</div>
                <div class="stat-label">BELUM MEMILIH</div>
            </div>
            <div class="stat-item" style="background-color: #faf5ff; grid-column: span 2;">
                <div class="stat-value">{{ $statistik['persentase_partisipasi'] }}%</div>
                <div class="stat-label">TINGKAT PARTISIPASI</div>
            </div>
        </div>
    </div>

    <!-- Formatur Terpilih -->
    @if(count($calon_terpilih) > 0)
    <div class="section">
        <div class="section-title">🏆 FORMATUR TERPILIH</div>
        <div class="formatur-section">
            @foreach($calon_terpilih as $index => $item)
            <div class="formatur-item">
                <div class="formatur-rank">{{ $index + 1 }}</div>
                <div style="flex: 1;">
                    <strong>{{ $item['calon']->peserta->nama }}</strong>
                    <div style="font-size: 10px; color: #6b7280;">
                        {{ $item['calon']->peserta->asal_pimpinan }} • 
                        {{ $item['jumlah_suara'] }} suara ({{ number_format($item['persentase'], 1) }}%)
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Hasil Lengkap -->
    <div class="section">
        <div class="section-title">📈 HASIL LENGKAP PEMILIHAN</div>
        <table class="table">
            <thead>
                <tr>
                    <th class="peringkat">#</th>
                    <th>CALON</th>
                    <th>ASAL PIMPINAN</th>
                    <th>JUMLAH SUARA</th>
                    <th>PERSENTASE</th>
                    <th>STATUS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($hasil as $item)
                <tr class="{{ $item['status_terpilih'] ? 'terpilih' : '' }}">
                    <td class="peringkat">{{ $item['peringkat'] }}</td>
                    <td>
                        <strong>{{ $item['calon']->peserta->nama }}</strong>
                    </td>
                    <td>{{ $item['calon']->peserta->asal_pimpinan }}</td>
                    <td>{{ $item['jumlah_suara'] }}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span>{{ number_format($item['persentase'], 1) }}%</span>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: {{ $item['persentase'] }}%"></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($item['status_terpilih'])
                            <span class="terpilih-badge">TERPILIH</span>
                        @else
                            <span style="color: #6b7280;">Tidak Terpilih</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Summary -->
    <div class="section">
        <div style="background-color: #eff6ff; padding: 15px; border-radius: 8px; text-align: center;">
            <div style="font-size: 14px; font-weight: bold; color: #1e40af; margin-bottom: 5px;">
                TOTAL SUARA SAH: {{ $statistik['memilih'] }} SUARA
            </div>
            <div style="font-size: 12px; color: #4b5563;">
                Dari {{ $statistik['total_peserta'] }} peserta • Tingkat Partisipasi: {{ $statistik['persentase_partisipasi'] }}%
            </div>
            <div style="margin-top: 10px; font-size: 11px; color: #059669;">
                @if($statistik['kuorum_terpenuhi'])
                ✅ KUORUM TERPENUHI (Minimal: {{ $pemilihan->minimal_kehadiran }} kehadiran)
                @else
                ❌ KUORUM BELUM TERPENUHI (Minimal: {{ $pemilihan->minimal_kehadiran }} kehadiran)
                @endif
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Dokumen ini dicetak secara otomatis dari Sistem Pemilihan • Hak Cipta © {{ date('Y') }}</p>
    </div>
</body>
</html>