<div class="min-h-screen bg-[#f6fbff] px-4 py-10">
    <div class="max-w-4xl mx-auto space-y-6">
        @if (session('saved'))
            <div class="bg-green-50 border border-green-200 text-green-800 body-small p-4 rounded-xl">
                {{ session('saved') }}
            </div>
        @endif

        <div class="card-refined surface-blue p-8 soft-shadow-refined space-y-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="body-small text-[#5BA3D6] uppercase tracking-[0.2em]">My Goal</p>
                    <h1 class="heading-2 mb-1">マイゴール</h1>
                    <p class="body-text text-[#1E3A5F]/80">5-7個の質問に答えると、AIが3-5個のゴール候補を提案します。</p>
                </div>
                @if($currentGoal)
                    <span class="px-3 py-1 bg-white border border-blue-200 rounded-full body-small text-[#2E5C8A]">現在のゴールあり</span>
                @else
                    <span class="px-3 py-1 bg-white border border-blue-200 rounded-full body-small text-[#2E5C8A]">未設定</span>
                @endif
            </div>

            @if($currentGoal)
                <div class="bg-white rounded-xl p-6 border border-blue-100 space-y-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="body-small text-blue-600 mb-2">現在のゴールイメージ</p>
                        </div>
                        <div class="flex items-center gap-2">
                            @if(!$isEditingGoal)
                                <div class="flex items-center gap-2 bg-[#f4f8ff] border border-blue-100 rounded-full px-2 py-1">
                                    <button
                                        wire:click="setDisplayMode('text')"
                                        class="px-3 py-1 rounded-full body-small {{ $displayMode === 'text' ? 'bg-[#2E5C8A] text-white' : 'text-[#2E5C8A]' }}">
                                        文字
                                    </button>
                                    <button
                                        wire:click="setDisplayMode('image')"
                                        class="px-3 py-1 rounded-full body-small {{ $displayMode === 'image' ? 'bg-[#2E5C8A] text-white' : 'text-[#2E5C8A]' }}">
                                        図式
                                    </button>
                                </div>
                                <button
                                    wire:click="startEditingGoal"
                                    class="px-3 py-1 rounded-full body-small bg-[#2E5C8A] text-white hover:bg-[#1E3A5F] transition-colors">
                                    編集
                                </button>
                            @endif
                        </div>
                    </div>

                    @if($isEditingGoal)
                        {{-- 編集モード --}}
                        <div class="space-y-4">
                            <textarea
                                wire:model="editingGoalText"
                                rows="5"
                                class="w-full rounded-xl border-2 border-[#2E5C8A]/20 bg-white text-[#2E5C8A] px-4 py-3 body-text leading-relaxed focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"
                                placeholder="ゴールイメージを入力してください"></textarea>
                            @error('editingGoalText')
                                <p class="body-small text-red-600">{{ $message }}</p>
                            @enderror
                            <div class="flex items-center justify-end gap-2">
                                <button
                                    wire:click="cancelEditingGoal"
                                    class="btn-secondary">
                                    キャンセル
                                </button>
                                <button
                                    wire:click="saveEditedGoal"
                                    class="btn-primary">
                                    保存
                                </button>
                            </div>
                        </div>
                    @else
                        {{-- 表示モード --}}
                        @if($displayMode === 'image')
                            @if($currentGoalImageUrl)
                                <div class="bg-[#F6FBFF] border border-blue-100 rounded-xl p-4">
                                    <img src="{{ $currentGoalImageUrl }}" alt="ゴールイメージ" class="w-full rounded-lg">
                                </div>
                            @else
                                <div class="bg-[#F6FBFF] border border-dashed border-blue-200 rounded-xl p-6 text-center">
                                    <p class="body-small text-[#1E3A5F]/70 mb-3">図式がまだありません。生成しますか？</p>
                                    <button wire:click="generateGoalImage" class="btn-primary">図式を生成する</button>
                                </div>
                            @endif
                        @else
                            <p class="body-text text-[#1E3A5F] whitespace-pre-line leading-relaxed">{{ $currentGoal }}</p>
                        @endif
                    @endif
                </div>
            @endif
        </div>

        {{-- 質問ステップ --}}
        @if($step === 'questions')
            {{-- ローディングアニメーション（質問生成中） --}}
            <div wire:loading.delay class="card-refined bg-white p-12 soft-shadow-refined text-center">
                <div class="flex flex-col items-center justify-center space-y-6">
                    {{-- 回転する円のアニメーション --}}
                    <div class="flex items-center justify-center">
                        <div class="w-16 h-16 border-4 border-blue-200 border-t-blue-500 rounded-full animate-spin"></div>
                    </div>
                    <div class="space-y-2">
                        <p class="heading-3 text-[#2E5C8A]">質問を生成しています...</p>
                        <p class="body-text text-[#1E3A5F]/70">あなたの診断結果を分析して、最適な質問を作成中です</p>
                    </div>
                </div>
            </div>

            @if(empty($questions))
                {{-- 質問がまだ生成されていない場合 --}}
                <div class="card-refined bg-white p-12 soft-shadow-refined text-center">
                    <div class="flex flex-col items-center justify-center space-y-6">
                        {{-- 回転する円のアニメーション --}}
                        <div class="flex items-center justify-center">
                            <div class="w-16 h-16 border-4 border-blue-200 border-t-blue-500 rounded-full animate-spin"></div>
                        </div>
                        <div class="space-y-2">
                            <p class="heading-3 text-[#2E5C8A]">質問を生成しています...</p>
                            <p class="body-text text-[#1E3A5F]/70">あなたの診断結果を分析して、最適な質問を作成中です</p>
                        </div>
                    </div>
                </div>
            @else
                {{-- 質問表示（1つずつ） --}}
                <div class="card-refined bg-white p-8 soft-shadow-refined space-y-6">
                    <div class="flex items-center justify-between">
                        <h2 class="heading-3 text-xl">質問に答える</h2>
                        <p class="body-small text-[#1E3A5F]/60">
                            {{ $currentQuestionIndex + 1 }}/{{ count($questions) }}
                        </p>
                    </div>

                    {{-- 進捗バー --}}
                    <div class="w-full bg-[#E8F4FF] rounded-full h-2 overflow-hidden">
                        <div 
                            class="h-2 bg-[#6BB6FF] transition-all duration-500"
                            style="width: {{ count($questions) > 0 ? (($currentQuestionIndex + 1) / count($questions)) * 100 : 0 }}%"
                        ></div>
                    </div>

                    @if(isset($questions[$currentQuestionIndex]))
                        @php
                            $question = $questions[$currentQuestionIndex];
                        @endphp
                        <div class="space-y-4" wire:key="question-{{ $currentQuestionIndex }}">
                            <div class="space-y-2">
                                <p class="body-text font-semibold text-[#2E5C8A]">
                                    Q{{ $currentQuestionIndex + 1 }}. {{ $question['question'] ?? '' }}
                                </p>
                                @if(!empty($question['example']))
                                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
                                        <p class="body-small text-blue-700 font-medium mb-1">💡 回答例</p>
                                        <p class="body-small text-[#1E3A5F]/80">{{ $question['example'] }}</p>
                                    </div>
                                @endif
                            </div>
                            <textarea
                                wire:model.blur="answers.{{ $currentQuestionIndex }}"
                                wire:key="textarea-{{ $currentQuestionIndex }}"
                                rows="5"
                                class="w-full rounded-xl border-2 border-[#2E5C8A]/20 bg-white text-[#2E5C8A] px-4 py-3 body-text leading-relaxed focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"
                                placeholder="ここに回答を入力してください">{{ $answers[$currentQuestionIndex] ?? '' }}</textarea>
                        </div>

                        <div class="flex items-center justify-between pt-4">
                            <button
                                wire:click="prevQuestion"
                                @if($currentQuestionIndex === 0) disabled @endif
                                class="btn-secondary {{ $currentQuestionIndex === 0 ? 'opacity-50 cursor-not-allowed' : '' }}">
                                前へ
                            </button>
                            
                            @if($currentQuestionIndex < count($questions) - 1)
                                <button
                                    wire:click="nextQuestion"
                                    class="btn-primary">
                                    次へ
                                </button>
                            @else
                                <button
                                    wire:click="saveAnswers"
                                    class="btn-primary">
                                    すべて回答完了 → 候補を生成する
                                </button>
                            @endif
                        </div>
                    @endif

                    @error('answers')
                        <p class="body-small text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endif
        @endif

        {{-- 候補ステップ --}}
        @if($step === 'candidates')
            <div class="card-refined bg-white p-8 soft-shadow-refined space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="heading-3 text-xl">ゴール候補を選ぶ</h2>
                    <div class="flex gap-3">
                        <button wire:click="backToQuestions" class="btn-secondary text-sm">質問に戻る</button>
                    </div>
                </div>

                @forelse($candidates as $index => $candidate)
                    <div class="bg-[#F6FBFF] border border-blue-100 rounded-xl p-5 space-y-3">
                        <p class="body-text text-[#1E3A5F] whitespace-pre-line leading-relaxed">{{ $candidate }}</p>
                        <div class="space-y-2">
                            <label class="body-small text-[#1E3A5F]/70">編集して選ぶ（任意）</label>
                            <textarea
                                wire:model.defer="candidates.{{ $index }}"
                                rows="3"
                                class="w-full rounded-xl border-2 border-[#2E5C8A]/20 bg-white text-[#2E5C8A] px-4 py-3 body-text leading-relaxed focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"></textarea>
                        </div>
                        <div class="flex gap-3">
                            <button
                                wire:click="selectCandidate({{ $index }})"
                                class="btn-primary">
                                この候補を選ぶ
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="body-text text-[#1E3A5F]/70">候補を生成できませんでした。質問に戻って入力を見直してください。</p>
                @endforelse
            </div>
        @endif

        {{-- 完了ステップ --}}
        @if($step === 'completed')
            <div class="card-refined bg-white p-8 soft-shadow-refined space-y-4">
                <h2 class="heading-3 text-xl">ゴールを保存しました</h2>
                @if($selectedGoal)
                    <div class="bg-[#F6FBFF] border border-blue-100 rounded-xl p-6">
                        <p class="body-text text-[#1E3A5F] whitespace-pre-line leading-relaxed">{{ $selectedGoal }}</p>
                    </div>
                @endif
                <div class="flex gap-3">
                    <button wire:click="backToQuestions" class="btn-secondary">再度編集する</button>
                    <a href="{{ route('dashboard') }}" class="btn-primary">ダッシュボードへ</a>
                </div>
            </div>
        @endif
    </div>
</div>

