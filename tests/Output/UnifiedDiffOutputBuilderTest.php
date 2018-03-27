<?php
/*
 * This file is part of sebastian/diff.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpCsFixer\Diff\Output;

use PHPUnit\Framework\TestCase;
use PhpCsFixer\Diff\Differ;

/**
 * @covers PhpCsFixer\Diff\Output\UnifiedDiffOutputBuilder
 *
 * @uses PhpCsFixer\Diff\Differ
 * @uses PhpCsFixer\Diff\Output\AbstractChunkOutputBuilder
 * @uses PhpCsFixer\Diff\TimeEfficientLongestCommonSubsequenceCalculator
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
}
