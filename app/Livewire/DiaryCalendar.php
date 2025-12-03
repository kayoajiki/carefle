<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Diary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DiaryCalendar extends Component
{
    public $currentMonth;
    public $currentYear;
    public $selectedDate = null;
    public $selectedDiary = null;
    public $selectedDiaryId = null;
    public $showForm = false;

    public function mount()
    {
        $this->currentMonth = date('n');
        $this->currentYear = date('Y');
    }

    public function previousMonth()
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
        $this->selectedDate = null;
        $this->selectedDiary = null;
        $this->showForm = false;
    }

    public function nextMonth()
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
        $this->selectedDate = null;
        $this->selectedDiary = null;
        $this->showForm = false;
    }

    public function selectDate($date)
    {
        $this->selectedDate = $date;
        $this->loadDiaryForDate($date);
        $this->showForm = true;
    }

    public function loadDiaryForDate($date)
    {
        $diary = Diary::where('user_id', Auth::id())
            ->whereDate('date', $date)
            ->first();
        
        $this->selectedDiary = $diary;
        $this->selectedDiaryId = $diary?->id;
    }

    public function closeForm()
    {
        $this->showForm = false;
        // フォームを閉じる前に、選択した日付の日記を再取得
        if ($this->selectedDate) {
            $this->loadDiaryForDate($this->selectedDate);
        }
        $this->selectedDate = null;
        $this->selectedDiary = null;
    }

    public function deleteDiary($id)
    {
        $diary = Diary::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        // 写真を削除
        if ($diary->photo && Storage::disk('public')->exists($diary->photo)) {
            Storage::disk('public')->delete($diary->photo);
        }

        $diary->delete();
        $this->closeForm();
        session()->flash('message', '日記を削除しました');
        
        // カレンダーを更新
        $this->dispatch('$refresh');
    }

    public function getCalendarDays()
    {
        $firstDay = Carbon::create($this->currentYear, $this->currentMonth, 1);
        $lastDay = $firstDay->copy()->endOfMonth();
        
        $startDate = $firstDay->copy()->startOfWeek();
        $endDate = $lastDay->copy()->endOfWeek();

        $days = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $days[] = [
                'date' => $current->format('Y-m-d'),
                'day' => $current->day,
                'isCurrentMonth' => $current->month == $this->currentMonth,
                'isToday' => $current->isToday(),
            ];
            $current->addDay();
        }

        return $days;
    }

    public function getDiariesForMonth()
    {
        $firstDay = Carbon::create($this->currentYear, $this->currentMonth, 1)->startOfDay();
        $lastDay = $firstDay->copy()->endOfMonth()->endOfDay();

        $diaries = Diary::where('user_id', Auth::id())
            ->whereBetween('date', [$firstDay->format('Y-m-d'), $lastDay->format('Y-m-d')])
            ->get();

        // 日付をキーとしてマッピング（Carbonインスタンスを文字列に変換）
        return $diaries->keyBy(function ($diary) {
            return $diary->date->format('Y-m-d');
        });
    }

    protected $listeners = ['diary-saved' => 'refreshAfterSave'];

    public function refreshAfterSave()
    {
        // 選択中の日付があれば、その日の日記を再取得
        if ($this->selectedDate) {
            $this->loadDiaryForDate($this->selectedDate);
        }
    }

    public function render()
    {
        $days = $this->getCalendarDays();
        $diaries = $this->getDiariesForMonth();

        return view('livewire.diary-calendar', [
            'days' => $days,
            'diaries' => $diaries,
            'monthName' => Carbon::create($this->currentYear, $this->currentMonth, 1)->format('Y年n月'),
        ]);
    }
}
