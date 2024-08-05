1. #### Komenda do stworzenia projektu: ` composer create-project --prefer-dist laravel/laravel book-review`
2. Jeszcze nie włączyliśmy servera. Tworzymy Model: Book + Review
    1. `php artisan make:model Book -m`
    2. `php artisan make:model Review -v`
    3. To nam dodało się do ./database/migrations/ ale jeszcze nie ma w DB. W `./database/migrations/` są metody `up` i `down` i one robią populację/rollback w DB. Tam dodajemy pola do Modeli
    4. Włączamy dockera z DB. Aby stworzyć tabelę robimy `php artisan migrate`. To zaciąga wszystkie (nowe chyba) migrations i z nich robi tabele. 

 Mamy taki output:
 > php artisan migrate
>
 >  WARN  The database 'laravel-10-book-review' does not exist on the 'mysql' connection.  
>
>  Would you like to create it? (yes/no) [yes]
> ❯ yes
>
>   INFO  Preparing database.  
>
>  Creating migration >table ...................................................................................>........................ 126.18ms DONE
>
>   INFO  Running migrations.  
>
>  0001_01_01_000000_create_users_table ....................................................>........................................... 183.44ms DONE
>  0001_01_01_000001_create_cache_table ................................................................................................ 30.15ms DONE
>  0001_01_01_000002_create_jobs_table ................................................................................................ 116.60ms DONE
>  2024_07_31_095451_create_books_table ................................................................................................ 16.56ms DONE
>  2024_07_31_095540_create_reviews_table .............................................................................................. 14.20ms DONE
>
    5. Teraz scheam oraz table są w DB
3. #### *Reationship*
 One Book: many Rels. Book -> Parent_table; Review -> Child_table; W ./database/migrations w  Review tworzymy kolumnę z ForeignKey `$table->unsignedBigInteger('book_id');` Aby stworzyć relację robimy coś takiego w migration dla Review `$table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');` Po tym robimy update do DB: `php artisan migrate:reg=fresh` . Ale ralavel jeszcze nie wie że book+review są w relacji.
  * 1. Dodanie relacji po stonie Book: w app/Models dodajemy relację w funkcji reviews(), a po stronie Reviesws metodę `book()`.
  * 2. *Uwaga* Kolumna z foreign key w Review `book_id` jest w/g laravel convention, one-to-many. Model ma liczbę pojedynczą i automatycznie doda `_id`
4. Napełnianie DB przykładowymi obiektami Book+Review. `php artisan make:factory BookFactory --model=Book` . 
  `php artisan make:factory ReviewFactory --model=Review`. W tych factorkach dodajemy `definition` dla tych 2 modeli. 
    1. W `./data/seeders/DataBaseSeeder.php` usuwamy pozostałości po Userze. Chcemy mieć X książek i żeby każda miała a-c reviews. Implementujemy w BookFactory i w ReviewFactory jak chcemy mieć pola a w DatabaseSeeder implementujemy funkcję `run` która nam stworzy daną ilość obiektów. 
    2. W cmd `php artisan migrate:refresh --seed` 
5. #### *Tinker* 
`php arisan tinker`, Gdy tinker nie działa: `php artisan serve`  -> `.\routes\web.php` -> usuwamy ten boilerplate code z metody `Route::get` i wstawiamy `dd()` To zatrzymuje wykonanie i pokazuje rezultat (??) Pewnie coś a'la debug.
  * 1. Chcemy pokazać Book + wszystkie Review z nią związane. `$book = \App\Models\Book::find(1);` 
  > *Uwaga!* Zauważ, że nazwa przestrzeni nazw App powinna być pisana z wielkiej litery. 

