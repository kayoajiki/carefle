<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PersonalityAssessment;
use Illuminate\Support\Facades\Auth;

class PersonalityAssessmentVisualization extends Component
{
    public function render()
    {
        $assessments = PersonalityAssessment::where('user_id', Auth::id())
            ->latest('completed_at')
            ->latest('created_at')
            ->get()
            ->groupBy('assessment_type');

        $mbtiAssessments = $assessments->get('mbti', collect());
        $strengthAssessments = $assessments->get('strengthsfinder', collect());
        $enneagramAssessments = $assessments->get('enneagram', collect());
        $big5Assessments = $assessments->get('big5', collect());

        return view('livewire.personality-assessment-visualization', [
            'mbtiAssessments' => $mbtiAssessments,
            'mbtiLatest' => $mbtiAssessments->first(),
            'strengthAssessments' => $strengthAssessments,
            'strengthLatest' => $strengthAssessments->first(),
            'enneagramAssessments' => $enneagramAssessments,
            'enneagramLatest' => $enneagramAssessments->first(),
            'big5Assessments' => $big5Assessments,
            'big5Latest' => $big5Assessments->first(),
        ]);
    }
}
