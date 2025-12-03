<div class="content-padding section-spacing-sm">
    <div class="max-w-4xl mx-auto space-y-8">
        <header class="space-y-3">
            <h1 class="heading-2">未来メモ：直感で埋めるマイルストーン</h1>
            <p class="body-large text-slate-600">
                「タイトル → いつまでに → ラフなメモ → 行動のメモ」の4ステップだけ。悩んだら下のガイドを参考に、思いついた言葉をそのまま書き残してください。
            </p>
            <ul class="list-disc pl-6 body-small text-slate-500 space-y-1">
                <li>途中保存OK。まずは雑に書き出してから整えましょう。</li>
                <li>ミニマンダラは4マスだけ。空欄があっても大丈夫です。</li>
            </ul>
        </header>

        @if (session()->has('message'))
            <div class="bg-green-50 border border-green-200 text-green-800 body-small p-4 rounded-xl">
                {{ session('message') }}
            </div>
        @endif

        <section class="card-refined soft-shadow-refined p-8 space-y-5">
            <div>
                <label class="body-small font-semibold text-[#2E5C8A]">タイトル</label>
                <input type="text" wire:model.defer="title" class="w-full rounded-2xl border border-slate-200 px-4 py-3 body-text focus:ring-2 focus:ring-[#6BB6FF]" placeholder="例：2026年春までにキャリア伴走で独立する">
                <p class="body-small text-slate-500 mt-1">「誰のために / いつまでに / どうなりたいか」をひとことで。</p>
                @error('title') <p class="body-small text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="body-small font-semibold text-[#2E5C8A]">目標日（なくてもOK）</label>
                    <input type="date" wire:model.defer="target_date" class="w-full rounded-2xl border border-slate-200 px-4 py-3 body-text focus:ring-2 focus:ring-[#6BB6FF]">
                    <p class="body-small text-slate-500 mt-1">迷ったら目安の日付を入れておくだけでも◎</p>
                    @error('target_date') <p class="body-small text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="body-small font-semibold text-[#2E5C8A]">自由メモ</label>
                <textarea wire:model.defer="summary" rows="4" class="w-full rounded-2xl border border-slate-200 px-4 py-3 body-text focus:ring-2 focus:ring-[#6BB6FF]" placeholder="例：●●な人を支える存在になりたい。まずは月●件の相談実績を作る。"></textarea>
                <p class="body-small text-slate-500 mt-1">思い・背景・成功イメージなどをラフに。</p>
                @error('summary') <p class="body-small text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </section>

        <section class="card-refined soft-shadow-refined p-8 space-y-6">
            <div>
                <h2 class="heading-3 text-xl mb-1">ミニマンダラ（発散ガイド）</h2>
                <p class="body-small text-slate-500">中心に「ありたい状態」、四方向にキーワードと小さな一歩を書くだけの軽量版です。</p>
            </div>

            <div class="grid lg:grid-cols-3 gap-6">
                <div class="lg:col-span-1">
                    <label class="body-small font-semibold text-[#2E5C8A]">中心（ありたい姿）</label>
                    <textarea wire:model.defer="mandalaCenter" rows="4" class="w-full rounded-2xl border border-slate-200 px-4 py-3 body-text focus:ring-2 focus:ring-[#6BB6FF]" placeholder="例：信頼して相談される伴走者"></textarea>
                </div>
                <div class="lg:col-span-2 grid sm:grid-cols-2 gap-4">
                    @foreach($mandalaIdeas as $index => $value)
                        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-3">
                            <div>
                                <label class="body-small font-semibold text-[#2E5C8A]">キーワード {{ $index + 1 }}</label>
                                <input type="text" wire:model.defer="mandalaIdeas.{{ $index }}" class="w-full rounded-2xl border border-slate-200 px-3 py-2 body-small focus:ring-2 focus:ring-[#6BB6FF]" placeholder="例：実績づくり">
                            </div>
                            <div>
                                <label class="body-small font-semibold text-[#2E5C8A]">ひとまずやること</label>
                                <textarea wire:model.defer="mandalaActions.{{ $index }}" rows="2" class="w-full rounded-2xl border border-slate-200 px-3 py-2 body-small focus:ring-2 focus:ring-[#6BB6FF]" placeholder="例：週1で無料相談Dayを開く"></textarea>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="card-refined soft-shadow-refined p-8 space-y-5">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <h2 class="heading-3 text-xl">行動メモ</h2>
                    <p class="body-small text-slate-500">「いつ・何をするか」をラフに書き留めます。未入力の項目があっても構いません。</p>
                </div>
                <button type="button" wire:click="addActionItem" class="btn-secondary text-sm">＋ 行動を追加</button>
            </div>

            <div class="space-y-4">
                @foreach($actionItems as $index => $item)
                    <div class="border border-slate-200 rounded-3xl p-5 space-y-4 relative">
                        @if(count($actionItems) > 1)
                            <button type="button" wire:click="removeActionItem({{ $index }})" class="absolute top-4 right-4 text-slate-400 hover:text-red-500 body-small">削除</button>
                        @endif
                        <div>
                            <label class="body-small font-semibold text-[#2E5C8A]">アクション {{ $index + 1 }}</label>
                            <input type="text" wire:model.defer="actionItems.{{ $index }}.title" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 body-text focus:ring-2 focus:ring-[#6BB6FF]" placeholder="例：週1で練習セッションを実施">
                        </div>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="body-small font-semibold text-[#2E5C8A]">日付メモ</label>
                                <input type="date" wire:model.defer="actionItems.{{ $index }}.due_date" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 body-text focus:ring-2 focus:ring-[#6BB6FF]">
                            </div>
                            <div>
                                <label class="body-small font-semibold text-[#2E5C8A]">補足メモ</label>
                                <textarea wire:model.defer="actionItems.{{ $index }}.notes" rows="2" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 body-text focus:ring-2 focus:ring-[#6BB6FF]" placeholder="例：●●さんに声をかける／準備すること"></textarea>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="card-refined soft-shadow-refined p-8 space-y-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <h2 class="heading-3 text-xl">最近のマイルストーンを再利用</h2>
                    <p class="body-small text-slate-500">気になるエントリーを読み込んで、今回の内容に上書きできます。</p>
                </div>
                <button type="button" wire:click="save" class="btn-primary text-sm px-8 py-3 self-start md:self-auto">
                    この内容で保存
                </button>
            </div>
            <div class="divide-y divide-slate-200">
                @forelse($recentMilestones as $item)
                    <div class="py-3 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                        <div>
                            <p class="body-text font-semibold text-[#2E5C8A]">{{ $item->title }}</p>
                            <p class="body-small text-slate-500">
                                {{ $item->target_date?->format('Y/m/d') ?? '日付未設定' }}｜
                                {{ \Illuminate\Support\Str::limit($item->description, 50) }}
                            </p>
                        </div>
                        <button type="button" wire:click="loadMilestoneForEdit({{ $item->id }})" class="btn-secondary text-xs px-4 py-2">
                            これを編集
                        </button>
                    </div>
                @empty
                    <p class="body-small text-slate-500 py-4 text-center">まだ過去のマイルストーンはありません。</p>
                @endforelse
            </div>
        </section>
    </div>
</div>



