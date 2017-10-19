<?php

/*
 * This file is part of the GeckoPackages.
 *
 * (c) GeckoPackages https://github.com/GeckoPackages
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Diff\GeckoPackages\DiffOutputBuilder\Tests;

use PhpCsFixer\Diff\GeckoPackages\DiffOutputBuilder\ConfigurationException;
use PhpCsFixer\Diff\GeckoPackages\DiffOutputBuilder\UnifiedDiffOutputBuilder;
use PhpCsFixer\Diff\GeckoPackages\DiffOutputBuilder\Utils\PHPUnitPolyfill;
use PhpCsFixer\Diff\GeckoPackages\DiffOutputBuilder\Utils\UnifiedDiffAssertTrait;

/**
 * @author SpacePossum
 *
 * @internal
 */
final class UnifiedDiffOutputBuilderTest extends AbstractDiffOutputBuilderTest
{
    use UnifiedDiffAssertTrait;
    use PHPUnitPolyfill;

    /**
     * {@inheritdoc}
     */
    public function assertValidDiffFormat($diff)
    {
        $this->assertValidUnifiedDiffFormat($diff);
    }

    /**
     * {@inheritdoc}
     */
    public function provideOutputBuildingCases()
    {
        return UnifiedDiffOutputBuilderDataProvider::provideOutputBuildingCases();
    }

    /**
     * {@inheritdoc}
     */
    public function provideSample()
    {
        return UnifiedDiffOutputBuilderDataProvider::provideSample();
    }

    /**
     * @param string $expected
     * @param string $from
     * @param string $to
     *
     * @dataProvider provideBasicDiffGeneration
     */
    public function testBasicDiffGeneration($expected, $from, $to)
    {
        $diff = $this->getDiffer([
            'fromFile' => 'input.txt',
            'toFile' => 'output.txt',
        ])->diff($from, $to);

        $this->assertValidDiffFormat($diff);
        $this->assertSame($expected, $diff);
    }

    public function provideBasicDiffGeneration()
    {
        return UnifiedDiffOutputBuilderDataProvider::provideBasicDiffGeneration();
    }

    /**
     * @param string $expected
     * @param string $from
     * @param string $to
     * @param array  $config
     *
     * @dataProvider provideConfiguredDiffGeneration
     */
    public function testConfiguredDiffGeneration($expected, $from, $to, array $config = [])
    {
        $diff = $this->getDiffer(\array_merge([
            'fromFile' => 'input.txt',
            'toFile' => 'output.txt',
        ], $config))->diff($from, $to);

        $this->assertValidDiffFormat($diff);
        $this->assertSame($expected, $diff);
    }

    public function provideConfiguredDiffGeneration()
    {
        return [
            [
                '',
                "1\n2",
                "1\n2",
            ],
            [
                '',
                "1\n",
                "1\n",
            ],
            [
'--- input.txt
+++ output.txt
@@ -4 +4 @@
-X
+4
',
                "1\n2\n3\nX\n5\n6\n7\n8\n9\n0\n",
                "1\n2\n3\n4\n5\n6\n7\n8\n9\n0\n",
                [
                    'contextLines' => 0,
                ],
            ],
            [
'--- input.txt
+++ output.txt
@@ -3,3 +3,3 @@
 3
-X
+4
 5
',
                "1\n2\n3\nX\n5\n6\n7\n8\n9\n0\n",
                "1\n2\n3\n4\n5\n6\n7\n8\n9\n0\n",
                [
                    'contextLines' => 1,
                ],
            ],
            [
'--- input.txt
+++ output.txt
@@ -1,10 +1,10 @@
 1
 2
 3
-X
+4
 5
 6
 7
 8
 9
 0
',
                "1\n2\n3\nX\n5\n6\n7\n8\n9\n0\n",
                "1\n2\n3\n4\n5\n6\n7\n8\n9\n0\n",
                [
                    'contextLines' => 999,
                ],
            ],
            [
'--- input.txt
+++ output.txt
@@ -1,0 +1,2 @@
+
+A
',
                '',
                "\nA\n",
            ],
            [
'--- input.txt
+++ output.txt
@@ -1,2 +1,0 @@
-
-A
',
                "\nA\n",
                '',
            ],
            [
                '--- input.txt
+++ output.txt
@@ -1,5 +1,5 @@
 1
-X
+2
 3
-Y
+4
 5
@@ -8,3 +8,3 @@
 8
-X
+9
 0
',
                "1\nX\n3\nY\n5\n6\n7\n8\nX\n0\n",
                "1\n2\n3\n4\n5\n6\n7\n8\n9\n0\n",
                [
                    'commonLineThreshold' => 2,
                    'contextLines' => 1,
                ],
            ],
            [
                '--- input.txt
+++ output.txt
@@ -2 +2 @@
-X
+2
@@ -4 +4 @@
-Y
+4
@@ -9 +9 @@
-X
+9
',
                "1\nX\n3\nY\n5\n6\n7\n8\nX\n0\n",
                "1\n2\n3\n4\n5\n6\n7\n8\n9\n0\n",
                [
                    'commonLineThreshold' => 1,
                    'contextLines' => 0,
                ],
            ],
        ];
    }

