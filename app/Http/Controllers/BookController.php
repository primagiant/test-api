<?php

namespace App\Http\Controllers;

use App\Models\Books;
use App\Models\Transactions;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BookController extends Controller
{

    public function all()
    {
        try {
            $books = DB::transaction(function () {
                return Books::with(['transactions.user'])->get();
            });

            return response()->json(['data' => $books]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal mengambil data buku',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function simpan(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'penulis' => 'required|string|max:255',
        ]);
        $validated['status'] = false;
        $book = Books::create($validated);
        return response()->json(['message' => 'Buku berhasil ditambahkan', 'data' => $book], 201);
    }

    public function pinjam($id)
    {
        $userId = Auth::user()->id;
        $book = Books::find($id);

        if (!$book) {
            return response()->json(['message' => 'Buku tidak ditemukan'], 404);
        }

        if ($book->status === 'dipinjam') {
            return response()->json(['message' => 'Buku sedang dipinjam'], 400);
        }

        try {
            DB::beginTransaction();
            $book->status = 'dipinjam';
            $book->save();
            $transaction = Transactions::create([
                'buku_id' => $book->id,
                'user_id' => $userId,
                'tanggal_pinjam' => Carbon::now(),
                'tanggal_kembali' => null,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Buku berhasil dipinjam',
                'transaction' => $transaction
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['error' => 'Terjadi kesalahan: ' . $th->getMessage()], 500);
        }
    }

    public function kembalikan($id)
    {
        $userId = Auth::user()->id;
        $book = Books::find($id);

        if (!$book) {
            return response()->json(['message' => 'Buku tidak ditemukan'], 404);
        }

        if ($book->status === 'tersedia') {
            return response()->json(['message' => 'Buku belum dipinjam'], 400);
        }

        try {
            DB::beginTransaction();
            $book->status = 'tersedia';
            $book->save();
            $transaction = Transactions::where('buku_id', $book->id)
                ->where('user_id', $userId)
                ->whereNull('tanggal_kembali')
                ->latest('tanggal_pinjam')
                ->first();

            if (!$transaction) {
                return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
            }
            $transaction->tanggal_kembali = Carbon::now();
            $transaction->save();
            DB::commit();
            return response()->json([
                'message' => 'Buku berhasil dikembalikan',
                'transaction' => $transaction
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['error' => 'Terjadi kesalahan: ' . $th->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $book = Books::find($id);
        if (!$book) {
            return response()->json(['message' => 'Buku tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'judul' => 'sometimes|required|string|max:255',
            'penulis' => 'sometimes|required|string|max:255',
        ]);

        $book->update($validated);

        return response()->json(['message' => 'Buku berhasil diupdate', 'data' => $book]);
    }

    public function hapus($id)
    {
        $book = Books::find($id);
        if (!$book) {
            return response()->json(['message' => 'Buku tidak ditemukan'], 404);
        }
        $book->delete();
        return response()->json(['message' => 'Buku berhasil dihapus']);
    }
}
