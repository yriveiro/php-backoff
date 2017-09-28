<?php
namespace Yriveiro\Backoff;

use InvalidArgumentException;
use Yriveiro\Backoff\BackoffException;
use Yriveiro\Backoff\BackoffInterface;

class Backoff implements BackoffInterface
{
    protected $options = [];

    /**
     * @param array $options Configuration options.
     * @throws \InvalidArgumentException.
     */
    public function __construct($options = [])
    {
        $this->options = array_merge($this->getDefaultOptions(), $options);

        if (!is_int($this->options['cap'])) {
            throw new InvalidArgumentException('Cap must be a number');
        }

        if (!is_int($this->options['maxAttempts'])) {
            throw new InvalidArgumentException('maxAttempts must be a number');
        }
    }

    /**
     * Returns an array of Configuration.
     *
     * cap:         Max duration allowed (in microseconds). If backoff duration
     *              is greater than cap, cap is returned.
     * maxAttempts:  Number of attemps before thrown an Yriveiro\Backoff\BackoffException.
     *
     * @return mixed
     */
    public static function getDefaultOptions() : array
    {
        return [
            'cap' => 1000000,
            'maxAttempts' => 0
    	];
    }

    /**
     * Allows overwrite default option values.
     *
     * @param mixed
     */
    public function setOptions($options)
    {
        if (!is_array($options)) {
            $options = [$options];
        }

        $this->options = array_merge($this->options, $options);

        return $this;
    }

    /**
     * Exponential backoff algorithm.
     *
     * c = attempt
     *
     * E(c) = (2**c - 1)
     *
     * @param int $attempt Attempt number.
     *
     * @return float Time to sleep in microseconds before a new retry. The value
     *               is in microseconds to use with usleep, sleep function only
     *               works with seconds
     *
     * @throws \InvalidArgumentException.
     */
    public function exponential($attempt) : float
    {
        if (!is_int($attempt)) {
            throw new InvalidArgumentException('Attempt must be an integer');
        }

        if ($attempt < 1) {
            throw new InvalidArgumentException('Attempt must be >= 1');
        }

        if ($this->options['maxAttempts'] > 1
            && $attempt > $this->options['maxAttempts']
        ) {
            throw new BackoffException(
                sprintf(
                    "The number of max attempts (%s) was exceeded",
                    $this->options['maxAttempts']
                )
            );
        }

        $wait = (1 << ($attempt - 1)) * 1000;

        return ($this->options['cap'] < $wait) ? $this->options['cap'] : $wait;
    }

    /**
     * This method adds a half jitter value to exponential backoff value.
     *
     * @param int $attempt Attempt number.
     *
     * @return int
     */
    public function equalJitter($attempt) : int
    {
        $half = ($this->exponential($attempt) / 2);

        return (int) floor($half + $this->random(0.0, $half));
    }

    /**
     * This method adds a jitter value to exponential backoff value.
     *
     * @param int $attempt Attempt number.
     *
     * @return int
     */
    public function fullJitter($attempt) : int
    {
        return (int) floor($this->random(0.0, $this->exponential($attempt) / 2));
    }

    /**
     * Generates a random number between min and max.
     *
     * @param float $min
     * @param float $max
     *
     * @return float
     */
    protected function random($min, $max) : float
    {
        return ($min + lcg_value() * (abs($max - $min)));
    }
}
