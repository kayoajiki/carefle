#!/bin/bash

# データベース接続エラーの詳細診断スクリプト
# 本番環境で実行

echo "🔍 データベース接続エラーの詳細診断を開始します..."
echo ""

# 1. .envファイルの存在確認
if [ ! -f .env ]; then
    echo "❌ .envファイルが見つかりません"
    exit 1
fi

# 2. データベース設定の確認
echo "=== 1. データベース設定の確認 ==="
echo "----------------------------------------"
grep "^DB_" .env | sed 's/\(PASSWORD=\).*/\1***HIDDEN***/'
echo "----------------------------------------"
echo ""

# 3. 実際の設定値を取得
DB_CONNECTION=$(grep "^DB_CONNECTION=" .env | cut -d '=' -f2 | tr -d ' ')
DB_HOST=$(grep "^DB_HOST=" .env | cut -d '=' -f2 | tr -d ' ')
DB_PORT=$(grep "^DB_PORT=" .env | cut -d '=' -f2 | tr -d ' ')
DB_DATABASE=$(grep "^DB_DATABASE=" .env | cut -d '=' -f2 | tr -d ' ')
DB_USERNAME=$(grep "^DB_USERNAME=" .env | cut -d '=' -f2 | tr -d ' ')
DB_PASSWORD=$(grep "^DB_PASSWORD=" .env | cut -d '=' -f2)

echo "=== 2. 設定値の詳細 ==="
echo "接続タイプ: $DB_CONNECTION"
echo "ホスト: $DB_HOST"
echo "ポート: ${DB_PORT:-3306}"
echo "データベース: $DB_DATABASE"
echo "ユーザー名: $DB_USERNAME"
echo "パスワード: ${DB_PASSWORD:0:3}*** (長さ: ${#DB_PASSWORD})"
echo ""

# 4. パスワードの形式チェック
echo "=== 3. パスワードの形式チェック ==="
if [[ "$DB_PASSWORD" =~ ^[\"\''].*[\"\'']$ ]]; then
    echo "⚠️  パスワードが引用符で囲まれています"
    echo "   引用符を外すことを推奨します"
    CLEAN_PASSWORD=$(echo "$DB_PASSWORD" | sed "s/^[\"']//;s/[\"']$//")
    echo "   クリーンなパスワード: ${CLEAN_PASSWORD:0:3}***"
else
    echo "✅ パスワードの形式は正常です"
    CLEAN_PASSWORD="$DB_PASSWORD"
fi
echo ""

# 5. ホストへの接続テスト
echo "=== 4. ホストへの接続テスト ==="
if [ -n "$DB_HOST" ]; then
    echo "ホスト $DB_HOST への接続をテストしています..."
    if timeout 5 bash -c "echo > /dev/tcp/$DB_HOST/${DB_PORT:-3306}" 2>/dev/null; then
        echo "✅ ホスト $DB_HOST:${DB_PORT:-3306} に接続可能"
    else
        echo "❌ ホスト $DB_HOST:${DB_PORT:-3306} に接続できません"
        echo "   セキュリティグループの設定を確認してください"
    fi
else
    echo "⚠️  DB_HOSTが設定されていません"
fi
echo ""

# 6. MySQLクライアントの確認
echo "=== 5. MySQLクライアントの確認 ==="
if command -v mysql &> /dev/null; then
    echo "✅ MySQLクライアントがインストールされています"
    mysql --version
else
    echo "⚠️  MySQLクライアントがインストールされていません"
    echo "   直接接続テストはスキップします"
fi
echo ""

# 7. Laravelのキャッシュをクリア
echo "=== 6. Laravelキャッシュのクリア ==="
php artisan config:clear 2>/dev/null && echo "✅ config:clear 完了" || echo "⚠️  config:clear 失敗"
php artisan cache:clear 2>/dev/null && echo "✅ cache:clear 完了" || echo "⚠️  cache:clear 失敗"
echo ""

# 8. Laravel経由での接続テスト
echo "=== 7. Laravel経由での接続テスト ==="
php artisan tinker --execute="try { DB::connection()->getPdo(); echo '✅ データベース接続成功'; } catch (Exception \$e) { echo '❌ 接続失敗: ' . \$e->getMessage(); }" 2>&1
echo ""

# 9. 推奨される修正手順
echo "=== 8. 推奨される修正手順 ==="
echo ""
echo "以下の手順を試してください："
echo ""
echo "1. .envファイルのパスワードを確認・修正"
echo "   nano .env"
echo "   # DB_PASSWORD=your_actual_password  (引用符なし)"
echo ""
echo "2. キャッシュをクリア"
echo "   php artisan config:clear"
echo "   php artisan cache:clear"
echo ""
echo "3. RDSセキュリティグループを確認"
echo "   - AWS RDSコンソール → インスタンス → 接続とセキュリティ"
echo "   - インバウンドルールで MySQL/Aurora (3306) が許可されているか確認"
echo "   - ソース: EC2インスタンスのセキュリティグループ、または 172.31.17.195/32"
echo ""
echo "4. MySQLでユーザーと権限を確認"
echo "   mysql -h $DB_HOST -u root -p"
echo "   SELECT user, host FROM mysql.user WHERE user = '$DB_USERNAME';"
echo "   SHOW GRANTS FOR '$DB_USERNAME'@'%';"
echo ""
echo "5. 必要に応じてユーザーを作成・権限を付与"
echo "   CREATE USER '$DB_USERNAME'@'%' IDENTIFIED BY 'your_password';"
echo "   GRANT ALL PRIVILEGES ON $DB_DATABASE.* TO '$DB_USERNAME'@'%';"
echo "   FLUSH PRIVILEGES;"
echo ""