To pokaże tylko samego Book, bez review. 
   * 1. 1. Aby pokazać Review:  `Lazy loading` : `$reviews = $book->revievs;`  ( to `revievs` jest z błędem, ale to dobrze bo wiadomo skąd to jest, a mianowicie to jest nazwa metody w `class Book extends Model` To jest nazwa relationship, w tej metodzie w tinkerze używa się tego jako property. `$reviews = $book->revievs;` -> query all related reviews. 
   * 1. 2. Book + review : `\App\Models\Book::with('revievs')->find(1);` Jak poprzednio `revievs` to jest nazwa relationship która jest fukcją w klasie `class Book extends Model`. 
  * 1. 3. Pokaż 3 Books wraz z Reviews: `$books = \App\Models\Book::with('revievs')->take(3)->get();` 
  2. `Lazy loading` przyniesie dane, tylko gdy jest są potrzebne. To jest OK dla małej liczby recordów, gdy nie musimy się przejmować prefromance. 
  3. `Eager loading` chcemy mieć wszystkie dane za jednym query. Gdy mamy do czynienia  z dużą ilością danych. To minimalizyje ilość zapytań do DB. 
  4. Chcemy Book z id=2 : `$book = \App\Models\Book::find(2);`. Book ma metodę `load` do której można przekazać argument z relationship (jak jest więcej to odzielone przecinkiem albo jako lista) : ` $book->load('revievs');`
>  5. Dodanie nowego Review do Book. Wybieramy sobie book `$book = \App\Models\Book::find(1);` . Tworzymy nowy Review i nadajemy mu wartości w kilku polach:
> $review = new \App\Models\Review();                                                                                                              
> = App\Models\Review {#5796}
>
> $review->review = 'The review from tinker';                                                                                                      
> = "The review from tinker"
>
> $review->rating = 3;                                                                                                                             
> = 3
>
>   
 ale to jeszcze nie jest zapisane do DB. Nie ma nawet wymaganego book_id.   Można to zrobić z pomocą `$review->book_id = 1; $review->save();` 
Lepiej to zrobić tak:

`$book->revievs()->save($review);`             
>  = App\Models\Review {#5796
>     review: "The review from tinker",
>     rating: 3,
>     book_id: 1,
>     updated_at: "2024-08-01 08:02:41",
>     created_at: "2024-08-01 08:02:41",
>     id: 1792,
>   }

Sprawdzenie czy jest ten Review: `$book->revievs;`

**Uwaga:**
*facet po każdej komendzie robi restart tinker'a. To jest po to, że jak się zmieni coś w kodzie to tinker na nowo wszystko zaciąga. Nie widzi bieżących zmian, jak CMD..

5. 6. PreWork: Wchodzimy do Review.php i tworzymy `$fillable` czyli będzie można je zrobić `Mass assigned`. (trzeba restart tinker), następnie `$book=\App\Models\Book::find(2);` 
> = App\Models\Book {#5785
    id: 2,
    title: "Minus qui quis optio.",
    author: "Ansley Reynolds MD",
    created_at: "2024-05-21 19:54:33",
    updated_at: "2024-07-14 10:12:53",
  }

Następnie: `$review = $book->revievs()->create(['review'=>'Sample review', 'rating'=>5]);`

> $review = $book->revievs()->create(['review'=>'Sample review', 'rating'=>5]);                                                                    
= App\Models\Review {#5040
    review: "Sample review",
    rating: 5,
    book_id: 2,
    updated_at: "2024-08-01 08:21:54",
    created_at: "2024-08-01 08:21:54",
    id: 1793,
  }


5. 7. Mmay Review, a chcemy zobaczyć też  obiekt Book. 
> `$review = \App\Models\Review::find(1);`                                                                                                          
= App\Models\Review {#6018
    id: 1,
    book_id: 1,
    review: "Unde in inventore eligendi in ipsam. Placeat sed non voluptatum corporis. Similique ut maiores et ullam aut labore accusantium. Voluptates nesciunt ipsam itaque omnis voluptas rerum. Aspernatur aut velit deserunt aut ipsa sint ducimus.",
    rating: 4,
    created_at: "2022-11-06 17:56:18",
    updated_at: "2023-06-15 20:57:52",
  }

> `$review->book;   `                                                                                                                                
= App\Models\Book {#6131
    id: 1,
    title: "Earum ipsum incidunt soluta.",
    author: "Anjali Corwin",
    created_at: "2022-11-11 05:57:32",
    updated_at: "2023-03-06 17:56:31",
  }
5. 8. Przeniesienie Review z jednego Book do drugiego.  
> $review = \App\Models\Review::find(1);                                                                                                           
= App\Models\Review {#5787
    id: 1,
    book_id: 1,
    review: "Unde in inventore eligendi in ipsam. Placeat sed non voluptatum corporis. Similique ut maiores et ullam aut labore accusantium. Voluptates nesciunt ipsam itaque omnis voluptas rerum. Aspernatur aut velit deserunt aut ipsa sint ducimus.",
    rating: 4,
    created_at: "2022-11-06 17:56:18",
    updated_at: "2023-06-15 20:57:52",
  }

> $book2 = \App\Models\Book::find(2);                                                                                                              
= App\Models\Book {#5047
    id: 2,
    title: "Minus qui quis optio.",
    author: "Ansley Reynolds MD",
    created_at: "2024-05-21 19:54:33",
    updated_at: "2024-07-14 10:12:53",
  }


Przepisanie 
  > $book2->revievs()->save($review);                                                                                                                
= App\Models\Review {#5787
    id: 1,
    book_id: 2,
    review: "Unde in inventore eligendi in ipsam. Placeat sed non voluptatum corporis. Similique ut maiores et ullam aut labore accusantium. Voluptates nesciunt ipsam itaque omnis voluptas rerum. Aspernatur aut velit deserunt aut ipsa sint ducimus.",
    rating: 4,
    created_at: "2022-11-06 17:56:18",
    updated_at: "2024-08-01 08:37:31",
  }
  *Sprawdzenie* : `$review = \App\Models\Review::with('book')->find(1);`

> $review = \App\Models\Review::with('book')->find(1);                                                                                             
= App\Models\Review {#5016
    id: 1,
    book_id: 2,
    review: "Unde in inventore eligendi in ipsam. Placeat sed non voluptatum corporis. Similique ut maiores et ullam aut labore accusantium. Voluptates nesciunt ipsam itaque omnis voluptas rerum. Aspernatur aut velit deserunt aut ipsa sint ducimus.",
    rating: 4,
    created_at: "2022-11-06 17:56:18",
    updated_at: "2024-08-01 08:37:31",
    book: App\Models\Book {#6046
      id: 2,
      title: "Minus qui quis optio.",
      author: "Ansley Reynolds MD",
      created_at: "2024-05-21 19:54:33",
      updated_at: "2024-07-14 10:12:53",
    },
  }
6. #### Query Scopes
Warto sprawdzić docu odnośnie QueryBuilder.  
Pokaż książki z "qui" w tytule :  `\App\Models\Book::where('title', 'LIKE' , '%delectus%')->get();`  
Naszym zadaniem jest aby to query zaimplementować w kodzie. W medelu Book tworzymy metodę która zrobi to samo co powyższe query. **Uwaga:*** Nazwa tej metody musi się zaczynać od słówka _scope_ CośtamCośtam. I import musi być taki: `use Illuminate\Database\Eloquent\Builder;`  Tworzymy metodę `scopeTitle` i następnie wywołująmy ją w tinkerze: `\App\Models\Book::title('delectus')->get();`   
Mając tą metodę możemy jej użyć też tak: `\App\Models\Book::title('delectus')->where('created_at', '>' , '2023-09-05')->get();`  
**Uwaga:** Jak chcemy sobie podejrzeć jakie pod spodem jest sql query to robimy `toSql()`  
\App\Models\Book::title('delectus')->where('created_at', '>' ,'2023-09-05')->toSql();                                                            
= "select * from `books` where `title` LIKE ? and `created_at` > ?"  

7.  #### Aggregations   
Te metody bierze z dokumentacji Laravela
- counting: `\App\Models\Book::withCount('revievs')->get();` teraz widzimy, że każdy Book ma coś podobnego: `revievs_count: 19,`  
*latest()* jest wbudowane w laravela `\App\Models\Book::withCount('revievs')->latest()->limit(3)->get();`   
**Uwaga:** `q` robi że tinker się wyłącza.  
- average: 5 książek z najmniejszym rating: (porządek metod w query nie ma znaczenia)  
`\App\Models\Book::limit(5)->withAvg('revievs', 'rating')->orderBy('revievs_avg_rating')->get();`  
`revievs_avg_rating` -> Laravel to przeczyta :: relationship_funkcja_kolumna.  

Najlepsze książki, ale muszą mieć conajmniej 10 recenzji.  
`\App\Models\Book::withCount('revievs')->withAvg('revievs', 'rating')->having('revievs_count', '>', 10 )->orderBy('revievs_avg_rating', 'desc')->l
imit(5)->get();`  
Budujemy scope query w Books `scopePopular` i możemy ją wywołać z tinkera za pomocą `\App\Modles\Book::scopePopular()->get();` i ona robi to samo co query z tinkera. 

8. **Arrow function** ` 'revievs' => fn(Builder $q) => $this->dateRangeFilter($q, $from, $to)` , możemy stosować skrót `fn` . `=>` to jest zaminast nawiasów {} . W Arrow funciotn może być tylko jedno wyrażenie. Nie potrzeba na końcu `;` . W a/f nie dodajemy `use()` dla zewnętrznych zmiennych. Zewnętrzne zmienne , np. $from, $to są dostępne dla a/f.  
*Uwaga:* Jak się działa na _aggregate_ _functions_ to trzeba korzystać z `having` a nie z `where`. 

9. #### Controllers ####
 Nazwy `routes` które robią CRUD muszą być zgodne z nomeklaturą `store, crete, show, update, edit, destroy` . To na czym się wykonuje te operacje CRUD to jest `resource` (w poporzedniej appce to Task)
    1. **Resource controllers**  
    W resource controllers wszystkie nazwy route's będą generowane automatycznie. Np `task.show`  


| Verb      | URI                  | Action  | Route Name     |   |
|-----------|----------------------|---------|----------------|---|
| GET       | /photos              | index   | photos.index   |   |
| GET       | /photos/create       | create  | photos.create  |   |
| POST      | /photos              | store   | photos.store   |   |
| GET       | /photos/{photo}      | show    | photos.show    |   |
| GET       | /photos/{photo}/edit | edit    | photos.edit    |   |
| PUT/PATCH | /photos/{photo}      | update  | photos.update  |   |
| DELETE    | /photos/{photo}      | destroy | photos.destroy |   |

  2. Tworzenie controlerra  
    `php artisan make:controller PhotoController --resource` . Stworzy się nowa klasa w app/Http/Controllers . Następnie rejestrujemy resource route który wskazuje na ten kontroller w `web.php` poprzez dodanie `Route::resource('books' , BookController::class);`  
    Sprawdzenie poprzez `php artisan route:list` Powinny być widoczne wszystkie PUT, DELETE, GET..
    **Uwaga:** to wszystko mocno opiera się na namig convention. 
  3. Implementacja Controllera: metoda `when`  
  $books = Book::when($title, function(){}) --> wywoła funkcję gdy $title nie będzie null.   
  Metoda `compact('books')` -> znajduje zmienną 'books' , zamienia ją na  [], to jest zamiast   ['books' =>$books]

10. **Uwaga:** dotycząca lini w index.blade.php ` <a href=" {{ route('books.index' , [...request()->query()  ,'filter123' => $key]) }} "`  
`request()->query()` jest tablicą samą w sobie, wiec stosujemy `spread operator` czyli to wypakowuje każdy element z tej tablicy i daje go do tablicy w parametrze `route( )`  
`request()->query()` w Laravelu zwraca wszystkie parametry zapytania HTTP jako tablicę asocjacyjną. W kontekście Twojego kodu, `request()->query()` pobiera wszystkie parametry zapytania z bieżącego URL-a i przekazuje je do funkcji `route()`, aby zachować te parametry w nowym URL-u.  
W skrócie, request()->query() pozwala na zachowanie wszystkich istniejących parametrów zapytania w nowym URL-u, co jest przydatne przy filtrowaniu lub paginacji.
11. `match` -> to nie jest funkcja, to jest część języka. Jest to podobne do `switch`. W argumecie przekazuje się wartość filtra. Następnie definiuje się możliwe wartości , np popular_last_month; jeśli się zgadza to można wywołać odpowiednią funkcję.  
12. Do debugowania nadaje się to:
* dd($books);
* dump($filter);
* error_log("Mateusz filter-->"  . $filter);  

