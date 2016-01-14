<?php
namespace Yriveiro\Solr\Tests;

use ReflectionMethod;
use ReflectionProperty;
use InvalidArgumentException;
use Yriveiro\Backoff\Backoff;
use PHPUnit_Framework_TestCase as TestCase;

class BackoffTest extends TestCase
{
    private function elapsed()
    {
        static $last = null;

        $now = microtime(true);

        if ($last != null) {
            $elapsed = ($now - $last) * 1000;

            echo "\n<!-- $elapsed -->\n";
        }

        $last = $now;
    }

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

    public function testDefaultOptions()
    {
        $actual = Backoff::getDefaultOptions();
        $expected = array(
            'cap' => 1000000,
            'maxAttemps' => 0
        );

        $this->assertEquals($expected, $actual, "Default options differ");
    }

    public function testSetOptions()
    {
        $options['maxAttemps'] = 10;

        $this->backoff->setOptions($options);

        $options = new ReflectionProperty($this->backoff, 'options');
        $options->setAccessible(true);

        $expected = array(
            'cap' => 1000000,
            'maxAttemps' => 10
        );

        $this->assertEquals($expected, $options->getValue($this->backoff));
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
        $this->backoff->exponential('foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNegativeAttempt()
    {
        $this->backoff->exponential(-1);
    }
}