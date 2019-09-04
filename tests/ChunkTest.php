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
 * @covers PhpCsFixer\Diff\Chunk
 *
 * @uses PhpCsFixer\Diff\Line
 */
final class ChunkTest extends TestCase
{
    /**
     * @var Chunk
     */
    private $chunk;

    protected function setUp()
    {
        $this->chunk = new Chunk;
    }

    public function testHasInitiallyNoLines()
    {
        $this->assertSame([], $this->chunk->getLines());
    }

    public function testCanBeCreatedWithoutArguments()
    {
        $this->assertInstanceOf(Chunk::class, $this->chunk);
    }

    public function testStartCanBeRetrieved()
    {
        $this->assertSame(0, $this->chunk->getStart());
    }

    public function testStartRangeCanBeRetrieved()
    {
        $this->assertSame(1, $this->chunk->getStartRange());
    }

    public function testEndCanBeRetrieved()
    {
        $this->assertSame(0, $this->chunk->getEnd());
    }

    public function testEndRangeCanBeRetrieved()
    {
        $this->assertSame(1, $this->chunk->getEndRange());
    }

    public function testLinesCanBeRetrieved()
    {
        $this->assertSame([], $this->chunk->getLines());
    }

    public function testLinesCanBeSet()
    {
        $lines = [new Line(Line::ADDED, 'added'), new Line(Line::REMOVED, 'removed')];

        $this->chunk->setLines($lines);

        $this->assertSame($lines, $this->chunk->getLines());
    }
}
