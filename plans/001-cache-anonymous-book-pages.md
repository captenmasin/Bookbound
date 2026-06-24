# Plan 001: Cache anonymous book pages before database resolution

> **Executor instructions**: Follow this plan step by step. Run every
> verification command and confirm the expected result before continuing. If a
> STOP condition occurs, stop and report; do not improvise. When done, update
> this plan's row in `plans/README.md` unless a reviewer owns the index.
>
> **Drift check (run first)**:
> `git diff --stat 2805c42..HEAD -- app/Actions/Books app/Http/Controllers/BookController.php app/Http/Controllers/RatingController.php app/Http/Controllers/ReviewController.php config/books.php database/migrations routes/web.php tests/Feature/Http/Controllers/BookControllerTest.php tests/Feature/Http/Controllers/RatingControllerTest.php tests/Feature/Http/Controllers/ReviewControllerTest.php`
>
> If an in-scope file changed, compare the current code with the excerpts below.
> A material mismatch is a STOP condition.

## Status

- **Priority**: P1
- **Effort**: M (about one day including tests and production observation)
- **Risk**: MED — incorrect cache separation could expose user-specific data or
  serve stale public content
- **Depends on**: none
- **Category**: perf
- **Planned at**: commit `2805c42`, 2026-06-24

## Why this matters

Nightwatch recorded about 9,400 Postgres calls over 24 hours, with the same
book-page query group appearing about 670 times. That is roughly one
database-backed request every two minutes, faster than Laravel Cloud's
five-minute Serverless Postgres sleep delay. Postgres therefore cannot scale to
zero even though the workload is mostly repeated reads.

This change must make a cached anonymous request perform zero Postgres queries,
including the route lookup. Authenticated requests must continue using live
data so notes, custom covers, library status, and other user-specific values
cannot be shared. A six-hour Redis TTL bounds staleness and should reduce
crawler-driven wakeups from hundreds per day to a few cache refreshes.

## Current state

### Relevant files

- `routes/web.php:53-68` defines the public `books.show` route.
- `app/Models/Book.php:43-46` makes `path` the implicit binding key.
- `app/Http/Controllers/BookController.php:120-150` receives a bound `Book`,
  loads public and private relations, and defines two deferred props.
- `app/Http/Resources/BookResource.php:14-57` builds a request-aware resource.
- `config/books.php` is the appropriate home for the cache TTL/version.
- `database/migrations/2025_06_20_181705_add_path_to_books_table.php:19-20`
  added nullable `books.path` without an index.
- `tests/Feature/Http/Controllers/BookControllerTest.php` contains the existing
  Pest/Inertia feature coverage.

`app/Models/Book.php:43-46` currently guarantees a query before the controller:

```php
public function getRouteKeyName(): string
{
    return 'path';
}
```

`app/Http/Controllers/BookController.php:120-125` runs all relation queries on
every initial or deferred request, including anonymous requests:

```php
public function show(Request $request, Book $book)
{
    $book->load(['authors', 'reviews', 'ratings', 'publisher', 'tags',
        'users' => fn ($query) => $query->where('user_id', Auth::id()),
        'notes' => fn ($query) => $query->where('user_id', Auth::id()),
    ]);
```

`app/Http/Controllers/BookController.php:130-140` uses deferred props. Inertia
executes them in separate requests, so unconditional work above
`Inertia::render()` repeats:

```php
'related' => Inertia::defer(function () use ($book) {
    $relatedBooks = $book->relatedBooksBySearch(4);
    $relatedBooks->map(fn ($related) => $related->load(['authors']));

    return BookResource::collection($relatedBooks);
}),
'reviews' => Inertia::defer(fn () => ReviewResource::collection(
    $book->reviews->load('user', 'book')
        ->reject(fn ($review) => Auth::check() ? $review->user_id === Auth::id() : false)
)),
```

### Conventions and constraints

- Follow `tests/Feature/Http/Controllers/BookControllerTest.php`: Pest,
  factories, `RefreshDatabase`, and `AssertableInertia`.
- Follow the existing cache pattern in `app/Models/Book.php:128-183`:
  `Cache::remember()` with `now()->addHours(6)`.
- Preserve Inertia v2 deferred props and all client-visible prop shapes.
- Use explicit PHP types, constructor property promotion, and braces.
- Generate Laravel classes/migrations with `php artisan make:* --no-interaction`.
- Do not add dependencies.
- Cache plain arrays, never complete responses, Eloquent models, resources,
  closures, requests, or shared Inertia props.
- Public data may be stale for up to six hours. Authenticated data may not.
- The key must include a version component for future payload changes.

## Commands you will need

