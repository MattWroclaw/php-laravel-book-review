1. Komenda do stworzenia projektu: ` composer create-project --prefer-dist laravel/laravel book-review`
2. Jeszcze nie włączyliśmy servera. Tworzymy Model: Book + Review
2. 1. `php artisan make:model Book -m`
2. 2. `php artisan make:model Review -v`
2. 3. To nam dodało się do ./database/migrations/ ale jeszcze nie ma w DB. W `migrations` są metody `up` i `down` i one robią populację/rollback w DB. Tam dodajemy pola do Modeli
2. 4. Włączamy dockera z DB. Aby `php artisan migrate`. Mamy taki output:
 php artisan migrate

   WARN  The database 'laravel-10-book-review' does not exist on the 'mysql' connection.  

  Would you like to create it? (yes/no) [yes]
❯ yes

   INFO  Preparing database.  

  Creating migration table ........................................................................................................... 126.18ms DONE

   INFO  Running migrations.  

  0001_01_01_000000_create_users_table ............................................................................................... 183.44ms DONE
  0001_01_01_000001_create_cache_table ................................................................................................ 30.15ms DONE
  0001_01_01_000002_create_jobs_table ................................................................................................ 116.60ms DONE
  2024_07_31_095451_create_books_table ................................................................................................ 16.56ms DONE
  2024_07_31_095540_create_reviews_table .............................................................................................. 14.20ms DONE

  2. 5. Teraz scheam oraz table są w DB
  3. *Reationship* One Book: many Rels. Book -> Parent_table; Review -> Child_table; W ./database/migrations w  Review tworzymy kolumnę z ForeignKey `$table->unsignedBigInteger('book_id');` Aby stworzyć relację robimy coś takiego w migration dla Review `$table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');` Po tym robimy update do DB: `php artisan migrate:reg=fresh` . Ale ralavel jeszcze nie wie że book+review są w relacji.
  3. 1. Dodanie relacji po stonie Book: w app/Models dodajemy relację w funkcji reviews(), a po stronie Reviesws metodę `book()`.
  3. 2. *Uwaga* Kolumna z foreign key w Review `book_id` jest w/g laravel convention, one-to-many. Model ma liczbę pojedynczą i automatycznie doda `_id`