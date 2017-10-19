<?php

/*
 * This file is part of the GeckoPackages.
 *
 * (c) GeckoPackages https://github.com/GeckoPackages
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Diff\GeckoPackages\DiffOutputBuilder\Utils;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_TestCase;

/**
 * @author SpacePossum
 *
 * @internal
 */
trait PHPUnitPolyfill
{
    public function expectException($exception)
    {
        if (\method_exists(TestCase::class, 'expectException')) {
            return parent::expectException($exception);
        }

        $this->wellYeahShipIt('expectedException', $exception);
    }

    public function expectExceptionMessageRegExp($messageRegExp)
    {
        if (\method_exists(TestCase::class, 'expectExceptionMessageRegExp')) {
            return parent::expectExceptionMessageRegExp($messageRegExp);
        }

        $this->wellYeahShipIt('expectedExceptionMessageRegExp', $messageRegExp);
    }

    private function wellYeahShipIt($key, $value)
    {
        $self = new \ReflectionClass(PHPUnit_Framework_TestCase::class);
        $property = $self->getProperty($key);
        $property->setAccessible(true);
        $property->setValue($this, $value);
    }
}
