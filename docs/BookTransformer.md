# BookTransformer

Convert raw book data from external providers into a **standardized internal format** for your app.  
Currently supported providers:

- **ISBNdb**
- **Google Books**
- **Open Library**

The transformer hides upstream differences (field names, shapes, missing data) behind a consistent payload your UI and domain logic can rely on.

---

## ⚙️ Configuration

Choose the active provider via config/env.

```php
// config/books.php
return [
    // Valid: isbndb | google | openlibrary
    'provider' => env('BOOKS_API', 'isbndb'),
];
```

```env
# .env
BOOKS_API=isbndb
```

> Tip: In tests, switch providers per-spec with:
> ```php
> use Illuminate\Support\Facades\Config;
> Config::set('books.provider', 'openlibrary'); // or 'google' / 'isbndb'
> ```

---

## 🎯 Goal / Output Shape

All provider methods return a **normalized array** with stable keys:

```php
[
  'id'                => ?string,  // Provider’s own id (if any)
  'identifier'        => string,   // Primary ISBN (prefers ISBN-13)
  'codes'             => [
      ['type' => 'ISBN_13', 'identifier' => ?string],
      ['type' => 'ISBN_10', 'identifier' => ?string],
  ],
  'title'             => ?string,
  'pageCount'         => ?int,
  'tags'              => string[], // normalized subjects
  'publisher'         => ?['name' => string, 'uuid' => ?string],
  'description'       => ?string,  // raw
  'description_clean' => string,   // html-decoded, strip_tags, squished
  'authors'           => [ ['name' => string, 'uuid' => ?string], ... ],
  'edition'           => ?string,
  'binding'           => ?string,
  'language'          => ?string,  // code like 'eng'
  'published_date'    => ?string,  // year or date string
  'cover'             => ?string,  // medium
  'cover_large'       => ?string,  // large
  'service'           => string,   // ISBNdb | GoogleBooks | OpenLibrary
  'links'             => ['show' => string], // route('books.show'|preview)
]
```

---

## 🧩 Public API

### `BookTransformer::handle(array $data): ?array`

Routes to the correct provider transformer based on `config('books.provider')`:

```php
$provider = config('books.provider', '');

return match ($provider) {
  'isbndb'      => self::fromIsbn($data),
  'google'      => self::fromGoogleBooks($data),
  'openlibrary' => self::fromOpenLibrary($data),
  default       => throw new InvalidArgumentException("BookTransformer: unknown books.provider '{$provider}'."),
};
```

### Provider-specific methods

- `fromIsbn(array $data): array`
- `fromGoogleBooks(array $data): array`
- `fromOpenLibrary(array $data): array`

All three methods:
- pick a primary `identifier` (ISBN-13 → ISBN-10 → fallback as applicable),
- normalize authors/subjects,
- clean descriptions,
- create links based on whether the book already exists in your DB.

---

## 🔧 Normalization Helpers (internal)

- **`linksForIdentifier(string $identifier): array`**  
  Looks up an existing `Book` by `identifier` and returns a `show` or `preview` route.

- **`normalizeSubjects(array $raw): array`**  
  Replaces underscores, strips suffix after `--`, splits on `,`, `&`, `/`, trims, lowercases, converts to headline case, de-duplicates.

- **`mapAuthors(array $raw): array`**  
  Accepts arrays of strings or `['name' => '...']`, returns `['name' => ..., 'uuid' => null]`.

- **`cleanDescription(?string $desc): array{raw:?string, clean:string}`**  
  `html_entity_decode` → `strip_tags` → `squish`.

- **`pickIdentifierFromCodes(array $codes, ?string $fallback): ?string`**  
  Chooses best from `codes` array with preference for `ISBN_13`, then `ISBN_10`, else first available or `$fallback`.

- **`replaceQueryParam(string $url, string $key, string $value): string`**  
  Rewrites a query param only if it exists (used for Google Books cover URLs).

---

## 🔌 Provider Notes

### ISBNdb (`fromIsbn`)
- Accepts fields like `isbn13`, `isbn10`/`isbn`, `title`, `pages`, `subjects`, `publisher`, `language`, `date_published`, `image`, `image_original`, `overview`/`synopsis`/`description`.
- Builds `codes` from available ISBN fields.

### Google Books (`fromGoogleBooks`)
- Accepts a normalized payload with `codes` (preferred), plus `title`, `page_count`, `tags`, `publisher`, `description`/`description_clean`, `language`, `date_published`, and `cover`.
- Cover URLs: if present, automatically switch `edge=curl → none` and `zoom=1 → 0` (only when params exist).

### Open Library (`fromOpenLibrary`)
- Works for **search docs** (`/search.json`) and **edition payloads** (`/isbn/{isbn}.json`).
- Identifier chosen from `isbn_13`, `isbn_10`, or `isbn`.
- Subjects can be strings or objects (`['name' => '...']`).
- Authors prefer `author_expanded` (if your service expanded them), else `author_name` (from search docs).
- Cover URLs are assembled from `cover`, `cover_large`, `cover_i`, or `covers[]` using `covers.openlibrary.org`.

---

## 🧪 Testing Tips

- **Switch providers** per-test:
  ```php
  Config::set('books.provider', 'openlibrary');
  ```
- **Snapshot** the normalized output to detect accidental shape changes.
- **Edge cases to test**:
    - Missing ISBNs / codes (throws `InvalidArgumentException`).
    - Subjects with underscores + `--` suffix + multi-delimiters (`A & B, C/D`).
    - Authors provided as strings vs arrays.
    - Google Books covers missing `edge`/`zoom` params (no rewrite).
    - Open Library edition vs search-doc shapes.

---

## 📦 Example

```php
use App\Transformers\BookTransformer;
use Illuminate\Support\Facades\Config;

Config::set('books.provider', 'openlibrary');

$raw = [
  'isbn_13' => ['9780140328721'],
  'title'   => 'Matilda',
  'author_name' => ['Roald Dahl'],
  'subject' => ["Children's stories"],
  'number_of_pages_median' => 240,
];

$book = BookTransformer::handle($raw);

// e.g.
$book['title'];         // "Matilda"
$book['authors'][0];    // ['name' => 'Roald Dahl', 'uuid' => null]
$book['tags'];          // ["Children's Stories"]
$book['links']['show']; // route to show or preview
```
