<?php

namespace App\Livewire;

use App\Models\WcmSheet;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.simple_component')]
class WcmForm extends Component
{
    public int $step = 0; // 0..14 (WILL 5, CAN 5, MUST 5)
    public array $answers = [
        'will' => [],
        'can' => [],
        'must' => [],
    ];
    public ?int $draftId = null;
    public ?string $draftSavedAt = null;

    public function next(): void
    {
        if ($this->step < 14) $this->step++;
    }

    public function prev(): void
    {
        if ($this->step > 0) $this->step--;
    }

    public function finish(): mixed
    {
        $userId = Auth::id();

        // 10枚制限（確定のみ）
        $count = WcmSheet::where('user_id', $userId)->where('is_draft', false)->count();
        if ($count >= 10) {
            session()->flash('error', '保存上限（10件）に達しています。古いシートを削除してください。');
            return null;
        }

        $willText = trim(implode("\n\n", $this->answers['will']));
        $canText  = trim(implode("\n\n", $this->answers['can']));
        $mustText = trim(implode("\n\n", $this->answers['must']));

        if ($this->draftId) {
            $sheet = WcmSheet::where('id', $this->draftId)->where('user_id', $userId)->first();
            if ($sheet) {
                $maxVersion = WcmSheet::where('user_id', $userId)->where('is_draft', false)->max('version') ?? 0;
                $sheet->update([
                    'title'     => $sheet->title ?? 'WCMシート',
                    'will_text' => $willText,
                    'can_text'  => $canText,
                    'must_text' => $mustText,
                    'version'   => $maxVersion + 1,
                    'is_draft'  => false,
                ]);
                return redirect()->route('wcm.sheet', ['id' => $sheet->id]);
            }
        }

        $maxVersion = WcmSheet::where('user_id', $userId)->where('is_draft', false)->max('version') ?? 0;
        $sheet = WcmSheet::create([
            'user_id'   => $userId,
            'title'     => 'WCMシート',
            'will_text' => $willText,
            'can_text'  => $canText,
            'must_text' => $mustText,
            'version'   => $maxVersion + 1,
            'is_draft'  => false,
        ]);

        return redirect()->route('wcm.sheet', ['id' => $sheet->id]);
    }

    public function saveDraft(): void
    {
        $userId = Auth::id();
        $willText = trim(implode("\n\n", $this->answers['will']));
        $canText  = trim(implode("\n\n", $this->answers['can']));
        $mustText = trim(implode("\n\n", $this->answers['must']));

        if ($this->draftId) {
            WcmSheet::where('id', $this->draftId)
                ->where('user_id', $userId)
                ->update([
                    'title'     => 'WCMシート（下書き）',
                    'will_text' => $willText,
                    'can_text'  => $canText,
                    'must_text' => $mustText,
                    'is_draft'  => true,
                ]);
        } else {
            $sheet = WcmSheet::create([
                'user_id'   => $userId,
                'title'     => 'WCMシート（下書き）',
                'will_text' => $willText,
                'can_text'  => $canText,
                'must_text' => $mustText,
                'version'   => 1,
                'is_draft'  => true,
            ]);
            $this->draftId = $sheet->id;
        }
        $this->draftSavedAt = now()->format('H:i');
    }

    public function updatedAnswers(): void
    {
        $this->saveDraft();
    }

    public function render()
    {
        $questions = [
            'will' => [
                'これから3年後、どんな目標を達成していたいですか？',
                '何をしているときに、最も心が踊りますか？',
                'あなたにとって「絶対に譲れない価値観」は何ですか？',
                '今の延長線上にある5年後・10年後の姿に満足できますか？',
                '尊敬する人は誰で、その人のどこに憧れますか？',
            ],
            'can' => [
                '周囲から「あなたに任せたい」と言われたことは何ですか？',
                '最も誇りに思う成果は何ですか？その成功の要因は？',
                '他人が苦手でも、自分は苦労せずにできることは何ですか？',
                '困難を乗り越えたとき、どんな強みを発揮していましたか？',
                '継続的に努力してきたことは何ですか？なぜ続けられましたか？',
            ],
            'must' => [
                '現在あなたが担っている役割や責任には何がありますか？',
                'あなたや家族にとって、絶対に外せない生活条件は何ですか？',
                'あなたの周囲（上司・同僚・家族など）は何を期待していますか？',
                '社会や身の回りの問題で「放っておけない」と感じることは何ですか？',
                '今後必要だと感じているスキルや経験は何ですか？',
            ],
        ];

        return view('livewire.wcm-form', [
            'questions' => $questions,
        ]);
    }
}


