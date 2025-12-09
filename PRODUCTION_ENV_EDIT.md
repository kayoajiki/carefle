# 本番環境の`.env`ファイル修正手順

## 概要

本番環境の`.env`ファイルを安全に修正する手順を説明します。特に、データベース接続エラーが発生している場合の対処方法を含みます。

## 前提条件

- 本番サーバーへのSSHアクセス権限
- 本番サーバーのプロジェクトディレクトリへのアクセス権限
- データベースの接続情報（ホスト、データベース名、ユーザー名、パスワード）

## 手順

### 1. 本番サーバーにSSH接続

```bash
ssh user@your-production-server.com
```

**注意**: `user`と`your-production-server.com`は実際のサーバー情報に置き換えてください。

### 2. プロジェクトディレクトリに移動

```bash
cd /path/to/carefle
```

**注意**: `/path/to/carefle`は実際のプロジェクトディレクトリのパスに置き換えてください。

### 3. 現在の`.env`ファイルをバックアップ

**重要**: 修正前に必ずバックアップを取ります。

```bash
# バックアップファイルを作成（日時付き）
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)

# または、シンプルに
cp .env .env.backup
```

### 4. 現在の`.env`ファイルの内容を確認

```bash
# データベース設定部分を確認
cat .env | grep DB_
```

現在の設定を確認して、どの部分を修正する必要があるかを把握します。

### 5. `.env`ファイルを編集

#### 方法1: `nano`エディタを使用（推奨）

```bash
nano .env
```

**nanoエディタの操作方法**:
- カーソルキーで移動
- 編集後、`Ctrl + O`で保存
- `Enter`でファイル名を確認
- `Ctrl + X`で終了

#### 方法2: `vi`エディタを使用

```bash
vi .env
```

**viエディタの操作方法**:
- `i`キーで挿入モードに入る
- 編集後、`Esc`キーでコマンドモードに戻る
- `:wq`で保存して終了
- `:q!`で保存せずに終了

#### 方法3: `sed`コマンドで直接置換（特定の値のみ変更する場合）

```bash
# DB_USERNAMEを変更する例
sed -i 's/^DB_USERNAME=.*/DB_USERNAME=new_username/' .env

# DB_PASSWORDを変更する例
sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD=new_password/' .env

# DB_HOSTを変更する例
sed -i 's/^DB_HOST=.*/DB_HOST=new_host/' .env
```

### 6. データベース設定の修正例

データベース接続エラーが発生している場合、以下の設定を確認・修正します：

```env
# データベース接続設定
DB_CONNECTION=mysql
DB_HOST=your-database-host
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

**重要なポイント**:
- `DB_HOST`: データベースサーバーのホスト名またはIPアドレス
- `DB_PORT`: MySQLのポート番号（通常は3306）
- `DB_DATABASE`: データベース名
- `DB_USERNAME`: データベースユーザー名
- `DB_PASSWORD`: データベースパスワード（特殊文字が含まれる場合は引用符で囲む必要がある場合があります）

**パスワードに特殊文字が含まれる場合**:
```env
# パスワードに特殊文字が含まれる場合は、引用符で囲む
DB_PASSWORD="your_password_with_special_chars"
```

### 7. 設定の反映

`.env`ファイルを修正した後、Laravelのキャッシュをクリアして設定を反映させます。

```bash
# 設定キャッシュをクリア
php artisan config:clear

# アプリケーションキャッシュをクリア
php artisan cache:clear

# ルートキャッシュをクリア
php artisan route:clear

# ビューキャッシュをクリア
php artisan view:clear
```

### 8. 設定の確認

修正した設定が正しく読み込まれているか確認します。

```bash
# データベース接続をテスト
php artisan tinker
```

Tinkerで以下を実行：

```php
DB::connection()->getPdo();
```

エラーが発生しない場合は、接続が成功しています。`exit`でTinkerを終了します。

### 9. 動作確認

ブラウザで本番環境のURLにアクセスし、以下を確認：

- トップページが表示される
- ログイン・登録が動作する
- データベース接続エラーが解消されている

### 10. ログの確認

問題が発生した場合は、ログを確認します。

```bash
# Laravelのログを確認
tail -f storage/logs/laravel.log

