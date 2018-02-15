<?php
/*
 * This file is part of sebastian/diff.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpCsFixer\Diff\v3_0;

use PHPUnit\Framework\TestCase;

/**
 * @covers PhpCsFixer\Diff\v3_0\Line
 */
final class LineTest extends TestCase
{
    /**
     * @var Line
     */
    private $line;

    protected function setUp()
    {
        $this->line = new Line;
    }

    public function testCanBeCreatedWithoutArguments()
    {
        $this->assertInstanceOf(Line::class, $this->line);
    }

    public function testTypeCanBeRetrieved()
    {
        $this->assertSame(Line::UNCHANGED, $this->line->getType());
    }

    public function testContentCanBeRetrieved()
    {
        $this->assertSame('', $this->line->getContent());
    }
}
