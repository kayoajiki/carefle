# AWS Secrets Managerで固定パスワードを設定する方法

## 問題

AWS Secrets Managerからデータベースの認証情報を取得しているが、しょっちゅう変わってしまう（自動ローテーションが有効になっている可能性があります）。

## 解決方法

### 方法1: Secrets Managerの自動ローテーションを無効にする（推奨）

#### ステップ1: AWSコンソールでSecrets Managerを確認

1. AWSコンソールにログイン
2. **Secrets Manager**サービスに移動
3. データベース認証情報が保存されているシークレットを選択

#### ステップ2: 自動ローテーション設定を確認・無効化

1. シークレットの詳細ページで「**ローテーション設定**」タブを開く
2. 自動ローテーションが有効になっている場合、「**ローテーションを無効にする**」をクリック
3. 確認ダイアログで「**無効にする**」を選択

#### ステップ3: 固定のパスワードを設定

1. シークレットの詳細ページで「**シークレットの値を取得**」をクリック
2. 「**プレーンテキスト**」タブで現在の値を確認
3. 固定したいパスワードに変更する場合は、「**シークレットの値を更新**」をクリック

**重要**: パスワードを変更する場合は、MySQLデータベース側でも同じパスワードに変更する必要があります。

### 方法2: .envファイルに直接設定する（Secrets Managerを使わない）

Secrets Managerを使わずに、`.env`ファイルに直接固定のパスワードを設定する方法です。

#### ステップ1: 固定のパスワードを決定

強力な固定パスワードを生成します：

```bash
# ランダムなパスワードを生成（例）
openssl rand -base64 32
```

#### ステップ2: MySQLデータベースでパスワードを設定

```bash
# MySQLにrootで接続
mysql -u root -p
```

MySQL内で実行：

```sql
-- 既存のユーザーのパスワードを変更
ALTER USER 'admin'@'localhost' IDENTIFIED BY 'your-fixed-password-here';
ALTER USER 'admin'@'%' IDENTIFIED BY 'your-fixed-password-here';

-- 権限を反映
FLUSH PRIVILEGES;
```

#### ステップ3: .envファイルを更新

本番サーバーで`.env`ファイルを編集：

```bash
# .envファイルを編集
nano /path/to/carefle/.env
# または
vi /path/to/carefle/.env
```

以下のように設定：

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=carefle
DB_USERNAME=admin
DB_PASSWORD=your-fixed-password-here
```

#### ステップ4: 設定キャッシュをクリア

```bash
php artisan config:clear
php artisan cache:clear
```

#### ステップ5: 接続テスト

```bash
# 接続テスト
php artisan tinker
>>> DB::connection()->getPdo();
# 接続成功すれば、PDOオブジェクトが表示されます
>>> exit
```

### 方法3: Secrets Managerのシークレットを固定値に更新

Secrets Managerは使い続けるが、固定のパスワードに更新する方法です。

#### ステップ1: 固定のパスワードを決定

強力な固定パスワードを生成します。

#### ステップ2: MySQLデータベースでパスワードを設定

```sql
ALTER USER 'admin'@'localhost' IDENTIFIED BY 'your-fixed-password-here';
ALTER USER 'admin'@'%' IDENTIFIED BY 'your-fixed-password-here';
FLUSH PRIVILEGES;
```

#### ステップ3: Secrets Managerのシークレットを更新

1. AWSコンソールでSecrets Managerに移動
2. 該当のシークレットを選択
3. 「**シークレットの値を更新**」をクリック
4. JSON形式で以下のように更新：

```json
{
  "username": "admin",
  "password": "your-fixed-password-here",
  "engine": "mysql",
  "host": "127.0.0.1",
  "port": 3306,
  "dbname": "carefle"
}
```

5. 「**保存**」をクリック

#### ステップ4: 自動ローテーションを無効にする

方法1のステップ2を参照して、自動ローテーションを無効にします。

## 推奨される方法

**方法2（.envファイルに直接設定）**を推奨します。

理由：
- シンプルで管理が容易
- Secrets Managerのコストがかからない
- 自動ローテーションによる予期しない変更がない
- デプロイ時の設定が簡単

ただし、セキュリティ上の考慮事項：
- `.env`ファイルの権限を適切に設定（`chmod 600 .env`）
- `.env`ファイルがGitにコミットされないことを確認（`.gitignore`に含まれている）
- パスワードは強力なものを使用

## セキュリティのベストプラクティス

### .envファイルの権限設定

```bash
# .envファイルの権限を制限（所有者のみ読み書き可能）
chmod 600 /path/to/carefle/.env

# 所有者を確認
ls -la /path/to/carefle/.env
```

### パスワードの強度

- 最低16文字以上
- 大文字・小文字・数字・記号を含む
- 辞書に載っている単語を避ける
- 定期的に変更する（ただし、自動ローテーションは無効）

## トラブルシューティング

### パスワード変更後も接続できない場合

1. **MySQLのパスワードが正しく設定されているか確認**
   ```bash
   mysql -u admin -p
   # 新しいパスワードを入力
   ```

2. **.envファイルのパスワードが正しいか確認**
   ```bash
   cat .env | grep DB_PASSWORD
   ```

3. **設定キャッシュをクリア**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

4. **接続テスト**
   ```bash
   php artisan tinker
   >>> DB::connection()->getPdo();
   ```

## 注意事項

- パスワードを変更した場合は、必ずMySQLデータベース側でも同じパスワードに変更してください
- 本番環境の`.env`ファイルは絶対にGitにコミットしないでください
- パスワードは安全に管理し、必要最小限の人員のみがアクセスできるようにしてください

