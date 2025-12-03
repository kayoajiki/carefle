# Anthropic使用目的フォーム提出状況の確認方法

## 確認方法

### 方法1: モデルカタログからプレイグラウンドで確認（推奨）

1. **AWS Bedrockコンソールにアクセス**
   - https://console.aws.amazon.com/bedrock/ にアクセス
   - リージョンを `us-east-1` に設定（右上のリージョン選択）

2. **モデルカタログを開く**
   - 左メニューから「モデルカタログ」をクリック
   - または直接: https://console.aws.amazon.com/bedrock/home?region=us-east-1#/model-catalog

3. **Anthropicモデルを検索**
   - 検索バーに「Claude 3.5 Sonnet」または「Claude 3 Haiku」と入力
   - または「Anthropic」でフィルタリング

4. **モデルを選択**
   - 使用したいモデル（例: Claude 3.5 Sonnet）をクリック

5. **プレイグラウンドで開く**
   - モデル詳細ページで「プレイグラウンドで開く」または「Chat」タブをクリック
   - または「Text」タブをクリック

6. **確認**
   - **フォームが表示される場合**: まだ提出していない、または承認待ち
   - **プレイグラウンドが正常に開ける場合**: フォーム提出済みで有効化されている

### 方法2: 実際にAPIを呼び出して確認

1. **アプリケーションからテスト**
   - 内省チャット機能を試す
   - エラーメッセージを確認

2. **エラーメッセージの確認**
   - エラーが出ない場合: フォーム提出済みで有効化されている
   - 以下のエラーが出る場合: フォーム未提出または承認待ち
     ```
     Model use case details have not been submitted for this account.
     Fill out the Anthropic use case details form before using the model.
     ```

### 方法3: AWS CLIで確認（上級者向け）

```bash
aws bedrock list-foundation-models \
  --region us-east-1 \
  --query 'modelSummaries[?providerName==`Anthropic`]' \
  --output table
```

ただし、この方法ではフォーム提出状況は直接確認できません。

## エラーメッセージの種類

### フォーム未提出の場合
```
ResourceNotFoundException: Model use case details have not been submitted for this account. 
Fill out the Anthropic use case details form before using the model.
```

### フォーム提出済みだが承認待ちの場合
```
ResourceNotFoundException: Model use case details have not been submitted for this account. 
Fill out the Anthropic use case details form before using the model. 
If you have already filled out the form, try again in 15 minutes.
```

### 有効化済みの場合
- エラーが出ない
- プレイグラウンドで正常に動作する
- API呼び出しが成功する

## トラブルシューティング

### プレイグラウンドが開けない

1. **別のブラウザで試す**
2. **リージョンを確認**（us-east-1推奨）
3. **AWSアカウントの権限を確認**

### フォームが表示されない

- 既に提出済みの可能性があります
- 実際にAPIを呼び出してエラーが出ないか確認してください

### エラーが出続ける

1. **15分待つ**（フォーム提出後、承認まで最大15分かかります）
2. **別のモデルで試す**（例: Claude 3 Sonnet）
3. **IAM権限を確認**（`AmazonBedrockFullAccess`ポリシーがアタッチされているか）

## 確認のベストプラクティス

1. **まずプレイグラウンドで確認**
   - 最も簡単で確実な方法
   - フォーム提出が必要かどうかがすぐにわかる

2. **APIで実際にテスト**
   - アプリケーションから実際に呼び出してみる
   - エラーが出ないか確認

3. **ログを確認**
   - `storage/logs/laravel.log` を確認
   - エラーメッセージの詳細を確認

