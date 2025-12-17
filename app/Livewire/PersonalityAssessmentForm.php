<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PersonalityAssessment;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;

class PersonalityAssessmentForm extends Component
{
    public $assessmentId = null;
    public $assessmentType = 'mbti';
    public $assessmentName = '';
    public $completedAt = '';
    public $notes = '';

    // MBTI
    public $mbtiType = '';
    public $mbtiEI = 50;
    public $mbtiSN = 50;
    public $mbtiTF = 50;
    public $mbtiJP = 50;

    // ストレングスファインダー
    public $strengthsTop5 = [null, null, null, null, null];
    public $strengthsAll34 = [];

    // ストレングスファインダーの34の強みリスト
    public function getStrengthsListProperty(): array
    {
        return [
            'アレンジ',
            '運命思考',
            '回復志向',
            '学習欲',
            '活発性',
            '共感性',
            '競争性',
            '規律性',
            '原点思考',
            '個別化',
            'コミュニケーション',
            '最上志向',
            '自我',
            '自己確信',
            '社交性',
            '収集心',
            '指令性',
            '慎重さ',
            '信念',
            '親密性',
            '成長促進',
            '責任感',
            '達成欲',
            '調和性',
            '適応性',
            '内省',
            '包含',
            '分析思考',
            '未来志向',
            'ポジティブ',
            '目標志向',
            '戦略性',
            '着想',
            '公平性',
        ];
    }

    // エニアグラム
    public $enneagramType = '';
    public $enneagramWing = '';
    public $enneagramTritype = '';
    public $enneagramInstinctualVariant = '';

    // ビッグファイブ
    public $big5Openness = 50;
    public $big5Conscientiousness = 50;
    public $big5Extraversion = 50;
    public $big5Agreeableness = 50;
    public $big5Neuroticism = 50;

    // FFS
    public $ffsCondensing = 10;
    public $ffsAcceptance = 10;
    public $ffsDiscrimination = 10;
    public $ffsDiffusion = 10;
    public $ffsConservation = 10;

    protected function rules(): array
    {
        $rules = [
            'assessmentType' => 'required|in:mbti,strengthsfinder,enneagram,big5,ffs,custom',
            'assessmentName' => 'nullable|string|max:255',
            'completedAt' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
        ];

        if ($this->assessmentType === 'mbti') {
            $rules['mbtiType'] = 'required|string|max:10';
        }

        if ($this->assessmentType === 'strengthsfinder') {
            $rules['strengthsTop5'] = 'required|array|size:5';
            $rules['strengthsTop5.*'] = 'required|string|max:255';
        }

        if ($this->assessmentType === 'enneagram') {
            $rules['enneagramType'] = 'required|string|in:1,2,3,4,5,6,7,8,9';
        }

        if ($this->assessmentType === 'ffs') {
            $rules['ffsCondensing'] = 'required|integer|min:0|max:20';
            $rules['ffsAcceptance'] = 'required|integer|min:0|max:20';
            $rules['ffsDiscrimination'] = 'required|integer|min:0|max:20';
            $rules['ffsDiffusion'] = 'required|integer|min:0|max:20';
            $rules['ffsConservation'] = 'required|integer|min:0|max:20';
        }

        return $rules;
    }

    public function mount($id = null)
    {
        if ($id) {
            $this->loadAssessment($id);
        } else {
            $this->setDefaultAssessmentName();
        }
    }

    public function updatedAssessmentType()
    {
        $this->setDefaultAssessmentName();
        $this->resetFormFields();
    }

    private function setDefaultAssessmentName()
    {
        $names = [
            'mbti' => 'MBTI',
            'strengthsfinder' => 'ストレングスファインダー',
            'enneagram' => 'エニアグラム',
            'big5' => 'ビッグファイブ',
            'ffs' => 'FFS理論',
            'custom' => 'カスタム診断',
        ];
        $this->assessmentName = $names[$this->assessmentType] ?? '';
    }

    private function resetFormFields()
    {
        $this->mbtiType = '';
        $this->mbtiEI = 50;
        $this->mbtiSN = 50;
        $this->mbtiTF = 50;
        $this->mbtiJP = 50;
        $this->strengthsTop5 = [null, null, null, null, null];
        $this->strengthsAll34 = [];
        $this->enneagramType = '';
        $this->enneagramWing = '';
        $this->enneagramTritype = '';
        $this->enneagramInstinctualVariant = '';
        $this->big5Openness = 50;
        $this->big5Conscientiousness = 50;
        $this->big5Extraversion = 50;
        $this->big5Agreeableness = 50;
        $this->big5Neuroticism = 50;
        $this->ffsCondensing = 10;
        $this->ffsAcceptance = 10;
        $this->ffsDiscrimination = 10;
        $this->ffsDiffusion = 10;
        $this->ffsConservation = 10;
    }

