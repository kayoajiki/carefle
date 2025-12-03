# 本番環境へのデプロイ手順

## 前提条件

- 本番サーバーへのSSHアクセス権限
- 本番サーバーにPHP 8.2以上、Composer、Node.jsがインストールされていること
- 本番環境用の`.env`ファイルが準備されていること

## デプロイ手順

### 1. コードをGitHubにプッシュ（既に完了）

```bash
git add .
git commit -m "変更内容の説明"
git push origin main
```

### 2. 本番サーバーにSSH接続

```bash
ssh user@your-production-server.com
```

### 3. プロジェクトディレクトリに移動

```bash
cd /path/to/carefle
```

### 4. 最新のコードを取得

```bash
git pull origin main
```

### 5. 依存関係の更新

```bash
# Composer依存関係の更新
composer install --no-dev --optimize-autoloader

# npm依存関係の更新
npm ci
```

### 6. アセットのビルド

```bash
npm run build
```

### 7. 環境設定の確認

`.env`ファイルが本番環境用に正しく設定されているか確認：

```bash
# 重要な設定項目
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# データベース設定
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# AWS設定（Bedrock使用時）
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=your_region
```

### 8. データベースマイグレーション実行

```bash
php artisan migrate --force
```

**注意**: 本番環境では`--force`フラグが必要です（対話プロンプトをスキップ）

### 9. キャッシュのクリアと最適化

```bash
# 設定キャッシュのクリア
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# 本番環境用の最適化
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 10. ストレージリンクの確認

```bash
php artisan storage:link
```

### 11. 権限の設定

```bash
# storageとbootstrap/cacheディレクトリに書き込み権限を付与
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 12. アプリケーションの再起動（必要に応じて）

```bash
# PHP-FPMの再起動（サーバーによって異なる）
sudo systemctl restart php8.2-fpm
# または
sudo service php-fpm restart
```

### 13. 動作確認

ブラウザで本番環境のURLにアクセスし、以下を確認：
- トップページが表示される
- ログイン・登録が動作する
- 各機能が正常に動作する

## 自動デプロイ（推奨）

### GitHub Actionsを使用する場合

`.github/workflows/deploy.yml`を作成：

```yaml
name: Deploy to Production

on:
  push:
    branches:
      - main

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Deploy to server
      uses: appleboy/ssh-action@master
      with:
        host: ${{ secrets.HOST }}
        username: ${{ secrets.USERNAME }}
        key: ${{ secrets.SSH_KEY }}
        script: |
          cd /path/to/carefle
          git pull origin main
          composer install --no-dev --optimize-autoloader
          npm ci
          npm run build
          php artisan migrate --force
          php artisan config:cache
          php artisan route:cache
          php artisan view:cache
          php artisan storage:link
```

### デプロイスクリプトを使用する場合

`deploy.sh`を作成：

```bash
#!/bin/bash

set -e

echo "🚀 デプロイを開始します..."

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

## ロールバック手順

問題が発生した場合のロールバック：

```bash
# 前のコミットに戻る
git log  # コミット履歴を確認
git checkout <previous-commit-hash>

# 依存関係とアセットを再構築
composer install --no-dev --optimize-autoloader
npm ci
npm run build

# キャッシュをクリア
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 注意事項

1. **データベースバックアップ**: マイグレーション実行前に必ずデータベースをバックアップ
2. **メンテナンスモード**: デプロイ中はメンテナンスモードを有効化
   ```bash
   php artisan down
   # デプロイ作業
   php artisan up
   ```
3. **環境変数**: `.env`ファイルは絶対にGitにコミットしない
4. **ログ確認**: デプロイ後は`storage/logs/laravel.log`を確認
5. **テスト**: 可能であれば、ステージング環境で事前にテスト

## トラブルシューティング

### エラー: "Class not found"
```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### エラー: "Permission denied"
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### エラー: "Route not found"
```bash
php artisan route:clear
php artisan route:cache
```

### エラー: "View not found"
```bash
php artisan view:clear
php artisan view:cache
```



