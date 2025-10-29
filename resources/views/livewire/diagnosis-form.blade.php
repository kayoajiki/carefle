<div
    class="bg-white rounded-xl shadow-md p-6 flex flex-col gap-6"
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
        <div class="flex justify-between items-baseline mb-2">
            <div class="text-xs font-medium text-[#00473e]">
                Q<span x-text="currentIndex + 1"></span>/<span x-text="questions.length"></span>
            </div>
            <div class="text-[10px] text-[#475d5b]">約3分で完了します</div>
        </div>
        <div class="w-full bg-[#f2f7f5] rounded-full h-2 overflow-hidden">
            <div class="h-2 bg-[#faae2b] transition-all duration-300" :style="`width: ${progressPercent()}%`"></div>
        </div>
    </div>

    <!-- question text -->
    <div x-show="currentQuestion">
        <h2 class="text-base font-semibold text-[#00473e] leading-relaxed" x-text="currentQuestion.text"></h2>
        <p class="text-xs text-[#475d5b] mt-2 leading-relaxed" x-show="currentQuestion.helper" x-text="currentQuestion.helper"></p>
    </div>

    <!-- answer scale buttons -->
    <div class="flex flex-col gap-3" x-show="currentQuestion">
        <template x-for="opt in currentQuestion.options" :key="opt.value">
            <button
                type="button"
                class="w-full border rounded-lg px-4 py-3 text-left text-sm font-medium transition-colors"
                :class="currentAnswer.answer_value === opt.value ? 'bg-[#faae2b] text-[#00473e] border-[#faae2b]' : 'border-[#00473e]/20 hover:border-[#faae2b]/50'"
                @click="selectOption(opt.value)"
            >
                <div class="text-[#00473e] text-sm font-semibold" x-text="opt.label"></div>
                <div class="text-[11px] text-[#475d5b] leading-snug" x-text="opt.desc"></div>
            </button>
        </template>
    </div>

    <!-- optional comment -->
    <div class="flex flex-col gap-2" x-show="currentQuestion">
        <label class="text-xs font-medium text-[#00473e]">
            よければ一言メモ（任意）
        </label>
        <textarea
            class="w-full text-sm rounded-md border border-[#00473e]/20 bg-[#f2f7f5] text-[#00473e] p-3 focus:outline-none focus:ring-2 focus:ring-[#faae2b]"
            rows="3"
            placeholder="今いちばん引っかかっていること、嬉しいこと、しんどいことなど自由に。次回の面談で深く扱いやすくなります。"
            x-model="currentAnswer.comment"
            @input="updateComment($event.target.value)"
        ></textarea>
    </div>

    <!-- nav buttons -->
    <div class="flex items-center justify-between pt-4 border-t border-[#00473e]/10">
        <button
            class="text-xs text-[#475d5b] underline underline-offset-2"
            @click="$wire.saveDraft()"
        >
            いったん保存してあとで続ける
        </button>

        <div class="flex gap-3">
            <button
                class="text-xs px-4 py-2 rounded-md border border-[#00473e]/30 text-[#00473e] bg-white font-medium"
                x-show="currentIndex > 0"
                @click="$wire.prevQuestion()"
            >
                戻る
            </button>

            <button
                class="text-xs px-4 py-2 rounded-md font-semibold bg-[#faae2b] text-[#00473e] shadow-sm"
                @click="isLast() ? $wire.finish() : $wire.nextQuestion()"
            >
                <span x-show="!isLast()">次へ</span>
                <span x-show="isLast()">結果を見る</span>
            </button>
        </div>
    </div>
</div>
