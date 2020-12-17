<?php

namespace Yriveiro\Backoff;

use InvalidArgumentException;

interface BackoffInterface
{
    const OPTION_CAP = 'cap';

    const OPTION_MAX_ATTEMPTS = 'maxAttempts';

    /**
     * Returns an array of Configuration.
     *
     * cap:          Max duration allowed (in microseconds). If backoff duration
     *               is greater than cap, cap is returned.
     * maxAttempts:  Number of attempts before thrown an \Yriveiro\Backoff\BackoffException.
     *
     * @return array
     */
    public static function getDefaultOptions(): array;

    /**
     * Allows overwrite default option values.
     *
     * @param array $options configuration options
     *
     * @return BackoffInterface
     */
    public function setOptions(array $options): BackoffInterface;

    /**
     * Exponential backoff algorithm.
     *
     * c = attempt
     *
     * E(c) = (2**c - 1)
     *
     * @param int $attempt attempt number
     *
     * @throws InvalidArgumentException
     * @throws BackoffException
     * @return float Time to sleep in microseconds before a new retry. The value
     *               is in microseconds to use with usleep, sleep function only
     *               works with seconds
     */
    public function exponential(int $attempt): float;

    /**
     * This method adds a half jitter value to exponential backoff value.
     *
     * @param int $attempt attempt number
     *
     * @throws BackoffException
     * @return int
     */
    public function equalJitter(int $attempt): int;

    /**
     * This method adds a jitter value to exponential backoff value.
     *
     * @param int $attempt attempt number
     *
     * @throws BackoffException
     * @return int
     */
    public function fullJitter(int $attempt): int;
}
