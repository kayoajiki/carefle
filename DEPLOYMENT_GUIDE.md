# キャリフレ 本番環境デプロイガイド

## 概要

本ドキュメントは、キャリフレの本番環境へのデプロイ手順と、よく発生する問題とその解決方法をまとめたものです。

## 前提条件

- 本番サーバー（EC2）へのSSHアクセス権限
- 本番データベース（RDS）への接続権限
- Gitリポジトリへのアクセス権限
- 本番環境用の`.env`ファイルが準備されていること

## 標準デプロイ手順

### 1. 本番サーバーにSSH接続

```bash
ssh ec2-user@your-production-server.com
```

### 2. プロジェクトディレクトリに移動

```bash
cd /path/to/carefle
```

### 3. 最新のコードを取得

```bash
git pull origin main
```

### 4. 依存関係の更新

```bash
# Composer依存関係の更新
composer install --no-dev --optimize-autoloader

# npm依存関係の更新
npm ci
```

### 5. アセットのビルド

```bash
npm run build
```

### 6. 環境設定の確認

`.env`ファイルが本番環境用に正しく設定されているか確認：

```bash
# データベース設定を確認
grep "^DB_" .env
```

**重要な設定項目**:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=your-rds-endpoint.rds.amazonaws.com
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password

AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=your_region
```

### 7. データベースマイグレーション実行

**重要**: マイグレーション実行前に、必ずデータベースのバックアップを取ってください。

```bash
# マイグレーションの状態を確認
php artisan migrate:status

# マイグレーションを実行
php artisan migrate --force
```

**注意**: 本番環境では`--force`フラグが必要です（対話プロンプトをスキップ）

### 8. キャッシュのクリアと最適化

```bash
# 設定キャッシュのクリア
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 本番環境用の最適化
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 9. ストレージリンクの確認

```bash
php artisan storage:link
```

### 10. 権限の設定

```bash
# storageとbootstrap/cacheディレクトリに書き込み権限を付与
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 11. 動作確認

ブラウザで本番環境のURLにアクセスし、以下を確認：
- トップページが表示される
- ログイン・登録が動作する
- 各機能が正常に動作する

## よく発生する問題と解決方法

### 問題1: データベース接続エラー

#### エラーメッセージ
```
SQLSTATE[HY000] [1045] Access denied for user 'admin'@'172.31.17.195' (using password: YES)
```

#### 原因
- パスワードが間違っている
- ユーザーが存在しない
- セキュリティグループで接続が許可されていない
- `.env`ファイルの`DB_PASSWORD`が正しく設定されていない

#### 解決方法

**ステップ1: `.env`ファイルの設定を確認**

```bash
# データベース設定を確認
grep "^DB_" .env
```

**ステップ2: 直接MySQL接続でテスト**

```bash
# .envから設定を取得
DB_HOST=$(grep "^DB_HOST=" .env | cut -d '=' -f2 | tr -d ' ')
DB_USERNAME=$(grep "^DB_USERNAME=" .env | cut -d '=' -f2 | tr -d ' ')
DB_DATABASE=$(grep "^DB_DATABASE=" .env | cut -d '=' -f2 | tr -d ' ')

# 直接MySQL接続を試す
mysql -h "$DB_HOST" -u "$DB_USERNAME" -p "$DB_DATABASE"
```

**ステップ3: パスワードの確認・修正**

```bash
# .envファイルを編集
nano .env
```

パスワードに引用符が含まれている場合、外してください：
```env
# 正しい形式
DB_PASSWORD=your_actual_password

# 特殊文字が含まれる場合のみ引用符を使用
DB_PASSWORD="your_password_with_special_chars"
```

**ステップ4: キャッシュをクリア**

```bash
php artisan config:clear
php artisan cache:clear
```

**ステップ5: RDSセキュリティグループの確認**

AWS RDSコンソールで以下を確認：
1. RDSインスタンス → 「接続とセキュリティ」タブ
2. セキュリティグループをクリック
3. インバウンドルールを確認：
   - タイプ: MySQL/Aurora
   - ポート: 3306
   - ソース: EC2インスタンスのセキュリティグループ、または `172.31.17.195/32`

### 問題2: マイグレーションエラー - テーブルが既に存在する

#### エラーメッセージ
```
SQLSTATE[42S01]: Base table or view already exists: 1050 Table 'diagnoses' already exists
```

#### 原因
- テーブルは既に存在するが、`migrations`テーブルに記録されていない
- マイグレーションの状態とデータベースの実際の状態が不一致

#### 解決方法

**方法1: 既存のマイグレーションを手動で記録（推奨）**

```bash
# MySQLに接続
mysql -h your-rds-endpoint.rds.amazonaws.com -u admin -p carefle
```

MySQLで：

```sql
-- 最新のbatch番号を取得
SELECT MAX(batch) as max_batch FROM migrations;

