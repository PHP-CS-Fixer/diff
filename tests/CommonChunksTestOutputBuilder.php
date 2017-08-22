<?php
/*
 * This file is part of sebastian/diff.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpCsFixer\Diff\Tests;

use PhpCsFixer\Diff\Output\AbstractChunkOutputBuilder;

/**
 * @author SpacePossum
 * @internal
 */
final class CommonChunksTestOutputBuilder extends AbstractChunkOutputBuilder
{
    public function getDiff(array $diff)
    {
        return '';
    }

    public function getChunks(array $diff, $lineThreshold)
    {
        return $this->getCommonChunks($diff, $lineThreshold);
    }
}
