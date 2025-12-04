#!/bin/bash

# データベースサーバーの場所を判別するスクリプト
# 本番環境で実行

echo "🔍 データベースサーバーの場所を判別しています..."
echo ""

# 1. .envファイルからDB_HOSTを確認
if [ -f .env ]; then
    DB_HOST=$(grep "^DB_HOST=" .env | cut -d '=' -f2 | tr -d ' ')
    echo "📋 .envファイルの設定:"
    echo "  DB_HOST: $DB_HOST"
    echo ""
    
    # DB_HOSTの値で判別
    if [ "$DB_HOST" = "localhost" ] || [ "$DB_HOST" = "127.0.0.1" ]; then
        echo "✅ 同じEC2インスタンス上にある可能性が高いです"
        echo "   (localhost または 127.0.0.1 が設定されています)"
    elif [ -z "$DB_HOST" ]; then
        echo "⚠️  DB_HOSTが設定されていません"
    else
        echo "✅ 別のサーバーにある可能性が高いです"
        echo "   (DB_HOST: $DB_HOST)"
    fi
    echo ""
fi

# 2. ローカルにMySQL/MariaDBがインストールされているか確認
echo "🔍 ローカルのMySQL/MariaDBサービスを確認しています..."
if systemctl list-units --type=service | grep -q "mysql\|mariadb"; then
    echo "✅ MySQL/MariaDBサービスがローカルに存在します"
    systemctl list-units --type=service | grep -E "mysql|mariadb" | head -3
    echo ""
    
    # サービスのステータスを確認
    if systemctl is-active --quiet mysql 2>/dev/null || systemctl is-active --quiet mariadb 2>/dev/null; then
        echo "✅ MySQL/MariaDBサービスが起動しています"
        echo ""
        
        # ローカル接続を試みる
        echo "🔍 ローカル接続をテストしています..."
        if mysql -u root -e "SELECT 1;" 2>/dev/null; then
            echo "✅ ローカルでMySQLに接続できました（同じEC2インスタンス上です）"
        else
            echo "⚠️  ローカルでMySQLに接続できませんでした（rootパスワードが必要かもしれません）"
        fi
    else
        echo "⚠️  MySQL/MariaDBサービスが停止しています"
    fi
else
    echo "❌ MySQL/MariaDBサービスがローカルに存在しません（別のサーバーにある可能性が高いです）"
fi
echo ""

# 3. 現在のEC2インスタンスのIPアドレスを確認
echo "🔍 現在のEC2インスタンスの情報:"
INSTANCE_IP=$(curl -s http://169.254.169.254/latest/meta-data/local-ipv4 2>/dev/null)
PUBLIC_IP=$(curl -s http://169.254.169.254/latest/meta-data/public-ipv4 2>/dev/null)
HOSTNAME=$(hostname)

echo "  ホスト名: $HOSTNAME"
if [ -n "$INSTANCE_IP" ]; then
    echo "  プライベートIP: $INSTANCE_IP"
fi
if [ -n "$PUBLIC_IP" ]; then
    echo "  パブリックIP: $PUBLIC_IP"
fi
echo ""

# 4. DB_HOSTへの接続テスト
if [ -f .env ] && [ -n "$DB_HOST" ] && [ "$DB_HOST" != "localhost" ] && [ "$DB_HOST" != "127.0.0.1" ]; then
    echo "🔍 DB_HOST ($DB_HOST) への接続をテストしています..."
    if ping -c 1 -W 2 "$DB_HOST" > /dev/null 2>&1; then
        echo "✅ $DB_HOST に接続可能（別のサーバーです）"
        
        # ポート3306が開いているか確認
        if timeout 2 bash -c "echo > /dev/tcp/$DB_HOST/3306" 2>/dev/null; then
            echo "✅ ポート3306が開いています（MySQLが稼働している可能性が高いです）"
        else
            echo "⚠️  ポート3306に接続できません"
        fi
    else
        echo "❌ $DB_HOST に接続できません"
    fi
    echo ""
fi

# 5. まとめ
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📊 判別結果:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

if [ "$DB_HOST" = "localhost" ] || [ "$DB_HOST" = "127.0.0.1" ]; then
    echo "✅ 【方法A】データベースサーバーは同じEC2インスタンス上にあります"
    echo ""
    echo "接続方法:"
    echo "  sudo mysql -u root -p"
    echo "  または"
    echo "  mysql -u admin -p"
elif systemctl list-units --type=service | grep -q "mysql\|mariadb"; then
    echo "✅ 【方法A】データベースサーバーは同じEC2インスタンス上にあります"
    echo ""
    echo "接続方法:"
    echo "  sudo mysql -u root -p"
    echo "  または"
    echo "  mysql -u admin -p"
else
    echo "✅ 【方法B】データベースサーバーは別のサーバーにあります"
    echo ""
    echo "接続方法:"
    echo "  1. データベースサーバーにSSH接続"
    echo "  2. mysql -u root -p で接続"
    echo ""
    echo "または、リモートから接続:"
    echo "  mysql -h $DB_HOST -u admin -p"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

