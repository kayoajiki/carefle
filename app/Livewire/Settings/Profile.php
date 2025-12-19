<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Profile extends Component
{
    public string $name = '';
    public string $email = '';
    public string $birthdate = '';
    public string $gender = '';
    public string $prefecture = '';
    public string $occupation = '';
    public string $industry = '';
    public string $employment_type = '';
    public string $work_experience_years = '';
    public string $education = '';

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

    // 雇用形態リスト
    public function getEmploymentTypesProperty()
    {
        return [
            '正社員', '契約社員', '派遣社員', 'パート・アルバイト', '業務委託・フリーランス', 'その他'
        ];
    }

    // 最終学歴リスト
    public function getEducationsProperty()
    {
        return [
            '高卒', '専門学校卒', '短大・高専卒', '大学卒', '大学院卒（修士）', '大学院卒（博士）', 'その他'
        ];
    }

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();
        
        $this->name = $user->name ?? '';
        $this->email = $user->email ?? '';
        
        if ($user->birthdate) {
            $this->birthdate = $user->birthdate->format('Y-m-d');
        }
        
        $this->gender = $user->gender ?? '';
        $this->prefecture = $user->prefecture ?? '';
        $this->occupation = $user->occupation ?? '';
        $this->industry = $user->industry ?? '';
        $this->employment_type = $user->employment_type ?? '';
        $this->work_experience_years = $user->work_experience_years ?? '';
        $this->education = $user->education ?? '';
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
            'birthdate' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other', 'prefer_not_to_say'])],
            'prefecture' => ['nullable', 'string'],
            'occupation' => ['nullable', 'string'],
            'industry' => ['nullable', 'string'],
            'employment_type' => ['nullable', 'string'],
            'work_experience_years' => ['nullable'],
            'education' => ['nullable', 'string'],
        ], [
            'name.required' => 'お名前は必須です。',
            'email.required' => 'メールアドレスは必須です。',
            'email.email' => '有効なメールアドレスを入力してください。',
            'email.unique' => 'このメールアドレスは既に使用されています。',
            'birthdate.before' => '正しい生年月日を入力してください。',
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'birthdate' => $validated['birthdate'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'prefecture' => $validated['prefecture'] ?? null,
            'occupation' => $validated['occupation'] ?? null,
            'industry' => $validated['industry'] ?? null,
            'employment_type' => $validated['employment_type'] ?? null,
            'work_experience_years' => $validated['work_experience_years'] ?? null,
            'education' => $validated['education'] ?? null,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
        
        session()->flash('message', 'プロフィールを更新しました。');
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}
