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
 * @covers PhpCsFixer\Diff\v3_0\Output\DiffOnlyOutputBuilder
 *
 * @uses PhpCsFixer\Diff\v3_0\Differ
 * @uses PhpCsFixer\Diff\v3_0\TimeEfficientLongestCommonSubsequenceCalculator
 */
final class DiffOnlyOutputBuilderTest extends TestCase
{
    /**
     * @param string $expected
     * @param string $from
     * @param string $to
     * @param string $header
     *
     * @dataProvider textForNoNonDiffLinesProvider
     */
    public function testDiffDoNotShowNonDiffLines($expected, $from, $to, $header = '')
    {
        $differ = new Differ(new DiffOnlyOutputBuilder($header));

        $this->assertSame($expected, $differ->diff($from, $to));
    }

    public function textForNoNonDiffLinesProvider()
    {
        return [
            [
                " #Warnings contain different line endings!\n-A\r\n+B\n",
                "A\r\n",
                "B\n",
            ],
            [
                "-A\n+B\n",
                "\nA",
                "\nB",
            ],
            [
                '',
                'a',
                'a',
            ],
            [
                "-A\n+C\n",
                "A\n\n\nB",
                "C\n\n\nB",
            ],
            [
                "header\n",
                'a',
                'a',
                'header',
            ],
            [
                "header\n",
                'a',
                'a',
                "header\n",
            ],
        ];
    }
}
