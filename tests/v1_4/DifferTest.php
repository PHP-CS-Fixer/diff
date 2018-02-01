<?php
/*
 * This file is part of sebastian/diff.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpCsFixer\Diff\v1_4;

use PhpCsFixer\Diff\v1_4\LCS\MemoryEfficientImplementation;
use PhpCsFixer\Diff\v1_4\LCS\TimeEfficientImplementation;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PhpCsFixer\Diff\v1_4\Differ
 *
 * @uses \PhpCsFixer\Diff\v1_4\LCS\MemoryEfficientImplementation
 * @uses \PhpCsFixer\Diff\v1_4\LCS\TimeEfficientImplementation
 * @uses \PhpCsFixer\Diff\v1_4\Chunk
 * @uses \PhpCsFixer\Diff\v1_4\Diff
 * @uses \PhpCsFixer\Diff\v1_4\Line
 * @uses \PhpCsFixer\Diff\v1_4\Parser
 */
class DifferTest extends TestCase
{
    const REMOVED = 2;
    const ADDED   = 1;
    const OLD     = 0;

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
     * @param string|array $from
     * @param string|array $to
     * @dataProvider arrayProvider
     */
    public function testArrayRepresentationOfDiffCanBeRenderedUsingTimeEfficientLcsImplementation(array $expected, $from, $to)
    {
        $this->assertEquals($expected, $this->differ->diffToArray($from, $to, new TimeEfficientImplementation));
    }

    /**
     * @param string $expected
     * @param string $from
     * @param string $to
     * @dataProvider textProvider
     */
    public function testTextRepresentationOfDiffCanBeRenderedUsingTimeEfficientLcsImplementation($expected, $from, $to)
    {
        $this->assertEquals($expected, $this->differ->diff($from, $to, new TimeEfficientImplementation));
    }

    /**
     * @param array        $expected
     * @param string|array $from
     * @param string|array $to
     * @dataProvider arrayProvider
     */
    public function testArrayRepresentationOfDiffCanBeRenderedUsingMemoryEfficientLcsImplementation(array $expected, $from, $to)
    {
        $this->assertEquals($expected, $this->differ->diffToArray($from, $to, new MemoryEfficientImplementation));
    }

    /**
     * @param string $expected
     * @param string $from
     * @param string $to
     * @dataProvider textProvider
     */
    public function testTextRepresentationOfDiffCanBeRenderedUsingMemoryEfficientLcsImplementation($expected, $from, $to)
    {
        $this->assertEquals($expected, $this->differ->diff($from, $to, new MemoryEfficientImplementation));
    }

    public function testCustomHeaderCanBeUsed()
    {
        $differ = new Differ('CUSTOM HEADER');

        $this->assertEquals(
            "CUSTOM HEADER@@ @@\n-a\n+b\n",
            $differ->diff('a', 'b')
        );
    }

    public function testTypesOtherThanArrayAndStringCanBePassed()
    {
        $this->assertEquals(
            "--- Original\n+++ New\n@@ @@\n-1\n+2\n",
            $this->differ->diff(1, 2)
        );
    }

    public function arrayProvider()
    {
        return array(
            array(
                array(
                    array('a', self::REMOVED),
                    array('b', self::ADDED)
                ),
                'a',
                'b'
            ),
            array(
                array(
                    array('ba', self::REMOVED),
                    array('bc', self::ADDED)
                ),
                'ba',
                'bc'
            ),
            array(
                array(
                    array('ab', self::REMOVED),
                    array('cb', self::ADDED)
                ),
                'ab',
                'cb'
            ),
            array(
                array(
                    array('abc', self::REMOVED),
                    array('adc', self::ADDED)
                ),
                'abc',
                'adc'
            ),
            array(
                array(
                    array('ab', self::REMOVED),
                    array('abc', self::ADDED)
                ),
                'ab',
                'abc'
            ),
            array(
                array(
                    array('bc', self::REMOVED),
                    array('abc', self::ADDED)
                ),
                'bc',
                'abc'
            ),
            array(
                array(
                    array('abc', self::REMOVED),
                    array('abbc', self::ADDED)
                ),
                'abc',
                'abbc'
            ),
            array(
                array(
                    array('abcdde', self::REMOVED),
                    array('abcde', self::ADDED)
                ),
                'abcdde',
                'abcde'
            ),
            'same start' => array(
                array(
                    array(17, self::OLD),
                    array('b', self::REMOVED),
                    array('d', self::ADDED),
                ),
                array(30 => 17, 'a' => 'b'),
                array(30 => 17, 'c' => 'd'),
            ),
            'same end' => array(
                array(
                    array(1, self::REMOVED),
                    array(2, self::ADDED),
                    array('b', self::OLD),
                ),
                array(1 => 1, 'a' => 'b'),
                array(1 => 2, 'a' => 'b'),
            ),
            'same start (2), same end (1)' => array(
                array(
                    array(17, self::OLD),
                    array(2, self::OLD),
                    array(4, self::REMOVED),
                    array('a', self::ADDED),
                    array(5, self::ADDED),
                    array('x', self::OLD),
                ),
                array(30 => 17, 1 => 2, 2 => 4, 'z' => 'x'),
                array(30 => 17, 1 => 2, 3 => 'a', 2 => 5, 'z' => 'x'),
            ),
            'same' => array(
                array(
                    array('x', self::OLD),
                ),
                array('z' => 'x'),
                array('z' => 'x'),
            ),
            'diff' => array(
                array(
                    array('y', self::REMOVED),
                    array('x', self::ADDED),
                ),
                array('x' => 'y'),
                array('z' => 'x'),
            ),
            'diff 2' => array(
                array(
                    array('y', self::REMOVED),
                    array('b', self::REMOVED),
                    array('x', self::ADDED),
                    array('d', self::ADDED),
                ),
                array('x' => 'y', 'a' => 'b'),
                array('z' => 'x', 'c' => 'd'),
            ),
            'test line diff detection' => array(
                array(
                    array(
                        '#Warnings contain different line endings!',
                        self::OLD,
                    ),
                    array(
                        '<?php',
                        self::OLD,
                    ),
                    array(
                        '',
                        self::OLD,
                    ),
                ),
                "<?php\r\n",
                "<?php\n"
            )
        );
    }

