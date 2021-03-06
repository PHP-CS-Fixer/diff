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
use PhpCsFixer\Diff\Output\UnifiedDiffOutputBuilder;

/**
 * @covers PhpCsFixer\Diff\Differ
 * @covers PhpCsFixer\Diff\Output\UnifiedDiffOutputBuilder
 *
 * @uses PhpCsFixer\Diff\MemoryEfficientLongestCommonSubsequenceCalculator
 * @uses PhpCsFixer\Diff\TimeEfficientLongestCommonSubsequenceCalculator
 * @uses PhpCsFixer\Diff\Output\AbstractChunkOutputBuilder
 */
final class DifferTest extends TestCase
{
    /**
     * @var Differ
     */
    private $differ;

    protected function setUp()
    {
        $this->differ = new Differ;
    }

    /**
     * @param array        $expected
     * @param array|string $from
     * @param array|string $to
     *
     * @dataProvider arrayProvider
     */
    public function testArrayRepresentationOfDiffCanBeRenderedUsingTimeEfficientLcsImplementation(array $expected, $from, $to)
    {
        $this->assertSame($expected, $this->differ->diffToArray($from, $to, new TimeEfficientLongestCommonSubsequenceCalculator));
    }

    /**
     * @param string $expected
     * @param string $from
     * @param string $to
     *
     * @dataProvider textProvider
     */
    public function testTextRepresentationOfDiffCanBeRenderedUsingTimeEfficientLcsImplementation($expected, $from, $to)
    {
        $this->assertSame($expected, $this->differ->diff($from, $to, new TimeEfficientLongestCommonSubsequenceCalculator));
    }

    /**
     * @param array        $expected
     * @param array|string $from
     * @param array|string $to
     *
     * @dataProvider arrayProvider
     */
    public function testArrayRepresentationOfDiffCanBeRenderedUsingMemoryEfficientLcsImplementation(array $expected, $from, $to)
    {
        $this->assertSame($expected, $this->differ->diffToArray($from, $to, new MemoryEfficientLongestCommonSubsequenceCalculator));
    }

    /**
     * @param string $expected
     * @param string $from
     * @param string $to
     *
     * @dataProvider textProvider
     */
    public function testTextRepresentationOfDiffCanBeRenderedUsingMemoryEfficientLcsImplementation($expected, $from, $to)
    {
        $this->assertSame($expected, $this->differ->diff($from, $to, new MemoryEfficientLongestCommonSubsequenceCalculator));
    }

    public function testTypesOtherThanArrayAndStringCanBePassed()
    {
        $this->assertSame(
            "--- Original\n+++ New\n@@ @@\n-1\n+2\n",
            $this->differ->diff(1, 2)
        );
    }

    public function testArrayDiffs()
    {
        $this->assertSame(
            '--- Original
+++ New
@@ @@
-one
+two
',
            $this->differ->diff(['one'], ['two'])
        );
    }

    public function arrayProvider()
    {
        return [
            [
                [
                    ['a', Differ::REMOVED],
                    ['b', Differ::ADDED],
                ],
                'a',
                'b',
            ],
            [
                [
                    ['ba', Differ::REMOVED],
                    ['bc', Differ::ADDED],
                ],
                'ba',
                'bc',
            ],
            [
                [
                    ['ab', Differ::REMOVED],
                    ['cb', Differ::ADDED],
                ],
                'ab',
                'cb',
            ],
            [
                [
                    ['abc', Differ::REMOVED],
                    ['adc', Differ::ADDED],
                ],
                'abc',
                'adc',
            ],
            [
                [
                    ['ab', Differ::REMOVED],
                    ['abc', Differ::ADDED],
                ],
                'ab',
                'abc',
            ],
            [
                [
                    ['bc', Differ::REMOVED],
                    ['abc', Differ::ADDED],
                ],
                'bc',
                'abc',
            ],
            [
                [
                    ['abc', Differ::REMOVED],
                    ['abbc', Differ::ADDED],
                ],
                'abc',
                'abbc',
            ],
            [
                [
                    ['abcdde', Differ::REMOVED],
                    ['abcde', Differ::ADDED],
                ],
                'abcdde',
                'abcde',
            ],
            'same start' => [
                [
                    [17, Differ::OLD],
                    ['b', Differ::REMOVED],
                    ['d', Differ::ADDED],
                ],
                [30 => 17, 'a' => 'b'],
                [30 => 17, 'c' => 'd'],
            ],
            'same end' => [
                [
                    [1, Differ::REMOVED],
                    [2, Differ::ADDED],
                    ['b', Differ::OLD],
                ],
                [1 => 1, 'a' => 'b'],
                [1 => 2, 'a' => 'b'],
            ],
            'same start (2), same end (1)' => [
                [
                    [17, Differ::OLD],
                    [2, Differ::OLD],
                    [4, Differ::REMOVED],
                    ['a', Differ::ADDED],
                    [5, Differ::ADDED],
                    ['x', Differ::OLD],
                ],
                [30 => 17, 1 => 2, 2 => 4, 'z' => 'x'],
                [30 => 17, 1 => 2, 3 => 'a', 2 => 5, 'z' => 'x'],
            ],
            'same' => [
                [
                    ['x', Differ::OLD],
                ],
                ['z' => 'x'],
                ['z' => 'x'],
            ],
            'diff' => [
                [
                    ['y', Differ::REMOVED],
                    ['x', Differ::ADDED],
                ],
                ['x' => 'y'],
                ['z' => 'x'],
            ],
            'diff 2' => [
                [
                    ['y', Differ::REMOVED],
                    ['b', Differ::REMOVED],
                    ['x', Differ::ADDED],
                    ['d', Differ::ADDED],
                ],
                ['x' => 'y', 'a' => 'b'],
                ['z' => 'x', 'c' => 'd'],
            ],
            'test line diff detection' => [
                [
                    [
                        "#Warnings contain different line endings!\n",
                        Differ::DIFF_LINE_END_WARNING,
                    ],
                    [
                        "<?php\r\n",
                        Differ::REMOVED,
                    ],
                    [
                        "<?php\n",
                        Differ::ADDED,
                    ],
                ],
                "<?php\r\n",
                "<?php\n",
            ],
            'test line diff detection in array input' => [
                [
                    [
                        "#Warnings contain different line endings!\n",
                        Differ::DIFF_LINE_END_WARNING,
                    ],
                    [
                        "<?php\r\n",
                        Differ::REMOVED,
                    ],
                    [
                        "<?php\n",
                        Differ::ADDED,
                    ],
                ],
                ["<?php\r\n"],
                ["<?php\n"],
            ],
        ];
    }

