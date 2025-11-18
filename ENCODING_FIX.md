# 文字化け対策

## 実装した対策

### 1. 自動エンコーディング検出と変換
- PDFから抽出したテキストのエンコーディングを自動検出
- UTF-8以外のエンコーディング（SJIS、EUC-JP、ISO-2022-JPなど）をUTF-8に変換
- 検出できない場合は、複数のエンコーディングを順番に試行

### 2. 不正文字の除去
- 制御文字（改行・タブ以外）を除去
- 不正なUTF-8文字を修正

### 3. メタデータの文字化け対策
- PDFのタイトル、作成者などのメタデータも同様にエンコーディング変換

## 対応しているエンコーディング

- UTF-8（優先）
- SJIS（Shift_JIS）
- EUC-JP
- ISO-2022-JP
- Windows-1252
- ISO-8859-1
- ASCII

## 追加の対策（必要に応じて）

### 選択肢1: PDFパーサーの設定変更
```php
// Parserの設定を変更
$config = new \Smalot\PdfParser\Config();
$config->setFontSpaceLimit(-50);
$config->setRetainImageContent(false);
$this->parser = new Parser([], $config);
```

### 選択肢2: 別のPDFパーサーライブラリの使用
- `spatie/pdf` - より高度な文字エンコーディング処理
- `setasign/fpdi` - PDF操作に特化
- `tcpdf` - PDF生成・解析

### 選択肢3: OCR機能の追加（画像ベースPDF用）
画像のみのPDFの場合、OCR（光学文字認識）が必要：
- `spatie/pdf-to-image` + `tesseract-ocr`
- AWS Textract
- Google Cloud Vision API

### 選択肢4: 手動エンコーディング指定
管理画面からPDFのエンコーディングを手動で指定できる機能を追加

## トラブルシューティング

### まだ文字化けする場合

1. **PDFのエンコーディングを確認**
   - PDFのプロパティで文字エンコーディングを確認
   - 特定のエンコーディングが判明している場合は、コードに追加

2. **ログで確認**
   ```php
   Log::info('PDFエンコーディング', [
       'detected' => mb_detect_encoding($text),
       'sample' => mb_substr($text, 0, 100),
   ]);
   ```

3. **PDFの再作成**
   - 可能であれば、PDFをUTF-8で再作成

4. **OCRの検討**
   - 画像ベースのPDFの場合は、OCR機能の追加を検討

