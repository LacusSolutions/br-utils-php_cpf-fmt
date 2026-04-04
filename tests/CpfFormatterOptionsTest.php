<?php

declare(strict_types=1);

namespace Lacus\CpfFmt\Tests;

use Exception;
use InvalidArgumentException;
use Lacus\CpfFmt\CpfFormatterOptions;
use PHPUnit\Framework\TestCase;
use TypeError;

class CpfFormatterOptionsTest extends TestCase
{
    public function testConstructorWithAllNoParameters(): void
    {
        $options = new CpfFormatterOptions();

        $this->assertFalse($options->isEscaped());
        $this->assertFalse($options->isHidden());
        $this->assertEquals('*', $options->getHiddenKey());
        $this->assertEquals(3, $options->getHiddenStart());
        $this->assertEquals(10, $options->getHiddenEnd());
        $this->assertEquals('.', $options->getDotKey());
        $this->assertEquals('-', $options->getDashKey());
        $this->assertIsCallable($options->getOnFail());
    }

    public function testConstructorWithAllNullParameters(): void
    {
        $options = new CpfFormatterOptions(
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
        );

        $this->assertFalse($options->isEscaped());
        $this->assertFalse($options->isHidden());
        $this->assertEquals('*', $options->getHiddenKey());
        $this->assertEquals(3, $options->getHiddenStart());
        $this->assertEquals(10, $options->getHiddenEnd());
        $this->assertEquals('.', $options->getDotKey());
        $this->assertEquals('-', $options->getDashKey());
        $this->assertIsCallable($options->getOnFail());
    }

    public function testConstructorWithAllParameters(): void
    {
        $onFailCallback = function (string $value): string {
            return 'ERROR: ' . $value;
        };

        $options = new CpfFormatterOptions(
            true,
            true,
            '#',
            1,
            8,
            '|',
            '~',
            $onFailCallback,
        );

        $this->assertTrue($options->isEscaped());
        $this->assertTrue($options->isHidden());
        $this->assertEquals('#', $options->getHiddenKey());
        $this->assertEquals(1, $options->getHiddenStart());
        $this->assertEquals(8, $options->getHiddenEnd());
        $this->assertEquals('|', $options->getDotKey());
        $this->assertEquals('~', $options->getDashKey());
        $this->assertSame($onFailCallback, $options->getOnFail());
    }

    public function testMergeWithPartialOverrides(): void
    {
        $originalOptions = new CpfFormatterOptions(
            false,
            false,
            '*',
            3,
            10,
            '.',
            '-',
            null,
        );

        $mergedOptions = $originalOptions->merge(
            true,        // override
            null,        // keep original
            '#',      // override
            null,   // keep original
            null,     // keep original
            '|',         // override
            null,       // keep original
            null         // keep original
        );

        $this->assertTrue($mergedOptions->isEscaped());
        $this->assertFalse($mergedOptions->isHidden());
        $this->assertEquals('#', $mergedOptions->getHiddenKey());
        $this->assertEquals(3, $mergedOptions->getHiddenStart());
        $this->assertEquals(10, $mergedOptions->getHiddenEnd());
        $this->assertEquals('|', $mergedOptions->getDotKey());
        $this->assertEquals('-', $mergedOptions->getDashKey());
    }

    public function testSetEscape(): void
    {
        $options = new CpfFormatterOptions();

        $options->setEscape(true);
        $this->assertTrue($options->isEscaped());

        $options->setEscape(false);
        $this->assertFalse($options->isEscaped());
    }

    public function testSetHide(): void
    {
        $options = new CpfFormatterOptions();

        $options->setHide(true);
        $this->assertTrue($options->isHidden());

        $options->setHide(false);
        $this->assertFalse($options->isHidden());
    }

    public function testSetHiddenKey(): void
    {
        $options = new CpfFormatterOptions();

        $options->setHiddenKey('X');
        $this->assertEquals('X', $options->getHiddenKey());

        $options->setHiddenKey('?');
        $this->assertEquals('?', $options->getHiddenKey());
    }

    public function testSetHiddenRangeWithValidValues(): void
    {
        $options = new CpfFormatterOptions();

        $options->setHiddenRange(0, 10);
        $this->assertEquals(0, $options->getHiddenStart());
        $this->assertEquals(10, $options->getHiddenEnd());

        $options->setHiddenRange(5, 7);
        $this->assertEquals(5, $options->getHiddenStart());
        $this->assertEquals(7, $options->getHiddenEnd());
    }

    public function testSetHiddenRangeWithSwappedValues(): void
    {
        $options = new CpfFormatterOptions();

        // Test that start > end gets swapped
        $options->setHiddenRange(8, 2);
        $this->assertEquals(2, $options->getHiddenStart());
        $this->assertEquals(8, $options->getHiddenEnd());
    }

    public function testSetHiddenRangeWithInvalidStart(): void
    {
        $options = new CpfFormatterOptions();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Option "hiddenStart" must be an integer between 0 and 10.');

        $options->setHiddenRange(-1, 5);
    }