| Purpose | Command | Expected on success |
|---------|---------|---------------------|
| Generate action | `php artisan make:class Actions/Books/GetPublicBookPageData --no-interaction` | exit 0; class created |
| Generate migration | `php artisan make:migration add_index_to_books_path_column --table=books --no-interaction` | exit 0; migration created |
| Focused tests | `php artisan test --compact --filter='BookController|RatingController|ReviewController'` | exit 0 |
| Format PHP | `vendor/bin/pint --dirty --format agent` | exit 0 |
| Full suite | `php artisan test --compact` | exit 0 |

## Scope

**In scope** (the only source/test files to modify or create):

- `app/Actions/Books/GetPublicBookPageData.php` (create)
- `app/Http/Controllers/BookController.php`
- `app/Http/Controllers/RatingController.php`
- `app/Http/Controllers/ReviewController.php`
- `config/books.php`
- `database/migrations/*_add_index_to_books_path_column.php` (create)
- `tests/Feature/Http/Controllers/BookControllerTest.php`
- `tests/Feature/Http/Controllers/RatingControllerTest.php`
- `tests/Feature/Http/Controllers/ReviewControllerTest.php`
- `plans/README.md` (status only)

**Out of scope**:

- `.env.example` — it has an unrelated user modification; preserve it.
- `resources/js/**` and `app/Http/Resources/**`.
- Authenticated caching, full-response caching, and Cloudflare configuration.
- Bot blocking, rate limits, robots rules, and WAF changes.
- Laravel Cloud resource or credential changes.
- Dependency changes.

## Git workflow

- Branch: `codex/001-cache-anonymous-book-pages`
- Preserve and do not stage the existing `.env.example` change.
- Suggested commit: `Cache anonymous book pages`.
- Do not push or open a PR unless instructed.

## Steps

### Step 1: Add cache configuration

Extend `config/books.php` with `public_page_cache` containing:

- `ttl_seconds`: `(int) env('BOOK_PUBLIC_PAGE_CACHE_TTL', 21600)`.
- `version`: the literal string `v1`.

Do not edit the already-dirty `.env.example`. The action must read this config;
do not duplicate the TTL in controller code.

**Verify**:

```bash
php artisan tinker --execute 'dump(config("books.public_page_cache"));'
```

Expected: `ttl_seconds => 21600` and `version => "v1"`.

### Step 2: Add the lookup index

Generate the migration. In `up`, add a normal index to nullable `books.path`.
In `down`, drop it with Laravel's schema builder. Do not make it unique:
production duplicates/nulls have not been audited, and uniqueness is not needed
for this performance fix.

**Verify**: `php artisan test --compact --filter='shows a single book'` exits 0.

### Step 3: Create the anonymous page-data action

Generate `app/Actions/Books/GetPublicBookPageData.php`, use the existing
`App\Actions\Books` namespace and `AsAction`, and implement explicitly typed:

```php
public function handle(string $path, Request $request): array
public function forget(Book|string $book): void
public function cacheKey(string $path): string
```

`cacheKey()` must combine `book-page:public`, the configured version, and a safe
digest of the path. `forget()` must forget exactly that path's key.

`handle()` must use `Cache::remember()` and the configured TTL. Only inside the
cache-miss closure should it:

1. Resolve `Book::query()->where('path', $path)->firstOrFail()`.
2. Load `authors`, `reviews`, `ratings`, `publisher`, and `tags`; never anonymous
   `users` or `notes`.
3. Generate related books with the existing `relatedBooksBySearch(4)` behavior,
   load their authors, and preserve ordering.
4. Resolve public reviews with the same user/book loading as today, without the
   authenticated-user rejection.
5. Resolve all resources to plain arrays while the request is anonymous.
6. Return an array containing `book`, `average_rating`, `related`, `reviews`,
   and `meta`, preserving current values and metadata fallbacks.

Add a PHPDoc array shape. Do not catch `ModelNotFoundException`; missing paths
must remain 404 responses.

**Verify**: `php artisan test --compact --filter=BookController` exits 0.

### Step 4: Split anonymous and authenticated controller paths

Change only the show controller signature to receive the raw route value:

```php
public function show(Request $request, string $book)
```

Do not rename `{book}` or change the route name/URL. Then:

- For authenticated requests, resolve the model by `path` and execute today's
  live behavior. A private helper is encouraged. Preserve user relations,
  filtering, breadcrumbs, metadata, and deferred props.
- For anonymous requests, call
  `GetPublicBookPageData::run($book, $request)` before any Eloquent operation.
  Render from its plain payload. Keep `related` and `reviews` wrapped in
  `Inertia::defer()`, but those closures must only return cached arrays.

The anonymous branch must not call `Auth::id()`, resolve `Book` independently,
or load user relations. The authenticated branch must never use the anonymous
cached `book` array.

**Verify**: `php artisan test --compact --filter=BookController` exits 0.

### Step 5: Invalidate after public review/rating changes

Inject `GetPublicBookPageData` into `ReviewController` and `RatingController`
using constructor property promotion. After each successful mutation, call
`forget($book)` before returning. Cover:

