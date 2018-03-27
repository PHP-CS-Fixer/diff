<?php
/*
 * This file is part of sebastian/diff.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpCsFixer\Diff;

use PHPUnit\Framework\TestCase;

/**
 * @covers PhpCsFixer\Diff\InvalidArgumentException
 */
final class InvalidArgumentExceptionTest extends TestCase
{
    public function testInvalidArgumentException()
    {
        $previousException = new \LogicException();
        $message           = 'test';
        $code              = 123;

        $exception = new InvalidArgumentException($message, $code, $previousException);

        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertSame($previousException, $exception->getPrevious());
    }
}
