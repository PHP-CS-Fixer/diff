<?php
/*
 * This file is part of sebastian/diff.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpCsFixer\Diff\GeckoPackages\DiffOutputBuilder\Tests;

use PhpCsFixer\Diff\v2_0\Differ;
use PhpCsFixer\Diff\v2_0\Output\DiffOutputBuilderInterface;
use PHPUnit\Framework\TestCase;

/**
 * @author SpacePossum
 *
 * @coversNothing
 *
 * @internal
 */
abstract class AbstractDiffOutputBuilderTest extends TestCase
{
    /**
     * @var string
     */
    private $differClass;

    abstract public function provideOutputBuildingCases();

    abstract public function provideSample();

    abstract public function assertValidDiffFormat($diff);

    /**
     * @param string $expected
     * @param string $from
     * @param string $to
     * @param array  $options
     *
     * @dataProvider provideOutputBuildingCases
     */
    public function testOutputBuilding($expected, $from, $to, array $options)
    {
        $diff = $this->getDiffer($options)->diff($from, $to);

        $this->assertValidDiffFormat($diff);
        $this->assertSame($expected, $diff);
    }

    /**
     * @param string $expected
     * @param string $from
     * @param string $to
     * @param array  $options
     *
     * @dataProvider provideSample
     */
    public function testSample($expected, $from, $to, array $options)
    {
        $diff = $this->getDiffer($options)->diff($from, $to);

        $this->assertValidDiffFormat($diff);
        $this->assertSame($expected, $diff);
    }

    /**
     * Returns a new instance of a Differ with a new instance of the class (DiffOutputBuilderInterface) under test.
     *
     * @param array $options
     *
     * @return Differ
     */
    protected function getDiffer(array $options = [])
    {
        if (null === $this->differClass) {
            // map test class name (child) back to the Differ being tested.
            $childClass = \get_class($this);
            $differClass = 'PhpCsFixer\\Diff\\GeckoPackages\\DiffOutputBuilder\\'.\substr($childClass, \strrpos($childClass, '\\') + 1, -4);

            // basic tests: class must exist...
            $this->assertTrue(\class_exists($differClass));

            // ...and must implement DiffOutputBuilderInterface
            $implements = \class_implements($differClass);
            $this->assertInternalType('array', $implements);
            $this->assertArrayHasKey(DiffOutputBuilderInterface::class, $implements);

            $this->differClass = $differClass;
        }

        return new Differ(new $this->differClass($options));
    }
}
