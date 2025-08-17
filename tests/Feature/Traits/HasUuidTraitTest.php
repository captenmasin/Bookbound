<?php

use App\Traits\HasUuid;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Schema::create('has_uuid_test_models', function (Blueprint $table) {
        $table->id();
        $table->string('uuid')->unique();
        $table->string('title')->nullable();
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('has_uuid_test_models');
});

class HasUuidTestModel extends Model
{
    use HasUuid;

    protected $table = 'has_uuid_test_models';

    protected $guarded = [];

    public $timestamps = true;
}

it('assigns a UUID on create via the creating event', function () {
    $m = HasUuidTestModel::create(['title' => 'Example']);

    expect($m->uuid)->not->toBeNull()
        ->and($m->uuid)->toBeString()
        // hashids are [a-zA-Z0-9] by default; adjust if you configure alphabet
        ->and($m->uuid)->toMatch('/^[a-zA-Z0-9]+$/')
        // default padding in the trait is 10; Hashids ensures >= padding length
        ->and(strlen($m->uuid))->toBeGreaterThanOrEqual(10);
});

it('persists unique UUIDs across multiple creates', function () {
    $count = 50;

    $uuids = collect(range(1, $count))
        ->map(fn ($i) => HasUuidTestModel::create(['title' => "Row $i"])->uuid);

    expect($uuids->unique()->count())->toBe($count);
});

it('can generate a padded hashid of a custom length when called directly (unit-ish)', function () {
    $m = new HasUuidTestModel;

    $uuid12 = $m->generateHashid(padding: 12);
    $uuid20 = $m->generateHashid(padding: 20);

    expect($uuid12)->toBeString()
        ->and(strlen($uuid12))->toBeGreaterThanOrEqual(12)
        ->and($uuid20)->toBeString()
        ->and(strlen($uuid20))->toBeGreaterThanOrEqual(20);
});

it('overwrites any pre-set uuid on creating (current trait behavior)', function () {
    // Current implementation *always* overwrites on creating
    $m = new HasUuidTestModel(['uuid' => 'will-be-overwritten', 'title' => 'Preset']);
    $m->save();

    expect($m->uuid)->not->toBe('will-be-overwritten');
});

it('regenerates on collision (exercises the while branch with uniqid append)', function () {
    // Throwaway model using the trait (same as in your file)
    if (! class_exists(HasUuidTestModel::class, false)) {
        class HasUuidTestModel extends Model
        {
            use HasUuid;

            protected $table = 'has_uuid_test_models';

            protected $guarded = [];
        }
    }

    // 1) Predict the first UUID by seeding the global RNG used by rand()
    mt_srand(424242); // rand() is an alias of mt_rand() in modern PHP
    $predicted = (new HasUuidTestModel)->generateHashid(10);

    // 2) Insert a row with that UUID to force a collision on the next create()
    DB::table('has_uuid_test_models')->insert([
        'uuid' => $predicted,
        'title' => 'occupied',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // 3) Reseat the RNG so the next call inside the creating event generates the same value
    mt_srand(424242);

    // 4) Create a new model; the first generated UUID collides, triggering the while() and recursive call (line 29)
    $m = HasUuidTestModel::create(['title' => 'new']);

    expect($m->uuid)->not->toBe($predicted)          // was regenerated due to collision
        ->and(HasUuidTestModel::where('uuid', $predicted)->exists())->toBeTrue()
        ->and(HasUuidTestModel::count())->toBe(2);
});
