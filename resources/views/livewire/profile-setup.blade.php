<div class="flex flex-col gap-6">
    <div class="flex w-full flex-col text-center mb-4">
        <h1 class="text-2xl md:text-3xl font-bold brand-headline mb-2">プロフィール登録</h1>
        <p class="text-sm text-dim leading-relaxed">
            診断を始める前に、基本的な情報を入力してください
        </p>
    </div>

    <!-- Validation Errors -->
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 text-sm p-3 rounded-md">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form wire:submit="save" class="flex flex-col gap-5">
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
                class="w-full px-4 py-3 rounded-lg border border-[#2E5C8A]/20 bg-white text-[#2E5C8A] focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-transparent"
                placeholder="山田 太郎"
            />
            @error('name')
                <p class="text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Birthdate -->
        <div class="flex flex-col gap-2">
            <label for="birthdate" class="text-sm font-medium text-[#2E5C8A]">
                生年月日 <span class="text-red-500">*</span>
            </label>
            <input
                id="birthdate"
                wire:model="birthdate"
                type="date"
                required
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
                性別 <span class="text-red-500">*</span>
            </label>
            <div class="grid grid-cols-2 gap-3">
                <label class="flex items-center gap-2 p-3 rounded-lg border cursor-pointer transition-colors"
                       :class="$wire.gender === 'male' ? 'border-[#6BB6FF] bg-[#fff9eb]' : 'border-[#2E5C8A]/20 hover:border-[#6BB6FF]/50'">
                    <input
                        type="radio"
                        wire:model="gender"
                        value="male"
                        class="w-4 h-4 text-[#6BB6FF] border-[#2E5C8A]/20 focus:ring-[#6BB6FF]"
                    />
                    <span class="text-sm text-[#2E5C8A]">男性</span>
                </label>
                <label class="flex items-center gap-2 p-3 rounded-lg border cursor-pointer transition-colors"
                       :class="$wire.gender === 'female' ? 'border-[#6BB6FF] bg-[#fff9eb]' : 'border-[#2E5C8A]/20 hover:border-[#6BB6FF]/50'">
                    <input
                        type="radio"
                        wire:model="gender"
                        value="female"
                        class="w-4 h-4 text-[#6BB6FF] border-[#2E5C8A]/20 focus:ring-[#6BB6FF]"
                    />
                    <span class="text-sm text-[#2E5C8A]">女性</span>
                </label>
                <label class="flex items-center gap-2 p-3 rounded-lg border cursor-pointer transition-colors"
                       :class="$wire.gender === 'other' ? 'border-[#6BB6FF] bg-[#fff9eb]' : 'border-[#2E5C8A]/20 hover:border-[#6BB6FF]/50'">
                    <input
                        type="radio"
                        wire:model="gender"
                        value="other"
                        class="w-4 h-4 text-[#6BB6FF] border-[#2E5C8A]/20 focus:ring-[#6BB6FF]"
                    />
                    <span class="text-sm text-[#2E5C8A]">その他</span>
                </label>
                <label class="flex items-center gap-2 p-3 rounded-lg border cursor-pointer transition-colors"
                       :class="$wire.gender === 'prefer_not_to_say' ? 'border-[#6BB6FF] bg-[#fff9eb]' : 'border-[#2E5C8A]/20 hover:border-[#6BB6FF]/50'">
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

        <!-- Prefecture (必須) -->
        <div class="flex flex-col gap-2">
            <label for="prefecture" class="text-sm font-medium text-[#2E5C8A]">
                居住地（都道府県） <span class="text-red-500">*</span>
            </label>
            <select
                id="prefecture"
                wire:model="prefecture"
                required
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

        <!-- Occupation (任意) -->
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

        <!-- Industry (任意) -->
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

        <!-- Employment Type (任意) -->
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

        <!-- Work Experience Years (任意) -->
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

        <!-- Education (任意) -->
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

        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="save"
            class="w-full px-8 py-4 rounded-xl font-bold text-lg bg-gradient-to-r from-[#6BB6FF] to-[#5AA5E6] text-white shadow-lg hover:shadow-xl hover:from-[#5AA5E6] hover:to-[#4A95D6] transform hover:scale-[1.02] transition-all duration-200 mt-4 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
        >
            <span wire:loading.remove wire:target="save" class="flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                診断を始める
            </span>
            <span wire:loading wire:target="save" class="flex items-center justify-center gap-2">
                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                保存中...
            </span>
        </button>
    </form>

    <p class="text-xs text-dim text-center leading-relaxed">
        ご入力いただいた情報は、診断結果の分析にのみ使用されます。<br>
        プライバシーを尊重し、安全に管理いたします。
    </p>
</div>
