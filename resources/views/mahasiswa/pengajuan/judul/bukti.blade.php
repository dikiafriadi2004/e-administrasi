<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Persetujuan Judul — {{ $user->name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            color: #000;
            background: #fff;
            padding: 30px 40px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 12px;
            margin-bottom: 24px;
        }
        .header h1 { font-size: 14pt; font-weight: bold; text-transform: uppercase; margin-bottom: 4px; }
        .header h2 { font-size: 11pt; font-weight: normal; }
        .title {
            text-align: center;
            margin-bottom: 24px;
        }
        .title h3 {
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
        }
        table.info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        table.info td {
            padding: 4px 8px;
            vertical-align: top;
            font-size: 11pt;
        }
        table.info td:first-child {
            width: 180px;
            font-weight: bold;
        }
        table.info td:nth-child(2) { width: 16px; }
        .status-badge {
            display: inline-block;
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
            border-radius: 20px;
            padding: 3px 14px;
            font-size: 10pt;
            font-weight: bold;
        }
        .note {
            border: 1px solid #aaa;
            border-radius: 6px;
            padding: 12px 16px;
            font-size: 10pt;
            color: #555;
            margin-bottom: 24px;
            background: #fafafa;
        }
        .ttd-area {
            margin-top: 40px;
            display: flex;
            justify-content: flex-end;
        }
        .ttd-box {
            text-align: center;
            width: 260px;
        }
        .ttd-box .space { height: 60px; }
        .ttd-box .name { font-weight: bold; border-top: 1px solid #000; padding-top: 4px; }
        @media print {
            body { padding: 20px 30px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    {{-- Tombol Print (hilang saat print) --}}
    <div class="no-print" style="text-align:right; margin-bottom:16px;">
        <button onclick="window.print()"
                style="background:#059669;color:white;padding:8px 20px;border:none;border-radius:8px;font-size:13px;cursor:pointer;font-family:sans-serif;">
            🖨️ Cetak / Print
        </button>
        <button onclick="window.close()"
                style="background:#6b7280;color:white;padding:8px 20px;border:none;border-radius:8px;font-size:13px;cursor:pointer;font-family:sans-serif;margin-left:8px;">
            Tutup
        </button>
    </div>

    {{-- Kop --}}
    <div class="header">
        <h1>Program Studi {{ \App\Models\Pengaturan::nilai('nama_prodi', '—') }}</h1>
        <h2>{{ \App\Models\Pengaturan::nilai('nama_fakultas', '') }}
            @if(\App\Models\Pengaturan::nilai('nama_universitas')) — {{ \App\Models\Pengaturan::nilai('nama_universitas') }} @endif
        </h2>
    </div>

    {{-- Judul Dokumen --}}
    <div class="title">
        <h3>Bukti Persetujuan Judul Skripsi</h3>
    </div>

    {{-- Data --}}
    <table class="info">
        <tr>
            <td>Nama Mahasiswa</td><td>:</td>
            <td>{{ $user->name }}</td>
        </tr>
        <tr>
            <td>NIM</td><td>:</td>
            <td>{{ $mahasiswa->nim }}</td>
        </tr>
        <tr>
            <td>Angkatan</td><td>:</td>
            <td>{{ $mahasiswa->angkatan ?? '—' }}</td>
        </tr>
        <tr>
            <td>Judul Skripsi</td><td>:</td>
            <td><strong>{{ $pengajuanJudul->judul }}</strong></td>
        </tr>
        <tr>
            <td>Bidang Kajian</td><td>:</td>
            <td>{{ $pengajuanJudul->bidang_kajian ?? '—' }}</td>
        </tr>
        <tr>
            <td>Dosen Pembimbing</td><td>:</td>
            <td>{{ $pembimbing?->nama ?? '—' }}</td>
        </tr>
        <tr>
            <td>Tanggal Persetujuan</td><td>:</td>
            <td>{{ $tanggal }}</td>
        </tr>
        <tr>
            <td>Status</td><td>:</td>
            <td><span class="status-badge">✓ Disetujui Kaprodi</span></td>
        </tr>
    </table>

    <div class="note">
        <strong>Catatan:</strong> Dokumen ini merupakan bukti bahwa judul skripsi mahasiswa tersebut di atas
        telah disetujui oleh Kepala Program Studi dan dosen pembimbing telah ditetapkan.
        Mahasiswa diharapkan menyerahkan dokumen ini kepada dosen pembimbing sebagai tanda dimulainya
        proses bimbingan skripsi.
    </div>

    {{-- TTD --}}
    <div class="ttd-area">
        <div class="ttd-box">
            <p>{{ \App\Models\Pengaturan::nilai('kota_prodi', '—') }}, {{ $tanggal }}</p>
            <p>Kepala Program Studi,</p>
            <div class="space"></div>
            <p class="name">{{ \App\Models\Pengaturan::nilai('nama_kaprodi', '—') }}</p>
            <p style="font-size:10pt;">NIP. {{ \App\Models\Pengaturan::nilai('nip_kaprodi', '—') }}</p>
        </div>
    </div>

</body>
</html>