    public function textProvider()
    {
        return array(
            array(
                "--- Original\n+++ New\n@@ @@\n-a\n+b\n",
                'a',
                'b'
            ),
            array(
                "--- Original\n+++ New\n@@ @@\n-ba\n+bc\n",
                'ba',
                'bc'
            ),
            array(
                "--- Original\n+++ New\n@@ @@\n-ab\n+cb\n",
                'ab',
                'cb'
            ),
            array(
                "--- Original\n+++ New\n@@ @@\n-abc\n+adc\n",
                'abc',
                'adc'
            ),
            array(
                "--- Original\n+++ New\n@@ @@\n-ab\n+abc\n",
                'ab',
                'abc'
            ),
            array(
                "--- Original\n+++ New\n@@ @@\n-bc\n+abc\n",
                'bc',
                'abc'
            ),
            array(
                "--- Original\n+++ New\n@@ @@\n-abc\n+abbc\n",
                'abc',
                'abbc'
            ),
            array(
                "--- Original\n+++ New\n@@ @@\n-abcdde\n+abcde\n",
                'abcdde',
                'abcde'
            ),
            array(
                "--- Original\n+++ New\n@@ @@\n-A\n+A1\n B\n",
                "A\nB",
                "A1\nB",
            ),
            array(
                <<<EOF
--- Original
+++ New
@@ @@
 a
-b
+p
@@ @@
 i
-j
+w
 k

EOF
            ,
                "a\nb\nc\nd\ne\nf\ng\nh\ni\nj\nk",
                "a\np\nc\nd\ne\nf\ng\nh\ni\nw\nk",
            ),
            array(
                <<<EOF
--- Original
+++ New
@@ @@
 a
-b
+p
@@ @@
 i
-j
+w
 k

EOF
                ,
                "a\nb\nc\nd\ne\nf\ng\nh\ni\nj\nk",
                "a\np\nc\nd\ne\nf\ng\nh\ni\nw\nk",
            ),
        );
    }

    /**
     * @param string $expected
     * @param string $from
     * @param string $to
     * @dataProvider textForNoNonDiffLinesProvider
     */
    public function testDiffDoNotShowNonDiffLines($expected, $from, $to)
    {
        $differ = new Differ('', false);
        $this->assertSame($expected, $differ->diff($from, $to));
    }

    public function textForNoNonDiffLinesProvider()
    {
        return array(
            array(
                '', 'a', 'a'
            ),
            array(
                "-A\n+C\n",
                "A\n\n\nB",
                "C\n\n\nB",
            ),
        );
    }

    /**
     * @requires PHPUnit 5.7
     */
    public function testDiffToArrayInvalidFromType()
    {
        $differ = new Differ;

        $this->expectException('\InvalidArgumentException');
        $this->expectExceptionMessageRegExp('#^"from" must be an array or string\.$#');

        $differ->diffToArray(null, '');
    }

    /**
     * @requires PHPUnit 5.7
     */
    public function testDiffInvalidToType()
    {
        $differ = new Differ;

        $this->expectException('\InvalidArgumentException');
        $this->expectExceptionMessageRegExp('#^"to" must be an array or string\.$#');

        $differ->diffToArray('', new \stdClass);
    }
}
