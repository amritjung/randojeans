<?php

use Tests\TestCase;

/*
 * Boots the app without RefreshDatabase, so this check can never migrate or
 * wipe a real database even if the test environment were misconfigured.
 */
uses(TestCase::class);

test('the test suite uses an in-memory sqlite database', function () {
    $connection = config('database.default');

    expect($connection)->toBe('sqlite')
        ->and(config("database.connections.{$connection}.database"))->toBe(':memory:')
        ->and(config("database.connections.{$connection}.url"))->toBeEmpty();
});
