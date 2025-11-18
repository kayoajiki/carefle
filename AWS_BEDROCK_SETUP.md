# AWS Bedrock セットアップガイド

## 必要な認証情報

AWS Bedrockを使用するには、以下の認証情報が必要です：

- **AWS Access Key ID**（アクセスキーID）
- **AWS Secret Access Key**（シークレットアクセスキー）
- **AWS Region**（リージョン、例: `us-east-1`, `ap-northeast-1`）

## 認証情報の取得方法

### 方法1: IAMユーザーを作成（推奨・本番環境向け）

#### ステップ1: AWSコンソールにログイン

1. **AWSコンソールにアクセス**
   - ブラウザで https://console.aws.amazon.com/ を開く
   - AWSアカウントでログイン

#### ステップ2: IAMサービスに移動

1. **サービス検索**
   - 画面上部の検索バーに「IAM」と入力
   - 「IAM」サービスをクリック
   - または、直接 https://console.aws.amazon.com/iam/ にアクセス

#### ステップ3: ユーザーを作成

1. **ユーザー一覧ページに移動**
   - 左側のメニューから「ユーザー」をクリック

2. **ユーザー追加ボタンをクリック**
   - 画面右上の「ユーザーを追加」ボタンをクリック

#### ステップ4: ユーザー名を入力

1. **ユーザー名を入力**
   - 「ユーザー名」フィールドに名前を入力
   - 例: `carefle-bedrock-user` または `bedrock-user`

2. **「次へ」ボタンをクリック**
   - 画面下部の「次へ」ボタンをクリック

#### ステップ5: ユーザーの詳細を指定（オプション）

1. **このステップはスキップ可能です**
   - タグや説明を追加したい場合は入力
   - 特に必要なければ、そのまま「次へ」をクリック

2. **「次へ」ボタンをクリック**

#### ステップ6: 許可を設定（権限を付与）

1. **「ユーザーに直接ポリシーをアタッチ」を選択**
   - 画面に表示されるオプションから「ユーザーに直接ポリシーをアタッチ」を選択
   - （他のオプション: 「グループに追加」「ポリシーをコピー」は使用しません）

2. **ポリシーを検索**
   - 検索バーに「Bedrock」と入力
   - `AmazonBedrockFullAccess` を検索

3. **ポリシーを選択**
   - `AmazonBedrockFullAccess` の左側のチェックボックスにチェックを入れる
   - このポリシーには以下の権限が含まれます：
     - `bedrock:InvokeModel` - モデルを呼び出す
     - `bedrock:InvokeModelWithResponseStream` - ストリーミング応答
     - `aws-marketplace:Subscribe` - Marketplaceサブスクリプション（初回使用時）

4. **「次へ」ボタンをクリック**

5. **確認画面**
   - 設定内容を確認
   - 「ユーザーを作成」ボタンをクリック

#### ステップ7: アクセスキーを作成（重要！）

**新しいUIでは、ユーザー作成後にアクセスキーを別途作成する必要があります。**

1. **ユーザー作成完了後**
   - 「ユーザーが正常に作成されました」というメッセージが表示されます
   - この時点では、まだアクセスキーは作成されていません

2. **作成したユーザーを選択**
   - ユーザー一覧から、今作成したユーザー名をクリック
   - ユーザーの詳細ページが開きます

3. **「セキュリティ認証情報」タブをクリック**
   - ユーザー詳細ページの上部にタブがあります
   - 「セキュリティ認証情報」タブをクリック

4. **「アクセスキーを作成」をクリック**
   - 「アクセスキー」セクションまでスクロール
   - 「アクセスキーを作成」ボタンをクリック

5. **使用例を選択**
   - 「使用例」のドロップダウンから「アプリケーションコードを実行する」を選択
   - または「その他」を選択
   - 「次へ」をクリック

6. **説明タグ（オプション）**
   - 説明を入力（例: 「Bedrock PDF文字化け修正用」）
   - またはそのまま「アクセスキーを作成」をクリック

7. **アクセスキーを取得**
   - 「アクセスキーID」と「シークレットアクセスキー」が表示されます
   - ⚠️ **重要**: シークレットアクセスキーはこの時点でしか表示されません
   - 必ずコピーして安全な場所に保存してください
   - 「.csvのダウンロード」ボタンでCSVファイルとして保存することもできます

8. **「完了」をクリック**

#### セキュリティの注意事項

