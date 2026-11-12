<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamMode;
use App\Models\Soal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class FiguralController extends Controller
{
    /**
     * Tampilkan halaman manajemen soal figural (TIU)
     */
    public function index(Request $request)
    {
        // Filter hanya soal berjenis Figural / Visual
        $baseQuery = Soal::where('kategori', 'TIU');

        if (Schema::hasColumn('soals', 'exam_mode_id') && Schema::hasTable('exam_modes')) {
            $baseQuery->where(function ($q) {
                $q->whereNull('exam_mode_id')
                  ->orWhereHas('examMode', function ($sub) {
                      $sub->where('has_figural', true);
                  });
            });
        }

        $baseQuery->where(function ($q) {
                $q->where('sub_topik', 'LIKE', '%Figural%')
                  ->orWhere('sub_topik', 'LIKE', '%Spasial%')
                  ->orWhere('sub_topik', 'LIKE', '%Gambar%')
                  ->orWhere('sub_topik', 'LIKE', '%Visual%')
                  ->orWhere('sub_topik', 'LIKE', '%Pola%')
                  ->orWhereNotNull('gambar_path');
            });

        // Statistik
        $totalTiu = (clone $baseQuery)->count();
        // Terisi gambar jika gambar_path NOT NULL
        $totalGambar = (clone $baseQuery)->whereNotNull('gambar_path')->count();
        $totalTanpaGambar = max(0, $totalTiu - $totalGambar);

        // Filter pencarian opsional
        $query = (clone $baseQuery)->orderBy('created_at', 'desc');

        if ($request->has('filter')) {
            if ($request->filter === 'ada_gambar') {
                $query->whereNotNull('gambar_path');
            } elseif ($request->filter === 'tanpa_gambar') {
                $query->whereNull('gambar_path');
            }
        }

        $soals = $query->paginate(20);
        $figuralModes = Schema::hasTable('exam_modes')
            ? ExamMode::query()->active()->where('has_figural', true)->orderBy('name')->get(['id', 'name', 'code'])
            : collect();

        return view('admin.figural_soal', compact('soals', 'totalTiu', 'totalGambar', 'totalTanpaGambar', 'figuralModes'));
    }

    /**
     * Upload / Update gambar pada soal beserta opsi A-E
     */
    public function upload(Request $request, $id)
    {
        $fields = ['gambar', 'gambar_a', 'gambar_b', 'gambar_c', 'gambar_d', 'gambar_e'];
        $rules = [];
        foreach ($fields as $f) {
            $rules[$f] = 'nullable|image|mimes:jpeg,png,jpg|max:2048';
        }
        
        $request->validate($rules, [
            'image' => 'File harus berupa gambar terkecuali dibiarkan kosong.',
            'mimes' => 'Format gambar harus jpeg, png, atau jpg.',
            'max'   => 'Ukuran gambar maksimal 2MB per file.',
        ]);

        $soal = Soal::findOrFail($id);
        $updatedData = [];
        $hasUploads = false;

        foreach ($fields as $field) {
            if ($request->hasFile($field)) {
                $dbField = $field === 'gambar' ? 'gambar_path' : $field;
                
                // Hapus gambar lama jika ada
                if ($soal->$dbField && Storage::disk('public')->exists($soal->$dbField)) {
                    Storage::disk('public')->delete($soal->$dbField);
                }

                // Simpan gambar baru
                $updatedData[$dbField] = $request->file($field)->store('soal_gambar', 'public');
                $hasUploads = true;
            }
        }

        if ($hasUploads) {
            $soal->update($updatedData);
            return back()->with('success', 'Gambar terbaru berhasil diupload.');
        }

        return back()->with('error', 'Tidak ada file gambar yang diupload.');
    }

    /**
     * Hapus semua gambar dari soal
     */
    public function destroyGambar($id)
    {
        $soal = Soal::findOrFail($id);
        $fields = ['gambar_path', 'gambar_a', 'gambar_b', 'gambar_c', 'gambar_d', 'gambar_e'];

        foreach ($fields as $f) {
            if ($soal->$f && Storage::disk('public')->exists($soal->$f)) {
                Storage::disk('public')->delete($soal->$f);
            }
        }

        $soal->update([
            'gambar_path' => null,
            'gambar_a' => null,
            'gambar_b' => null,
            'gambar_c' => null,
            'gambar_d' => null,
            'gambar_e' => null,
        ]);

        return back()->with('success', 'Semua gambar berhasil dihapus dari soal ini.');
    }
}
