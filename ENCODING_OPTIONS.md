# 文字化け対策の選択肢

現在の文字化けは、PDFのフォントエンコーディング（特にCIDフォント）が正しく処理されていない可能性があります。

## 実装済みの対策

1. **複数エンコーディングの自動試行**
   - SJIS、EUC-JP、ISO-2022-JP、CP932などを順番に試行
   - 日本語文字の含有率をスコアリングして最適なエンコーディングを選択

2. **PDFパーサーの設定最適化**
   - フォントスペースの調整
   - 画像コンテンツの除外

## 追加の選択肢

### 選択肢1: 別のPDFパーサーライブラリを使用

#### A. `spatie/pdf-to-text` (pdftotextコマンドを使用)
```bash
composer require spatie/pdf-to-text
```

**メリット**:
- より正確なテキスト抽出
- エンコーディング処理が優秀

**デメリット**:
- `pdftotext` コマンドが必要（システムにインストールが必要）

#### B. `setasign/fpdi` + `setasign/fpdf`
```bash
composer require setasign/fpdi setasign/fpdf
```

**メリット**:
- PDF操作に特化
- フォント情報の詳細な取得が可能

### 選択肢2: OCR機能の追加（画像ベースPDF用）

画像のみのPDFや、フォントが正しく埋め込まれていないPDFの場合：

#### A. Tesseract OCR
```bash
# macOS
brew install tesseract tesseract-lang

# パッケージ
composer require thiagoalessio/tesseract_ocr
```

#### B. AWS Textract
- 既にAWS SDKがインストール済み
- 高精度なOCR
- 有料

#### C. Google Cloud Vision API
- 高精度なOCR
- 有料

### 選択肢3: PDFの前処理

PDFを画像に変換してからOCRを実行：

```bash
composer require spatie/pdf-to-image
```

### 選択肢4: 手動エンコーディング指定機能

管理画面からPDFのエンコーディングを手動で指定できる機能を追加。

## 推奨される対応

1. **まず試す**: 現在の実装で再処理してみる
2. **それでもダメな場合**: `spatie/pdf-to-text` を試す（pdftotextコマンドが必要）
3. **最後の手段**: OCR機能を追加

## 次のステップ

1. 既存の職務経歴書を「再処理」で試す
2. まだ文字化けする場合は、どの選択肢を試すかお知らせください

