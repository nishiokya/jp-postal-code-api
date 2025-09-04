<?php
namespace Ttskch\JpPostalCodeApi\Suggest;

use Symfony\Component\Console\Style\SymfonyStyle;
use Ttskch\JpPostalCodeApi\FileSystem\BaseDirectoryInterface;

interface SuggestIndexBuilderInterface
{
    public function build(BaseDirectoryInterface $baseDir, ?SymfonyStyle $io = null): void;
}