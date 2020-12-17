<?php

namespace Yriveiro\Backoff;

use InvalidArgumentException;

class Backoff implements BackoffInterface
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param array $options configuration options
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->getDefaultOptions(), $options);

        if (!is_int($this->options[BackoffInterface::OPTION_CAP])) {
            throw new InvalidArgumentException('Cap must be a number');
        }

        if (!is_int($this->options[BackoffInterface::OPTION_MAX_ATTEMPTS])) {
            throw new InvalidArgumentException('maxAttempts must be a number');
        }
    }

    /**
     * @inheritDoc
     */
    public static function getDefaultOptions(): array
    {
        return [
            BackoffInterface::OPTION_CAP          => 1000000,
            BackoffInterface::OPTION_MAX_ATTEMPTS => 0,
        ];
    }

    /**
     * @inheritDoc
     */
    public function setOptions(array $options): BackoffInterface
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function exponential(int $attempt): float
    {
        if (!is_int($attempt)) {
            throw new InvalidArgumentException('Attempt must be an integer');
        }

        if ($attempt < 1) {
            throw new InvalidArgumentException('Attempt must be >= 1');
        }

        if ($this->maxAttemptsExceeded($attempt)) {
            throw new BackoffException(
                sprintf(
                    'The number of max attempts (%s) was exceeded',
                    $this->options[BackoffInterface::OPTION_MAX_ATTEMPTS]
                )
            );
        }

        $wait = (1 << ($attempt - 1)) * 1000;

        return ($this->options[BackoffInterface::OPTION_CAP]
            < $wait) ? $this->options[BackoffInterface::OPTION_CAP] : $wait;
    }

    /**
     * @inheritDoc
     */
    public function equalJitter(int $attempt): int
    {
        $half = ($this->exponential($attempt) / 2);

        return (int)floor($half + $this->random(0.0, $half));
    }

    /**
     * @inheritDoc
     */
    public function fullJitter(int $attempt): int
    {
        return (int)floor($this->random(0.0, $this->exponential($attempt) / 2));
    }

    /**
     * Generates a random number between min and max.
     *
     * @param float $min
     * @param float $max
     *
     * @return float
     */
    protected function random(float $min, float $max): float
    {
        return $min + lcg_value() * (abs($max - $min));
    }

    /**
     * Check if we are above the maximum number of attempts.
     *
     * @param int $attempt the current attempt of retry
     *
     * @return bool
     */
    private function maxAttemptsExceeded(int $attempt): bool
    {
        return $this->options[BackoffInterface::OPTION_MAX_ATTEMPTS] > 1
            && $attempt > $this->options[BackoffInterface::OPTION_MAX_ATTEMPTS];
    }
}
