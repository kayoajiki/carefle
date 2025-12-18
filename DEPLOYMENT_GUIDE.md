# 本番環境デプロイガイド

このガイドでは、carefleアプリケーションを本番環境にデプロイする手順を説明します。

## 前提条件

- 本番サーバーへのSSHアクセス権限
- 本番サーバーにPHP 8.2以上、Composer、Node.js 18以上がインストールされていること
- 本番データベース（MySQL）が設定済みであること
- AWS認証情報が設定済みであること（Bedrock使用時）

## デプロイ前のチェックリスト

### 1. ローカルでの確認

- [ ] すべての変更がコミットされている
- [ ] テストが通っている（`php artisan test`）
- [ ] リンターエラーがない
- [ ] マイグレーションファイルが正しく動作する
- [ ] `.env.example`が最新である

### 2. 本番環境の準備

- [ ] `.env`ファイルが本番環境用に設定されている
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] データベース接続情報が正しい
- [ ] AWS認証情報が設定されている
- [ ] ストレージリンクが設定されている（`php artisan storage:link`）

## デプロイ手順

### 方法1: Git経由でデプロイ（推奨）

#### ステップ1: ローカルで変更をコミット・プッシュ

```bash
# 変更をコミット
git add -A
git commit -m "feat: 変更内容の説明"

# 本番ブランチ（main）にプッシュ
git push origin main
```

#### ステップ2: 本番サーバーにSSH接続

```bash
ssh user@your-production-server.com
```

#### ステップ3: 本番サーバーで最新コードを取得

```bash
cd /path/to/carefle
git pull origin main
```

#### ステップ4: 依存関係の更新

```bash
# Composer依存関係のインストール（本番用）
composer install --no-dev --optimize-autoloader

# NPM依存関係のインストール
npm install

# アセットのビルド
npm run build
```

#### ステップ5: Laravelの最適化

```bash
# 設定キャッシュ
php artisan config:cache

# ルートキャッシュ
php artisan route:cache

# ビューキャッシュ
php artisan view:cache

# イベントキャッシュ（Laravel 11+）
php artisan event:cache
```

#### ステップ6: データベースマイグレーション

```bash
# マイグレーション実行
php artisan migrate --force
```

⚠️ **注意**: `--force`フラグは本番環境でのみ使用してください。

#### ステップ7: ストレージリンクの確認

```bash
# ストレージリンクが存在するか確認
php artisan storage:link
```

#### ステップ8: 権限の設定

```bash
# ストレージとキャッシュディレクトリの権限設定
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### ステップ9: アプリケーションの再起動

```bash
# PHP-FPMの再起動（必要に応じて）
sudo systemctl restart php8.2-fpm

# または、Webサーバーの再起動
sudo systemctl restart nginx
# または
sudo systemctl restart apache2
```

#### ステップ10: 動作確認

- [ ] アプリケーションが正常に表示される
- [ ] ログインができる
- [ ] 主要な機能が動作する
- [ ] エラーログに問題がない（`storage/logs/laravel.log`）

### 方法2: 自動デプロイ（GitHub Actions等）

CI/CDパイプラインを設定している場合、`main`ブランチへのプッシュで自動デプロイが実行されます。

## 本番環境の.env設定例

```env
APP_NAME=carefle
APP_ENV=production
APP_KEY=base64:your-generated-key-here
APP_DEBUG=false
APP_URL=https://your-domain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

# データベース設定
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=carefle
DB_USERNAME=carefle_user
DB_PASSWORD=your-secure-password

# AWS設定（Bedrock使用時）
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=us-east-1
AWS_BEDROCK_REGION=us-east-1
AWS_BEDROCK_MODEL_ID=anthropic.claude-3-5-sonnet-20241022-v2:0

# その他の設定
BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# メール設定（必要に応じて）
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

## トラブルシューティング

### エラー: "Access denied for user" (データベース接続エラー)

このエラーは、データベースの認証情報が正しくないか、MySQLユーザーに適切な権限が付与されていない場合に発生します。

