<div class="w-full max-w-6xl mx-auto px-6 md:px-8 pb-6 md:pb-8">
        <!-- ヘッダー -->
        <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="heading-2 mb-2">自己診断結果</h1>
                <p class="body-large">
                    MBTI、ストレングスファインダー、エニアグラム、ビッグファイブ、FFSなどの診断結果を登録できます。
                </p>
            </div>
            <a href="{{ route('assessments.visualization') }}" class="btn-secondary text-sm text-center w-full md:w-auto">
                可視化ページを見る
            </a>
        </div>

        @if(session('message'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 body-small p-4 rounded-xl">
                {{ session('message') }}
            </div>
        @endif

        <!-- 診断タイプ選択タブ -->
        <div class="card-refined p-8 mb-8">
            <div class="flex flex-wrap gap-2 mb-6">
                <button
                    wire:click="$set('assessmentType', 'mbti')"
                    class="px-4 py-2 rounded-lg transition-colors {{ $assessmentType === 'mbti' ? 'bg-[#6BB6FF] text-white' : 'bg-white border-2 border-[#2E5C8A]/20 text-[#2E5C8A] hover:bg-[#E8F4FF]' }}"
                >
                    MBTI
                </button>
                <button
                    wire:click="$set('assessmentType', 'strengthsfinder')"
                    class="px-4 py-2 rounded-lg transition-colors {{ $assessmentType === 'strengthsfinder' ? 'bg-[#6BB6FF] text-white' : 'bg-white border-2 border-[#2E5C8A]/20 text-[#2E5C8A] hover:bg-[#E8F4FF]' }}"
                >
                    ストレングスファインダー
                </button>
                <button
                    wire:click="$set('assessmentType', 'enneagram')"
                    class="px-4 py-2 rounded-lg transition-colors {{ $assessmentType === 'enneagram' ? 'bg-[#6BB6FF] text-white' : 'bg-white border-2 border-[#2E5C8A]/20 text-[#2E5C8A] hover:bg-[#E8F4FF]' }}"
                >
                    エニアグラム
                </button>
                <button
                    wire:click="$set('assessmentType', 'big5')"
                    class="px-4 py-2 rounded-lg transition-colors {{ $assessmentType === 'big5' ? 'bg-[#6BB6FF] text-white' : 'bg-white border-2 border-[#2E5C8A]/20 text-[#2E5C8A] hover:bg-[#E8F4FF]' }}"
                >
                    ビッグファイブ
                </button>
                <button
                    wire:click="$set('assessmentType', 'ffs')"
                    class="px-4 py-2 rounded-lg transition-colors {{ $assessmentType === 'ffs' ? 'bg-[#6BB6FF] text-white' : 'bg-white border-2 border-[#2E5C8A]/20 text-[#2E5C8A] hover:bg-[#E8F4FF]' }}"
                >
                    FFS
                </button>
            </div>

            <!-- 共通フィールド -->
            <div class="mb-6">
                <label class="body-small font-medium text-[#2E5C8A] mb-2 block">診断名</label>
                <input
                    type="text"
                    wire:model.defer="assessmentName"
                    class="w-full rounded-xl border-2 border-[#2E5C8A]/20 bg-white text-[#2E5C8A] px-4 py-3 body-text focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"
                    placeholder="例: MBTI、ストレングスファインダー"
                />
            </div>

            <div class="mb-6">
                <label class="body-small font-medium text-[#2E5C8A] mb-2 block">診断日</label>
                <input
                    type="date"
                    wire:model.defer="completedAt"
                    class="w-full rounded-xl border-2 border-[#2E5C8A]/20 bg-white text-[#2E5C8A] px-4 py-3 body-text focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"
                />
            </div>

            <!-- MBTI フォーム -->
            @if($assessmentType === 'mbti')
                <div class="mb-6">
                    <label class="body-small font-medium text-[#2E5C8A] mb-2 block">MBTIタイプ <span class="text-red-500">*</span></label>
                    <select
                        wire:model.defer="mbtiType"
                        class="w-full rounded-xl border-2 border-[#2E5C8A]/20 bg-white text-[#2E5C8A] px-4 py-3 body-text focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"
                    >
                        <option value="">選択してください</option>
                        <option value="INTJ">INTJ</option>
                        <option value="INTP">INTP</option>
                        <option value="ENTJ">ENTJ</option>
                        <option value="ENTP">ENTP</option>
                        <option value="INFJ">INFJ</option>
                        <option value="INFP">INFP</option>
                        <option value="ENFJ">ENFJ</option>
                        <option value="ENFP">ENFP</option>
                        <option value="ISTJ">ISTJ</option>
                        <option value="ISFJ">ISFJ</option>
                        <option value="ESTJ">ESTJ</option>
                        <option value="ESFJ">ESFJ</option>
                        <option value="ISTP">ISTP</option>
                        <option value="ISFP">ISFP</option>
                        <option value="ESTP">ESTP</option>
                        <option value="ESFP">ESFP</option>
                    </select>
                    @error('mbtiType')
                        <span class="body-small text-red-600 mt-2 block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-6">
                    <label class="body-small font-medium text-[#2E5C8A] mb-3 block">各次元のパーセンテージ（任意）</label>
                    <div class="space-y-4">
                        <div>
                            <label class="body-small text-[#1E3A5F] mb-2 block">E/I: {{ $mbtiEI }}%</label>
                            <input type="range" min="0" max="100" wire:model.defer="mbtiEI" class="w-full accent-[#6BB6FF]">
                        </div>
                        <div>
                            <label class="body-small text-[#1E3A5F] mb-2 block">S/N: {{ $mbtiSN }}%</label>
                            <input type="range" min="0" max="100" wire:model.defer="mbtiSN" class="w-full accent-[#6BB6FF]">
                        </div>
                        <div>
                            <label class="body-small text-[#1E3A5F] mb-2 block">T/F: {{ $mbtiTF }}%</label>
                            <input type="range" min="0" max="100" wire:model.defer="mbtiTF" class="w-full accent-[#6BB6FF]">
                        </div>
                        <div>
                            <label class="body-small text-[#1E3A5F] mb-2 block">J/P: {{ $mbtiJP }}%</label>
                            <input type="range" min="0" max="100" wire:model.defer="mbtiJP" class="w-full accent-[#6BB6FF]">
                        </div>
                    </div>
                </div>
            @endif

            <!-- FFS フォーム -->
            @if($assessmentType === 'ffs')
                <div class="mb-6">
                    <label class="body-small font-medium text-[#2E5C8A] mb-3 block">5つの特性（0-20）</label>
                    <p class="body-small text-[#1E3A5F] mb-4">凝縮性・受容性・弁別性・拡散性・保全性のバランスをスライダーで記録します</p>
                    <div class="space-y-4">
                        <div>
                            <label class="body-small text-[#1E3A5F] mb-2 block">凝縮性: {{ $ffsCondensing }}</label>
                            <input type="range" min="0" max="20" wire:model.live="ffsCondensing" class="w-full accent-[#6BB6FF]">
                        </div>
                        <div>
                            <label class="body-small text-[#1E3A5F] mb-2 block">受容性: {{ $ffsAcceptance }}</label>
                            <input type="range" min="0" max="20" wire:model.live="ffsAcceptance" class="w-full accent-[#6BB6FF]">
                        </div>
                        <div>
                            <label class="body-small text-[#1E3A5F] mb-2 block">弁別性: {{ $ffsDiscrimination }}</label>
                            <input type="range" min="0" max="20" wire:model.live="ffsDiscrimination" class="w-full accent-[#6BB6FF]">
                        </div>
                        <div>
                            <label class="body-small text-[#1E3A5F] mb-2 block">拡散性: {{ $ffsDiffusion }}</label>
                            <input type="range" min="0" max="20" wire:model.live="ffsDiffusion" class="w-full accent-[#6BB6FF]">
                        </div>
                        <div>
                            <label class="body-small text-[#1E3A5F] mb-2 block">保全性: {{ $ffsConservation }}</label>
                            <input type="range" min="0" max="20" wire:model.live="ffsConservation" class="w-full accent-[#6BB6FF]">
                        </div>
                    </div>
                </div>
            @endif

            <!-- ストレングスファインダー フォーム -->
            @if($assessmentType === 'strengthsfinder')
                <div class="mb-6">
                    <label class="body-small font-medium text-[#2E5C8A] mb-2 block">上位5つの強み <span class="text-red-500">*</span></label>
                    <p class="body-small text-[#1E3A5F] mb-4">34の強みから上位5つを選択してください</p>
                    <div class="space-y-3">
                        @foreach([0, 1, 2, 3, 4] as $index)
                            <div class="flex items-center gap-2">
                                <span class="body-small text-[#1E3A5F] w-8 font-semibold">{{ $index + 1 }}位</span>
                                <select
                                    wire:model.defer="strengthsTop5.{{ $index }}"
                                    class="flex-1 rounded-xl border-2 border-[#2E5C8A]/20 bg-white text-[#2E5C8A] px-4 py-2 body-text focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"
                                >
                                    <option value="">選択してください</option>
                                    @php
                                        $selectedStrengths = array_filter($strengthsTop5, fn($s) => $s !== null && $s !== $strengthsTop5[$index]);
                                    @endphp
                                    @foreach($this->strengthsList as $strength)
                                        <option value="{{ $strength }}" {{ in_array($strength, $selectedStrengths) ? 'disabled' : '' }}>
                                            {{ $strength }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    </div>
                    @error('strengthsTop5')
                        <span class="body-small text-red-600 mt-2 block">{{ $message }}</span>
                    @enderror
                    @error('strengthsTop5.*')
                        <span class="body-small text-red-600 mt-2 block">{{ $message }}</span>
                    @enderror
                </div>
            @endif

            <!-- エニアグラム フォーム -->
            @if($assessmentType === 'enneagram')
                <div class="mb-6">
                    <label class="body-small font-medium text-[#2E5C8A] mb-2 block">タイプ <span class="text-red-500">*</span></label>
                    <select
                        wire:model.defer="enneagramType"
                        class="w-full rounded-xl border-2 border-[#2E5C8A]/20 bg-white text-[#2E5C8A] px-4 py-3 body-text focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"
                    >
                        <option value="">選択してください</option>
                        <option value="1">タイプ1 - 完全主義者</option>
                        <option value="2">タイプ2 - 援助者</option>
                        <option value="3">タイプ3 - 達成者</option>
                        <option value="4">タイプ4 - 個性主義者</option>
                        <option value="5">タイプ5 - 探求者</option>
                        <option value="6">タイプ6 - 忠実な懐疑主義者</option>
                        <option value="7">タイプ7 - 熱中する人</option>
                        <option value="8">タイプ8 - 挑戦者</option>
                        <option value="9">タイプ9 - 平和主義者</option>
                    </select>
                    @error('enneagramType')
                        <span class="body-small text-red-600 mt-2 block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-6">
                    <label class="body-small font-medium text-[#2E5C8A] mb-2 block">ウィング（任意）</label>
                    <input
                        type="text"
                        wire:model.defer="enneagramWing"
                        class="w-full rounded-xl border-2 border-[#2E5C8A]/20 bg-white text-[#2E5C8A] px-4 py-3 body-text focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"
                        placeholder="例: 5w4"
                    />
                </div>

                <div class="mb-6">
                    <label class="body-small font-medium text-[#2E5C8A] mb-2 block">トリタイプ（任意）</label>
                    <input
                        type="text"
                        wire:model.defer="enneagramTritype"
                        class="w-full rounded-xl border-2 border-[#2E5C8A]/20 bg-white text-[#2E5C8A] px-4 py-3 body-text focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"
                        placeholder="例: 5-4-9"
                    />
                </div>

                <div class="mb-6">
                    <label class="body-small font-medium text-[#2E5C8A] mb-2 block">本能型（任意）</label>
                    <input
                        type="text"
                        wire:model.defer="enneagramInstinctualVariant"
                        class="w-full rounded-xl border-2 border-[#2E5C8A]/20 bg-white text-[#2E5C8A] px-4 py-3 body-text focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"
                        placeholder="例: sp/sx"
                    />
                </div>
            @endif

            <!-- ビッグファイブ フォーム -->
            @if($assessmentType === 'big5')
                <div class="mb-6">
                    <label class="body-small font-medium text-[#2E5C8A] mb-3 block">5つの因子（0-100）</label>
                    <div class="space-y-4">
                        <div>
                            <label class="body-small text-[#1E3A5F] mb-2 block">開放性（Openness）: {{ $big5Openness }}%</label>
                            <input type="range" min="0" max="100" wire:model.defer="big5Openness" class="w-full accent-[#6BB6FF]">
                        </div>
                        <div>
                            <label class="body-small text-[#1E3A5F] mb-2 block">誠実性（Conscientiousness）: {{ $big5Conscientiousness }}%</label>
                            <input type="range" min="0" max="100" wire:model.defer="big5Conscientiousness" class="w-full accent-[#6BB6FF]">
                        </div>
                        <div>
                            <label class="body-small text-[#1E3A5F] mb-2 block">外向性（Extraversion）: {{ $big5Extraversion }}%</label>
                            <input type="range" min="0" max="100" wire:model.defer="big5Extraversion" class="w-full accent-[#6BB6FF]">
                        </div>
                        <div>
                            <label class="body-small text-[#1E3A5F] mb-2 block">協調性（Agreeableness）: {{ $big5Agreeableness }}%</label>
                            <input type="range" min="0" max="100" wire:model.defer="big5Agreeableness" class="w-full accent-[#6BB6FF]">
                        </div>
                        <div>
                            <label class="body-small text-[#1E3A5F] mb-2 block">神経症傾向（Neuroticism）: {{ $big5Neuroticism }}%</label>
                            <input type="range" min="0" max="100" wire:model.defer="big5Neuroticism" class="w-full accent-[#6BB6FF]">
                        </div>
                    </div>
                </div>
            @endif

            <!-- メモ -->
            <div class="mb-6">
                <label class="body-small font-medium text-[#2E5C8A] mb-2 block">メモ（任意）</label>
                <textarea
                    wire:model.defer="notes"
                    rows="4"
                    class="w-full rounded-xl border-2 border-[#2E5C8A]/20 bg-white text-[#2E5C8A] px-4 py-3 body-text focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"
                    placeholder="診断結果についてのメモや気づきを記録できます"
                ></textarea>
            </div>

            <!-- 保存ボタン -->
            <div class="flex justify-end">
                <button wire:click="save" class="btn-primary">
                    {{ $assessmentId ? '更新する' : '保存する' }}
                </button>
            </div>
        </div>

        <!-- 登録済みの診断結果一覧 -->
        @if($assessments->count() > 0)
            <div class="card-refined p-8">
                <h2 class="heading-3 text-xl mb-6">登録済みの診断結果</h2>
                <div class="space-y-4">
                    @foreach($assessments as $assessment)
                        <div class="border-2 border-[#2E5C8A]/20 rounded-xl p-4">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <h3 class="body-text font-semibold text-[#2E5C8A]">
                                        {{ $assessment->assessment_name ?: ucfirst($assessment->assessment_type) }}
                                    </h3>
                                    @if($assessment->completed_at)
                                        <p class="body-small text-[#1E3A5F] mt-1">
                                            {{ $assessment->completed_at->format('Y年n月j日') }}
                                        </p>
                                    @endif
                                </div>
                                <div class="flex gap-2">
                                    <button
                                        wire:click="loadAssessment({{ $assessment->id }})"
                                        class="btn-secondary text-sm"
                                    >
                                        編集
                                    </button>
                                    <button
                                        wire:click="delete({{ $assessment->id }})"
                                        onclick="return confirm('この診断結果を削除しますか？')"
                                        class="btn-secondary text-sm border-red-400 text-red-600 hover:bg-red-50"
                                    >
                                        削除
                                    </button>
                                </div>
                            </div>
                            @if($assessment->notes)
                                <p class="body-small text-[#1E3A5F] mt-2">{{ $assessment->notes }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
</div>
