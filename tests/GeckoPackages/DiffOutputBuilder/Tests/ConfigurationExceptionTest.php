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

use PhpCsFixer\Diff\GeckoPackages\DiffOutputBuilder\ConfigurationException;
use PHPUnit\Framework\TestCase;

/**
 * @author SpacePossum
 *
 * @internal
 */
final class ConfigurationExceptionTest extends TestCase
{
    public function testConstruct()
    {
        $e = new ConfigurationException('test', 'A', 'B');

        $this->assertSame(0, $e->getCode());
        $this->assertNull($e->getPrevious());
        $this->assertSame('Option "test" must be A, got "string#B".', $e->getMessage());
    }
}
