# データベース設定ガイド

## 開発環境（SQLite）

開発環境では、デフォルトでSQLiteが使用されます。追加の設定は不要です。

```env
DB_CONNECTION=sqlite
```

## 本番環境（MySQL）

本番環境でMySQLを使用する場合、以下の手順に従って設定してください。

### 1. MySQLデータベースの作成

MySQLサーバーに接続し、データベースとユーザーを作成します：

```sql
CREATE DATABASE carefle CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'carefle_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON carefle.* TO 'carefle_user'@'localhost';
FLUSH PRIVILEGES;
```

### 2. .envファイルの設定

本番環境の`.env`ファイルで、以下のようにMySQL設定を有効化します：

```env
# データベース接続をMySQLに変更
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=carefle
DB_USERNAME=carefle_user
DB_PASSWORD=strong_password_here

# 文字セットと照合順序（オプション、デフォルト値）
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

### 3. マイグレーションの実行

データベース接続を変更した後、マイグレーションを実行します：

```bash
php artisan migrate
```

### 4. 設定の確認

データベース接続が正しく設定されているか確認します：

```bash
php artisan db:show
```

または、Tinkerで確認：

```bash
php artisan tinker
>>> DB::connection()->getPdo();
```

## 環境別の設定例

### 開発環境（.env）

```env
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=sqlite
```

### 本番環境（.env）

```env
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=carefle
DB_USERNAME=carefle_user
DB_PASSWORD=your_secure_password
```

## トラブルシューティング

### 接続エラーが発生する場合

1. **MySQLサーバーが起動しているか確認**
   ```bash
   # macOS
   brew services list
   
   # Linux
   sudo systemctl status mysql
   ```

2. **認証情報が正しいか確認**
   - ユーザー名とパスワードが正しいか
   - データベース名が存在するか
   - ユーザーに適切な権限が付与されているか

3. **ポート番号を確認**
   - デフォルトは3306
   - カスタムポートを使用している場合は`.env`で指定

4. **文字セットの問題**
   - `utf8mb4`を使用することを推奨（絵文字対応）

### マイグレーションエラー

SQLiteからMySQLに移行する場合、一部のデータ型や制約が異なる場合があります。エラーが発生した場合は、マイグレーションファイルを確認してください。

## バックアップ

本番環境では定期的なバックアップを実施してください：

```bash
# データベースのバックアップ
mysqldump -u carefle_user -p carefle > backup_$(date +%Y%m%d).sql

# リストア
mysql -u carefle_user -p carefle < backup_20250101.sql
```

## セキュリティのベストプラクティス

1. **強力なパスワードを使用**
2. **最小権限の原則** - アプリケーション用ユーザーには必要な権限のみ付与
3. **SSL接続の使用**（可能な場合）
4. **環境変数の保護** - `.env`ファイルは絶対にコミットしない
5. **定期的なアップデート** - MySQLサーバーを最新のセキュリティパッチに更新

