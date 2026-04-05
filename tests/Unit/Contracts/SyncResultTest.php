<?php

use Illuminate\Support\Collection;
use TheShit\Finance\Contracts\SyncResult;

it('stores all properties correctly', function () {
    $added    = collect(['a']);
    $modified = collect(['b']);
    $removed  = collect(['c']);

    $result = new SyncResult(
        added:      $added,
        modified:   $modified,
        removed:    $removed,
        nextCursor: 'cursor_abc123',
        hasMore:    true,
    );

    expect($result->added)->toBe($added)
        ->and($result->modified)->toBe($modified)
        ->and($result->removed)->toBe($removed)
        ->and($result->nextCursor)->toBe('cursor_abc123')
        ->and($result->hasMore)->toBeTrue();
});

it('accepts hasMore as false', function () {
    $result = new SyncResult(
        added:      collect(),
        modified:   collect(),
        removed:    collect(),
        nextCursor: '',
        hasMore:    false,
    );

    expect($result->hasMore)->toBeFalse()
        ->and($result->added)->toBeInstanceOf(Collection::class);
});
