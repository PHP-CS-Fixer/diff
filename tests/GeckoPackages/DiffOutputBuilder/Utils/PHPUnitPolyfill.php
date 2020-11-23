<?php
/*
 * This file is part of sebastian/diff.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpCsFixer\Diff\GeckoPackages\DiffOutputBuilder\Utils;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_TestCase;

if (\method_exists(TestCase::class, 'expectException')) {
    trait PHPUnitPolyfill
    {
    }
} else {
    /**
     * @author SpacePossum
     *
     * @internal
     */
    trait PHPUnitPolyfill
    {
        public function expectException($exception)
        {
            $this->wellYeahShipIt('expectedException', $exception);
        }

        public function expectExceptionMessageRegExp($messageRegExp)
        {
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
}
