<?php
namespace Yriveiro\Backoff;

interface BackoffInterface
{
    public static function getDefaultOptions();
    public function exponential($attempt);
    public function equalJitter($attempt);
    public function fullJitter($attempt);
}
