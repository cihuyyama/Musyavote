<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Peserta - {{ $total_peserta }} Peserta</title>

    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 20px;
        }

        .page-break {
            page-break-after: always;
        }

        .kartu-peserta-container {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .kartu-peserta {
            width: 25%;
            display: inline-block;
            vertical-align: top;
            margin-right: 1.5%;
            margin-bottom: 15px;
            padding: 15px;
            text-align: center;
            page-break-inside: avoid;
            border: 1px solid #e2e8f0; /* Border ditambahkan */
            border-radius: 8px;
            background-color: #fff;
        }

        /* hilangkan margin kanan untuk kolom terakhir */
        .kartu-peserta:nth-child(3n) {
            margin-right: 0;
        }

        .qrcode-img {
            width: 150px;
            height: 150px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
        }

        .nama-peserta {
            margin-top: 10px;
            font-size: 14px;
            font-weight: bold;
            color: #1e293b;
        }

        .kode-unik {
            display: inline-block;
            margin-top: 8px;
            background: #f1f5f9; /* Warna biru diubah menjadi abu-abu muda */
            color: #475569; /* Warna teks diubah menjadi abu-abu gelap */
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            border: 1px solid #e2e8f0; /* Border untuk kode unik */
        }

        /* Header */
        .document-header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0; /* Warna biru diubah menjadi abu-abu */
        }

        .document-title {
            font-size: 18px;
            font-weight: bold;
            color: #334155; /* Warna biru diubah menjadi abu-abu gelap */
        }

        .document-subtitle {
            font-size: 13px;
            color: #64748b; /* Warna sedikit lebih terang */
            margin-top: 5px;
        }

        .password {
            margin-top: 8px;
            font-size: 12px;
            color: #475569;
            background: #f8fafc;
            padding: 4px 8px;
            border-radius: 4px;
            border: 1px dashed #e2e8f0;
            display: inline-block;
        }

        @media print {
            .kartu-peserta {
                width: 25%;
                border: 1px solid #d1d5db; /* Border lebih jelas untuk print */
            }
            
            .kartu-peserta:nth-child(3n) {
                margin-right: 0;
            }
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <div class="document-header">
        <div class="document-title">KARTU PESERTA PEMILIHAN</div>
        <div class="document-subtitle">
            Total: {{ $total_peserta }} Peserta • Dicetak: {{ $tanggal_cetak }}
        </div>
    </div>


    <!-- LIST KARTU PESERTA -->
    <div class="kartu-peserta-container">
        @foreach ($pesertas as $index => $peserta)
            <div class="kartu-peserta">

                <!-- NAMA -->
                <div class="nama-peserta">{{ $peserta->nama }}</div>

                <!-- KODE UNIK -->
                <div class="kode-unik">ID: {{ $peserta->kode_unik }}</div>

                <!-- PASSWORD -->
                <div class="password">{{ $peserta->password_plain }}</div>
            </div>

            <!-- PAGE BREAK SETIAP 18 KARTU (3 x 6 BARIS) -->
            @if (($index + 1) % 21 == 0)
                <div class="page-break"></div>
            @endif
        @endforeach
    </div>



    @if ($total_peserta == 0)
        <div style="text-align:center; margin-top:50px; color:#64748b;">
            <h3>Tidak ada data peserta</h3>
        </div>
    @endif

</body>

</html>