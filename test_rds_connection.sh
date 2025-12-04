#!/bin/bash

# RDS接続テストスクリプト

echo "🔍 RDS接続をテストします..."
echo ""

# 1. 変数の設定を確認
echo "=== 1. 変数の設定確認 ==="
DB_HOST=$(grep "^DB_HOST=" .env | cut -d '=' -f2 | tr -d ' ')
DB_USERNAME=$(grep "^DB_USERNAME=" .env | cut -d '=' -f2 | tr -d ' ')
DB_DATABASE=$(grep "^DB_DATABASE=" .env | cut -d '=' -f2 | tr -d ' ')
DB_PASSWORD=$(grep "^DB_PASSWORD=" .env | cut -d '=' -f2)

echo "DB_HOST: $DB_HOST"
echo "DB_USERNAME: $DB_USERNAME"
echo "DB_DATABASE: $DB_DATABASE"
echo "DB_PASSWORD: ${DB_PASSWORD:0:3}***"
echo ""

# 2. パスワードから引用符を除去
CLEAN_PASSWORD=$(echo "$DB_PASSWORD" | sed "s/^[\"']//;s/[\"']$//" | tr -d ' ')

# 3. MySQLクライアントがインストールされているか確認
echo "=== 2. MySQLクライアントの確認 ==="
if command -v mysql &> /dev/null; then
    echo "✅ MySQLクライアントがインストールされています"
    mysql --version
else
    echo "❌ MySQLクライアントがインストールされていません"
    echo "インストール方法:"
    echo "  sudo yum install mysql -y  # Amazon Linux"
    echo "  または"
    echo "  sudo apt-get install mysql-client -y  # Ubuntu"
    exit 1
fi
echo ""

# 4. 接続テスト（方法1: パスワードを直接指定）
echo "=== 3. 接続テスト（パスワードを直接指定） ==="
echo "コマンド: mysql -h \"$DB_HOST\" -u \"$DB_USERNAME\" -p\"$CLEAN_PASSWORD\" \"$DB_DATABASE\" -e \"SELECT 1;\""
echo ""

if mysql -h "$DB_HOST" -u "$DB_USERNAME" -p"$CLEAN_PASSWORD" "$DB_DATABASE" -e "SELECT 1;" 2>&1; then
    echo "✅ 接続成功！"
    CONNECTION_SUCCESS=true
else
    echo "❌ 接続失敗"
    CONNECTION_SUCCESS=false
fi
echo ""

# 5. 接続テスト（方法2: インタラクティブにパスワードを入力）
if [ "$CONNECTION_SUCCESS" = false ]; then
    echo "=== 4. 接続テスト（インタラクティブ） ==="
    echo "以下のコマンドで手動で接続を試してください："
    echo ""
    echo "mysql -h $DB_HOST -u $DB_USERNAME -p $DB_DATABASE"
    echo ""
    echo "パスワードを入力してください"
    echo ""
fi

# 6. ネットワーク接続の確認
echo "=== 5. ネットワーク接続の確認 ==="
echo "RDSエンドポイントへの接続をテスト:"
if ping -c 1 -W 2 "$DB_HOST" > /dev/null 2>&1; then
    echo "✅ $DB_HOST に接続可能"
else
    echo "⚠️  $DB_HOST にpingできません（DNS解決の問題かもしれません）"
fi

# ポート3306が開いているか確認
echo ""
echo "ポート3306への接続をテスト:"
if timeout 3 bash -c "echo > /dev/tcp/$DB_HOST/3306" 2>/dev/null; then
    echo "✅ ポート3306が開いています"
else
    echo "❌ ポート3306に接続できません"
    echo "   セキュリティグループの設定を確認してください"
fi
echo ""

# 7. 推奨される次のステップ
echo "=== 6. 推奨される次のステップ ==="
if [ "$CONNECTION_SUCCESS" = false ]; then
    echo "1. セキュリティグループでEC2インスタンス（172.31.17.195）からの接続を許可"
    echo "2. RDSコンソールでマスターパスワードを確認/リセット"
    echo "3. .envファイルのDB_PASSWORDを正しい値に更新"
    echo "4. キャッシュをクリア: php artisan config:clear"
else
    echo "✅ 直接MySQL接続は成功しました"
    echo "Laravelの接続エラーは、キャッシュの問題の可能性があります"
    echo "以下を実行してください:"
    echo "  php artisan config:clear"
    echo "  php artisan cache:clear"
    echo "  rm -f bootstrap/cache/config.php"
fi