-- 既存のマイグレーションを記録（存在しない場合のみ）
INSERT INTO migrations (migration, batch) 
SELECT '2025_10_29_104060_create_diagnoses_table', 
       (SELECT MAX(batch) FROM migrations) + 1
WHERE NOT EXISTS (
    SELECT 1 FROM migrations 
    WHERE migration = '2025_10_29_104060_create_diagnoses_table'
);

-- 確認
SELECT * FROM migrations ORDER BY batch DESC, id DESC LIMIT 10;

exit;
```

**方法2: 必要なマイグレーションのみ個別に実行**

```bash
# Pending状態のマイグレーションを個別に実行
php artisan migrate --path=database/migrations/2025_12_03_053126_create_reflection_chat_conversations_table.php --force
php artisan migrate --path=database/migrations/2025_12_03_053123_add_reflection_fields_to_diaries_table.php --force
```

### 問題3: マイグレーションエラー - カラムが既に存在する

#### エラーメッセージ
```
SQLSTATE[42S21]: Column already exists: 1060 Duplicate column name 'reflection_type'
```

#### 原因
- カラムは既に存在するが、`migrations`テーブルに記録されていない

#### 解決方法

**ステップ1: テーブルの構造を確認**

```bash
# MySQLに接続
mysql -h your-rds-endpoint.rds.amazonaws.com -u admin -p carefle
```

MySQLで：

```sql
-- テーブルの構造を確認
DESCRIBE diaries;
```

**ステップ2: 既存のマイグレーションを記録**

```sql
-- 既存のマイグレーションを記録
INSERT INTO migrations (migration, batch) 
SELECT '2025_12_03_053123_add_reflection_fields_to_diaries_table', 
       (SELECT MAX(batch) FROM migrations) + 1
WHERE NOT EXISTS (
    SELECT 1 FROM migrations 
    WHERE migration = '2025_12_03_053123_add_reflection_fields_to_diaries_table'
);

exit;
```

### 問題4: Git pull後のマイグレーション未実行

#### エラーメッセージ
```
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'carefle.reflection_chat_conversations' doesn't exist
```

#### 原因
- Git pullで新しいマイグレーションファイルが追加されたが、マイグレーションが実行されていない

#### 解決方法

**ステップ1: マイグレーションの状態を確認**

```bash
php artisan migrate:status
```

**ステップ2: マイグレーションを実行**

```bash
php artisan migrate --force
```

**ステップ3: エラーが発生した場合**

既存のテーブル/カラムが存在する場合は、上記の「問題2」「問題3」の解決方法を参照してください。

### 問題5: `.env`ファイルの`DB_PASSWORD`が設定されていない

#### 症状
- `grep "^DB_PASSWORD=" .env`の結果が空

#### 解決方法

```bash
# .envファイルを編集
nano .env
```

以下を追加：

```env
DB_PASSWORD=your_actual_password
```

**注意**: パスワードに引用符は不要です（特殊文字が含まれる場合のみ引用符を使用）

### 問題6: キャッシュが残っている

#### 症状
- `.env`ファイルを修正したが、変更が反映されない

#### 解決方法

```bash
# すべてのキャッシュをクリア
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 設定キャッシュファイルを削除
rm -f bootstrap/cache/config.php
```

## デプロイチェックリスト

デプロイ前に以下を確認：

- [ ] データベースのバックアップを取得
- [ ] `.env`ファイルの設定を確認
- [ ] データベース接続をテスト
- [ ] マイグレーションの状態を確認
- [ ] 必要な依存関係がインストールされている
- [ ] アセットがビルドされている

デプロイ後に以下を確認：

- [ ] マイグレーションが正常に実行された
- [ ] キャッシュがクリアされた
- [ ] アプリケーションが正常に動作する
- [ ] エラーログを確認（`storage/logs/laravel.log`）

## トラブルシューティングコマンド集

### データベース接続の確認

```bash
# 直接MySQL接続でテスト
DB_HOST=$(grep "^DB_HOST=" .env | cut -d '=' -f2 | tr -d ' ')
DB_USERNAME=$(grep "^DB_USERNAME=" .env | cut -d '=' -f2 | tr -d ' ')
DB_DATABASE=$(grep "^DB_DATABASE=" .env | cut -d '=' -f2 | tr -d ' ')
mysql -h "$DB_HOST" -u "$DB_USERNAME" -p "$DB_DATABASE"
```

### マイグレーションの状態確認

```bash
# マイグレーションの状態を確認
php artisan migrate:status

