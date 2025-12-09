<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\CareerMilestone;
use App\Services\MilestoneProgressService;
use Illuminate\Support\Facades\Auth;

class MilestoneProgressVisualization extends Component
{
    public $milestoneId = null;
    public $selectedMilestoneId = null;
    public $progressData = null;
    public $feedback = null;

    protected MilestoneProgressService $progressService;

    public function boot()
    {
        $this->progressService = app(MilestoneProgressService::class);
    }

    public function mount($milestoneId = null)
    {
        $this->milestoneId = $milestoneId;
        $this->selectedMilestoneId = $milestoneId;
        
        if ($milestoneId) {
            $this->loadProgress($milestoneId);
        }
    }

    public function selectMilestone($milestoneId)
    {
        $this->selectedMilestoneId = $milestoneId;
        $this->loadProgress($milestoneId);
    }

    public function loadProgress($milestoneId)
    {
        $analysis = $this->progressService->analyzeProgress($milestoneId);
        
        if ($analysis) {
            $this->progressData = $analysis['progress'];
            $this->feedback = $analysis['feedback'];
        }
    }

    public function render()
    {
        $milestones = CareerMilestone::where('user_id', Auth::id())
            ->whereIn('status', ['planned', 'in_progress'])
            ->orderBy('target_date')
            ->get();

        $selectedMilestone = $this->selectedMilestoneId
            ? CareerMilestone::where('id', $this->selectedMilestoneId)
                ->where('user_id', Auth::id())
                ->with(['actionItems'])
                ->first()
            : null;

        return view('livewire.milestone-progress-visualization', [
            'milestones' => $milestones,
            'selectedMilestone' => $selectedMilestone,
        ]);
    }
}