    public function testSetHiddenRangeWithInvalidEnd(): void
    {
        $options = new CpfFormatterOptions();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Option "hiddenRange.end" must be an integer between 0 and 10.');

        $options->setHiddenRange(5, 11);
    }

    public function testSetHiddenRangeWithStartTooHigh(): void
    {
        $options = new CpfFormatterOptions();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Option "hiddenStart" must be an integer between 0 and 10.');

        $options->setHiddenRange(11, 5);
    }

    public function testSetDotKey(): void
    {
        $options = new CpfFormatterOptions();

        $options->setDotKey('|');
        $this->assertEquals('|', $options->getDotKey());

        $options->setDotKey(' ');
        $this->assertEquals(' ', $options->getDotKey());
    }

    public function testSetDashKey(): void
    {
        $options = new CpfFormatterOptions();

        $options->setDashKey('~');
        $this->assertEquals('~', $options->getDashKey());

        $options->setDashKey('_');
        $this->assertEquals('_', $options->getDashKey());
    }

    public function testSetOnFailWithValidCallback(): void
    {
        $options = new CpfFormatterOptions();

        $callback = function (string $value): string {
            return 'ERROR: ' . $value;
        };

        $options->setOnFail($callback);
        $this->assertSame($callback, $options->getOnFail());
    }

    public function testSetOnFailWithInvalidCallback(): void
    {
        $options = new CpfFormatterOptions();

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('must be of type Closure, string given');

        $options->setOnFail('not a callback');
    }

    public function testSetOnFailWithArray(): void
    {
        $options = new CpfFormatterOptions();

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('must be of type Closure, array given');

        $options->setOnFail(['not', 'callable']);
    }

    public function testSetOnFailWithNull(): void
    {
        $options = new CpfFormatterOptions();

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('must be of type Closure, null given');

        $options->setOnFail(null);
    }

    public function testSetOnFailWithInt(): void
    {
        $options = new CpfFormatterOptions();

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('must be of type Closure, int given');

        $options->setOnFail(123);
    }

    public function testBoundaryValuesForHiddenRange(): void
    {
        $options = new CpfFormatterOptions();

        // Test minimum values
        $options->setHiddenRange(0, 0);
        $this->assertEquals(0, $options->getHiddenStart());
        $this->assertEquals(0, $options->getHiddenEnd());

        // Test maximum values
        $options->setHiddenRange(10, 10);
        $this->assertEquals(10, $options->getHiddenStart());
        $this->assertEquals(10, $options->getHiddenEnd());
    }

    public function testDefaultOnFailCallbackBehavior(): void
    {
        $options = new CpfFormatterOptions();
        $callback = $options->getOnFail();

        $result = $callback('test input', new Exception('test error'));

        $this->assertEquals('test input', $result);
    }

    public function testMergeReturnsNewInstance(): void
    {
        $originalOptions = new CpfFormatterOptions(null, null, null, null, null, null, null, null);
        $mergedOptions = $originalOptions->merge(null, null, null, null, null, null, null, null);

        $this->assertNotSame($originalOptions, $mergedOptions);
        $this->assertInstanceOf(CpfFormatterOptions::class, $mergedOptions);
    }

    public function testMergeWithAllNullsPreservesOriginalValues(): void
    {
        $originalOptions = new CpfFormatterOptions(
            true,
            true,
            '#',
            1,
            8,
            '|',
            '~',
            function (string $value): string {
                return 'ERROR: ' . $value;
            },
        );

        $mergedOptions = $originalOptions->merge(null, null, null, null, null, null, null, null);

        $this->assertTrue($mergedOptions->isEscaped());
        $this->assertTrue($mergedOptions->isHidden());
        $this->assertEquals('#', $mergedOptions->getHiddenKey());
        $this->assertEquals(1, $mergedOptions->getHiddenStart());
        $this->assertEquals(8, $mergedOptions->getHiddenEnd());
        $this->assertEquals('|', $mergedOptions->getDotKey());
        $this->assertEquals('~', $mergedOptions->getDashKey());
    }

    public function testConstructorWithMixedNullAndValidValues(): void
    {
        $onFailCallback = function (string $value): string {
            return 'CUSTOM: ' . $value;
        };

        $options = new CpfFormatterOptions(
            true,
            null,      // should default to false
            null,   // should default to '*'
            5,
            null,   // should default to 10
            null,      // should default to '.'
            '~',
            $onFailCallback
        );

        $this->assertTrue($options->isEscaped());
        $this->assertFalse($options->isHidden());
        $this->assertEquals('*', $options->getHiddenKey());
        $this->assertEquals(5, $options->getHiddenStart());
        $this->assertEquals(10, $options->getHiddenEnd());
        $this->assertEquals('.', $options->getDotKey());
        $this->assertEquals('~', $options->getDashKey());
        $this->assertSame($onFailCallback, $options->getOnFail());
    }
}