- `ReviewController::store` and `destroy`.
- `RatingController::store`, `update`, and `destroy`.

Do not invalidate notes, library status, user tags, or custom covers because
they are private and absent from the anonymous payload. Admin edits to books,
authors, tags, publishers, and primary covers remain bounded by the six-hour
TTL and are deferred to avoid a broad observer graph.

**Verify**:
`php artisan test --compact --filter='RatingController|ReviewController'` exits 0.

### Step 6: Add cache-boundary regression tests

Extend the existing controller tests. Flush cache at the start of each
cache-specific test. Add:

1. First anonymous request succeeds and populates the exact `cacheKey()`.
2. After warming, call `$this->expectsDatabaseQueryCount(0)` immediately before
   a second anonymous request. Assert success and the same identifier/title.
   Do no DB-backed setup/assertion after setting the zero-query expectation.
3. A warmed Inertia partial request for `related` and `reviews` returns the
   expected arrays without application DB queries. If the helper adds setup
   queries, isolate the HTTP request with `DB::listen()`.
4. Warm as guest, then authenticate a user who owns the book. Assert live
   `in_library`, status, tags, notes/rating/review as appropriate. Then assert a
   guest response contains none of that private state.
5. Unknown paths return 404 and create no successful page cache entry.

Add one test each to the RatingController and ReviewController files:
pre-populate the page key, perform a successful mutation, and assert it was
forgotten.

**Verify**:

```bash
php artisan test --compact --filter='BookController|RatingController|ReviewController'
```

Expected: all old/new tests pass. The zero-query cache-hit assertion is
mandatory; checking only `Cache::has()` is insufficient.

### Step 7: Format and run regression gates

```bash
vendor/bin/pint --dirty --format agent
php artisan test --compact --filter='BookController|RatingController|ReviewController'
php artisan test --compact
git status --short
git diff --check
```

Expected: formatter/tests exit 0; `git diff --check` is empty; status lists only
in-scope files, plan files, and the pre-existing `.env.example` modification.

### Step 8: Deploy and validate the hypothesis

Deploy normally through Laravel Cloud. Confirm production still uses Redis.
Observe a comparable 24-hour period in Nightwatch and Laravel Cloud. Record:

- Count of `select * from "books" where "path" = ? limit 1`.
- Total database calls.
- Serverless Postgres CU growth and active periods.
- Cache hit/miss activity if available.

Acceptance target: the repeated path query falls at least 90% from roughly 670
calls/24h, and CU growth falls materially below 2 CU/day under comparable
traffic. If it does not, inspect Nightwatch Requests attached to the remaining
query family before adding bot rules.

## Test plan

- Add the five BookController cache/isolation cases from Step 6.
- Add one invalidation case to each RatingController and ReviewController test.
- Do not add browser tests unless the frontend contract unexpectedly changes.
- Both commands must pass:

```bash
php artisan test --compact --filter='BookController|RatingController|ReviewController'
php artisan test --compact
```

## Done criteria

- [ ] A second anonymous `books.show` request performs zero DB queries.
- [ ] Authenticated requests bypass anonymous cache and retain personalization.
- [ ] Anonymous deferred props use the cached bundle.
- [ ] Review/rating mutations forget the affected page key.
- [ ] Unknown paths still return 404.
- [ ] `books.path` has a non-unique index.
- [ ] No dependency, frontend, URL/name, or public prop-shape changes.
- [ ] Pint, focused tests, full tests, and `git diff --check` pass.
- [ ] `.env.example` remains untouched by this work.
- [ ] Production observation shows a 90% path-query reduction, or the plan is
      marked BLOCKED with Nightwatch evidence.
- [ ] `plans/README.md` status is updated.

## STOP conditions

Stop and report if:

- Production cache is not Redis or another shared persistent Laravel store.
- Anonymous rendering requires user-specific state in the shared payload.
- Receiving raw `{book}` changes route URLs, named routes, or write-route model
  binding.
- Resources cannot resolve to plain arrays without retaining models/request
  state.
- The zero-query hit still queries after two reasonable isolation attempts.
- The fix requires frontend/auth changes or a new package.
- Focused tests fail before implementation.
- Current code materially drifted from the excerpts at `2805c42`.

## Maintenance notes

- Increment `config('books.public_page_cache.version')` when the payload shape
  changes incompatibly; old Redis keys will age out.
- Review authentication separation first. Cached data may contain intentionally
  public reviews and aggregate ratings, but must never contain private notes,
  library state, user-assigned tags, a user's own rating/review controls, or
  custom covers.
- Public admin edits may remain stale until TTL expiry. Add targeted
  invalidation later only if that becomes a product issue.
- If Postgres remains awake after this query reduction, use Nightwatch request
  context to identify the next query family before adding crawler blocking.
- HTTP `cache.headers` or edge caching is a later option only after responses
  can safely vary by authentication and cookies.
