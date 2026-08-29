<?php

namespace App\Http\Controllers;

use App\Services\TemplatePreviewService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Endpoint untuk render preview surat dari template DOCX aktif.
 *
 * GET /preview-surat?jenis=aktif_kuliah&nama_mahasiswa=...&nim=...&...
 *
 * Response: HTML lengkap siap ditampilkan di <iframe>.
 * Dapat diakses oleh semua role yang sudah login.
 */
class PreviewSuratController extends Controller
{
    private const JENIS_TERSEDIA = [
        'aktif_kuliah',
        'seminar_proposal',
        'sidang_skripsi',
        'undangan_penguji',
        'izin_magang',
        'rekomendasi_magang',
        'izin_penelitian',
    ];

    public function __invoke(Request $request, TemplatePreviewService $service): Response
    {
        $jenis = $request->query('jenis', 'aktif_kuliah');

        if (! in_array($jenis, self::JENIS_TERSEDIA, true)) {
            $jenis = 'aktif_kuliah';
        }

        // Semua query parameter selain 'jenis' diteruskan sebagai placeholder
        $data = collect($request->query())
            ->except('jenis')
            ->map(fn ($v) => is_string($v) ? strip_tags($v) : '')
            ->toArray();

        $html = $service->render($jenis, $data);

        return response($html, 200)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('X-Frame-Options', 'SAMEORIGIN')
            ->header('Cache-Control', 'no-store');
    }
}