    public function loadAssessment($id)
    {
        $assessment = PersonalityAssessment::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $this->assessmentId = $assessment->id;
        $this->assessmentType = $assessment->assessment_type;
        $this->assessmentName = $assessment->assessment_name ?? '';
        $this->completedAt = $assessment->completed_at?->format('Y-m-d') ?? '';
        $this->notes = $assessment->notes ?? '';

        $data = $assessment->result_data ?? [];

        switch ($this->assessmentType) {
            case 'mbti':
                $this->mbtiType = $data['type'] ?? '';
                $this->mbtiEI = $data['percentage']['E/I'] ?? 50;
                $this->mbtiSN = $data['percentage']['S/N'] ?? 50;
                $this->mbtiTF = $data['percentage']['T/F'] ?? 50;
                $this->mbtiJP = $data['percentage']['J/P'] ?? 50;
                break;
            case 'strengthsfinder':
                $top5 = $data['top5'] ?? [];
                $this->strengthsTop5 = array_pad($top5, 5, null);
                $this->strengthsAll34 = $data['all34'] ?? [];
                break;
            case 'enneagram':
                $this->enneagramType = $data['type'] ?? '';
                $this->enneagramWing = $data['wing'] ?? '';
                $this->enneagramTritype = $data['tritype'] ?? '';
                $this->enneagramInstinctualVariant = $data['instinctual_variant'] ?? '';
                break;
            case 'big5':
                $this->big5Openness = $data['openness'] ?? 50;
                $this->big5Conscientiousness = $data['conscientiousness'] ?? 50;
                $this->big5Extraversion = $data['extraversion'] ?? 50;
                $this->big5Agreeableness = $data['agreeableness'] ?? 50;
                $this->big5Neuroticism = $data['neuroticism'] ?? 50;
                break;
            case 'ffs':
                $this->ffsCondensing = $data['condensing'] ?? 10;
                $this->ffsAcceptance = $data['acceptance'] ?? 10;
                $this->ffsDiscrimination = $data['discrimination'] ?? 10;
                $this->ffsDiffusion = $data['diffusion'] ?? 10;
                $this->ffsConservation = $data['conservation'] ?? 10;
                break;
        }
    }

    public function save()
    {
        $this->validate($this->rules());

        $resultData = $this->buildResultData();

        $data = [
            'user_id' => Auth::id(),
            'assessment_type' => $this->assessmentType,
            'assessment_name' => $this->assessmentName ?: null,
            'result_data' => $resultData,
            'completed_at' => $this->completedAt ?: null,
            'notes' => $this->notes ?: null,
        ];

        if ($this->assessmentId) {
            $assessment = PersonalityAssessment::where('user_id', Auth::id())
                ->where('id', $this->assessmentId)
                ->firstOrFail();
            $assessment->update($data);
            session()->flash('message', '診断結果を更新しました');
        } else {
            $assessment = PersonalityAssessment::create($data);
            
            // Update user's last_activity_at
            $user = Auth::user();
            if ($user) {
                $user->last_activity_at = now();
                $user->save();
            }
            
            // アクティビティログに記録
            $activityLogService = app(ActivityLogService::class);
            $activityLogService->logPersonalityAssessmentCompleted(Auth::id(), $assessment->id, $this->assessmentType);
            
            // 初回自己診断入力時にオンボーディング進捗を更新
            $hasOtherAssessments = PersonalityAssessment::where('user_id', Auth::id())
                ->where('id', '!=', $assessment->id)
                ->exists();
            
            if (!$hasOtherAssessments) {
                $progressService = app(\App\Services\OnboardingProgressService::class);
                $progressService->updateProgress(Auth::id(), 'assessment');
            }
            session()->flash('message', '診断結果を保存しました');
            $this->resetForm();
        }
    }

    private function buildResultData(): array
    {
        switch ($this->assessmentType) {
            case 'mbti':
                return [
                    'type' => $this->mbtiType,
                    'dimensions' => [
                        'E/I' => strpos($this->mbtiType, 'E') !== false ? 'E' : 'I',
                        'S/N' => strpos($this->mbtiType, 'S') !== false ? 'S' : 'N',
                        'T/F' => strpos($this->mbtiType, 'T') !== false ? 'T' : 'F',
                        'J/P' => strpos($this->mbtiType, 'J') !== false ? 'J' : 'P',
                    ],
                    'percentage' => [
                        'E/I' => $this->mbtiEI,
                        'S/N' => $this->mbtiSN,
                        'T/F' => $this->mbtiTF,
                        'J/P' => $this->mbtiJP,
                    ],
                ];
            case 'strengthsfinder':
                return [
                    'top5' => array_values(array_filter($this->strengthsTop5, fn($s) => !empty($s))),
                    'all34' => $this->strengthsAll34,
                ];
            case 'enneagram':
                return [
                    'type' => $this->enneagramType,
                    'wing' => $this->enneagramWing,
                    'tritype' => $this->enneagramTritype,
                    'instinctual_variant' => $this->enneagramInstinctualVariant,
                ];
            case 'big5':
                return [
                    'openness' => $this->big5Openness,
                    'conscientiousness' => $this->big5Conscientiousness,
                    'extraversion' => $this->big5Extraversion,
                    'agreeableness' => $this->big5Agreeableness,
                    'neuroticism' => $this->big5Neuroticism,
                ];
            case 'ffs':
                return [
                    'condensing' => $this->ffsCondensing,
                    'acceptance' => $this->ffsAcceptance,
                    'discrimination' => $this->ffsDiscrimination,
                    'diffusion' => $this->ffsDiffusion,
                    'conservation' => $this->ffsConservation,
                ];
            default:
                return [];
        }
    }

    public function delete($id)
    {
        $assessment = PersonalityAssessment::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();
        
        // アクティビティログに記録
        $activityLogService = app(ActivityLogService::class);
        $activityLogService->logPersonalityAssessmentDeleted(Auth::id(), $id);
        
        $assessment->delete();
        session()->flash('message', '診断結果を削除しました');
    }

    private function resetForm()
    {
        $this->assessmentId = null;
        $this->completedAt = '';
        $this->notes = '';
        $this->resetFormFields();
    }

    public function render()
    {
        $assessments = PersonalityAssessment::where('user_id', Auth::id())
            ->orderBy('completed_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.personality-assessment-form', [
            'assessments' => $assessments,
        ]);
    }
}
