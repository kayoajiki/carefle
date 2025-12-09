#!/bin/bash

# 本番環境のデータベース接続エラー修正スクリプト
# 使用方法: 本番サーバーで実行

echo "🔧 データベース接続エラーの修正を開始します..."

# 1. .envファイルのバックアップ
echo "📦 .envファイルをバックアップしています..."
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
echo "✅ バックアップ完了"

# 2. 現在のデータベース設定を確認
echo ""
echo "📋 現在のデータベース設定:"
echo "----------------------------------------"
grep "^DB_" .env
echo "----------------------------------------"

# 3. キャッシュをクリア
echo ""
echo "🧹 キャッシュをクリアしています..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo "✅ キャッシュクリア完了"

# 4. データベース接続をテスト
echo ""
echo "🔍 データベース接続をテストしています..."
php artisan tinker --execute="DB::connection()->getPdo(); echo '✅ データベース接続成功';" 2>&1

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ データベース接続が正常に動作しています！"
else
    echo ""
    echo "❌ データベース接続に失敗しました。"
    echo ""
    echo "以下の点を確認してください："
    echo "1. .envファイルのDB_USERNAMEとDB_PASSWORDが正しいか"
    echo "2. データベースユーザーが存在するか"
    echo "3. ユーザーに適切な権限が付与されているか"
    echo "4. 接続元IPアドレス（172.31.17.195）からの接続が許可されているか"
    echo ""
    echo "MySQLで確認するコマンド："
    echo "  SELECT user, host FROM mysql.user WHERE user = 'admin';"
    echo "  SHOW GRANTS FOR 'admin'@'%';"
fi


