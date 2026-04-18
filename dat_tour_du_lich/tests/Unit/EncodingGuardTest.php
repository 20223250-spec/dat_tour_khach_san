<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class EncodingGuardTest extends TestCase
{
    private const CHECK_DIRECTORIES = [
        'resources/views',
        'lang',
    ];

    private const ALLOWED_EXTENSIONS = [
        'php',
        'json',
    ];

    private const MOJIBAKE_MARKERS = [
        'Ã',
        'Ä',
        'á»',
        'áº',
        'Æ°',
        'â€™',
        'â€œ',
        'â€',
        '�',
    ];

    public function test_views_and_lang_files_do_not_contain_mojibake(): void
    {
        $projectRoot = dirname(__DIR__, 2);
        $issues = [];

        foreach (self::CHECK_DIRECTORIES as $directory) {
            $absoluteDirectory = $projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $directory);

            if (!is_dir($absoluteDirectory)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($absoluteDirectory, RecursiveDirectoryIterator::SKIP_DOTS)
            );

            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                if (!$file->isFile()) {
                    continue;
                }

                if (!in_array($file->getExtension(), self::ALLOWED_EXTENSIONS, true)) {
                    continue;
                }

                $relativePath = str_replace($projectRoot . DIRECTORY_SEPARATOR, '', $file->getPathname());
                $lines = file($file->getPathname(), FILE_IGNORE_NEW_LINES);

                if ($lines === false) {
                    $issues[] = $relativePath . ': unable to read file';

                    continue;
                }

                foreach ($lines as $index => $line) {
                    foreach (self::MOJIBAKE_MARKERS as $marker) {
                        if (!str_contains($line, $marker)) {
                            continue;
                        }

                        $issues[] = sprintf(
                            '%s:%d contains "%s"',
                            $relativePath,
                            $index + 1,
                            $marker
                        );
                        break;
                    }
                }
            }
        }

        $this->assertSame(
            [],
            $issues,
            "Found possible mojibake text. Please fix encoding to UTF-8:\n" . implode("\n", $issues)
        );
    }
}

