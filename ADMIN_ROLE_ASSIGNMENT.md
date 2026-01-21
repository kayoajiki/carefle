# 管理者権限付与コマンド

## 概要

既存ユーザーに管理者権限を付与するためのArtisanコマンドです。メールアドレス、ID、または環境変数から指定できます。

## 使用方法

### 1. メールアドレスで指定

```bash
php artisan admin:assign user@example.com
```

### 2. IDで指定

```bash
php artisan admin:assign --id=1
```

または、引数として数値を指定：

```bash
php artisan admin:assign 1
```

### 3. 環境変数から自動設定（推奨）

`.env`ファイルに`ADMIN_USER_ID`を設定：

```env
# 開発環境
ADMIN_USER_ID=1

# 本番環境
ADMIN_USER_ID=2
```

その後、以下のコマンドで環境変数から自動的に管理者権限を付与：

```bash
php artisan admin:assign --use-env
```

これにより、開発環境と本番環境で異なるIDに管理者権限を付与できます。

## セキュリティ

### ✅ 安全な点

1. **メールアドレスはコードに含まれません**
   - コマンド実行時に引数として渡すだけ
   - コード内にハードコードされていない
   - GitHubにコミットされることはありません

2. **`.env`ファイルはGitHubにコミットされません**
   - `.gitignore`に`.env`が含まれているため、環境変数も安全です

3. **コマンドファイル自体は安全**
   - `app/Console/Commands/AssignAdminRole.php`には機密情報は含まれていません
   - メールアドレスは実行時にのみ指定されます

### ⚠️ 注意事項

1. **本番環境での実行**
   - SSH接続で本番サーバーに接続してから実行してください
   - 実行前にメールアドレスを正確に確認してください

2. **ログファイル**
   - コマンド実行時のログが`storage/logs/laravel.log`に記録される可能性があります
   - 本番環境ではログファイルのアクセス権限を適切に設定してください

3. **実行履歴**
   - シェルの履歴（`.bash_history`など）にコマンドが残る可能性があります
   - 必要に応じて履歴をクリアしてください：
     ```bash
     history -c  # 現在のセッションの履歴をクリア
     ```

## 本番環境での実行手順

### 方法1: 環境変数を使用（推奨）

本番環境の`.env`ファイルに`ADMIN_USER_ID`を設定し、環境変数から自動的に管理者権限を付与します。

#### ステップ1: 本番サーバーにSSH接続

```bash
ssh ec2-user@your-production-server.com
```

#### ステップ2: プロジェクトディレクトリに移動

```bash
cd /path/to/carefle
```

#### ステップ3: `.env`ファイルを編集

```bash
nano .env
```

以下の行を追加（または既存の値を変更）：

```env
ADMIN_USER_ID=2
```

**重要**: 開発環境の`.env`には設定しないでください。本番環境の`.env`にのみ設定します。

#### ステップ4: コマンド実行

```bash
php artisan admin:assign --use-env
```

これで、本番環境の`.env`に設定されたIDのユーザーに管理者権限が付与されます。

### 方法2: IDを直接指定

本番環境で直接IDを指定して管理者権限を付与します。

#### ステップ1: 本番サーバーにSSH接続

```bash
ssh ec2-user@your-production-server.com
```

#### ステップ2: プロジェクトディレクトリに移動

```bash
cd /path/to/carefle
```

#### ステップ3: コマンド実行（IDを指定）

```bash
php artisan admin:assign --id=2
```

または、引数として数値を指定：

```bash
php artisan admin:assign 2
```

### 方法3: メールアドレスで指定

本番環境のユーザーのメールアドレスを指定して管理者権限を付与します。

#### ステップ1: 本番サーバーにSSH接続

```bash
ssh ec2-user@your-production-server.com
```

#### ステップ2: プロジェクトディレクトリに移動

```bash
cd /path/to/carefle
```

#### ステップ3: コマンド実行（メールアドレスを指定）

```bash
php artisan admin:assign admin@example.com
```

### ステップ4: 確認

実行結果を確認し、管理者権限が正しく付与されたことを確認してください。

```bash
php artisan tinker
>>> \App\Models\User::find(2)->is_admin;
=> true
```

## ユーザーIDの確認方法

### 方法1: 管理者画面で確認（推奨）

1. 管理者画面にログイン
2. 「ユーザー管理」ページにアクセス
3. ユーザー一覧の「ID」列でユーザーIDを確認
4. または、ユーザーの「詳細」をクリックして、基本情報タブでユーザーIDを確認

### 方法2: メールアドレスからIDを確認

メールアドレスがわかっている場合、以下のコマンドでIDを確認できます：

```bash
php artisan tinker
>>> $user = \App\Models\User::where('email', 'user@example.com')->first();
>>> echo "ユーザーID: " . $user->id . "\n";
>>> echo "名前: " . $user->name . "\n";
>>> echo "メール: " . $user->email . "\n";
```

### 方法3: コマンド実行時に確認

メールアドレスでコマンドを実行すると、ユーザーIDも表示されます：

```bash
php artisan admin:assign user@example.com
```

実行結果にユーザーIDが表示されます。

## トラブルシューティング

### エラー: "メールアドレスのユーザーが見つかりません"

- メールアドレスのスペルミスを確認
- データベースでユーザーの存在を確認：
  ```bash
  php artisan tinker
  >>> \App\Models\User::where('email', 'user@example.com')->first();
  ```

### エラー: "Class not found"

オートローダーを再生成：
```bash
composer dump-autoload
```

## ベストプラクティス

1. **最小権限の原則**: 必要最小限のユーザーにのみ管理者権限を付与
2. **定期的な見直し**: 管理者権限を持つユーザーを定期的に見直す
3. **監査ログ**: 管理者権限の付与・削除を記録する（将来的な機能拡張）
4. **2要素認証**: 管理者アカウントには2要素認証を有効にすることを推奨
