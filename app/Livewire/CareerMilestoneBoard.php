<?php

namespace App\Livewire;

use App\Models\CareerMilestone;
use App\Models\MilestoneActionItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CareerMilestoneBoard extends Component
{
    public ?int $selectedMilestoneId = null;
    public ?int $formMilestoneId = null;
    public bool $showForm = false;

    protected $listeners = [
        'milestoneSaved' => 'handleMilestoneSaved',
        'closeForm' => 'closeForm',
    ];

    public function mount(): void
    {
        $this->selectedMilestoneId = $this->firstMilestoneId();
    }

    public function selectMilestone(int $milestoneId): void
    {
        $this->selectedMilestoneId = $milestoneId;
    }

    public function openCreateForm(): void
    {
        $this->formMilestoneId = null;
        $this->showForm = true;
    }

    public function openEditForm(?int $milestoneId = null): void
    {
        $this->formMilestoneId = $milestoneId;
        $this->showForm = true;
    }

    public function deleteMilestone(int $milestoneId): void
    {
        $milestone = CareerMilestone::where('id', $milestoneId)
            ->where('user_id', Auth::id())
            ->first();

        if ($milestone) {
            $milestone->delete();
            
            // 削除されたマイルストーンが選択されていた場合、選択を解除
            if ($this->selectedMilestoneId === $milestoneId) {
                $this->selectedMilestoneId = $this->firstMilestoneId();
            }
        }
    }

    public function markMilestoneAsAchieved(int $milestoneId): void
    {
        $milestone = CareerMilestone::where('id', $milestoneId)
            ->where('user_id', Auth::id())
            ->first();

        if ($milestone) {
            $milestone->status = 'achieved';
            $milestone->save();
            
            // 完了したマイルストーンが選択されていた場合、選択を解除
            if ($this->selectedMilestoneId === $milestoneId) {
                $this->selectedMilestoneId = $this->firstMilestoneId();
            }
        }
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->formMilestoneId = null;
    }

    public function markActionDone(int $actionItemId): void
    {
        $action = MilestoneActionItem::where('id', $actionItemId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$action) {
            return;
        }

        $action->status = 'completed';
        $action->completed_at = now();
        $action->save();
    }

    public function deleteActionItem(int $actionItemId): void
    {
        $action = MilestoneActionItem::where('id', $actionItemId)
            ->where('user_id', Auth::id())
            ->first();

        if ($action) {
            $action->delete();
        }
    }

    public function moveActionItem(int $actionItemId, int $targetMilestoneId): void
    {
        $action = MilestoneActionItem::where('id', $actionItemId)
            ->where('user_id', Auth::id())
            ->first();

        $targetMilestone = CareerMilestone::where('id', $targetMilestoneId)
            ->where('user_id', Auth::id())
            ->first();

        if ($action && $targetMilestone) {
            $action->career_milestone_id = $targetMilestoneId;
            $action->save();
        }
    }

    public function handleMilestoneSaved(?int $milestoneId = null): void
    {
        $this->closeForm();

        if ($milestoneId) {
            $this->selectedMilestoneId = $milestoneId;
        } elseif (!$this->selectedMilestoneId) {
            $this->selectedMilestoneId = $this->firstMilestoneId();
        }
    }

    public function render()
    {
        $milestones = $this->milestones();
        $achievedMilestones = $this->achievedMilestones();

        $detailMilestone = $milestones->firstWhere('id', $this->selectedMilestoneId)
            ?: $milestones->first();

        $summary = $this->buildSummary($milestones);
        $groups = $this->groupMilestonesBySchedule($milestones);

        return view('livewire.career-milestone-board', [
            'milestones' => $milestones,
            'achievedMilestones' => $achievedMilestones,
            'summary' => $summary,
            'groups' => $groups,
            'detailMilestone' => $detailMilestone,
        ]);
    }

    private function milestones()
    {
        return CareerMilestone::with(['actionItems' => function ($query) {
                $query->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
                    ->orderBy('due_date')
                    ->orderBy('title');
            }])
            ->where('user_id', Auth::id())
            ->where('status', '!=', 'achieved') // 完了したものは除外
            ->orderByRaw('CASE WHEN target_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('target_date')
            ->latest('created_at')
            ->get();
    }

    private function achievedMilestones()
    {
        return CareerMilestone::with(['actionItems' => function ($query) {
                $query->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
                    ->orderBy('due_date')
                    ->orderBy('title');
            }])
            ->where('user_id', Auth::id())
            ->where('status', 'achieved')
            ->orderBy('updated_at', 'desc') // 完了日時順
            ->get();
    }

    private function firstMilestoneId(): ?int
    {
        return CareerMilestone::where('user_id', Auth::id())
            ->orderByRaw('CASE WHEN target_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('target_date')
            ->latest('created_at')
            ->value('id');
    }

    private function buildSummary($milestones): array
    {
        $next = $milestones->filter(fn ($m) => $m->target_date)
            ->sortBy('target_date')
            ->first();

        $pendingActions = $milestones->flatMap->actionItems
            ->filter(fn ($action) => $action->status === 'pending')
            ->count();

        $within30Days = $milestones->filter(function ($milestone) {
            if (!$milestone->target_date) {
                return false;
            }

            $date = Carbon::parse($milestone->target_date);
            return $date->isBetween(now(), now()->copy()->addDays(30));
        })->count();

        return [
            'total' => $milestones->count(),
            'next' => $next,
            'pendingActions' => $pendingActions,
            'within30Days' => $within30Days,
        ];
    }

    private function groupMilestonesBySchedule($milestones): array
    {
        $now = now();
        $endOfMonth = $now->copy()->endOfMonth();
        $endOfQuarter = $now->copy()->addMonths(3)->endOfDay();

        // 各マイルストーンに達成率を計算して追加
        $milestonesWithProgress = $milestones->map(function ($milestone) {
            $totalActions = $milestone->actionItems->count();
            $completedActions = $milestone->actionItems->where('status', 'completed')->count();
            $completionRate = $totalActions > 0 ? round(($completedActions / $totalActions) * 100, 1) : 0;
            
            $milestone->completion_rate = $completionRate;
            $milestone->total_actions = $totalActions;
            $milestone->completed_actions = $completedActions;
            
            return $milestone;
        });

        return [
            '今月まで' => $milestonesWithProgress->filter(function ($milestone) use ($endOfMonth, $now) {
                if (!$milestone->target_date) {
                    return false;
                }
                $date = Carbon::parse($milestone->target_date);
                return $date->between($now->copy()->startOfMonth(), $endOfMonth);
            }),
            '今四半期' => $milestonesWithProgress->filter(function ($milestone) use ($endOfMonth, $endOfQuarter) {
                if (!$milestone->target_date) {
                    return false;
                }
                $date = Carbon::parse($milestone->target_date);
                return $date->greaterThan($endOfMonth) && $date->lte($endOfQuarter);
            }),
            'その先・時期未定' => $milestonesWithProgress->filter(function ($milestone) use ($endOfQuarter) {
                if (!$milestone->target_date) {
                    return true;
                }
                return Carbon::parse($milestone->target_date)->gt($endOfQuarter);
            }),
        ];
    }
}


