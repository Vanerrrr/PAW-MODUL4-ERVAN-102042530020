<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Menampilkan halaman katalog buku dengan fitur pencarian, filter, sorting, dan paginasi.
     */
    public function index(Request $request)
    {
        // TODO 3: Lengkapi logika index():
        // 1. Buat query pencarian berdasarkan judul, penulis, atau penerbit jika ada input 'search'
        // 2. Tambahkan filter penerbit dan filter ketersediaan stok jika ada input 'penerbit' / 'status'
        // 3. Tambahkan pengurutan data berdasarkan input 'sort' (judul, tahun, stok, default: latest)
        // 4. Lakukan paginasi 8 data dengan withQueryString()
        // 5. Ambil list penerbit unik untuk opsi dropdown filter
        // 6. Return view 'home' dengan data buku dan list penerbit

        // 1 & 2. Pencarian + filter penerbit + filter stok
        $buku = Buku::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->input('search');
                $q->where(function ($w) use ($s) {
                    $w->where('judul', 'like', "%{$s}%")
                      ->orWhere('penulis', 'like', "%{$s}%")
                      ->orWhere('penerbit', 'like', "%{$s}%");
                });
            })
            ->when($request->filled('penerbit'), fn ($q) => $q->where('penerbit', $request->input('penerbit')))
            ->when($request->input('status') === 'tersedia', fn ($q) => $q->where('stok', '>', 0))
            ->when($request->input('status') === 'habis', fn ($q) => $q->where('stok', '<=', 0));

        // 3. Pengurutan
        switch ($request->input('sort')) {
            case 'judul_asc':
                $buku->orderBy('judul', 'asc');
                break;
            case 'judul_desc':
                $buku->orderBy('judul', 'desc');
                break;
            case 'tahun_desc':
                $buku->orderBy('tahun_terbit', 'desc');
                break;
            case 'tahun_asc':
                $buku->orderBy('tahun_terbit', 'asc');
                break;
            case 'stok_desc':
                $buku->orderBy('stok', 'desc');
                break;
            default:
                $buku->latest();
                break;
        }

        // 4. Paginasi 8 data
        $buku = $buku->paginate(8)->withQueryString();

        // 5. Daftar penerbit unik
        $listPenerbit = Buku::select('penerbit')->distinct()->orderBy('penerbit', 'asc')->pluck('penerbit');

        // 6. Kirim ke view
        return view('home', compact('buku', 'listPenerbit'));
    }

    /**
     * Menampilkan halaman detail informasi sebuah buku untuk publik/mahasiswa.
     */
    public function show(Buku $buku)
    {
        // TODO 3: Kembalikan view 'home_show' membawa data $buku
        return view('home_show', compact('buku'));
    }
}