- ⚠️ **アクセスキーは絶対に公開しないでください**
- ⚠️ **GitHubやその他の公開リポジトリにコミットしないでください**
- ⚠️ **`.env`ファイルは`.gitignore`に含まれていることを確認してください**
- ⚠️ **定期的にアクセスキーをローテーション（更新）することを推奨します**

### 方法2: 一時的な認証情報（短期APIキー）

AWS STS（Security Token Service）を使用して一時的な認証情報を取得できます。

**メリット**:
- セキュリティが高い（有効期限がある）
- 本番環境で推奨

**デメリット**:
- 定期的に更新が必要
- 設定が複雑

**短期APIキーでも動作しますが、有効期限が切れると動作しなくなります。**

## Bedrockへのアクセス有効化

**重要**: 2024年9月29日以降、Model accessページは廃止され、すべてのAWSアカウントでモデルが自動的に有効化されています。

**手動での有効化作業は不要です！**

### 初回使用時の注意点

3rdパーティのモデル（AnthropicのClaudeなど）を初めて呼び出す際：
- AWS Marketplaceを通じて自動的にサブスクライブが行われます
- 初回呼び出しを行うIAMユーザー/ロールに `aws-marketplace:Subscribe` アクションの許可が必要な場合があります

### IAMポリシーに追加する権限（必要に応じて）

```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": [
                "bedrock:InvokeModel",
                "bedrock:InvokeModelWithResponseStream",
                "aws-marketplace:Subscribe"
            ],
            "Resource": "*"
        }
    ]
}
```

または、`AmazonBedrockFullAccess` ポリシーを使用すれば、必要な権限がすべて含まれています。

## .envファイルの設定

`.env`ファイルに以下の設定を追加：

```env
# AWS認証情報
AWS_ACCESS_KEY_ID=your_access_key_id_here
AWS_SECRET_ACCESS_KEY=your_secret_access_key_here
AWS_DEFAULT_REGION=us-east-1

# Bedrock設定
AWS_BEDROCK_REGION=us-east-1
AWS_BEDROCK_MODEL_ID=anthropic.claude-3-5-sonnet-20241022-v2:0
AWS_BEDROCK_MAX_TOKENS=8192
AWS_BEDROCK_TEMPERATURE=0.3
AWS_BEDROCK_TOP_P=0.9
AWS_BEDROCK_FIX_GARBLED_TEXT=true
```

## リージョンの選択

Bedrockが利用可能なリージョン：
- `us-east-1` (バージニア) - 推奨
- `us-west-2` (オレゴン)
- `ap-northeast-1` (東京) - 日本からは低レイテンシ

## セキュリティのベストプラクティス

1. **最小権限の原則**
   - Bedrockに必要な権限のみを付与
   - カスタムIAMポリシーを作成することを推奨

2. **アクセスキーの管理**
   - アクセスキーは絶対に公開しない
   - `.env`ファイルは`.gitignore`に含まれていることを確認
   - 定期的にローテーション

3. **本番環境では**
   - IAMロールを使用（EC2やECSの場合）
   - 環境変数ではなく、AWS Secrets Managerを使用

## カスタムIAMポリシーの例

最小限の権限のみを付与する場合：

```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": [
                "bedrock:InvokeModel",
                "bedrock:InvokeModelWithResponseStream"
            ],
            "Resource": "arn:aws:bedrock:*::foundation-model/anthropic.claude-3-5-sonnet-20241022-v2:0"
        }
    ]
}
```

## トラブルシューティング

### エラー: "AccessDeniedException"

- IAMユーザーに適切な権限が付与されていない
- `AmazonBedrockFullAccess` ポリシーがアタッチされているか確認
- 初回使用時は `aws-marketplace:Subscribe` 権限が必要な場合があります

### エラー: "InvalidSignatureException"

- アクセスキーIDまたはシークレットアクセスキーが間違っている
- リージョンが正しく設定されているか確認

### エラー: "ModelAccessDeniedException"

- モデルは自動的に有効化されているため、通常は発生しません
- IAM権限を確認してください
- 初回使用時は、AWS Marketplaceのサブスクリプションが自動的に作成されます

## コストについて

- Claude 3.5 Sonnet: 入力 $3/1Mトークン、出力 $15/1Mトークン
- 文字化け修正は1回のPDF処理で1回のAPI呼び出し
- 料金は使用量に応じて課金されます

詳細: https://aws.amazon.com/bedrock/pricing/

