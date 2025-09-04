# Claude AI Assistant Integration

このドキュメントは、jp-postal-code-api プロジェクトでの Claude AI との協力・開発に関する情報をまとめています。

## プロジェクト概要

jp-postal-code-api は、日本の郵便番号データを提供するAPIプロジェクトです。日本郵便が提供するCSVデータを解析し、JSON形式のAPIとして提供します。

## Claude との協力内容

### 開発支援
- コードレビューとリファクタリングの提案
- PHPコードの品質向上
- テストコードの改善
- ドキュメントの整備

### 自動化タスク
- GitHub Actions ワークフローの最適化
- データ処理ロジックの改善
- エラーハンドリングの強化

## プロジェクト構造

```
src/
├── JpPostalCodeApi.php        # メインAPIクラス
├── Command/                   # コマンドライン処理
├── Csv/                      # CSV解析処理
├── DataSource/               # データソース管理
├── Model/                    # データモデル
└── Util/                     # ユーティリティ
```

## 重要なファイル

- `bin/console build`: データビルドコマンド
- `.github/workflows/cron.yaml`: 定期実行ワークフロー
- `composer.json`: 依存関係定義
- `phpunit.xml.dist`: テスト設定

## 開発ガイドライン

### コーディング規約
- PSR-4 オートローディング
- PSR-12 コーディングスタイル
- PHPStan レベル9 での静的解析
- PHPUnit でのテストカバレッジ

### データ処理
- 日本郵便のKEN_ALLデータを使用
- 事業所個別郵便番号データにも対応
- ローマ字データの処理

## CI/CD

GitHub Actions による自動化：
- 毎日00:00 JSTでデータ更新
- コードの品質チェック
- 自動テスト実行
- APIドキュメント生成

## API仕様

### エンドポイント
- `GET /api/v1/{postal_code}.json`
- レスポンス形式：JSON
- 文字エンコーディング：UTF-8

### レスポンス例
```json
{
  "data": [
    {
      "postal_code": "1000001",
      "prefecture": "東京都",
      "city": "千代田区",
      "town": "千代田"
    }
  ]
}
```

## 今後の改善点

- [ ] API レスポンスの最適化
- [ ] データ更新頻度の調整
- [ ] エラーハンドリングの強化
- [ ] パフォーマンスの向上
- [ ] ドキュメントの充実

## 連絡先

プロジェクトに関する質問や提案は、GitHub Issues をご利用ください。

---

*このドキュメントは Claude AI アシスタントとの協力により作成・更新されています。*
