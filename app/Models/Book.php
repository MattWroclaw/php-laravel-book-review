<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

use function Laravel\Prompts\select;

class Book extends Model
{
    use HasFactory;

    public function revievs() {
        return $this->hasMany(Review::class);
    }  

    public function scopeTitle(Builder $query, string $title) : Builder {
        return $query->where('title', 'LIKE', '%'.$title.'%' );
    }

    // implemetujemy metode ktora zwwraca najpopluarniejsze ksiazki
    // Builder ma być z Illuminate\Database\Eloquent\Builder
    // :Builder oznacza ze metoda zwraca obiekt klasy Builder
    public function scopePopular(Builder $query, $from=null, $to=null) : Builder|QueryBuilder {
        return $query->withCount([
            'revievs' => fn(Builder $q) => $this->dateRangeFilter($q, $from, $to)
            ])
        ->orderBy('revievs_count', 'desc');
    }

    public function scopeHighestRated(Builder $query, $from=null, $to=null) :Builder {
        return $query->withAvg(
            [
                'revievs' => fn(Builder $q) => $this->dateRangeFilter($q, $from, $to)
                ]
            ,
            'rating')
        ->orderBy('revievs_avg_rating', 'desc');
    }


    public function scopeMinReviews(Builder $query, int $reviewCount): Builder | QueryBuilder{
        return $query->having('revievs_count', '>=', $reviewCount);
        // korzystamy z having a nie z where, bo having działa na wynikach agregatów
    }

    private function dateRangeFilter(Builder $query, $from=null, $to=null) {
        // funkcja nie musi niczego zwracać, bo modyfikuje obiekt Builder

        if($from && !$to){
            $query->where('created_at', '>=', $from);
        }elseif (!$from && $to) {
            $query->where('created_at', '<=' , $to);
        }elseif ($from && $to){
            $query->whereBetween('created_at', [$from, $to]);
        }
    }
}
