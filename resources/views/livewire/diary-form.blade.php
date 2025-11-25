<div class="card-refined p-8">
    @if(session('message'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 body-small p-4 rounded-xl">
            {{ session('message') }}
        </div>
    @endif

    <div class="mb-6">
        <label class="body-small font-medium text-[#2E5C8A] mb-2 block">日付 <span class="text-red-500">*</span></label>
        <input
            type="date"
            wire:model.defer="date"
            class="w-full rounded-xl border-2 border-[#2E5C8A]/20 bg-white text-[#2E5C8A] px-4 py-3 body-text focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"
        />
        @error('date')
            <span class="body-small text-red-600 mt-2 block">{{ $message }}</span>
        @enderror
    </div>

    {{-- モチベーションバー --}}
    <div class="mb-6" x-data="{ localMotivation: @entangle('motivation') }">
        <label class="body-small font-medium text-[#2E5C8A] mb-3 block">
            モチベーション（0〜100）
        </label>
        <div class="flex items-center gap-4">
            <input
                type="range"
                min="0"
                max="100"
                x-model="localMotivation"
                @input="localMotivation = $event.target.value"
                class="w-full accent-[#6BB6FF]"
            />
            <div class="body-text font-semibold text-[#2E5C8A] w-12 text-right" x-text="localMotivation">
                {{ $motivation }}
            </div>
        </div>
        @error('motivation')
            <span class="body-small text-red-600 mt-2 block">{{ $message }}</span>
        @enderror
    </div>

    {{-- 今日の出来事 --}}
    <div class="mb-6">
        <label class="body-small font-medium text-[#2E5C8A] mb-2 block">今日の出来事（任意）</label>
        <textarea
            wire:model.defer="content"
            rows="6"
            class="w-full rounded-xl border-2 border-[#2E5C8A]/20 bg-white text-[#2E5C8A] px-4 py-3 body-text leading-relaxed focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"
            placeholder="今日あったこと、感じたことなどを自由に書いてください。"
        ></textarea>
        @error('content')
            <span class="body-small text-red-600 mt-2 block">{{ $message }}</span>
        @enderror
    </div>

    {{-- 写真アップロード --}}
    <div class="mb-6">
        <label class="body-small font-medium text-[#2E5C8A] mb-2 block">写真（任意、1日1枚まで）</label>
        
        @if($existingPhoto)
            <div class="mb-4 relative">
                <img src="{{ asset('storage/' . $existingPhoto) }}" alt="日記の写真" class="w-full max-w-md rounded-xl border-2 border-[#2E5C8A]/20">
                <button
                    type="button"
                    wire:click="deletePhoto"
                    class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-red-600 transition-colors"
                    onclick="return confirm('写真を削除しますか？')"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        @if(!$existingPhoto)
            <div class="border-2 border-dashed border-[#2E5C8A]/30 rounded-xl p-6 text-center hover:border-[#6BB6FF] transition-colors">
                <input
                    type="file"
                    wire:model="photo"
                    accept="image/*"
                    class="hidden"
                    id="photo-upload"
                />
                <label for="photo-upload" class="cursor-pointer">
                    <svg class="w-12 h-12 text-[#6BB6FF] mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="body-text text-[#2E5C8A] mb-1">写真をアップロード</p>
                    <p class="body-small text-[#1E3A5F]">クリックまたはドラッグ&ドロップ</p>
                </label>
            </div>
        @else
            <div class="border-2 border-dashed border-[#2E5C8A]/30 rounded-xl p-4 text-center">
                <input
                    type="file"
                    wire:model="photo"
                    accept="image/*"
                    class="hidden"
                    id="photo-replace"
                />
                <label for="photo-replace" class="cursor-pointer body-small text-[#2E5C8A] hover:text-[#6BB6FF] transition-colors">
                    写真を差し替える
                </label>
            </div>
        @endif

        @if($photo)
            <div class="mt-4">
                <p class="body-small text-[#1E3A5F] mb-2">プレビュー:</p>
                <img src="{{ $photo->temporaryUrl() }}" alt="プレビュー" class="w-full max-w-md rounded-xl border-2 border-[#2E5C8A]/20">
            </div>
        @endif

        @error('photo')
            <span class="body-small text-red-600 mt-2 block">{{ $message }}</span>
        @enderror
    </div>

    {{-- 保存ボタン --}}
    <div class="flex justify-end">
        <button
            wire:click="save"
            class="btn-primary"
        >
            保存
        </button>
    </div>
</div>
