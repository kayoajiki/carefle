<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileStatsController extends Controller
{
    /**
     * Display profile statistics.
     */
    public function index(Request $request)
    {
        // フィルタリング用の日付範囲
        $minDate = User::min('created_at');
        $defaultStartDate = $minDate ? \Carbon\Carbon::parse($minDate)->format('Y-m-d') : now()->subYear()->format('Y-m-d');
        $startDate = $request->get('start_date', $defaultStartDate);
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // プロフィール完了済みユーザーのみを対象
        $query = User::where('profile_completed', true);
        
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        // 性別の分布
        $genderStats = $query->clone()
            ->select('gender', DB::raw('count(*) as count'))
            ->whereNotNull('gender')
            ->groupBy('gender')
            ->get()
            ->mapWithKeys(function ($item) {
                $labels = [
                    'male' => '男性',
                    'female' => '女性',
                    'other' => 'その他',
                    'prefer_not_to_say' => '回答しない',
                ];
                return [$labels[$item->gender] ?? $item->gender => $item->count];
            });

        // 都道府県の分布（上位10）
        $prefectureStats = $query->clone()
            ->select('prefecture', DB::raw('count(*) as count'))
            ->whereNotNull('prefecture')
            ->groupBy('prefecture')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->mapWithKeys(fn($item) => [$item->prefecture => $item->count]);

        // 職種の分布
        $occupationStats = $query->clone()
            ->select('occupation', DB::raw('count(*) as count'))
            ->whereNotNull('occupation')
            ->groupBy('occupation')
            ->orderByDesc('count')
            ->get()
            ->mapWithKeys(fn($item) => [$item->occupation => $item->count]);

        // 業界の分布
        $industryStats = $query->clone()
            ->select('industry', DB::raw('count(*) as count'))
            ->whereNotNull('industry')
            ->groupBy('industry')
            ->orderByDesc('count')
            ->get()
            ->mapWithKeys(fn($item) => [$item->industry => $item->count]);

        // 雇用形態の分布
        $employmentTypeStats = $query->clone()
            ->select('employment_type', DB::raw('count(*) as count'))
            ->whereNotNull('employment_type')
            ->groupBy('employment_type')
            ->orderByDesc('count')
            ->get()
            ->mapWithKeys(fn($item) => [$item->employment_type => $item->count]);

        // 勤続年数の分布
        $workExperienceStats = $query->clone()
            ->select('work_experience_years', DB::raw('count(*) as count'))
            ->whereNotNull('work_experience_years')
            ->where('work_experience_years', '!=', 'not_working')
            ->groupBy('work_experience_years')
            ->get()
            ->map(function ($item) {
                $label = $item->work_experience_years;
                if ($label === '0') {
                    $label = '0年（入社したて）';
                } elseif ($label === '11') {
                    $label = '11〜15年';
                } elseif ($label === '16') {
                    $label = '16〜20年';
                } elseif ($label === '21') {
                    $label = '21年以上';
                } else {
                    $label = $label . '年';
                }
                return ['label' => $label, 'count' => $item->count, 'sort_order' => (int)$item->work_experience_years];
            })
            ->sortBy('sort_order')
            ->values();

        // 最終学歴の分布
        $educationStats = $query->clone()
            ->select('education', DB::raw('count(*) as count'))
            ->whereNotNull('education')
            ->groupBy('education')
            ->orderByDesc('count')
            ->get()
            ->mapWithKeys(fn($item) => [$item->education => $item->count]);

        // 総ユーザー数（プロフィール完了済み）
        $totalUsers = $query->clone()->count();

        return view('admin.profile-stats', [
            'genderStats' => $genderStats,
            'prefectureStats' => $prefectureStats,
            'occupationStats' => $occupationStats,
            'industryStats' => $industryStats,
            'employmentTypeStats' => $employmentTypeStats,
            'workExperienceStats' => $workExperienceStats,
            'educationStats' => $educationStats,
            'totalUsers' => $totalUsers,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    /**
     * Export profile statistics to CSV.
     */
    public function export(Request $request)
    {
        $minDate = User::min('created_at');
        $defaultStartDate = $minDate ? \Carbon\Carbon::parse($minDate)->format('Y-m-d') : now()->subYear()->format('Y-m-d');
        $startDate = $request->get('start_date', $defaultStartDate);
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $query = User::where('profile_completed', true);
        
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $users = $query->select([
            'name',
            'email',
            'gender',
            'prefecture',
            'occupation',
            'industry',
            'employment_type',
            'work_experience_years',
            'education',
            'created_at',
        ])->get();

        $filename = 'profile_stats_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            
            // BOMを追加（Excelで文字化けしないように）
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // ヘッダー
            fputcsv($file, [
                '名前',
                'メールアドレス',
                '性別',
                '都道府県',
                '職種',
                '業界',
                '雇用形態',
                '勤続年数',
                '最終学歴',
                '登録日時',
            ]);

            // データ
            foreach ($users as $user) {
                $genderLabels = [
                    'male' => '男性',
                    'female' => '女性',
                    'other' => 'その他',
                    'prefer_not_to_say' => '回答しない',
                ];
                
                $workExpLabel = $user->work_experience_years;
                if ($workExpLabel === 'not_working') {
                    $workExpLabel = '現在は働いていない';
                } elseif ($workExpLabel === '0') {
                    $workExpLabel = '0年（入社したて）';
                } elseif ($workExpLabel === '11') {
                    $workExpLabel = '11〜15年';
                } elseif ($workExpLabel === '16') {
                    $workExpLabel = '16〜20年';
                } elseif ($workExpLabel === '21') {
                    $workExpLabel = '21年以上';
                } else {
                    $workExpLabel = $workExpLabel . '年';
                }

                fputcsv($file, [
                    $user->name,
                    $user->email,
                    $genderLabels[$user->gender] ?? $user->gender,
                    $user->prefecture ?? '',
                    $user->occupation ?? '',
                    $user->industry ?? '',
                    $user->employment_type ?? '',
                    $workExpLabel,
                    $user->education ?? '',
                    $user->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