    public function textProvider()
    {
        return [
            [
                "--- Original\n+++ New\n@@ @@\n-a\n+b\n",
                'a',
                'b',
            ],
            [
                "--- Original\n+++ New\n@@ @@\n-A\n+A1\n B\n",
                "A\nB",
                "A1\nB",
            ],
            [
                <<<EOF
--- Original
+++ New
@@ @@
 a
-b
+p
 c
 d
 e
@@ @@
 g
 h
 i
-j
+w
 k

EOF
            ,
                "a\nb\nc\nd\ne\nf\ng\nh\ni\nj\nk\n",
                "a\np\nc\nd\ne\nf\ng\nh\ni\nw\nk\n",
            ],
            [
                <<<EOF
--- Original
+++ New
@@ @@
-A
+B
 1
 2
 3

EOF
            ,
                "A\n1\n2\n3\n4\n5\n6\n1\n1\n1\n1\n1\n1\n1\n1\n1\n1\n1\n1\n1\n1\n1\n",
                "B\n1\n2\n3\n4\n5\n6\n1\n1\n1\n1\n1\n1\n1\n1\n1\n1\n1\n1\n1\n1\n1\n",
            ],
            [
                "--- Original\n+++ New\n@@ @@\n #Warnings contain different line endings!\n-<?php\r\n+<?php\n A\n",
                "<?php\r\nA\n",
                "<?php\nA\n",
            ],
            [
                "--- Original\n+++ New\n@@ @@\n #Warnings contain different line endings!\n-a\r\n+\n+c\r\n",
                "a\r\n",
                "\nc\r",
            ],
        ];
    }

    public function testDiffToArrayInvalidFromType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('#^"from" must be an array or string\.$#');

        $this->differ->diffToArray(null, '');
    }

    public function testDiffInvalidToType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('#^"to" must be an array or string\.$#');

        $this->differ->diffToArray('', new \stdClass);
    }

    /**
     * @param array  $expected
     * @param string $input
     *
     * @dataProvider provideSplitStringByLinesCases
     */
    public function testSplitStringByLines(array $expected, $input)
    {
        $reflection = new \ReflectionObject($this->differ);
        $method     = $reflection->getMethod('splitStringByLines');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invoke($this->differ, $input));
    }

    public function provideSplitStringByLinesCases()
    {
        return [
            [
                [],
                '',
            ],
            [
                ['a'],
                'a',
            ],
            [
                ["a\n"],
                "a\n",
            ],
            [
                ["a\r"],
                "a\r",
            ],
            [
                ["a\r\n"],
                "a\r\n",
            ],
            [
                ["\n"],
                "\n",
            ],
            [
                ["\r"],
                "\r",
            ],
            [
                ["\r\n"],
                "\r\n",
            ],
            [
                [
                    "A\n",
                    "B\n",
                    "\n",
                    "C\n",
                ],
                "A\nB\n\nC\n",
            ],
            [
                [
                    "A\r\n",
                    "B\n",
                    "\n",
                    "C\r",
                ],
                "A\r\nB\n\nC\r",
            ],
            [
                [
                    "\n",
                    "A\r\n",
                    "B\n",
                    "\n",
                    'C',
                ],
                "\nA\r\nB\n\nC",
            ],
        ];
    }

    public function testConstructorNull()
    {
        $this->assertAttributeInstanceOf(
            UnifiedDiffOutputBuilder::class,
            'outputBuilder',
            new Differ(null)
        );
    }

    public function testConstructorString()
    {
        $this->assertAttributeInstanceOf(
            UnifiedDiffOutputBuilder::class,
            'outputBuilder',
            new Differ("--- Original\n+++ New\n")
        );
    }

    public function testConstructorInvalidArgInt()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/^Expected builder to be an instance of DiffOutputBuilderInterface, <null> or a string, got integer "1"\.$/');

        new Differ(1);
    }

    public function testConstructorInvalidArgObject()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/^Expected builder to be an instance of DiffOutputBuilderInterface, <null> or a string, got instance of "SplFileInfo"\.$/');

        new Differ(new \SplFileInfo(__FILE__));
    }
}