    public function testReUseBuilder()
    {
        $differ = $this->getDiffer([
            'fromFile' => 'input.txt',
            'toFile' => 'output.txt',
        ]);

        $diff = $differ->diff("A\nB\n", "A\nX\n");
        $this->assertSame(
'--- input.txt
+++ output.txt
@@ -1,2 +1,2 @@
 A
-B
+X
',
            $diff
        );

        $diff = $differ->diff("A\n", "A\n");
        $this->assertSame(
            '',
            $diff
        );
    }

    public function testEmptyDiff()
    {
        $builder = new UnifiedDiffOutputBuilder([
            'fromFile' => 'input.txt',
            'toFile' => 'output.txt',
        ]);

        $this->assertSame(
            '',
            $builder->getDiff([])
        );
    }

    /**
     * @param array  $options
     * @param string $message
     *
     * @dataProvider provideInvalidConfiguration
     */
    public function testInvalidConfiguration(array $options, $message)
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessageRegExp(\sprintf('#^%s$#', \preg_quote($message, '#')));

        new UnifiedDiffOutputBuilder($options);
    }

    public function provideInvalidConfiguration()
    {
        $time = \time();

        return [
            [
                ['collapseRanges' => 1],
                'Option "collapseRanges" must be a bool, got "integer#1".',
            ],
            [
                ['contextLines' => 'a'],
                'Option "contextLines" must be an int >= 0, got "string#a".',
            ],
            [
                ['commonLineThreshold' => -2],
                'Option "commonLineThreshold" must be an int > 0, got "integer#-2".',
            ],
            [
                ['commonLineThreshold' => 0],
                'Option "commonLineThreshold" must be an int > 0, got "integer#0".',
            ],
            [
                ['fromFile' => new \SplFileInfo(__FILE__)],
                'Option "fromFile" must be a string, got "SplFileInfo".',
            ],
            [
                ['fromFile' => null],
                'Option "fromFile" must be a string, got "<null>".',
            ],
            [
                [
                    'fromFile' => __FILE__,
                    'toFile' => 1,
                ],
                'Option "toFile" must be a string, got "integer#1".',
            ],
            [
                [
                    'fromFile' => __FILE__,
                    'toFile' => __FILE__,
                    'toFileDate' => $time,
                ],
                'Option "toFileDate" must be a string or <null>, got "integer#'.$time.'".',
            ],
            [
                [],
                'Option "fromFile" must be a string, got "<null>".',
            ],
        ];
    }

    /**
     * @param string $expected
     * @param string $from
     * @param string $to
     * @param int    $threshold
     *
     * @dataProvider provideCommonLineThresholdCases
     */
    public function testCommonLineThreshold($expected, $from, $to, $threshold)
    {
        $diff = $this->getDiffer([
            'fromFile' => 'input.txt',
            'toFile' => 'output.txt',
            'commonLineThreshold' => $threshold,
            'contextLines' => 0,
        ])->diff($from, $to);

        $this->assertValidDiffFormat($diff);
        $this->assertSame($expected, $diff);
    }

    public function provideCommonLineThresholdCases()
    {
        return [
            [
'--- input.txt
+++ output.txt
@@ -2,3 +2,3 @@
-X
+B
 C12
-Y
+D
@@ -7 +7 @@
-X
+Z
',
                "A\nX\nC12\nY\nA\nA\nX\n",
                "A\nB\nC12\nD\nA\nA\nZ\n",
                2,
            ],
            [
'--- input.txt
+++ output.txt
@@ -2 +2 @@
-X
+B
@@ -4 +4 @@
-Y
+D
',
                "A\nX\nV\nY\n",
                "A\nB\nV\nD\n",
                1,
            ],
        ];
    }
}
