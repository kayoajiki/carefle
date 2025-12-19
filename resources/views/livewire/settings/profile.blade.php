<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout heading="プロフィール" subheading="プロフィール情報を更新してください">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <!-- Name -->
            <div class="flex flex-col gap-2">
                <label for="name" class="text-sm font-medium text-[#2E5C8A]">
                    お名前 <span class="text-red-500">*</span>
                </label>
                <input
                    id="name"
                    wire:model="name"
                    type="text"
                    required
                    autofocus
                    autocomplete="name"
                    class="w-full px-4 py-3 rounded-lg border border-[#2E5C8A]/20 bg-white text-[#2E5C8A] focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-transparent"
                    placeholder="山田 太郎"
                />
                @error('name')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div class="flex flex-col gap-2">
                <label for="email" class="text-sm font-medium text-[#2E5C8A]">
                    メールアドレス <span class="text-red-500">*</span>
                </label>
                <input
                    id="email"
                    wire:model="email"
                    type="email"
                    required
                    autocomplete="email"
                    class="w-full px-4 py-3 rounded-lg border border-[#2E5C8A]/20 bg-white text-[#2E5C8A] focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-transparent"
                    placeholder="email@example.com"
                />
                @error('email')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !auth()->user()->hasVerifiedEmail())
                    <div class="mt-4">
                        <p class="text-sm text-[#1E3A5F]">
                            メールアドレスが認証されていません。
                            <button type="button" wire:click.prevent="resendVerificationNotification" class="text-sm text-[#6BB6FF] hover:text-[#5AA5E6] underline">
                                認証メールを再送信する
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 text-sm font-medium text-green-600">
                                新しい認証リンクをメールアドレスに送信しました。
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Birthdate -->
            <div class="flex flex-col gap-2">
                <label for="birthdate" class="text-sm font-medium text-[#2E5C8A]">
                    生年月日 <span class="text-xs text-dim">（任意）</span>
                </label>
                <input
                    id="birthdate"
                    wire:model="birthdate"
                    type="date"
                    max="{{ date('Y-m-d', strtotime('-1 day')) }}"
                    class="w-full px-4 py-3 rounded-lg border border-[#2E5C8A]/20 bg-white text-[#2E5C8A] focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-transparent"
                />
                @error('birthdate')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Gender -->
            <div class="flex flex-col gap-2">
                <label class="text-sm font-medium text-[#2E5C8A] mb-1">
                    性別 <span class="text-xs text-dim">（任意）</span>
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center gap-2 p-3 rounded-lg border cursor-pointer transition-colors"
                           :class="$wire.gender === 'male' ? 'border-[#6BB6FF] bg-[#E8F4FF]' : 'border-[#2E5C8A]/20 hover:border-[#6BB6FF]/50'">
                        <input
                            type="radio"
                            wire:model="gender"
                            value="male"
                            class="w-4 h-4 text-[#6BB6FF] border-[#2E5C8A]/20 focus:ring-[#6BB6FF]"
                        />
                        <span class="text-sm text-[#2E5C8A]">男性</span>
                    </label>
                    <label class="flex items-center gap-2 p-3 rounded-lg border cursor-pointer transition-colors"
                           :class="$wire.gender === 'female' ? 'border-[#6BB6FF] bg-[#E8F4FF]' : 'border-[#2E5C8A]/20 hover:border-[#6BB6FF]/50'">
                        <input
                            type="radio"
                            wire:model="gender"
                            value="female"
                            class="w-4 h-4 text-[#6BB6FF] border-[#2E5C8A]/20 focus:ring-[#6BB6FF]"
                        />
                        <span class="text-sm text-[#2E5C8A]">女性</span>
                    </label>
                    <label class="flex items-center gap-2 p-3 rounded-lg border cursor-pointer transition-colors"
                           :class="$wire.gender === 'other' ? 'border-[#6BB6FF] bg-[#E8F4FF]' : 'border-[#2E5C8A]/20 hover:border-[#6BB6FF]/50'">
                        <input
                            type="radio"
                            wire:model="gender"
                            value="other"
                            class="w-4 h-4 text-[#6BB6FF] border-[#2E5C8A]/20 focus:ring-[#6BB6FF]"
                        />
                        <span class="text-sm text-[#2E5C8A]">その他</span>
                    </label>
                    <label class="flex items-center gap-2 p-3 rounded-lg border cursor-pointer transition-colors"
                           :class="$wire.gender === 'prefer_not_to_say' ? 'border-[#6BB6FF] bg-[#E8F4FF]' : 'border-[#2E5C8A]/20 hover:border-[#6BB6FF]/50'">
                        <input
                            type="radio"
                            wire:model="gender"
                            value="prefer_not_to_say"
                            class="w-4 h-4 text-[#6BB6FF] border-[#2E5C8A]/20 focus:ring-[#6BB6FF]"
                        />
                        <span class="text-sm text-[#2E5C8A]">回答しない</span>
                    </label>
                </div>
                @error('gender')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Prefecture -->
            <div class="flex flex-col gap-2">
                <label for="prefecture" class="text-sm font-medium text-[#2E5C8A]">
                    居住地（都道府県） <span class="text-xs text-dim">（任意）</span>
                </label>
                <select
                    id="prefecture"
                    wire:model="prefecture"
                    class="w-full px-4 py-3 rounded-lg border border-[#2E5C8A]/20 bg-white text-[#2E5C8A] focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-transparent"
                >
                    <option value="">選択してください</option>
                    @foreach($this->prefectures as $pref)
                        <option value="{{ $pref }}">{{ $pref }}</option>
                    @endforeach
                </select>
                @error('prefecture')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Occupation -->
            <div class="flex flex-col gap-2">
                <label for="occupation" class="text-sm font-medium text-[#2E5C8A]">
                    職種 <span class="text-xs text-dim">（任意）</span>
                </label>
                <select
                    id="occupation"
                    wire:model="occupation"
                    class="w-full px-4 py-3 rounded-lg border border-[#2E5C8A]/20 bg-white text-[#2E5C8A] focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-transparent"
                >
                    <option value="">選択してください</option>
                    @foreach($this->occupations as $occ)
                        <option value="{{ $occ }}">{{ $occ }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Industry -->
            <div class="flex flex-col gap-2">
                <label for="industry" class="text-sm font-medium text-[#2E5C8A]">
                    業界 <span class="text-xs text-dim">（任意）</span>
                </label>
                <select
                    id="industry"
                    wire:model="industry"
                    class="w-full px-4 py-3 rounded-lg border border-[#2E5C8A]/20 bg-white text-[#2E5C8A] focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-transparent"
                >
                    <option value="">選択してください</option>
                    @foreach($this->industries as $ind)
                        <option value="{{ $ind }}">{{ $ind }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Employment Type -->
            <div class="flex flex-col gap-2">
                <label for="employment_type" class="text-sm font-medium text-[#2E5C8A]">
                    雇用形態 <span class="text-xs text-dim">（任意）</span>
                </label>
                <select
                    id="employment_type"
                    wire:model="employment_type"
                    class="w-full px-4 py-3 rounded-lg border border-[#2E5C8A]/20 bg-white text-[#2E5C8A] focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-transparent"
                >
                    <option value="">選択してください</option>
                    @foreach($this->employmentTypes as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Work Experience Years -->
            <div class="flex flex-col gap-2">
                <label for="work_experience_years" class="text-sm font-medium text-[#2E5C8A]">
                    勤続年数 <span class="text-xs text-dim">（任意）</span>
                </label>
                <select
                    id="work_experience_years"
                    wire:model="work_experience_years"
                    class="w-full px-4 py-3 rounded-lg border border-[#2E5C8A]/20 bg-white text-[#2E5C8A] focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-transparent"
                >
                    <option value="">選択してください</option>
                    <option value="not_working">現在は働いていない</option>
                    <option value="0">0年（入社したて）</option>
                    @for($i = 1; $i <= 10; $i++)
                        <option value="{{ $i }}">{{ $i }}年</option>
                    @endfor
                    <option value="11">11〜15年</option>
                    <option value="16">16〜20年</option>
                    <option value="21">21年以上</option>
                </select>
                @error('work_experience_years')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Education -->
            <div class="flex flex-col gap-2">
                <label for="education" class="text-sm font-medium text-[#2E5C8A]">
                    最終学歴 <span class="text-xs text-dim">（任意）</span>
                </label>
                <select
                    id="education"
                    wire:model="education"
                    class="w-full px-4 py-3 rounded-lg border border-[#2E5C8A]/20 bg-white text-[#2E5C8A] focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-transparent"
                >
                    <option value="">選択してください</option>
                    @foreach($this->educations as $edu)
                        <option value="{{ $edu }}">{{ $edu }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-4 pt-4">
                <div class="flex items-center justify-end">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="updateProfileInformation"
                        class="px-6 py-3 rounded-lg font-semibold bg-[#6BB6FF] text-white hover:bg-[#5AA5E6] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span wire:loading.remove wire:target="updateProfileInformation">保存</span>
                        <span wire:loading wire:target="updateProfileInformation" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            保存中...
                        </span>
                    </button>
                </div>

                @if (session('message'))
                    <div class="text-sm text-green-600">
                        {{ session('message') }}
                    </div>
                @endif

                <x-action-message class="me-3" on="profile-updated">
                    保存しました。
                </x-action-message>
            </div>
        </form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>
