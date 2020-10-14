<?php
/*
 * This file is part of sebastian/diff.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpCsFixer\Diff\v3_0\Output;

use PHPUnit\Framework\TestCase;
use PhpCsFixer\Diff\v3_0\Differ;

/**
 * @covers PhpCsFixer\Diff\v3_0\Output\UnifiedDiffOutputBuilder
 *
 * @uses PhpCsFixer\Diff\v3_0\Differ
 * @uses PhpCsFixer\Diff\v3_0\Output\AbstractChunkOutputBuilder
 * @uses PhpCsFixer\Diff\v3_0\TimeEfficientLongestCommonSubsequenceCalculator
 */
final class UnifiedDiffOutputBuilderTest extends TestCase
{
    /**
     * @param string $expected
     * @param string $from
     * @param string $to
     * @param string $header
     *
     * @dataProvider headerProvider
     */
    public function testCustomHeaderCanBeUsed($expected, $from, $to, $header)
    {
        $differ = new Differ(new UnifiedDiffOutputBuilder($header));

        $this->assertSame(
            $expected,
            $differ->diff($from, $to)
        );
    }

    public function headerProvider()
    {
        return [
            [
                "CUSTOM HEADER\n@@ @@\n-a\n+b\n",
                'a',
                'b',
                'CUSTOM HEADER',
            ],
            [
                "CUSTOM HEADER\n@@ @@\n-a\n+b\n",
                'a',
                'b',
                "CUSTOM HEADER\n",
            ],
            [
                "CUSTOM HEADER\n\n@@ @@\n-a\n+b\n",
                'a',
                'b',
                "CUSTOM HEADER\n\n",
            ],
            [
                "@@ @@\n-a\n+b\n",
                'a',
                'b',
                '',
            ],
        ];
    }

    /**
     * @param string $expected
     * @param string $from
     * @param string $to
     *
     * @dataProvider provideDiffWithLineNumbers
     */
    public function testDiffWithLineNumbers($expected, $from, $to)
    {
        $differ = new Differ(new UnifiedDiffOutputBuilder("--- Original\n+++ New\n", true));
        $this->assertSame($expected, $differ->diff($from, $to));
    }

    public function provideDiffWithLineNumbers()
    {
        return UnifiedDiffOutputBuilderDataProvider::provideDiffWithLineNumbers();
    }

    /**
     * @param string $from
     * @param string $to
     *
     * @dataProvider provideStringsThatAreTheSame
     */
    public function testEmptyDiffProducesEmptyOutput($from, $to)
    {
        $differ = new Differ(new UnifiedDiffOutputBuilder('', false));
        $output = $differ->diff($from, $to);
        $this->assertEmpty($output);
    }

    public function provideStringsThatAreTheSame()
    {
        return [
            ['', ''],
            ['a', 'a'],
            ['these strings are the same', 'these strings are the same'],
            ["\n", "\n"],
            ["multi-line strings\nare the same", "multi-line strings\nare the same"]
        ];
    }
}
