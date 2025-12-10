#!/bin/bash

# データベース設定確認スクリプト
# 本番環境で実行

echo "🔍 データベース設定を確認しています..."
echo ""

# .envファイルの存在確認
if [ ! -f .env ]; then
    echo "❌ .envファイルが見つかりません"
    exit 1
fi

echo "📋 現在のデータベース設定:"
echo "----------------------------------------"
grep "^DB_" .env | sed 's/\(PASSWORD=\).*/\1***HIDDEN***/'
echo "----------------------------------------"
echo ""

# 実際の設定値を取得（パスワードは非表示）
DB_CONNECTION=$(grep "^DB_CONNECTION=" .env | cut -d '=' -f2)
DB_HOST=$(grep "^DB_HOST=" .env | cut -d '=' -f2)
DB_PORT=$(grep "^DB_PORT=" .env | cut -d '=' -f2)
DB_DATABASE=$(grep "^DB_DATABASE=" .env | cut -d '=' -f2)
DB_USERNAME=$(grep "^DB_USERNAME=" .env | cut -d '=' -f2)

echo "接続情報:"
echo "  接続タイプ: $DB_CONNECTION"
echo "  ホスト: $DB_HOST"
echo "  ポート: $DB_PORT"
echo "  データベース: $DB_DATABASE"
echo "  ユーザー名: $DB_USERNAME"
echo ""

# ホストへの接続テスト
if [ -n "$DB_HOST" ]; then
    echo "🔍 データベースホストへの接続をテストしています..."
    if ping -c 1 "$DB_HOST" > /dev/null 2>&1; then
        echo "✅ ホスト $DB_HOST に接続可能"
    else
        echo "❌ ホスト $DB_HOST に接続できません"
    fi
fi

echo ""
echo "📝 次のステップ:"
echo "1. 上記の設定が正しいか確認してください"
echo "2. MySQLでユーザーと権限を確認してください"
echo "3. 必要に応じて、.envファイルを修正してください"



