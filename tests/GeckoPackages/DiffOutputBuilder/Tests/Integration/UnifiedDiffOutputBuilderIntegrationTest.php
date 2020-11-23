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

use PhpCsFixer\Diff\GeckoPackages\DiffOutputBuilder\UnifiedDiffOutputBuilder;
use PhpCsFixer\Diff\GeckoPackages\DiffOutputBuilder\Utils\UnifiedDiffAssertTrait;
use PhpCsFixer\Diff\v2_0\Differ;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * @author SpacePossum
 *
 * @requires OS Linux
 *
 * @coversNothing
 *
 * @internal
 */
final class UnifiedDiffOutputBuilderIntegrationTest extends TestCase
{
    use UnifiedDiffAssertTrait;

    private $dir;

    private $fileFrom;

    private $fileTo;

    private $filePatch;

    protected function setUp()
    {
        $this->dir = __DIR__.'/out/';
        $this->fileFrom = $this->dir.'from.txt';
        $this->fileTo = $this->dir.'to.txt';
        $this->filePatch = $this->dir.'diff.patch';

        $this->cleanUpTempFiles();
    }

    /**
     * Integration test
     *
     * - get a file pair
     * - create a `diff` between the files
     * - test applying the diff using `git apply`
     * - test applying the diff using `patch`
     *
     * @param string $fileFrom
     * @param string $fileTo
     *
     * @dataProvider provideFilePairs
     */
    public function testIntegrationUsingPHPFileInVendor($fileFrom, $fileTo)
    {
        $from = @\file_get_contents($fileFrom);
        $this->assertInternalType('string', $from, \sprintf('Failed to read file "%s".', $fileFrom));

        $to = @\file_get_contents($fileTo);
        $this->assertInternalType('string', $to, \sprintf('Failed to read file "%s".', $fileTo));

        $diff = (new Differ(new UnifiedDiffOutputBuilder(['fromFile' => 'Original', 'toFile' => 'New'])))->diff($from, $to);

        if ('' === $diff && $from === $to) {
            // odd case: test after executing as it is more efficient than to read the files and check the contents every time
            $this->addToAssertionCount(1);

            return;
        }

        $this->assertNotSame('', $diff);
        $this->assertValidUnifiedDiffFormat($diff);
        $this->doIntegrationTest($diff, $from, $to);
    }

    /**
     * @param string $expected
     * @param string $from
     * @param string $to
     *
     * @dataProvider provideOutputBuildingCases
     * @dataProvider provideSample
     * @dataProvider provideBasicDiffGeneration
     */
    public function testIntegrationOfUnitTestCases($expected, $from, $to)
    {
        $this->doIntegrationTest($expected, $from, $to);
    }

    public function provideOutputBuildingCases()
    {
        return UnifiedDiffOutputBuilderDataProvider::provideOutputBuildingCases();
    }

    public function provideSample()
    {
        return UnifiedDiffOutputBuilderDataProvider::provideSample();
    }

    public function provideBasicDiffGeneration()
    {
        return UnifiedDiffOutputBuilderDataProvider::provideBasicDiffGeneration();
    }

    public function provideFilePairs()
    {
        $cases = [];
        $fromFile = __FILE__;
        $vendorDir = \realpath(__DIR__.'/../../../../../vendor');
        $vendorDirLength = \strlen($vendorDir);
        $fileIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($vendorDir, \RecursiveDirectoryIterator::SKIP_DOTS));

        /** @var \SplFileInfo $file */
        foreach ($fileIterator as $file) {
            if ('php' !== $file->getExtension()) {
                continue;
            }

            $toFile = $file->getPathname();
            $cases[\sprintf("Diff file:\n\"%s\"\nvs.\n\"%s\"\n", \substr(\realpath($fromFile), $vendorDirLength), \substr(\realpath($toFile), $vendorDirLength))] = [$fromFile, $toFile];
            $fromFile = $toFile;
        }

