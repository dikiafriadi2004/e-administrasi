<?php

namespace App\Exports;

use App\Models\PengajuanSurat;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ArsipSuratExport implements FromQuery, WithColumnWidths, WithHeadings, WithMapping, WithStyles, WithTitle
{
    private const JENIS_LIST = [
        'aktif_kuliah' => 'Aktif Kuliah',
        'seminar_proposal' => 'Seminar Proposal',
        'sidang_skripsi' => 'Sidang Skripsi',
        'undangan_penguji' => 'Undangan Penguji',
    ];

    private const STATUS_LIST = [
        'diajukan' => 'Diajukan',
        'diverifikasi' => 'Diverifikasi',
        'menunggu_ttd' => 'Menunggu TTD',
        'sudah_ditandatangani' => 'Sudah Ditandatangani',
        'selesai' => 'Selesai',
        'ditolak' => 'Ditolak',
    ];

    public function __construct(
        private readonly ?string $jenis = null,
        private readonly ?string $status = null,
        private readonly ?string $q = null,
        private readonly ?string $dari = null,
        private readonly ?string $sampai = null,
    ) {}

    public function query(): Builder
    {
        $query = PengajuanSurat::with(['mahasiswa.user', 'pengajuanJudul.dosenPembimbing', 'dosenPenguji'])
            ->latest();

        if ($this->jenis) {
            $query->where('jenis_surat', $this->jenis);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->q) {
            $cari = $this->q;
            $query->where(function ($q) use ($cari) {
                $q->whereHas('mahasiswa.user', fn ($u) => $u->where('name', 'like', "%{$cari}%"))
                    ->orWhereHas('mahasiswa', fn ($m) => $m->where('nim', 'like', "%{$cari}%"))
                    ->orWhere('nomor_surat', 'like', "%{$cari}%");
            });
        }

        if ($this->dari && $this->sampai) {
            $query->whereBetween('created_at', [
                $this->dari.' 00:00:00',
                $this->sampai.' 23:59:59',
            ]);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'No.',
            'NIM',
            'Nama Mahasiswa',
            'Jenis Surat',
            'Nomor Surat',
            'Status',
            'Tanggal Pengajuan',
            'Tanggal Selesai',
            'Dosen Pembimbing',
            'Dosen Penguji',
            'Catatan Penolakan',
        ];
    }

    /** @param PengajuanSurat $row */
    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->mahasiswa?->nim ?? '-',
            $row->mahasiswa?->user?->name ?? '-',
            self::JENIS_LIST[$row->jenis_surat] ?? $row->jenis_surat,
            $row->nomor_surat ?? '-',
            self::STATUS_LIST[$row->status] ?? $row->status,
            $row->created_at?->format('d/m/Y'),
            $row->status === 'selesai' ? $row->updated_at?->format('d/m/Y') : '-',
            $row->pengajuanJudul?->dosenPembimbing?->nama ?? '-',
            $row->dosenPenguji?->nama ?? '-',
            $row->catatan_penolakan ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Header row bold
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 12,
            'C' => 28,
            'D' => 22,
            'E' => 28,
            'F' => 22,
            'G' => 16,
            'H' => 16,
            'I' => 28,
            'J' => 28,
            'K' => 40,
        ];
    }

    public function title(): string
    {
        return 'Arsip Surat';
    }
}
