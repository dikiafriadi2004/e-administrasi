<?php

namespace App\Services;

use App\Exceptions\InvalidStateTransitionException;
use App\Models\PengajuanJudul;
use App\Models\PengajuanSurat;
use App\Models\StatusHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * State machine aplikasi e-administrasi.
 *
 * PENGAJUAN AKADEMIK (judul / seminar / sidang):
 *   diajukan → disetujui (Kaprodi tetapkan pembimbing/penguji)
 *            → ditolak (Kaprodi)
 *
 * SURAT AKTIF KULIAH (dan surat lain dari mahasiswa):
 *   diajukan → menunggu_ttd  (Admin generate surat)
 *           → sudah_ditandatangani (Admin upload scan setelah Kaprodi TTD)
 *           → selesai (Admin tandai selesai)
 *           → ditolak (Admin)
 */
class PengajuanStateService
{
    // ─── Pengajuan Akademik ───────────────────────────────────────────────────

    /**
     * Kaprodi setujui judul: diajukan → disetujui (wajib ada pembimbing)
     */
    public function setujuiJudul(PengajuanJudul $judul, User $actor): void
    {
        if ($judul->status !== 'diajukan') {
            throw InvalidStateTransitionException::dariKe($judul->status, 'setujui_judul');
        }

        if (! $judul->dosen_pembimbing_id) {
            throw new \DomainException('Dosen pembimbing harus dipilih sebelum menyetujui judul.');
        }

        $judul->update(['status' => 'disetujui']);

        Cache::forget('rasio_dosen');
        Cache::forget('top_dosen_tersedia');

        $this->recordHistory($judul, 'diajukan', 'disetujui', $actor,
            'Judul disetujui dan dosen pembimbing ditetapkan oleh Kaprodi.');
    }

    /**
     * Kaprodi setujui seminar/sidang (PengajuanSurat): diajukan → disetujui
     * Untuk seminar_proposal dan sidang_skripsi, penguji 1 wajib sudah dipilih.
     */
    public function setujuiPengajuanAkademik(PengajuanSurat $surat, User $actor): void
    {
        if ($surat->status !== 'diajukan') {
            throw InvalidStateTransitionException::dariKe($surat->status, 'setujui_akademik');
        }

        if (in_array($surat->jenis_surat, ['seminar_proposal', 'sidang_skripsi'])) {
            if (! $surat->dosen_penguji_id) {
                throw new \DomainException('Dosen penguji 1 harus dipilih sebelum menyetujui pengajuan ini.');
            }
            if (! $surat->dosen_penguji_2_id) {
                throw new \DomainException('Dosen penguji 2 harus dipilih sebelum menyetujui pengajuan ini.');
            }
        }

        $surat->update(['status' => 'disetujui']);

        Cache::forget('rasio_dosen');
        Cache::forget('top_dosen_tersedia');

        $catatan = match ($surat->jenis_surat) {
            'seminar_proposal' => 'Pengajuan seminar proposal disetujui oleh Kaprodi.',
            'sidang_skripsi' => 'Pengajuan sidang skripsi disetujui dan penguji ditetapkan oleh Kaprodi.',
            default => 'Pengajuan disetujui oleh Kaprodi.',
        };

        $this->recordHistory($surat, 'diajukan', 'disetujui', $actor, $catatan);
    }

    // ─── Surat (Aktif Kuliah, Undangan, dll) ─────────────────────────────────

    /**
     * Admin verifikasi berkas sidang skripsi: diajukan → diajukan (status tetap)
     * tapi catatan_admin diisi dan berkas_diverifikasi = true.
     * Jika berkas OK, admin teruskan ke Kaprodi.
     */
    public function verifikasiBerkas(PengajuanSurat $surat, User $actor, string $catatan, bool $lulus): void
    {
        abort_unless($surat->jenis_surat === 'sidang_skripsi', 422, 'Hanya untuk sidang skripsi.');

        if (! $lulus) {
            // Berkas kurang — kembalikan ke mahasiswa dengan catatan
            $surat->update([
                'catatan_admin' => $catatan,
                'berkas_diverifikasi' => false,
            ]);

            $this->recordHistory($surat, $surat->status, $surat->status, $actor,
                "Berkas dikembalikan: {$catatan}");

            return;
        }

        // Berkas OK — tandai terverifikasi, status tetap diajukan (menunggu Kaprodi)
        $surat->update([
            'catatan_admin' => null,
            'berkas_diverifikasi' => true,
        ]);

        $this->recordHistory($surat, $surat->status, $surat->status, $actor,
            'Berkas sidang diverifikasi admin dan dinyatakan lengkap. Menunggu persetujuan Kaprodi.');
    }

    /**
     * Admin generate surat: diajukan → menunggu_ttd
     * Dipanggil setelah generate DOCX+PDF berhasil.
     */
    public function generateSurat(PengajuanSurat $surat, User $actor): void
    {
        if ($surat->status !== 'diajukan') {
            throw InvalidStateTransitionException::dariKe($surat->status, 'generate_surat');
        }

        $surat->update(['status' => 'menunggu_ttd']);

        $this->recordHistory($surat, 'diajukan', 'menunggu_ttd', $actor,
            'Surat digenerate oleh admin, menunggu tanda tangan Kaprodi.');
    }

    /**
     * Upload scan: menunggu_ttd → sudah_ditandatangani
     */
    public function uploadScan(PengajuanSurat $surat, User $actor, string $scanPath): void
    {
        if ($surat->status !== 'menunggu_ttd') {
            throw InvalidStateTransitionException::dariKe($surat->status, 'upload_scan');
        }

        $surat->update([
            'file_scan' => $scanPath,
            'status' => 'sudah_ditandatangani',
        ]);

        $this->recordHistory($surat, 'menunggu_ttd', 'sudah_ditandatangani', $actor,
            'Scan surat yang sudah ditandatangani berhasil diupload.');
    }

    /**
     * Tandai selesai: sudah_ditandatangani → selesai
     */
    public function selesaikan(PengajuanSurat $surat, User $actor): void
    {
        if ($surat->status !== 'sudah_ditandatangani') {
            throw InvalidStateTransitionException::dariKe($surat->status, 'selesaikan');
        }

        $surat->update(['status' => 'selesai']);

        $this->recordHistory($surat, 'sudah_ditandatangani', 'selesai', $actor,
            'Pengajuan selesai. Mahasiswa dapat mengunduh surat.');
    }

    // ─── Bersama (akademik & surat) ───────────────────────────────────────────

    /**
     * Tolak pengajuan — bisa dipanggil di status apapun sebelum selesai.
     */
    public function tolak(Model $pengajuan, User $actor, string $catatan): void
    {
        if ($pengajuan->status === 'selesai') {
            throw InvalidStateTransitionException::dariKe($pengajuan->status, 'tolak');
        }

        $statusLama = $pengajuan->status;
        $pengajuan->update([
            'status' => 'ditolak',
            'catatan_penolakan' => $catatan,
        ]);

        $this->recordHistory($pengajuan, $statusLama, 'ditolak', $actor, "Ditolak: {$catatan}");
    }

    // ─── Helper ───────────────────────────────────────────────────────────────

    private function recordHistory(
        Model $pengajuan,
        string $statusLama,
        string $statusBaru,
        User $actor,
        ?string $catatan = null
    ): void {
        StatusHistory::create([
            'model_type' => get_class($pengajuan),
            'model_id' => $pengajuan->id,
            'status_lama' => $statusLama,
            'status_baru' => $statusBaru,
            'catatan' => $catatan,
            'changed_by' => $actor->id,
            'created_at' => now(),
        ]);
    }
}