# 特定のマイグレーションのみ実行
php artisan migrate --path=database/migrations/your_migration_file.php --force
```

### キャッシュのクリア

```bash
# すべてのキャッシュをクリア
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
rm -f bootstrap/cache/config.php
```

### ログの確認

```bash
# 最新のエラーログを確認
tail -n 100 storage/logs/laravel.log | grep -i error

# リアルタイムでログを監視
tail -f storage/logs/laravel.log
```

## 自動デプロイスクリプト

`deploy.sh`を作成：

```bash
#!/bin/bash

set -e

echo "🚀 デプロイを開始します..."

# バックアップ（オプション）
# mysqldump -h your-rds-endpoint -u admin -p carefle > backup_$(date +%Y%m%d_%H%M%S).sql

# 最新のコードを取得
git pull origin main

# 依存関係の更新
composer install --no-dev --optimize-autoloader
npm ci

# アセットのビルド
npm run build

# マイグレーション実行
php artisan migrate --force

# キャッシュのクリアと最適化
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ストレージリンク
php artisan storage:link

# 権限の設定
chmod -R 775 storage bootstrap/cache

echo "✅ デプロイが完了しました！"
```

実行権限を付与：

```bash
chmod +x deploy.sh
```

デプロイ時は：

```bash
./deploy.sh
```

## データベースサーバーの場所を判別する方法

### 方法1: `.env`ファイルの`DB_HOST`を確認

```bash
grep "^DB_HOST=" .env
```

- `DB_HOST=localhost` または `DB_HOST=127.0.0.1` → **同じEC2インスタンス上**
- `DB_HOST=別のホスト名` → **別のサーバー（RDSなど）**

### 方法2: ローカルのMySQLサービスを確認

```bash
systemctl list-units --type=service | grep -E "mysql|mariadb"
```

- サービスが存在する → **同じEC2インスタンス上**
- サービスが存在しない → **別のサーバー**

### 方法3: 直接MySQL接続でテスト

```bash
# ローカル接続を試す
sudo mysql -u root -e "SELECT 1;"
```

- 接続できる → **同じEC2インスタンス上**
- 接続できない → **別のサーバー**

## よくある質問（FAQ）

### Q: マイグレーションを実行するタイミングは？

A: Git pull後、依存関係の更新後、アセットのビルド前に実行してください。

### Q: マイグレーションエラーが発生した場合、どうすればいい？

A: まず、マイグレーションの状態を確認し、既存のテーブル/カラムが存在する場合は、対応するマイグレーションを`migrations`テーブルに手動で記録してください。

### Q: `.env`ファイルのパスワードに引用符は必要？

A: 通常は不要です。特殊文字が含まれる場合のみ引用符を使用してください。

### Q: デプロイ中にメンテナンスモードを有効化すべき？

A: はい、推奨されます：

```bash
php artisan down
# デプロイ作業
php artisan up
```

### Q: マイグレーションをロールバックする方法は？

A: 特定のマイグレーションをロールバック：

```bash
php artisan migrate:rollback --step=1
```

## まとめ

本番環境へのデプロイでは、以下を必ず確認してください：

1. ✅ データベース接続が正常に動作する
2. ✅ `.env`ファイルの設定が正しい
3. ✅ マイグレーションの状態とデータベースの実際の状態が一致している
4. ✅ キャッシュがクリアされている
5. ✅ すべてのマイグレーションが実行されている

問題が発生した場合は、上記のトラブルシューティング手順を参照してください。


