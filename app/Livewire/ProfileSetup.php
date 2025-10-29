<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileSetup extends Component
{
    public $name = '';
    public $birthdate = '';
    public $gender = '';
    public $prefecture = '';
    public $occupation = '';
    public $industry = '';
    public $work_experience_years = '';
    public $education = '';

    // 都道府県リスト
    public function getPrefecturesProperty()
    {
        return [
            '北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県',
            '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県',
            '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県',
            '静岡県', '愛知県', '三重県', '滋賀県', '京都府', '大阪府', '兵庫県',
            '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県',
            '徳島県', '香川県', '愛媛県', '高知県', '福岡県', '佐賀県', '長崎県',
            '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'
        ];
    }

    // 職種リスト
    public function getOccupationsProperty()
    {
        return [
            '営業', 'マーケティング', '人事・総務', '経理・財務', '法務', 'コンサルタント',
            'エンジニア・開発', 'デザイナー', '企画・商品開発', '営業企画', '広報・PR',
            'クリエイティブ', 'マネジメント', '事務・管理', '研究・開発', 'その他'
        ];
    }

    // 業界リスト
    public function getIndustriesProperty()
    {
        return [
            'IT・インターネット', '通信・情報', 'メーカー・製造', '金融・保険', '不動産',
            '商社・流通', '小売・外食', '広告・マスコミ', 'コンサルティング', '人材',
            '教育・研究', '医療・介護', '美容・アパレル', '旅行・レジャー', '食品・飲料',
            '建設・インフラ', 'エネルギー', '官公庁・自治体', 'その他'
        ];
    }

    // 最終学歴リスト
    public function getEducationsProperty()
    {
        return [
            '高卒', '専門学校卒', '短大・高専卒', '大学卒', '大学院卒（修士）', '大学院卒（博士）', 'その他'
        ];
    }

    public function mount()
    {
        $user = Auth::user();
        
        // プロフィールが既に完了している場合は診断ページにリダイレクト
        if ($user->profile_completed) {
            $this->redirect(route('diagnosis.start'));
            return;
        }
        
        $this->name = $user->name ?? '';
        
        if ($user->birthdate) {
            $this->birthdate = $user->birthdate->format('Y-m-d');
        }
        
        $this->gender = $user->gender ?? '';
        $this->prefecture = $user->prefecture ?? '';
        $this->occupation = $user->occupation ?? '';
        $this->industry = $user->industry ?? '';
        $this->work_experience_years = $user->work_experience_years ?? '';
        $this->education = $user->education ?? '';
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'birthdate' => ['required', 'date', 'before:today'],
            'gender' => ['required', Rule::in(['male', 'female', 'other', 'prefer_not_to_say'])],
            'prefecture' => ['required', 'string'],
            'occupation' => ['nullable', 'string'],
            'industry' => ['nullable', 'string'],
            'work_experience_years' => ['nullable', 'integer', 'min:0', 'max:50'],
            'education' => ['nullable', 'string'],
        ], [
            'name.required' => 'お名前は必須です。',
            'birthdate.required' => '生年月日は必須です。',
            'birthdate.before' => '正しい生年月日を入力してください。',
            'gender.required' => '性別を選択してください。',
            'prefecture.required' => '居住地（都道府県）を選択してください。',
            'work_experience_years.integer' => '勤続年数は数値で入力してください。',
            'work_experience_years.min' => '勤続年数は0年以上で入力してください。',
            'work_experience_years.max' => '勤続年数は50年以下で入力してください。',
        ]);

        $user = Auth::user();
        $user->update([
            'name' => $validated['name'],
            'birthdate' => $validated['birthdate'],
            'gender' => $validated['gender'],
            'prefecture' => $validated['prefecture'],
            'occupation' => $validated['occupation'] ?? null,
            'industry' => $validated['industry'] ?? null,
            'work_experience_years' => $validated['work_experience_years'] ?? null,
            'education' => $validated['education'] ?? null,
            'profile_completed' => true,
        ]);

        return $this->redirect(route('diagnosis.start'));
    }

    public function render()
    {
        return view('livewire.profile-setup');
    }
}
