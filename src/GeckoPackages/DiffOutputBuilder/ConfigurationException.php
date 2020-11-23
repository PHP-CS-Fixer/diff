<?php
/*
 * This file is part of sebastian/diff.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PhpCsFixer\Diff\GeckoPackages\DiffOutputBuilder;

use Exception;

final class ConfigurationException extends \InvalidArgumentException
{
    public function __construct(
        $option,
        $expected,
        $value,
        $code = 0,
        Exception $previous = null
    ) {
        parent::__construct(
            \sprintf(
                'Option "%s" must be %s, got "%s".',
                $option,
                $expected,
                \is_object($value) ? \get_class($value) : (null === $value ? '<null>' : \gettype($value).'#'.$value)
            ),
            $code,
            $previous
        );
    }
}
