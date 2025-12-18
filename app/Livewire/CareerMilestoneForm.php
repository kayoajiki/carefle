<?php

namespace App\Livewire;

use App\Models\CareerMilestone;
use App\Models\MilestoneActionItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CareerMilestoneForm extends Component
{
    public ?int $milestoneId = null;

    public string $title = '';
    public ?string $target_date = null;
    public string $summary = '';

    public string $mandalaCenter = '';

    public array $actionItems = [];

    public function mount(?int $milestoneId = null): void
    {
        $this->milestoneId = $milestoneId;
        $this->actionItems = $this->actionItems ?: [$this->blankActionItem()];

        if ($this->milestoneId) {
            $this->loadMilestone($this->milestoneId);
        }
    }

    private function blankActionItem(): array
    {
        return [
            'id' => null,
            'title' => '',
            'due_date' => '',
            'notes' => '',
        ];
    }

    private function loadMilestone(int $id): void
    {
        $milestone = CareerMilestone::where('user_id', Auth::id())
            ->with('actionItems')
            ->findOrFail($id);

        $this->title = $milestone->title;
        $this->target_date = optional($milestone->target_date)?->format('Y-m-d');
        $this->summary = (string) $milestone->description;

        // will_themeを優先、なければmandala_dataから取得
        $this->mandalaCenter = $milestone->will_theme ?? ($milestone->mandala_data['center'] ?? '');

        $this->actionItems = $milestone->actionItems
            ->map(function (MilestoneActionItem $item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'due_date' => optional($item->due_date)?->format('Y-m-d'),
                    'notes' => $item->description,
                ];
            })
            ->toArray();

        if (empty($this->actionItems)) {
            $this->actionItems = [$this->blankActionItem()];
        }
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'target_date' => 'nullable|date',
            'summary' => 'nullable|string|max:2000',
            'mandalaCenter' => 'nullable|string|max:255',
            'actionItems' => 'array|min:1',
            'actionItems.*.title' => 'nullable|string|max:255',
            'actionItems.*.due_date' => 'nullable|date',
            'actionItems.*.notes' => 'nullable|string|max:500',
        ];
    }

    public function addActionItem(): void
    {
        $this->actionItems[] = $this->blankActionItem();
    }

    public function removeActionItem(int $index): void
    {
        if (count($this->actionItems) <= 1) {
            return;
        }

        unset($this->actionItems[$index]);
        $this->actionItems = array_values($this->actionItems);
    }

    public function loadMilestoneForEdit(int $milestoneId): void
    {
        $this->milestoneId = $milestoneId;
        $this->loadMilestone($milestoneId);
    }

    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {
            $targetYear = $this->target_date ? Carbon::parse($this->target_date)->year : null;

            $milestoneData = [
                'user_id' => Auth::id(),
                'wcm_sheet_id' => null,
                'linked_life_event_id' => null,
                'target_year' => $targetYear,
                'target_date' => $this->target_date,
                'title' => $this->title,
                'will_theme' => $this->mandalaCenter,
                'description' => $this->summary,
                'category' => 'career',
                'status' => 'planned',
                'impact_score' => 0,
                'effort_score' => 0,
                'achievement_rate' => 0,
                'progress_points' => 0,
                'action_overview' => $this->generateActionOverview(),
            ];

            if ($this->milestoneId) {
                $milestone = CareerMilestone::where('user_id', Auth::id())->findOrFail($this->milestoneId);
                $milestone->update($milestoneData);
            } else {
                $milestone = CareerMilestone::create($milestoneData);
                $this->milestoneId = $milestone->id;
                
                // アクティビティログに記録
                app(\App\Services\ActivityLogService::class)->logCareerMilestoneCreated(Auth::id(), $milestone->id);
            }

            $this->syncActionItems($milestone);
            
            // 見直し日時を更新
            $mappingProgressService = app(\App\Services\MappingProgressService::class);
            $mappingProgressService->markItemAsReviewed(Auth::id(), 'milestones');
        });

        session()->flash('message', 'マイルストーンを保存しました。');
        $this->dispatch('milestoneSaved', $this->milestoneId);
    }

    private function generateActionOverview(): string
    {
        return collect($this->actionItems)
            ->filter(fn ($item) => !empty($item['title']))
            ->pluck('title')
            ->take(5)
            ->implode(' / ');
    }

    private function syncActionItems(CareerMilestone $milestone): void
    {
        $existingIds = collect($this->actionItems)
            ->pluck('id')
            ->filter()
            ->all();

        $milestone->actionItems()
            ->whereNotIn('id', $existingIds)
            ->delete();

        foreach ($this->actionItems as $item) {
            if (empty($item['title'])) {
                continue;
            }

            $data = [
                'user_id' => Auth::id(),
                'title' => $item['title'],
                'description' => $item['notes'],
                'due_date' => $item['due_date'],
                'priority' => 'medium',
                'status' => 'pending',
                'impact_score' => 0,
                'effort_score' => 0,
                'points_awarded' => 0,
            ];

            if (!empty($item['id'])) {
                $action = MilestoneActionItem::where('id', $item['id'])
                    ->where('user_id', Auth::id())
                    ->where('career_milestone_id', $milestone->id)
                    ->first();

                if ($action) {
                    $action->update($data);
                }
            } else {
                $milestone->actionItems()->create($data);
            }
        }
    }

    public function render()
    {
        $recentMilestones = CareerMilestone::where('user_id', Auth::id())
            ->latest('updated_at')
            ->take(5)
            ->get();

        return view('livewire.career-milestone-form', [
            'recentMilestones' => $recentMilestones,
        ]);
    }
}
