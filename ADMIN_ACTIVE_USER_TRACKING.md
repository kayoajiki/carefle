# アクティブユーザー追跡：負荷を抑えた実装方法

## 推奨アプローチ：ハイブリッド方式

### 1. セッションテーブルを活用（既存インフラ）

既存の`sessions`テーブルには以下の情報が含まれています：
- `user_id`: ユーザーID（NULLの場合は未ログイン）
- `last_activity`: 最終アクティビティ時刻（Unixタイムスタンプ）
- `ip_address`: IPアドレス
- `user_agent`: ユーザーエージェント

**メリット**:
- 既存のインフラを活用（追加のテーブル不要）
- Laravelが自動的に管理
- インデックスが既に存在（`user_id`, `last_activity`）

**アクティブユーザーの判定**:
```sql
-- 過去24時間以内にアクティブなユーザー（DAU）
SELECT DISTINCT user_id 
FROM sessions 
WHERE user_id IS NOT NULL 
  AND last_activity >= UNIX_TIMESTAMP(NOW() - INTERVAL 24 HOUR);

-- 過去7日間以内にアクティブなユーザー（WAU）
SELECT DISTINCT user_id 
FROM sessions 
WHERE user_id IS NOT NULL 
  AND last_activity >= UNIX_TIMESTAMP(NOW() - INTERVAL 7 DAY);

-- 過去30日間以内にアクティブなユーザー（MAU）
SELECT DISTINCT user_id 
FROM sessions 
WHERE user_id IS NOT NULL 
  AND last_activity >= UNIX_TIMESTAMP(NOW() - INTERVAL 30 DAY);
```

### 2. ユーザーテーブルに軽量なカラムを追加（補完）

セッションテーブルだけでは不十分な場合の補完として、`users`テーブルに以下を追加：

```php
// マイグレーション
Schema::table('users', function (Blueprint $table) {
    $table->timestamp('last_login_at')->nullable()->after('email_verified_at');
    $table->timestamp('last_activity_at')->nullable()->after('last_login_at');
    $table->index('last_activity_at'); // クエリ高速化のため
});
```

**更新タイミング**:
- `last_login_at`: ログイン成功時のみ更新（頻度：低）
- `last_activity_at`: 主要な操作（診断完了、日記作成など）の際に更新（頻度：中）

**メリット**:
- 更新頻度が低いため、負荷が少ない
- ユーザー一覧でのソート・フィルタリングが高速
- セッションテーブルと併用することで、より正確な追跡が可能

### 3. キャッシュを活用した集計（パフォーマンス最適化）

アクティブユーザー数の集計は重い処理になるため、キャッシュを使用：

```php
// アクティブユーザー数をキャッシュ（5分間有効）
$dau = Cache::remember('admin:dau', 300, function () {
    return DB::table('sessions')
        ->whereNotNull('user_id')
        ->where('last_activity', '>=', now()->subDay()->timestamp)
        ->distinct('user_id')
        ->count('user_id');
});

$wau = Cache::remember('admin:wau', 300, function () {
    return DB::table('sessions')
        ->whereNotNull('user_id')
        ->where('last_activity', '>=', now()->subWeek()->timestamp)
        ->distinct('user_id')
        ->count('user_id');
});

$mau = Cache::remember('admin:mau', 300, function () {
    return DB::table('sessions')
        ->whereNotNull('user_id')
        ->where('last_activity', '>=', now()->subMonth()->timestamp)
        ->distinct('user_id')
        ->count('user_id');
});
```

**メリット**:
- 5分間キャッシュすることで、データベースへの負荷を大幅に削減
- 管理画面の表示が高速化
- リアルタイム性は多少犠牲になるが、管理画面では許容範囲

### 4. バッチ処理で定期的に集計（オプション）

より正確な統計が必要な場合、バッチ処理で定期的に集計：