        return $cases;
    }

    /**
     * Compare diff create by builder and against one create by `diff` command.
     *
     * @param string $diff
     * @param string $from
     * @param string $to
     *
     * @dataProvider provideBasicDiffGeneration
     */
    public function testIntegrationDiffOutputBuilderVersusDiffCommand($diff, $from, $to)
    {
        $this->assertNotFalse(\file_put_contents($this->fileFrom, $from));
        $this->assertNotFalse(\file_put_contents($this->fileTo, $to));

        $p = new Process(\sprintf('diff -u %s %s', \escapeshellarg($this->fileFrom), \escapeshellarg($this->fileTo)));
        $p->run();
        $this->assertSame(1, $p->getExitCode());

        $output = $p->getOutput();

        $diffLines = \preg_split('/(.*\R)/', $diff, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $diffLines[0] = \preg_replace('#^\-\-\- .*#', '--- /'.$this->fileFrom, $diffLines[0], 1);
        $diffLines[1] = \preg_replace('#^\+\+\+ .*#', '+++ /'.$this->fileFrom, $diffLines[1], 1);
        $diff = \implode('', $diffLines);

        $outputLines = \preg_split('/(.*\R)/', $output, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $outputLines[0] = \preg_replace('#^\-\-\- .*#', '--- /'.$this->fileFrom, $outputLines[0], 1);
        $outputLines[1] = \preg_replace('#^\+\+\+ .*#', '+++ /'.$this->fileFrom, $outputLines[1], 1);
        $output = \implode('', $outputLines);

        $this->assertSame($diff, $output);
    }

    private function doIntegrationTest($diff, $from, $to)
    {
        if ('' === $diff) {
            $this->addToAssertionCount(1); // Empty diff has no integration test part.

            return;
        }

        $diffLines = \preg_split('/(.*\R)/', $diff, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $diffLines[0] = \preg_replace('#^\-\-\- .*#', '--- /'.$this->fileFrom, $diffLines[0], 1);
        $diffLines[1] = \preg_replace('#^\+\+\+ .*#', '+++ /'.$this->fileFrom, $diffLines[1], 1);
        $diff = \implode('', $diffLines);

        $this->assertNotFalse(\file_put_contents($this->fileFrom, $from));
        $this->assertNotFalse(\file_put_contents($this->filePatch, $diff));

        $command = \sprintf(
            'git --git-dir %s apply --check -v --unsafe-paths %s', // --unidiff-zero --ignore-whitespace
            \escapeshellarg($this->dir),
            \escapeshellarg($this->filePatch)
        );

        $p = new Process($command);
        $p->run();

        $this->assertTrue(
            $p->isSuccessful(),
            \sprintf(
                "Command exec. was not successful:\n\"%s\"\nOutput:\n\"%s\"\nStdErr:\n\"%s\"\nExit code %d.\n",
                $command,
                $p->getOutput(),
                $p->getErrorOutput(),
                $p->getExitCode()
            )
        );

        $command = \sprintf(
            'patch -u --verbose --posix %s < %s',
            \escapeshellarg($this->fileFrom),
            \escapeshellarg($this->filePatch)
        );

        $p = new Process($command);
        $p->run();

        $output = $p->getOutput();

        $this->assertTrue(
            $p->isSuccessful(),
            \sprintf(
                "Command exec. was not successful:\n\"%s\"\nOutput:\n\"%s\"\nStdErr:\n\"%s\"\nExit code %d.\n",
                $command,
                $output,
                $p->getErrorOutput(),
                $p->getExitCode()
            )
        );

        $this->assertStringEqualsFile(
            $this->fileFrom, $to,
            \sprintf('Patch command "%s".', $command)
        );
    }

    protected function tearDown()
    {
        $this->cleanUpTempFiles();
    }

    private function cleanUpTempFiles()
    {
        @\unlink($this->fileFrom.'.orig');
        @\unlink($this->fileFrom.'.rej');
        @\unlink($this->fileFrom);
        @\unlink($this->fileTo);
        @\unlink($this->filePatch);
    }
}
