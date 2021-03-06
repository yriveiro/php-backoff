<?php
namespace Yriveiro\Backoff\Tests;

use TypeError;
use ReflectionProperty;
use InvalidArgumentException;
use Yriveiro\Backoff\Backoff;
use PHPUnit\Framework\TestCase;

class BackoffTest extends TestCase
{
    protected $backoff;

    public function setUp()
    {
        $this->backoff = new Backoff();
    }

    public function testCreateInstance()
    {
        $backoff = new Backoff();

        $this->assertInstanceOf(
            'Yriveiro\Backoff\Backoff',
            $backoff,
            "Backoff should be an instance of Yriveiro\Backoff\Backoff"
        );
    }

    public function testCreateInstanceWithOptions()
    {
        $options = Backoff::getDefaultOptions();
        $options['cap'] = 2000000;
        $options['maxAttempts'] = 10;

        $backoff = new Backoff($options);

        $this->assertInstanceOf(
            'Yriveiro\Backoff\Backoff',
            $backoff,
            "Backoff should be an instance of Yriveiro\Backoff\Backoff"
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateInstanceWithOptionsCapNotInteger()
    {
        $options = Backoff::getDefaultOptions();
        $options['cap'] = 'foo';

        $backoff = new Backoff($options);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateInstanceWithOptionsmaxAttemptsNotInteger()
    {
        $options = Backoff::getDefaultOptions();
        $options['maxAttempts'] = 'foo';

        $backoff = new Backoff($options);
    }

    public function testDefaultOptions()
    {
        $actual = Backoff::getDefaultOptions();
        $expected = [
            'cap' => 1000000,
            'maxAttempts' => 0,
        ];

        $this->assertEquals($expected, $actual, 'Default options differ');
    }

    public function testSetOptions()
    {
        $options['maxAttempts'] = 10;

        $this->backoff->setOptions($options);

        $options = new ReflectionProperty($this->backoff, 'options');
        $options->setAccessible(true);

        $expected = [
            'cap' => 1000000,
            'maxAttempts' => 10,
        ];

        $this->assertEquals($expected, $options->getValue($this->backoff));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetOptionsNotAnArray()
    {
        try {
            $this->backoff->setOptions(10);
        } catch (TypeError $e) {
            throw new InvalidArgumentException();
        }
    }

    /**
     * @expectedException \Yriveiro\Backoff\BackoffException
     */
    public function testMaxAttempts()
    {
        $options['maxAttempts'] = 3;

        $this->backoff->setOptions($options);

        foreach (range(1, 10) as $attempt) {
            $this->backoff->exponential($attempt);
        }
    }

    public function testExponential()
    {
        $backoff = $this->backoff->exponential(1);
        $this->assertEquals(1000, $backoff);
        $backoff = $this->backoff->exponential(2);
        $this->assertEquals(2000, $backoff);
        $backoff = $this->backoff->exponential(3);
        $this->assertEquals(4000, $backoff);
    }

    public function testFullJitter()
    {
        foreach (range(1, 10) as $attempt) {
            $start = microtime(true);
            $backoff = $this->backoff->fullJitter($attempt);

            usleep($backoff);

            $end = (microtime(true) - $start);
            $this->assertGreaterThan($backoff, $end * 1000000);
        }
    }

    public function testEqualJitter()
    {
        foreach (range(1, 10) as $attempt) {
            $start = microtime(true);
            $backoff = $this->backoff->equalJitter($attempt);

            usleep($backoff);

            $end = (microtime(true) - $start);
            $this->assertGreaterThan($backoff, $end * 1000000);
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWrongAttemptType()
    {
        try {
            $this->backoff->exponential('foo');
        } catch (TypeError $e) {
            throw new InvalidArgumentException();
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNegativeAttempt()
    {
        $this->backoff->exponential(-1);
    }
}
