<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request   $request)
    {
        $title = $request->input('title');
        // jest taka metoda w Book.php (modelu) ktora zwraca obiekt klasy Builder
        // scopeTitle
        /*
        $books = Book::when($title, function ($query, $title) {
            return $query->title($title);
        })->get();
*/
        // to samo za pomocą arrow function
        $books = Book::when($title, 
        fn($query, $title) => $query->title($title));
    // get() nie jest wywoływane, bo chcemy zmienić warunki zapytania
        $filter = $request->input('filter123', '');
        // input -> pobiera dane z formularza
        // 'filter' -> nazwa pola w formularzu

        
        $books = match($filter){
            'popular_last_month' => $books->popularLastMonth(),
            'popular_last_6months' => $books->popularLast6Months(),
            'highest_rated_last_month' => $books->highestRatedLastMonth(),
            'highest_rated_last_6months' => $books->popularLast6Months(),
            default => $books->latest()->withAvgRating()->withReviewsCount()
        };
        
        $books = $books->get();

        // dd($books);
        // dump($filter);
        // error_log("Mateusz filter-->" . $filter);
        // error_log("Mateusz2 books -->" . $books);

        return view('books.index', ['books' => $books]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     * To jest do obsługi formularza dodawania nowej ksiazki (resource)
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book) 
    {
        // to ma lepszy perfomance ale cośtam nie działa i przy widoku Book wyświetla Reviews w różnej kolejności
        // return view('books.show', ['book' => $book]);

        return view(
            'books.show',
            [
                'book' => $book->load([
                        'revievs' => fn($query) => $query->latest()
                    ])
            ]
        );
        

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     * Do obsługi formularza edycji ksiazki (resource)
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