# または、最新のエラーを確認
tail -n 100 storage/logs/laravel.log | grep -i error
```

## データベース接続エラーのトラブルシューティング

### エラー: "Access denied for user"

このエラーが発生する場合、以下の原因が考えられます：

1. **ユーザー名またはパスワードが間違っている**
   - `.env`ファイルの`DB_USERNAME`と`DB_PASSWORD`を確認
   - データベース管理者に正しい認証情報を確認

2. **ユーザーが存在しない**
   - MySQLに接続してユーザーの存在を確認：
   ```sql
   SELECT user, host FROM mysql.user WHERE user = 'your_username';
   ```

3. **権限が不足している**
   - ユーザーに適切な権限が付与されているか確認：
   ```sql
   SHOW GRANTS FOR 'your_username'@'%';
   ```

4. **ホストからの接続が許可されていない**
   - ユーザーが特定のホストからの接続のみ許可されている場合、接続元のIPアドレスを確認
   - 必要に応じて、ユーザーを作成または権限を付与：
   ```sql
   CREATE USER 'your_username'@'%' IDENTIFIED BY 'your_password';
   GRANT ALL PRIVILEGES ON your_database_name.* TO 'your_username'@'%';
   FLUSH PRIVILEGES;
   ```

### エラー: "Connection refused"

このエラーが発生する場合：

1. **データベースサーバーが起動しているか確認**
   ```bash
   # MySQLのステータスを確認
   sudo systemctl status mysql
   # または
   sudo service mysql status
   ```

2. **ホスト名またはIPアドレスが正しいか確認**
   - `.env`ファイルの`DB_HOST`を確認
   - データベースサーバーにpingを送信：
   ```bash
   ping your-database-host
   ```

3. **ポート番号が正しいか確認**
   - `.env`ファイルの`DB_PORT`を確認（通常は3306）
   - ポートが開いているか確認：
   ```bash
   telnet your-database-host 3306
   ```

### エラー: "Unknown database"

このエラーが発生する場合：

1. **データベース名が正しいか確認**
   - `.env`ファイルの`DB_DATABASE`を確認
   - MySQLに接続してデータベースの存在を確認：
   ```sql
   SHOW DATABASES;
   ```

2. **データベースが作成されていない場合**
   - データベースを作成：
   ```sql
   CREATE DATABASE your_database_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

## 安全な修正のベストプラクティス

1. **必ずバックアップを取る**
   - 修正前に`.env`ファイルをバックアップ
   - 問題が発生した場合にすぐにロールバックできるようにする

2. **メンテナンスモードを有効化（必要に応じて）**
   ```bash
   php artisan down
   # .envファイルを修正
   php artisan config:clear
   php artisan cache:clear
   php artisan up
   ```

3. **段階的に修正する**
   - 一度にすべてを変更せず、1つずつ修正して動作確認

4. **ログを監視する**
   - 修正後はログを監視して、エラーが発生していないか確認

5. **テスト環境で事前に確認（可能であれば）**
   - 本番環境で修正する前に、テスト環境で同じ設定を試す

## よくある設定項目

本番環境の`.env`ファイルで確認すべき主要な設定項目：

```env
# アプリケーション設定
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_KEY=base64:your-app-key

# データベース設定
DB_CONNECTION=mysql
DB_HOST=your-database-host
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password

# AWS設定（Bedrock使用時）
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=your_region
AWS_BEDROCK_MODEL_ID=anthropic.claude-3-5-sonnet-20241022-v2:0

# メール設定
MAIL_MAILER=smtp
MAIL_HOST=your-mail-host
MAIL_PORT=587
MAIL_USERNAME=your-mail-username
MAIL_PASSWORD=your-mail-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

# セッション設定
SESSION_DRIVER=database
SESSION_LIFETIME=120
```

## まとめ

本番環境の`.env`ファイルを修正する際は、以下の手順を守ってください：

1. ✅ バックアップを取る
2. ✅ 現在の設定を確認
3. ✅ 慎重に編集
4. ✅ キャッシュをクリア
5. ✅ 動作確認
6. ✅ ログを監視

問題が発生した場合は、バックアップから復元して、段階的に修正を進めてください。


