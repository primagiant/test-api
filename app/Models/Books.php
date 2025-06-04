<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Books extends Model
{
    /** @use HasFactory<\Database\Factories\BooksFactory> */
    use HasFactory;

    protected $fillable = [
        'judul',
        'penulis',
        'status',
    ];

    public function transactions()
    {
        return $this->hasMany(Transactions::class, 'buku_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'transactions', 'buku_id', 'user_id')
            ->withPivot('tanggal_pinjam', 'tanggal_kembali')
            ->withTimestamps();
    }
}
