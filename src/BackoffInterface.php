<?php
namespace Yriveiro\Backoff;

interface BackoffInterface
{
    public static function getDefaultOptions(): array;

    public function exponential(int $attempt): float;

    public function equalJitter(int $attempt): int;

    public function fullJitter(int $attempt): int;
}
