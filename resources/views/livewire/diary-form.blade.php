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

    {{-- 目標・目的との接続情報（ローディング中） --}}
    <div wire:loading wire:target="save" class="mb-6 bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-6">
        <div class="flex items-center justify-center gap-3 py-8">
            <svg class="animate-spin h-6 w-6 text-[#6BB6FF]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="body-text text-[#2E5C8A]">目標・目的との接続を分析中...</span>
        </div>
    </div>

    {{-- 目標・目的との接続情報 --}}
    @if(!empty($goalConnections))
        <div class="mb-6 bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-6" wire:loading.remove wire:target="save">
            <div class="flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-[#6BB6FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
                <h3 class="body-text font-semibold text-[#2E5C8A]">目標・目的との接続</h3>
            </div>
            <div class="space-y-3">
                @foreach($goalConnections as $connection)
                    <div class="bg-white rounded-lg p-4 border border-blue-100">
                        <div class="flex items-start justify-between gap-4 mb-2">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    @if($connection['type'] === 'milestone')
                                        <span class="inline-flex items-center px-2 py-1 rounded-md bg-blue-100 text-blue-800 body-small font-medium">
                                            マイルストーン
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-md bg-indigo-100 text-indigo-800 body-small font-medium">
                                            Willテーマ
                                        </span>
                                    @endif
                                    @if($connection['connected'])
                                        <span class="body-text font-medium text-[#2E5C8A]">
                                            {{ $connection['connected']['title'] }}
                                        </span>
                                    @endif
                                </div>
                                @if($connection['will_theme'])
                                    <p class="body-small text-[#1E3A5F]/80 mb-2">
                                        <span class="font-medium">テーマ:</span> {{ $connection['will_theme'] }}
                                    </p>
                                @endif
                                @if($connection['reason'])
                                    <p class="body-small text-[#1E3A5F]">
                                        {{ $connection['reason'] }}
                                    </p>
                                @endif
                            </div>
                            <div class="flex flex-col items-end">
                                <div class="text-right">
                                    <div class="body-small text-[#1E3A5F]/60 mb-1">接続度</div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-24 h-2 bg-gray-200 rounded-full overflow-hidden">
                                            <div 
                                                class="h-full bg-gradient-to-r from-blue-400 to-indigo-500 transition-all duration-300"
                                                style="width: {{ $connection['score'] }}%"
                                            ></div>
                                        </div>
                                        <span class="body-text font-semibold text-[#2E5C8A] w-10 text-right">
                                            {{ $connection['score'] }}%
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- アクションアイテム生成中（ローディング中） --}}
    <div wire:loading wire:target="save" class="mb-6 bg-blue-50 border border-blue-200 rounded-xl p-6">
        <div class="flex items-center justify-center gap-3 py-8">
            <svg class="animate-spin h-6 w-6 text-[#6BB6FF]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="body-text text-[#2E5C8A]">アクションアイテムを生成中...</span>
        </div>
    </div>

    {{-- 提案されたアクションアイテム --}}
    @if($showActionItems && !empty($suggestedActionItems))
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-xl p-6" wire:loading.remove wire:target="save">
            <div class="flex items-center justify-between mb-4">
                <h3 class="body-text font-semibold text-[#2E5C8A]">関連するマイルストーンへのアクション提案</h3>
                <button
                    wire:click="dismissActionItems"
                    class="text-[#1E3A5F]/60 hover:text-[#2E5C8A] transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="space-y-3">
                @foreach($suggestedActionItems as $index => $action)
                    <div class="bg-white rounded-lg p-4 border border-blue-100">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <h4 class="body-text font-medium text-[#2E5C8A] mb-1">{{ $action['title'] }}</h4>
                                @if(!empty($action['description']))
                                    <p class="body-small text-[#1E3A5F]/80">{{ $action['description'] }}</p>
                                @endif
                            </div>
                            <button
                                wire:click="acceptActionItem({{ $index }})"
                                class="px-4 py-2 bg-[#6BB6FF] text-white body-small font-medium rounded-lg hover:bg-[#5AA5E6] transition-colors whitespace-nowrap"
                            >
                                追加
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- 保存ボタン --}}
    <div class="flex justify-end gap-3">
        <button
            wire:click="save"
            class="btn-secondary"
            wire:loading.attr="disabled"
            wire:target="save"
        >
            <span wire:loading.remove wire:target="save">保存</span>
            <span wire:loading wire:target="save" class="flex items-center gap-2">
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                保存中...
            </span>
        </button>
        <button
            wire:click="saveWithActionSuggestion"
            class="btn-primary"
            wire:loading.attr="disabled"
            wire:target="saveWithActionSuggestion"
        >
            <span wire:loading.remove wire:target="saveWithActionSuggestion">アクション提案</span>
            <span wire:loading wire:target="saveWithActionSuggestion" class="flex items-center gap-2">
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                AI分析中...
            </span>
        </button>
    </div>
</div>