```php
// app/Console/Commands/UpdateActiveUserStats.php
// 毎時実行（cron: 0 * * * *）

public function handle()
{
    $stats = [
        'dau' => $this->countActiveUsers(now()->subDay()),
        'wau' => $this->countActiveUsers(now()->subWeek()),
        'mau' => $this->countActiveUsers(now()->subMonth()),
        'updated_at' => now(),
    ];
    
    Cache::put('admin:user_stats', $stats, 3600);
}

private function countActiveUsers($since)
{
    return DB::table('sessions')
        ->whereNotNull('user_id')
        ->where('last_activity', '>=', $since->timestamp)
        ->distinct('user_id')
        ->count('user_id');
}
```

---

## 実装の優先順位

### Phase 1: 最小限の実装（推奨・最初に実装）

1. **セッションテーブルを活用**
   - 既存のインフラを活用
   - 追加のマイグレーション不要
   - キャッシュを活用した集計

**負荷**: 非常に低い（キャッシュ使用時）

### Phase 2: 補完的な実装（必要に応じて）

2. **ユーザーテーブルにカラム追加**
   - `last_login_at`と`last_activity_at`を追加
   - ログイン時と主要操作時に更新

**負荷**: 低い（更新頻度が低い）

### Phase 3: 高度な実装（将来的に）

3. **バッチ処理での集計**
   - より正確な統計が必要な場合

**負荷**: 非常に低い（バックグラウンド処理）

---

## アクティブユーザーの定義（再定義）

### 方法1: セッションテーブルベース（推奨）

- **DAU**: 過去24時間以内にセッションがアクティブなユーザー
- **WAU**: 過去7日間以内にセッションがアクティブなユーザー
- **MAU**: 過去30日間以内にセッションがアクティブなユーザー

**メリット**:
- 既存インフラを活用
- 実装が簡単
- 負荷が低い

**注意点**:
- セッションが期限切れになると、アクティブユーザーから除外される
- セッション有効期限（デフォルト120分）を考慮する必要がある

### 方法2: アクティビティログベース（オプションCと組み合わせ）

- **DAU**: 過去24時間以内にアクティビティログに記録があるユーザー
- **WAU**: 過去7日間以内にアクティビティログに記録があるユーザー
- **MAU**: 過去30日間以内にアクティビティログに記録があるユーザー

**メリット**:
- より正確な追跡（実際の操作を記録）
- セッション期限切れの影響を受けない

**デメリット**:
- アクティビティログテーブルへのクエリが必要（負荷がやや高い）
- アクティビティログが記録されない操作は追跡できない

### 方法3: ハイブリッド（推奨）

- **セッションテーブル**: リアルタイムのアクティブユーザー数（キャッシュ使用）
- **アクティビティログ**: 詳細な分析用（必要に応じて）

**メリット**:
- 両方のメリットを活用
- 負荷を最小限に抑えつつ、詳細な分析も可能

---

## パフォーマンス比較

### セッションテーブルベース（キャッシュ使用）

```
クエリ時間: ~10-50ms（キャッシュヒット時: ~1ms）
データベース負荷: 低い（5分に1回のみ）
メモリ使用量: 低い（キャッシュキー3つ）
```

### アクティビティログベース

```
クエリ時間: ~100-500ms（インデックス使用時）
データベース負荷: 中程度（毎回クエリ）
メモリ使用量: 低い
```

### ハイブリッド

```
クエリ時間: ~10-50ms（キャッシュ使用時）
データベース負荷: 低い（セッションテーブル） + 中程度（アクティビティログ、必要時のみ）
メモリ使用量: 低い
```

---

## 推奨実装

**Phase 1（最初に実装）**: セッションテーブル + キャッシュ
- 既存インフラを活用
- 負荷が最も低い
- 実装が簡単

**Phase 2（必要に応じて）**: ユーザーテーブルにカラム追加
- より正確な追跡
- ユーザー一覧でのソート・フィルタリングが高速

**Phase 3（将来的に）**: アクティビティログとの組み合わせ
- より詳細な分析が必要な場合


