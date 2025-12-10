# Anthropic使用目的フォーム提出ガイド

## 概要

AWS BedrockでAnthropicのモデル（Claude）を初めて使用する際、使用目的フォームの提出が必要です。

## エラーメッセージ

以下のエラーが表示される場合、フォームの提出が必要です：

```
Model use case details have not been submitted for this account. 
Fill out the Anthropic use case details form before using the model.
```

## フォーム提出手順

### ステップ1: AWS Bedrockコンソールにアクセス

1. AWSコンソールにログイン
2. 検索バーで「Bedrock」と検索
3. 「Amazon Bedrock」サービスを選択
4. または直接 https://console.aws.amazon.com/bedrock/ にアクセス

### ステップ2: モデルアクセスページに移動

1. 左側のメニューから「モデルアクセス」をクリック
2. または「Foundation models」→「Model access」を選択

### ステップ3: Anthropicモデルを有効化

1. モデル一覧から「Anthropic」のセクションを探す
2. 使用したいモデル（例: Claude 3 Sonnet）の「Enable」ボタンをクリック
3. 使用目的フォームが表示されます

### ステップ4: 使用目的フォームを記入

1. **使用目的を選択**
   - 「General purpose / Chatbot」（一般的な用途/チャットボット）を選択
   - または「Other」を選択して説明を記入

2. **使用内容を記入**
   - 例: 「内省支援アプリケーションでの対話型チャット機能」
   - 例: 「ユーザーの内省を支援するAI伴走機能」

3. **提出**
   - フォームを送信
   - 承認まで数分〜15分程度かかる場合があります

### ステップ5: 承認を待つ

- フォーム提出後、承認されるまで待ちます
- 通常は数分で承認されますが、最大15分かかる場合があります
- 承認後、モデルが使用可能になります

## 確認方法

1. Bedrockコンソールの「モデルアクセス」ページで確認
2. Anthropicモデルのステータスが「Enabled」になっているか確認

## トラブルシューティング

### フォームが見つからない

- Bedrockコンソールの「モデルアクセス」ページを確認
- リージョンが正しいか確認（us-east-1推奨）

### 承認が遅い

- 最大15分待ってください
- それでも承認されない場合は、AWSサポートに問い合わせ

### それでもエラーが出る

- `.env`ファイルの設定を確認
- アクセスキーが正しいか確認
- IAMユーザーに`AmazonBedrockFullAccess`ポリシーがアタッチされているか確認



