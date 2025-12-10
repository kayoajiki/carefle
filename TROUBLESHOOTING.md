# トラブルシューティングガイド

## データベース接続エラー

### エラー: `SQLSTATE[HY000] [1045] Access denied for user 'admin'@'xxx' (using password: YES)`

**原因**: RDSへの接続認証情報が間違っている、または設定が正しく読み込まれていない

**対処方法**:

1. **`.env`ファイルの確認**
   ```bash
   cd /var/www/carefle
   cat .env | grep -E "DB_CONNECTION|DB_HOST|DB_DATABASE|DB_USERNAME|DB_PASSWORD"
   ```

2. **MySQLコマンドラインで接続テスト**
   ```bash
   mysql -h [RDSエンドポイント] -u [ユーザー名] -p
   ```
   - 接続できない場合: パスワードが間違っている可能性

3. **RDSのパスワードをリセット（AWS Secrets Manager使用時）**
   - AWSコンソール → RDS → データベース → 該当DB → 「変更」
   - 「マスターパスワードの変更」を選択
   - 新しいパスワードを設定
   - `.env`の`DB_PASSWORD`も更新

4. **設定とキャッシュをクリア**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   php artisan route:clear
   ```

5. **セキュリティグループの確認**
   - RDSのセキュリティグループで、EC2インスタンスからの接続が許可されているか確認
   - インバウンドルール: `MySQL/Aurora` (ポート3306) でEC2のセキュリティグループIDまたはIPアドレスを許可

---

## マイグレーションエラー

### エラー: `SQLSTATE[42S01]: Base table or view already exists: 1050 Table 'xxx' already exists`

**原因**: マイグレーションの状態と実際のデータベースの状態が一致していない（重複マイグレーション）

**対処方法**:

1. **マイグレーションの状態を確認**
   ```bash
   php artisan migrate:status
   ```

2. **重複マイグレーションをスキップ**
   ```bash
   mysql -h [RDSエンドポイント] -u [ユーザー名] -p [データベース名]
   ```
   MySQL内で：
   ```sql
   INSERT INTO migrations (migration, batch) VALUES ('マイグレーション名', バッチ番号);
   EXIT;
   ```

3. **新しいマイグレーションのみを個別に実行**
   ```bash
   php artisan migrate --path=database/migrations/YYYY_MM_DD_HHMMSS_migration_name.php --force
   ```

---

## 開発環境と本番環境の違い

- **開発環境**: SQLite (`DB_CONNECTION=sqlite`)
- **本番環境**: MySQL/RDS (`DB_CONNECTION=mysql`)

本番環境の`.env`で`DB_CONNECTION=mysql`になっているか確認すること。

---

## よくある問題と対処

### 1. パスワードが正しいのに接続できない
- `.env`のパスワードがクォートで囲まれているか確認
- 設定キャッシュをクリア
- RDSのセキュリティグループを確認

### 2. MySQLコマンドラインでは接続できるが、Laravelから接続できない
- `.env`のパスワードが正しく設定されているか確認
- `php artisan config:clear`を実行
- `php artisan config:cache`で設定を再生成

### 3. AWS Secrets Managerを使用している場合
- IAMロールに`secretsmanager:GetSecretValue`権限があるか確認
- シークレット名が正しいか確認
- 一時的に`.env`に直接パスワードを設定することも可能（非推奨）