#### ステップ1: .envファイルの確認

```bash
# .envファイルのデータベース設定を確認
cat .env | grep DB_
```

以下の設定が正しいか確認してください：
- `DB_CONNECTION=mysql`
- `DB_HOST`（通常は`127.0.0.1`または`localhost`）
- `DB_PORT`（通常は`3306`）
- `DB_DATABASE`（データベース名）
- `DB_USERNAME`（ユーザー名）
- `DB_PASSWORD`（パスワード）

#### ステップ2: MySQLに直接接続して確認

```bash
# MySQLに接続して認証情報を確認
mysql -u admin -p -h 127.0.0.1
# パスワードを入力
```

接続できない場合、認証情報が間違っている可能性があります。

#### ステップ3: MySQLユーザーの権限を確認・修正

MySQLにrootユーザーで接続して、ユーザーと権限を確認します：

```bash
# MySQLにrootで接続
mysql -u root -p
```

MySQL内で以下を実行：

```sql
-- 現在のユーザーと権限を確認
SELECT user, host FROM mysql.user WHERE user = 'admin';

-- ユーザーが存在しない場合、作成
CREATE USER 'admin'@'localhost' IDENTIFIED BY 'your-password-here';
CREATE USER 'admin'@'%' IDENTIFIED BY 'your-password-here';

-- データベースが存在しない場合、作成
CREATE DATABASE IF NOT EXISTS carefle CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 権限を付与
GRANT ALL PRIVILEGES ON carefle.* TO 'admin'@'localhost';
GRANT ALL PRIVILEGES ON carefle.* TO 'admin'@'%';

-- 権限を反映
FLUSH PRIVILEGES;

-- 確認
SHOW GRANTS FOR 'admin'@'localhost';
```

#### ステップ4: .envファイルの更新

MySQLで設定した認証情報を`.env`ファイルに反映します：

```bash
# .envファイルを編集
nano .env
# または
vi .env
```

以下のように設定：

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=carefle
DB_USERNAME=admin
DB_PASSWORD=your-password-here
```

#### ステップ5: 設定キャッシュをクリア

```bash
# 設定キャッシュをクリア
php artisan config:clear

# 接続テスト
php artisan tinker
>>> DB::connection()->getPdo();
# 接続成功すれば、PDOオブジェクトが表示されます
>>> exit
```

#### ステップ6: マイグレーションを再実行

```bash
php artisan migrate --force
```

### エラー: "Can't connect to local MySQL server through socket"

このエラーは、MySQLサーバーが起動していないか、RDSを使用している場合に発生します。

#### ケース1: EC2インスタンスにMySQLがインストールされている場合

##### ステップ1: MySQLサービスの状態を確認

```bash
# Amazon Linux 2 / RHEL / CentOS
# まず、どのサービス名でインストールされているか確認
sudo systemctl list-units --type=service | grep -i mysql
sudo systemctl list-units --type=service | grep -i mariadb

# 一般的なサービス名を試す
sudo systemctl status mysqld  # MySQLの場合
sudo systemctl status mysql   # 一部のディストリビューション
sudo systemctl status mariadb # MariaDBの場合（Amazon Linux 2のデフォルト）
```

##### ステップ2: MySQLサービスを起動

```bash
# Amazon Linux 2 / RHEL / CentOS
sudo systemctl start mysqld
sudo systemctl enable mysqld

# Ubuntu / Debian
sudo systemctl start mysql
sudo systemctl enable mysql
```

##### ステップ3: MySQLが起動しているか確認

```bash
# プロセスを確認
ps aux | grep mysql

