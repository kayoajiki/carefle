<div
    class="card-refined p-8 flex flex-col gap-8"
    x-data="{
        currentIndex: @entangle('currentIndex'),
        answers: @entangle('answers'),
        questions: @js($questions),
        get currentQuestion() {
            return this.questions[this.currentIndex] || null;
        },
        get currentAnswer() {
            if (!this.currentQuestion) return { answer_value: null, comment: '' };
            return this.answers[this.currentQuestion.id] || { answer_value: null, comment: '' };
        },
        selectOption(value) {
            @this.selectOption(this.currentQuestion.id, value);
        },
        updateComment(value) {
            @this.updateComment(this.currentQuestion.id, value);
        },
        progressPercent() {
            const answeredCount = Object.values(this.answers).filter(a => a && a.answer_value !== null).length;
            return Math.round((answeredCount / this.questions.length) * 100);
        },
        isLast() {
            return this.currentIndex === this.questions.length - 1;
        }
    }"
>
    @if(session('message'))
        <div class="bg-green-50 border border-green-200 text-green-800 text-xs p-3 rounded-md">
            {{ session('message') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 text-xs p-3 rounded-md">
            {{ session('error') }}
        </div>
    @endif

    <!-- progress bar + step info -->
    <div>
        <div class="flex justify-between items-baseline mb-3">
            <div class="body-small font-medium text-[#2E5C8A]">
                Q<span x-text="currentIndex + 1"></span>/<span x-text="questions.length"></span>
            </div>
            <div class="body-small">約3分で完了します</div>
        </div>
        <div class="w-full bg-[#F0F7FF] rounded-full h-3 overflow-hidden">
            <div class="h-3 bg-[#6BB6FF] transition-all duration-300 rounded-full" :style="`width: ${progressPercent()}%`"></div>
        </div>
    </div>

    <!-- question text -->
    <div x-show="currentQuestion" class="mb-6">
        <h2 class="heading-3 text-xl mb-3" x-text="currentQuestion.text"></h2>
        <p class="body-text" x-show="currentQuestion.helper" x-text="currentQuestion.helper"></p>
    </div>

    <!-- answer scale buttons -->
    <div class="flex flex-col gap-4" x-show="currentQuestion">
        <template x-for="opt in currentQuestion.options" :key="opt.value">
            <button
                type="button"
                class="w-full border-2 rounded-xl px-6 py-4 text-left transition-all duration-200"
                :class="currentAnswer.answer_value === opt.value ? 'bg-[#6BB6FF] text-[#2E5C8A] border-[#6BB6FF] shadow-sm' : 'border-[#2E5C8A]/20 bg-white hover:border-[#6BB6FF]/50 hover:bg-[#F0F7FF]'"
                @click="selectOption(opt.value)"
            >
                <div class="body-text font-semibold mb-1" x-text="opt.label"></div>
                <div class="body-small" x-text="opt.desc"></div>
            </button>
        </template>
    </div>

    <!-- optional comment -->
    <div class="flex flex-col gap-3" x-show="currentQuestion">
        <label class="body-small font-medium text-[#2E5C8A]">
            よければ一言メモ（任意）
        </label>
        <textarea
            class="w-full body-text rounded-xl border-2 border-[#2E5C8A]/20 bg-[#F0F7FF] text-[#2E5C8A] p-4 focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-[#6BB6FF] transition-all"
            rows="4"
            placeholder="今いちばん引っかかっていること、嬉しいこと、しんどいことなど自由に。次回の面談で深く扱いやすくなります。"
            x-model="currentAnswer.comment"
            @input="updateComment($event.target.value)"
        ></textarea>
    </div>

    <!-- nav buttons -->
    <div class="flex items-center justify-between pt-6 border-t border-[#2E5C8A]/10">
        <button
            class="body-small text-[#1E3A5F] underline underline-offset-2 hover:text-[#2E5C8A] transition-colors"
            @click="$wire.saveDraft()"
        >
            いったん保存してあとで続ける
        </button>

        <div class="flex gap-4">
            <button
                class="btn-secondary text-sm"
                x-show="currentIndex > 0"
                @click="$wire.prevQuestion()"
            >
                戻る
            </button>

            <button
                class="btn-primary text-sm"
                @click="isLast() ? $wire.finish() : $wire.nextQuestion()"
            >
                <span x-show="!isLast()">次へ</span>
                <span x-show="isLast()">結果を見る</span>
            </button>
        </div>
    </div>
</div>
