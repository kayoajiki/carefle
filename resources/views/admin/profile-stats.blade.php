<x-admin.layouts.app title="プロフィール統計">
    <div class="min-h-screen bg-gradient-to-b from-[#E9F2FF] to-[#F6FBFF]">
        <div class="w-full max-w-7xl mx-auto content-padding section-spacing-sm space-y-8">
            <div class="flex items-center justify-between">
                <h1 class="heading-1">プロフィール統計</h1>
                <div class="flex gap-3">
                    <a href="{{ route('admin.profile-stats.export', request()->query()) }}" 
                       class="btn-secondary flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        CSVエクスポート
                    </a>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="card-refined surface-blue p-6">
                <form method="GET" action="{{ route('admin.profile-stats.index') }}" class="space-y-4">
                    <h3 class="heading-3 text-lg mb-4">期間フィルター</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-[#2E5C8A] mb-2">開始日</label>
                            <input type="date" name="start_date" value="{{ $startDate }}" 
                                   class="w-full px-4 py-2 border border-[#2E5C8A]/30 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#2E5C8A] mb-2">終了日</label>
                            <input type="date" name="end_date" value="{{ $endDate }}" 
                                   class="w-full px-4 py-2 border border-[#2E5C8A]/30 rounded-lg">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="btn-primary w-full">フィルター適用</button>
                        </div>
                    </div>
                </form>
                <div class="mt-4">
                    <p class="body-text text-[#1E3A5F]/70">
                        対象ユーザー数: <span class="font-semibold text-[#2E5C8A]">{{ number_format($totalUsers) }}</span> 人
                    </p>
                </div>
            </div>

            <!-- Gender Statistics -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="card-refined surface-blue p-6">
                    <h3 class="heading-3 text-lg mb-4">性別の分布</h3>
                    <div class="h-64">
                        <canvas id="genderChart"></canvas>
                    </div>
                    <div class="mt-4">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-[#2E5C8A]/20">
                                    <th class="text-left py-2 px-3 font-semibold text-[#2E5C8A]">性別</th>
                                    <th class="text-right py-2 px-3 font-semibold text-[#2E5C8A]">人数</th>
                                    <th class="text-right py-2 px-3 font-semibold text-[#2E5C8A]">割合</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($genderStats as $gender => $count)
                                    <tr class="border-b border-[#2E5C8A]/10">
                                        <td class="py-2 px-3">{{ $gender }}</td>
                                        <td class="py-2 px-3 text-right">{{ number_format($count) }}</td>
                                        <td class="py-2 px-3 text-right">{{ $totalUsers > 0 ? number_format($count / $totalUsers * 100, 1) : 0 }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Prefecture Statistics -->
                <div class="card-refined surface-blue p-6">
                    <h3 class="heading-3 text-lg mb-4">都道府県の分布（上位10）</h3>
                    <div class="h-64">
                        <canvas id="prefectureChart"></canvas>
                    </div>
                    <div class="mt-4">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-[#2E5C8A]/20">
                                    <th class="text-left py-2 px-3 font-semibold text-[#2E5C8A]">都道府県</th>
                                    <th class="text-right py-2 px-3 font-semibold text-[#2E5C8A]">人数</th>
                                    <th class="text-right py-2 px-3 font-semibold text-[#2E5C8A]">割合</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($prefectureStats as $prefecture => $count)
                                    <tr class="border-b border-[#2E5C8A]/10">
                                        <td class="py-2 px-3">{{ $prefecture }}</td>
                                        <td class="py-2 px-3 text-right">{{ number_format($count) }}</td>
                                        <td class="py-2 px-3 text-right">{{ $totalUsers > 0 ? number_format($count / $totalUsers * 100, 1) : 0 }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Occupation and Industry Statistics -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="card-refined surface-blue p-6">
                    <h3 class="heading-3 text-lg mb-4">職種の分布</h3>
                    <div class="h-64">
                        <canvas id="occupationChart"></canvas>
                    </div>
                    <div class="mt-4 max-h-64 overflow-y-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-[#2E5C8A]/20 sticky top-0 bg-white">
                                    <th class="text-left py-2 px-3 font-semibold text-[#2E5C8A]">職種</th>
                                    <th class="text-right py-2 px-3 font-semibold text-[#2E5C8A]">人数</th>
                                    <th class="text-right py-2 px-3 font-semibold text-[#2E5C8A]">割合</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($occupationStats as $occupation => $count)
                                    <tr class="border-b border-[#2E5C8A]/10">
                                        <td class="py-2 px-3">{{ $occupation }}</td>
                                        <td class="py-2 px-3 text-right">{{ number_format($count) }}</td>
                                        <td class="py-2 px-3 text-right">{{ $totalUsers > 0 ? number_format($count / $totalUsers * 100, 1) : 0 }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-refined surface-blue p-6">
                    <h3 class="heading-3 text-lg mb-4">業界の分布</h3>
                    <div class="h-64">
                        <canvas id="industryChart"></canvas>
                    </div>
                    <div class="mt-4 max-h-64 overflow-y-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-[#2E5C8A]/20 sticky top-0 bg-white">
                                    <th class="text-left py-2 px-3 font-semibold text-[#2E5C8A]">業界</th>
                                    <th class="text-right py-2 px-3 font-semibold text-[#2E5C8A]">人数</th>
                                    <th class="text-right py-2 px-3 font-semibold text-[#2E5C8A]">割合</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($industryStats as $industry => $count)
                                    <tr class="border-b border-[#2E5C8A]/10">
                                        <td class="py-2 px-3">{{ $industry }}</td>
                                        <td class="py-2 px-3 text-right">{{ number_format($count) }}</td>
                                        <td class="py-2 px-3 text-right">{{ $totalUsers > 0 ? number_format($count / $totalUsers * 100, 1) : 0 }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Employment Type and Work Experience Statistics -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="card-refined surface-blue p-6">
                    <h3 class="heading-3 text-lg mb-4">雇用形態の分布</h3>
                    <div class="h-64">
                        <canvas id="employmentTypeChart"></canvas>
                    </div>
                    <div class="mt-4">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-[#2E5C8A]/20">
                                    <th class="text-left py-2 px-3 font-semibold text-[#2E5C8A]">雇用形態</th>
                                    <th class="text-right py-2 px-3 font-semibold text-[#2E5C8A]">人数</th>
                                    <th class="text-right py-2 px-3 font-semibold text-[#2E5C8A]">割合</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employmentTypeStats as $type => $count)
                                    <tr class="border-b border-[#2E5C8A]/10">
                                        <td class="py-2 px-3">{{ $type }}</td>
                                        <td class="py-2 px-3 text-right">{{ number_format($count) }}</td>
                                        <td class="py-2 px-3 text-right">{{ $totalUsers > 0 ? number_format($count / $totalUsers * 100, 1) : 0 }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-refined surface-blue p-6">
                    <h3 class="heading-3 text-lg mb-4">勤続年数の分布</h3>
                    <div class="h-64">
                        <canvas id="workExperienceChart"></canvas>
                    </div>
                    <div class="mt-4">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-[#2E5C8A]/20">
                                    <th class="text-left py-2 px-3 font-semibold text-[#2E5C8A]">勤続年数</th>
                                    <th class="text-right py-2 px-3 font-semibold text-[#2E5C8A]">人数</th>
                                    <th class="text-right py-2 px-3 font-semibold text-[#2E5C8A]">割合</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($workExperienceStats as $item)
                                    <tr class="border-b border-[#2E5C8A]/10">
                                        <td class="py-2 px-3">{{ $item['label'] }}</td>
                                        <td class="py-2 px-3 text-right">{{ number_format($item['count']) }}</td>
                                        <td class="py-2 px-3 text-right">{{ $totalUsers > 0 ? number_format($item['count'] / $totalUsers * 100, 1) : 0 }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Education Statistics -->
            <div class="card-refined surface-blue p-6">
                <h3 class="heading-3 text-lg mb-4">最終学歴の分布</h3>
                <div class="h-64">
                    <canvas id="educationChart"></canvas>
                </div>
                <div class="mt-4">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-[#2E5C8A]/20">
                                <th class="text-left py-2 px-3 font-semibold text-[#2E5C8A]">最終学歴</th>
                                <th class="text-right py-2 px-3 font-semibold text-[#2E5C8A]">人数</th>
                                <th class="text-right py-2 px-3 font-semibold text-[#2E5C8A]">割合</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($educationStats as $education => $count)
                                <tr class="border-b border-[#2E5C8A]/10">
                                    <td class="py-2 px-3">{{ $education }}</td>
                                    <td class="py-2 px-3 text-right">{{ number_format($count) }}</td>
                                    <td class="py-2 px-3 text-right">{{ $totalUsers > 0 ? number_format($count / $totalUsers * 100, 1) : 0 }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const colors = {
                primary: '#6BB6FF',
                secondary: '#2E5C8A',
                accent: '#F5AE2E',
                success: '#4CAF50',
                warning: '#FF9800',
                danger: '#F44336',
                info: '#2196F3',
            };

            // 性別の分布（円グラフ）
            @if($genderStats->isNotEmpty())
            const genderCtx = document.getElementById('genderChart');
            if (genderCtx) {
                new Chart(genderCtx, {
                    type: 'pie',
                    data: {
                        labels: @json($genderStats->keys()),
                        datasets: [{
                            data: @json($genderStats->values()),
                            backgroundColor: [
                                colors.primary,
                                colors.accent,
                                colors.success,
                                colors.info,
                            ],
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            }
                        }
                    }
                });
            }
            @endif

            // 都道府県の分布（棒グラフ）
            @if($prefectureStats->isNotEmpty())
            const prefectureCtx = document.getElementById('prefectureChart');
            if (prefectureCtx) {
                new Chart(prefectureCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($prefectureStats->keys()),
                        datasets: [{
                            label: '人数',
                            data: @json($prefectureStats->values()),
                            backgroundColor: colors.primary,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                            }
                        },
                        plugins: {
                            legend: {
                                display: false,
                            }
                        }
                    }
                });
            }
            @endif

            // 職種の分布（棒グラフ）
            @if($occupationStats->isNotEmpty())
            const occupationCtx = document.getElementById('occupationChart');
            if (occupationCtx) {
                new Chart(occupationCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($occupationStats->keys()->take(10)),
                        datasets: [{
                            label: '人数',
                            data: @json($occupationStats->values()->take(10)),
                            backgroundColor: colors.accent,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                            }
                        },
                        plugins: {
                            legend: {
                                display: false,
                            }
                        }
                    }
                });
            }
            @endif

            // 業界の分布（棒グラフ）
            @if($industryStats->isNotEmpty())
            const industryCtx = document.getElementById('industryChart');
            if (industryCtx) {
                new Chart(industryCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($industryStats->keys()->take(10)),
                        datasets: [{
                            label: '人数',
                            data: @json($industryStats->values()->take(10)),
                            backgroundColor: colors.success,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                            }
                        },
                        plugins: {
                            legend: {
                                display: false,
                            }
                        }
                    }
                });
            }
            @endif

            // 雇用形態の分布（円グラフ）
            @if($employmentTypeStats->isNotEmpty())
            const employmentTypeCtx = document.getElementById('employmentTypeChart');
            if (employmentTypeCtx) {
                new Chart(employmentTypeCtx, {
                    type: 'pie',
                    data: {
                        labels: @json($employmentTypeStats->keys()),
                        datasets: [{
                            data: @json($employmentTypeStats->values()),
                            backgroundColor: [
                                colors.primary,
                                colors.accent,
                                colors.success,
                                colors.warning,
                                colors.info,
                                colors.danger,
                            ],
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            }
                        }
                    }
                });
            }
            @endif

            // 勤続年数の分布（棒グラフ）
            @if($workExperienceStats->isNotEmpty())
            const workExperienceCtx = document.getElementById('workExperienceChart');
            if (workExperienceCtx) {
                new Chart(workExperienceCtx, {
                    type: 'bar',
                    data: {
                        labels: @json(collect($workExperienceStats)->pluck('label')),
                        datasets: [{
                            label: '人数',
                            data: @json(collect($workExperienceStats)->pluck('count')),
                            backgroundColor: colors.warning,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                            }
                        },
                        plugins: {
                            legend: {
                                display: false,
                            }
                        }
                    }
                });
            }
            @endif

            // 最終学歴の分布（円グラフ）
            @if($educationStats->isNotEmpty())
            const educationCtx = document.getElementById('educationChart');
            if (educationCtx) {
                new Chart(educationCtx, {
                    type: 'pie',
                    data: {
                        labels: @json($educationStats->keys()),
                        datasets: [{
                            data: @json($educationStats->values()),
                            backgroundColor: [
                                colors.primary,
                                colors.accent,
                                colors.success,
                                colors.warning,
                                colors.info,
                            ],
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            }
                        }
                    }
                });
            }
            @endif
        });
    </script>
</x-admin.layouts.app>