# ポート3306がリッスンしているか確認
sudo netstat -tlnp | grep 3306
# または
sudo ss -tlnp | grep 3306
```

##### ステップ4: MySQLに接続テスト

```bash
mysql -u root -p
```

#### ケース2: AWS RDSを使用している場合

RDSを使用している場合、`.env`ファイルの`DB_HOST`をRDSエンドポイントに設定する必要があります。

##### ステップ1: RDSエンドポイントを確認

AWSコンソールでRDSインスタンスのエンドポイントを確認します。

##### ステップ2: .envファイルを更新

```bash
# .envファイルを編集
nano .env
```

以下のように設定：

```env
DB_CONNECTION=mysql
DB_HOST=your-rds-endpoint.region.rds.amazonaws.com
DB_PORT=3306
DB_DATABASE=carefle
DB_USERNAME=admin
DB_PASSWORD=your-password-here
```

**重要**: RDSエンドポイントは、AWSコンソールのRDSダッシュボードで確認できます。

##### ステップ3: セキュリティグループの確認

RDSのセキュリティグループで、EC2インスタンスからの接続（ポート3306）が許可されているか確認してください。

##### ステップ4: 接続テスト

```bash
# RDSエンドポイントに接続テスト
mysql -h your-rds-endpoint.region.rds.amazonaws.com -u admin -p

# 接続できたら、設定キャッシュをクリア
php artisan config:clear

# Laravelから接続テスト
php artisan tinker
>>> DB::connection()->getPdo();
>>> exit
```

#### ケース3: MySQLがインストールされていない場合

EC2インスタンスにMySQLがインストールされていない場合、インストールするか、RDSを使用する必要があります。

##### MySQLをインストール（Amazon Linux 2）

```bash
# MariaDBをインストール（Amazon Linux 2のデフォルト）
sudo yum install -y mariadb-server
sudo systemctl start mariadb
sudo systemctl enable mariadb

# 初期設定
sudo mysql_secure_installation
```

##### MySQLをインストール（Ubuntu）

```bash
sudo apt update
sudo apt install -y mysql-server
sudo systemctl start mysql
sudo systemctl enable mysql

# 初期設定
sudo mysql_secure_installation
```

### エラー: "Class not found"

```bash
# オートローダーの再生成
composer dump-autoload
```

### エラー: "Permission denied"

```bash
# ストレージとキャッシュの権限を確認
chmod -R 775 storage bootstrap/cache
```

### エラー: "Route not found"

```bash
# ルートキャッシュをクリアして再生成
php artisan route:clear
php artisan route:cache
```

### エラー: "View not found"

```bash
# ビューキャッシュをクリア
php artisan view:clear
```

### パフォーマンスが遅い

```bash
# すべてのキャッシュをクリアして再生成
php artisan optimize:clear
php artisan optimize
```

## ロールバック手順

問題が発生した場合、前のバージョンにロールバックできます。

```bash
# 前のコミットに戻る
git log  # 前のコミットハッシュを確認
git checkout <previous-commit-hash>

# 依存関係とアセットを再構築
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Laravelの最適化
php artisan optimize

# 必要に応じてマイグレーションをロールバック
php artisan migrate:rollback --step=1
```

## セキュリティチェックリスト

- [ ] `APP_DEBUG=false`に設定されている
- [ ] `.env`ファイルが適切に保護されている（権限: 600）
- [ ] データベースパスワードが強力である
- [ ] AWS認証情報が安全に管理されている
- [ ] HTTPSが有効になっている
- [ ] 不要なファイルが公開されていない（`.git`, `.env`など）

## 定期メンテナンス

### ログの確認

```bash
# エラーログを確認
tail -f storage/logs/laravel.log
```

### キャッシュのクリア（必要に応じて）

```bash
# アプリケーションキャッシュ
php artisan cache:clear

# 設定キャッシュ
php artisan config:clear

# ビューキャッシュ
php artisan view:clear
```

### データベースのバックアップ

```bash
# 定期的なバックアップ（cronで設定推奨）
mysqldump -u carefle_user -p carefle > backup_$(date +%Y%m%d).sql
```

## 参考リンク

- [Laravel公式デプロイメントガイド](https://laravel.com/docs/deployment)
- [DATABASE_SETUP.md](./DATABASE_SETUP.md) - データベース設定
- [AWS_BEDROCK_SETUP.md](./AWS_BEDROCK_SETUP.md) - AWS Bedrock設定

