<?php
namespace Ttskch\JpPostalCodeApi\Suggest;

use Ttskch\JpPostalCodeApi\FileSystem\BaseDirectoryInterface;

final class SuggestIndexBuilder implements SuggestIndexBuilderInterface
{
    public function __construct(
        private readonly bool $buildZip3 = true,
        private readonly bool $buildZip7 = true,
        private readonly bool $buildLocalities = true,
        private readonly bool $buildPrefectures = true,
    ) {}

    /** JSON 1件 = 1郵便番号レコードを想定 */
    public function build(BaseDirectoryInterface $baseDir, ?\Symfony\Component\Console\Style\SymfonyStyle $io = null): void
    {
 
        // ここを BaseDirectory API に置換
        $suggestDir = $baseDir->ensureDir('suggest');

        // 出力ファイルを開く（必要なものだけ）
        $fhZip3 = $this->buildZip3 ? fopen("$suggestDir/zip3.txt", 'w') : null;
        $fhZip7 = $this->buildZip7 ? fopen("$suggestDir/zip7.jsonl", 'w') : null;
        $fhLoc  = $this->buildLocalities ? fopen("$suggestDir/localities.jsonl", 'w') : null;

        // 重複防止（適度に抑える）。巨大なら LRU/一時ファイル等に切替可能
        $seenZip3 = [];
        $seenCity = [];   // key: "{$pref}\t{$city}"
        $seenPref = [];

        // 生成済みJSONのルートを取得（BaseDirectory が知っている前提）
        $root = $baseDir->getJsonRootPath(); // ★ 実装に合わせてください

        $jsonFiles = glob($root . '/*.json');
        if (!$jsonFiles) {
            return; // JSONファイルが存在しない場合は処理を終了
        }

        foreach ($jsonFiles as $filePath) {
            $content = file_get_contents($filePath);
            if ($content === false) {
                continue;
            }
            $row = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            // APIResourceの構造に合わせてデータを取得
            $zip = $row['postalCode'] ?? null;
            $addresses = $row['addresses'] ?? [];
            
            foreach ($addresses as $addr) {
                $ja = $addr['ja'] ?? [];
                $en = $addr['en'] ?? [];
                
                $pref = $ja['prefecture'] ?? null;
                $city = $ja['address1'] ?? null;
                $town = trim(($ja['address2'] ?? '') . ' ' . ($ja['address3'] ?? '') . ' ' . ($ja['address4'] ?? ''));
                
                // ローマ字
                $prefR = $en['prefecture'] ?? null;
                $cityR = $en['address1'] ?? null;
                $townR = trim(($en['address2'] ?? '') . ' ' . ($en['address3'] ?? '') . ' ' . ($en['address4'] ?? ''));

                if (!$zip || !$pref || !$city) {
                    continue; // 最低限欠けたらスキップ
                }

                // 1) zip3
                if ($fhZip3) {
                    $zip3 = substr($zip, 0, 3);
                    if (!isset($seenZip3[$zip3])) {
                        fwrite($fhZip3, $zip3 . PHP_EOL);
                        $seenZip3[$zip3] = true;
                    }
                }

                // 2) zip7
                if ($fhZip7) {
                    $label = $pref . ' ' . $city . ($town ? ' ' . $town : '');
                    $romaji = trim(($prefR ?: '') . ' ' . ($cityR ?: '') . ' ' . ($townR ?: ''));
                    $item = [
                        'zip'   => $zip,
                        'label' => $label,
                        'romaji'=> $romaji ?: null,
                        'pref'  => $pref,
                        'city'  => $city,
                        'town'  => $town ?: null,
                        'kind'  => 'addr',
                    ];
                    fwrite($fhZip7, json_encode($item, JSON_UNESCAPED_UNICODE) . PHP_EOL);
                }

                // 3) localities（pref+cityをユニーク化）
                if ($fhLoc) {
                    $k = $pref . "\t" . $city;
                    if (!isset($seenCity[$k])) {
                        $romaji = trim(($prefR ?: '') . ' ' . ($cityR ?: ''));
                        $item = [
                            'label' => $pref . ' ' . $city,
                            'romaji'=> $romaji ?: null,
                            'pref'  => $pref,
                            'city'  => $city,
                            'kind'  => 'city',
                        ];
                        fwrite($fhLoc, json_encode($item, JSON_UNESCAPED_UNICODE) . PHP_EOL);
                        $seenCity[$k] = true;
                    }
                }

                // 4) prefectures（あとで1回で書く）
                if ($this->buildPrefectures && $pref) {
                    $seenPref[$pref] = true;
                }
            }

            // プログレスバーの進捗更新は呼び出し元で管理
        }

        if ($fhZip3) fclose($fhZip3);
        if ($fhZip7) fclose($fhZip7);
        if ($fhLoc)  fclose($fhLoc);

        if ($this->buildPrefectures) {
            $list = array_keys($seenPref);
            sort($list, SORT_STRING);
            file_put_contents("$suggestDir/prefectures.json", json_encode($list, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
        }
    }
}