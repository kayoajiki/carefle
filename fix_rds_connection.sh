#!/bin/bash

# AWS RDS接続エラー修正スクリプト

echo "🔍 AWS RDS接続エラーを修正します..."
echo ""

# .envファイルから設定を取得
DB_HOST=$(grep "^DB_HOST=" .env | cut -d '=' -f2)
DB_USERNAME=$(grep "^DB_USERNAME=" .env | cut -d '=' -f2)
DB_DATABASE=$(grep "^DB_DATABASE=" .env | cut -d '=' -f2)

echo "📋 現在の設定:"
echo "  DB_HOST: $DB_HOST"
echo "  DB_USERNAME: $DB_USERNAME"
echo "  DB_DATABASE: $DB_DATABASE"
echo ""

# RDSへの接続テスト
echo "🔍 RDSへの接続をテストしています..."
if mysql -h "$DB_HOST" -u "$DB_USERNAME" -p -e "SELECT 1;" 2>&1 | grep -q "Access denied"; then
    echo "❌ 認証エラーが発生しています"
    echo ""
    echo "考えられる原因:"
    echo "1. パスワードが間違っている"
    echo "2. ユーザーが存在しない"
    echo "3. セキュリティグループでEC2からの接続が許可されていない"
    echo ""
    echo "対処方法:"
    echo "1. AWS RDSコンソールでユーザーとパスワードを確認"
    echo "2. セキュリティグループでEC2インスタンス（172.31.17.195）からの接続を許可"
    echo "3. .envファイルのDB_PASSWORDを正しい値に修正"
fi



