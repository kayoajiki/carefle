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
    // フラット配列（0..14）: 保存時の確実な区切りのために使用
    public array $answersLinear = [];
    public ?int $draftId = null;
    public ?string $draftSavedAt = null;

    public function mount(): void
    {
        if (empty($this->answersLinear)) {
            $this->answersLinear = array_fill(0, 15, '');
        }
    }

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

        [$willText, $canText, $mustText] = $this->composeTexts();

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
        [$willText, $canText, $mustText] = $this->composeTexts();

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

    public function updatedAnswersLinear(): void
    {
        // フラット配列が更新された場合、セクション配列へも反映
        $this->answers['will'] = array_values(array_slice($this->answersLinear, 0, 5));
        $this->answers['can']  = array_values(array_slice($this->answersLinear, 5, 5));
        $this->answers['must'] = array_values(array_slice($this->answersLinear, 10, 5));
        $this->saveDraft();
    }

    private function composeTexts(): array
    {
        // フラット配列が優先。なければ従来配列を使用
        if (!empty($this->answersLinear)) {
            $will = array_filter(array_map('trim', array_slice($this->answersLinear, 0, 5)), fn($t)=>$t!=="");
            $can  = array_filter(array_map('trim', array_slice($this->answersLinear, 5, 5)), fn($t)=>$t!=="");
            $must = array_filter(array_map('trim', array_slice($this->answersLinear, 10, 5)), fn($t)=>$t!=="");
        } else {
            $will = array_filter(array_map('trim', $this->answers['will'] ?? []), fn($t)=>$t!=="");
            $can  = array_filter(array_map('trim', $this->answers['can'] ?? []), fn($t)=>$t!=="");
            $must = array_filter(array_map('trim', $this->answers['must'] ?? []), fn($t)=>$t!=="");
        }
        return [trim(implode("\n\n", $will)), trim(implode("\n\n", $can)), trim(implode("\n\n", $must))];
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

        $hints = [
            'will' => [
                '制約を取り払って理想の未来を思い描いてみましょう。なぜその目標を選んだのかも掘り下げると、自分の価値観や動機が見えてきます。',
                '夢中になって時間を忘れる瞬間を振り返ってみましょう。過去の出来事や趣味から一貫性のある興味が見つかるかもしれません。',
                '強い喜びや怒りを感じた過去の出来事を思い出し、「なぜそう感じたか？」を深掘りしてみましょう。',
                '現状維持で進んだ未来をリアルに想像してみましょう。そこに違和感や不安があるなら、今こそ変化のサインかもしれません。',
                '憧れの人物に共感する理由は、あなた自身が本当はなりたい姿を示しています。共通点や背景にも目を向けてみましょう。',
            ],
            'can' => [
                '人から褒められたことや信頼された経験を思い出してみましょう。自分では気づいていない強みに出会えるかもしれません。',
                '成果の背景にある自分の努力・スキル・工夫に注目してみてください。それがあなたの武器となる能力です。',
                '「当たり前にできること」は、あなたにとっての才能かもしれません。他人との違いに注目しましょう。',
                'どんな工夫や姿勢で困難を乗り越えたかを具体的に振り返りましょう。強みはピンチの中でこそ輝きます。',
                '長く取り組めた背景には、情熱や得意意識があります。「なぜ続けられたのか」にこそ、あなたの強みのヒントがあります。',
            ],
            'must' => [
                '仕事、家庭、地域などでの役割を整理し、それがキャリア選択にどう影響しているか考えてみましょう。',
                '収入・勤務地・勤務時間・健康など、生活維持に必要な条件をリストアップし、それがキャリアにどう関わるかを考えましょう。',
                '周囲の期待と自分の考える役割にズレがないかを見直してみましょう。そのギャップに向き合うことが大切です。',
                '強い使命感を抱くテーマがある場合、それはあなたの社会的役割（MUST）と一致する可能性があります。',
                '理想の自分に向かうために、今何を身につけるべきかを考えてみましょう。将来の後悔を防ぐためのヒントになります。',
            ],
        ];

        return view('livewire.wcm-form', [
            'questions' => $questions,
            'hints' => $hints,
        ]);
    }
}


