#!/bin/bash

# RDS接続エラーの詳細デバッグスクリプト

echo "🔍 RDS接続エラーの詳細デバッグを開始します..."
echo ""

# 1. .envファイルの設定を確認（パスワードは非表示）
echo "=== 1. .envファイルの設定確認 ==="
DB_CONNECTION=$(grep "^DB_CONNECTION=" .env | cut -d '=' -f2)
DB_HOST=$(grep "^DB_HOST=" .env | cut -d '=' -f2)
DB_PORT=$(grep "^DB_PORT=" .env | cut -d '=' -f2)
DB_DATABASE=$(grep "^DB_DATABASE=" .env | cut -d '=' -f2)
DB_USERNAME=$(grep "^DB_USERNAME=" .env | cut -d '=' -f2)
DB_PASSWORD=$(grep "^DB_PASSWORD=" .env | cut -d '=' -f2)

echo "DB_CONNECTION: $DB_CONNECTION"
echo "DB_HOST: $DB_HOST"
echo "DB_PORT: $DB_PORT"
echo "DB_DATABASE: $DB_DATABASE"
echo "DB_USERNAME: $DB_USERNAME"
echo "DB_PASSWORD: ${DB_PASSWORD:0:3}*** (最初の3文字のみ表示)"
echo ""

# 2. パスワードに引用符が含まれているか確認
if [[ "$DB_PASSWORD" =~ ^\".*\"$ ]] || [[ "$DB_PASSWORD" =~ ^\'.*\'$ ]]; then
    echo "⚠️  パスワードが引用符で囲まれています"
    echo "   引用符を外す必要があるかもしれません"
    echo ""
fi

# 3. パスワードの前後に空白があるか確認
if [[ "$DB_PASSWORD" =~ ^[[:space:]] ]] || [[ "$DB_PASSWORD" =~ [[:space:]]$ ]]; then
    echo "⚠️  パスワードの前後に空白があります"
    echo ""
fi

# 4. キャッシュの状態を確認
echo "=== 2. キャッシュの状態確認 ==="
if [ -f bootstrap/cache/config.php ]; then
    echo "⚠️  設定キャッシュが存在します（config:clearが必要です）"
else
    echo "✅ 設定キャッシュはクリアされています"
fi
echo ""

# 5. 直接MySQL接続をテスト
echo "=== 3. 直接MySQL接続テスト ==="
echo "以下のコマンドで接続を試みてください："
echo ""
echo "mysql -h $DB_HOST -u $DB_USERNAME -p $DB_DATABASE"
echo ""
echo "または、パスワードを直接指定："
echo "mysql -h $DB_HOST -u $DB_USERNAME -p'$DB_PASSWORD' $DB_DATABASE"
echo ""

# 6. 接続テスト（パスワードを直接指定）
echo "=== 4. 接続テスト（パスワードを直接指定） ==="
# パスワードから引用符を除去
CLEAN_PASSWORD=$(echo "$DB_PASSWORD" | sed "s/^[\"']//;s/[\"']$//")

if mysql -h "$DB_HOST" -u "$DB_USERNAME" -p"$CLEAN_PASSWORD" -e "SELECT 1;" 2>&1; then
    echo "✅ 接続成功！"
else
    echo "❌ 接続失敗"
    echo ""
    echo "考えられる原因:"
    echo "1. パスワードが間違っている"
    echo "2. ユーザーが存在しない"
    echo "3. セキュリティグループで接続が許可されていない"
    echo "4. ユーザーのホスト制限（'admin'@'%'ではなく'admin'@'特定のホスト'のみ許可）"
fi
echo ""

# 7. 推奨される修正手順
echo "=== 5. 推奨される修正手順 ==="
echo "1. .envファイルのDB_PASSWORDを確認（引用符を外す）"
echo "2. php artisan config:clear を実行"
echo "3. php artisan cache:clear を実行"
echo "4. 再度接続テスト"

