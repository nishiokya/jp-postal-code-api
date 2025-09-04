<?php

declare(strict_types=1);

namespace Ttskch\JpPostalCodeApi\FileSystem;

use Ttskch\JpPostalCodeApi\Model\ParsedCsvRow;

interface BaseDirectoryInterface
{
    public function clear(): void;

    public function putJsonFile(ParsedCsvRow $row, bool $en = false): void;

    public function countJsonFiles(): int;

    /** 出力ルート（例: /path/to/docs）を返す */
    public function getRootPath(): string;

    /** JSON群のルート（例: getRootPath() と同じ or その配下） */
    public function getJsonRootPath(): string;

    /** ルート配下にディレクトリを作ってフルパスを返す（存在すればそのまま返す） */
    public function ensureDir(string $relative): string;
}
