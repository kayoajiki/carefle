<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Diary;
use App\Notifications\DiaryReminder;
use Carbon\Carbon;

class SendDiaryReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'diary:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send diary reminders to users who have not written today';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        
        // 今日日記を書いていないユーザーを取得
        $usersWithoutDiary = User::whereDoesntHave('diaries', function ($query) use ($today) {
            $query->whereDate('date', $today);
        })->get();

        $count = 0;
        foreach ($usersWithoutDiary as $user) {
            // 連続記録があるユーザーのみにリマインダーを送信
            $hasRecentDiary = Diary::where('user_id', $user->id)
                ->whereDate('date', '>=', $today->copy()->subDays(7))
                ->exists();

            if ($hasRecentDiary) {
                $user->notify(new DiaryReminder());
                $count++;
            }
        }

        $this->info("Sent {$count} diary reminders.");
        return 0;
    }
}
