<x-layouts.app.sidebar :title="'診断結果'">
    <flux:main>
<div class="min-h-screen bg-[#EAF3FF] content-padding section-spacing-sm">
    @php
        // 満足度と重要度の差分を計算して強みと伸ばしどころを決定
        $workPillarScores = $workPillarScores ?? [];
        $importanceWork = $importanceWork ?? [];
        $pillarLabels = $pillarLabels ?? [];
        
        $diffScores = []; // 満足度 - 重要度の差分
        $strongestDiff = null;
        $strongestKey = null;
        $weakestDiff = null;
        $weakestKey = null;
        
        $allPillarsEqual = true; // すべてのpillarで満足度 = 重要度かどうか
        $allPillarsPositive = true; // すべてのpillarで満足度 > 重要度かどうか
        $hasValidDiff = false; // 有効な差分データがあるかどうか
        
        foreach ($pillarLabels as $key => $label) {
            $pillarWorkScore = $workPillarScores[$key] ?? null;
            $importanceScore = $importanceWork[$key] ?? null;
            
            if ($pillarWorkScore !== null && $importanceScore !== null) {
                $diff = $pillarWorkScore - $importanceScore;
                $diffScores[$key] = $diff;
                $hasValidDiff = true;
                
                // すべてのpillarで満足度 = 重要度かチェック
                if ($diff != 0) {
                    $allPillarsEqual = false;
                }
                
                // すべてのpillarで満足度 > 重要度かチェック
                if ($diff <= 0) {
                    $allPillarsPositive = false;
                }
                
                // 強み: 満足度が重要度より高い（差分が最大）
                if ($diff > 0 && ($strongestDiff === null || $diff > $strongestDiff)) {
                    $strongestDiff = $diff;
                    $strongestKey = $key;
                }
                
                // 伸ばしどころ: 重要度が満足度より高い（差分が最小、つまりマイナスが最大）
                if ($diff < 0 && ($weakestDiff === null || $diff < $weakestDiff)) {
                    $weakestDiff = $diff;
                    $weakestKey = $key;
                }
            }
        }
        
        // すべてのpillarで満足度 = 重要度の場合、満足度の最小値を選ぶ
        if ($hasValidDiff && $allPillarsEqual && $weakestKey === null) {
            $minWorkScore = null;
            $minWorkScoreKey = null;
            
            foreach ($pillarLabels as $key => $label) {
                $pillarWorkScore = $workPillarScores[$key] ?? null;
                
                if ($pillarWorkScore !== null && ($minWorkScore === null || $pillarWorkScore < $minWorkScore)) {
                    $minWorkScore = $pillarWorkScore;
                    $minWorkScoreKey = $key;
                }
            }
            
            if ($minWorkScoreKey !== null) {
                $weakestKey = $minWorkScoreKey;
            }
        }
        
        // 強みが見つからない場合（満足度が重要度より高いpillarが存在しない場合）、
        // 満足度の中で一番高い項目を選ぶ
        if ($strongestKey === null) {
            $maxWorkScore = null;
            $maxWorkScoreKey = null;
            
            foreach ($pillarLabels as $key => $label) {
                $pillarWorkScore = $workPillarScores[$key] ?? null;
                
                if ($pillarWorkScore !== null && ($maxWorkScore === null || $pillarWorkScore > $maxWorkScore)) {
                    $maxWorkScore = $pillarWorkScore;
                    $maxWorkScoreKey = $key;
                }
            }
            
            if ($maxWorkScoreKey !== null) {
                $strongestKey = $maxWorkScoreKey;
            }
        }
        
        // 強みと伸ばしどころのラベルを取得
        $strongLabel = $strongestKey !== null ? ($pillarLabels[$strongestKey] ?? '未計測') : '未計測';
        $focusLabel = $weakestKey !== null ? ($pillarLabels[$weakestKey] ?? '未計測') : '未計測';
        
        // すべてのpillarで満足度 > 重要度の場合、01のラベルを「すべて良好」に変更
        if ($hasValidDiff && $allPillarsPositive && $focusLabel === '未計測') {
            $focusLabel = 'すべて良好';
        }
        
        // どちらも見つからない場合は従来の方法で計算（フォールバック）
        if ($strongLabel === '未計測' && $focusLabel === '未計測') {
            $workDataSet = $radarWorkData ?? [];
            $labels = $radarLabels ?? [];
            $minScore = filled($workDataSet) ? min($workDataSet) : null;
            $maxScore = filled($workDataSet) ? max($workDataSet) : null;
            $focusLabel = $minScore !== null ? ($labels[array_search($minScore, $workDataSet)] ?? '未計測') : '未計測';
            $strongLabel = $maxScore !== null ? ($labels[array_search($maxScore, $workDataSet)] ?? '未計測') : '未計測';
        }
        
        // ラベルからキーを抽出する関数
        $getPillarKey = function($label) {
            if ($label === '未計測') return null;
            // "Purpose（目的）" → "purpose" に変換
            if (preg_match('/^([A-Za-z]+)/', $label, $matches)) {
                return strtolower($matches[1]);
            }
            return null;
        };
        
        // 各項目ごとのフォーカス領域コメント
        $focusComments = [
            'purpose' => '目的意識を明確にすることで、仕事へのモチベーションが向上します。',
            'profession' => '職業スキルや専門性を高めることで、仕事への自信が生まれます。',
            'people' => '人間関係を整えることで、職場の雰囲気が良くなります。',
            'privilege' => '待遇や環境を改善することで、働きやすさが向上します。',
            'progress' => '成長実感を得ることで、仕事へのやりがいが生まれます。',
        ];
        
        // 各項目ごとの強みコメント
        $strengthComments = [
            'purpose' => '目的意識が高い強みを活かして、他の領域の改善にも良い影響を与えます。',
            'profession' => '職業スキルが高い強みを活かして、自信を持って他の領域にも取り組めます。',
            'people' => '人間関係が良好な強みを活かして、チームワークや協力関係を築けます。',
            'privilege' => '待遇や環境が整っている強みを活かして、安心して他の領域に取り組めます。',
            'progress' => '成長実感がある強みを活かして、前向きに他の領域にも挑戦できます。',
        ];
        
        // フォーカス領域と強みのコメントを取得
        $focusKey = $getPillarKey($focusLabel);
        $strongKey = $getPillarKey($strongLabel);
        
        // すべてのpillarで満足度 > 重要度の場合の特別なコメント
        $isAllPositive = $hasValidDiff && $allPillarsPositive;
        
        if ($isAllPositive) {
            $focusComment = 'すべての領域で満足度が重要度を上回っており、全体的に良好な状態です。この状態を維持しながら、さらなる成長を目指していきましょう。';
            $strengthComment = 'すべての領域で満足度が重要度を上回っており、全体的に良好な状態です。この状態を維持しながら、さらなる成長を目指していきましょう。';
        } else {
            $focusComment = $focusKey && isset($focusComments[$focusKey]) ? $focusComments[$focusKey] : 'この領域に取り組むことで、満足度の向上が期待できます。';
            $strengthComment = $strongKey && isset($strengthComments[$strongKey]) ? $strengthComments[$strongKey] : 'この領域の強みを意識して行動することで、他の領域の改善にも良い影響を与えます。';
        }
        
        // 総評コメント（01と02の組み合わせに応じたコメント）
        $summaryComments = [
            // 同じpillarの場合
            'purpose-purpose' => '目的意識を高めることが課題であり、同時に強みでもあるという状況は、実は大きな成長のチャンスです。目的が曖昧な状態から明確になっていく過程で、あなたは「なぜ働くのか」という本質的な問いと向き合うことになります。この組み合わせは、単なる目標設定を超えて、仕事そのものの意味を再定義する機会を示しています。目的が明確になれば、他のすべての領域への取り組みにも自然と方向性が生まれ、一貫性のあるキャリアを築くことができます。',
            'profession-profession' => '職業スキルの向上が課題であり、同時に強みでもあるという状況は、専門家としての成長段階を示しています。スキルが高いからこそ、さらなる高みを目指す意欲が生まれ、同時に現状のスキルレベルに対する課題意識も持てているということです。この組み合わせは、あなたが専門性の深さと広がりの両方を追求できる段階にあることを示唆しています。技術的な成長が「なぜそのスキルが必要なのか」という問いと結びつくとき、単なるスキルアップを超えた、意味のある専門性が生まれます。',
            'people-people' => '人間関係の改善が課題であり、同時に強みでもあるという状況は、関係性の質を高める段階にあることを示しています。既に良好な関係があるからこそ、さらに深い信頼関係や協力関係を築くことができる可能性があります。この組み合わせは、表面的な関係から本質的なつながりへと進化させる機会を示しています。良好な人間関係は、単なる職場の雰囲気を超えて、仕事の質そのものを向上させる力を持っています。チームで互いを支え合う関係性が築ければ、個人の成長も加速します。',
            'privilege-privilege' => '待遇や環境の改善が課題であり、同時に強みでもあるという状況は、働きやすさの本質を考える機会です。現在の環境が整っているからこそ、さらなる改善の余地が見えてくるということは、あなたが環境の価値を理解し、それを最大限に活かしたいと考えている証拠です。この組み合わせは、物質的な環境だけでなく、心理的な働きやすさも含めた、包括的な環境づくりへの意識を示しています。環境が整うことで、他の領域に集中できる余裕が生まれ、より充実した働き方が可能になります。',
            'progress-progress' => '成長実感を得ることが課題であり、同時に強みでもあるという状況は、成長の質を高める段階にあることを示しています。既に成長を実感できているからこそ、さらなる成長への意欲が生まれ、同時に現状の成長スピードに対する課題意識も持てているということです。この組み合わせは、量的な成長から質的な成長へとシフトする機会を示しています。成長を実感しながら、その成長がどのような意味を持つのか、どこに向かっているのかを意識することで、より目的のある成長が可能になります。',
            
            // purpose（伸ばしどころ）の組み合わせ
            'purpose-profession' => '目的意識を明確にすることで、職業スキルを活かす方向性が見えてきます。専門性を目的に結びつけることで、より充実した仕事ができるでしょう。技術的な成長が「なぜ働くのか」という問いと結びつくとき、単なるスキルアップを超えた、意味のある専門性が生まれます。この組み合わせは、あなたが専門家としてのアイデンティティを確立し、同時にその専門性を社会や組織の目的に貢献させる道を示しています。目的が明確になれば、スキル向上のモチベーションも自然と高まり、継続的な学習が可能になります。',
            'purpose-people' => '目的意識を明確にすることで、人間関係の強みを活かした協力関係が築けます。チームで共通の目的に向かうことで、より良い成果が生まれます。目的が共有されると、人間関係は単なる職場の付き合いから、共に価値を創造するパートナーシップへと進化します。この組み合わせは、あなたが個人の目的をチームの目的と結びつけ、協力して大きな成果を生み出す可能性を示しています。良好な人間関係があるからこそ、目的を共有し、一緒に取り組むことができます。目的に向かう過程で、人間関係もさらに深まっていくでしょう。',
            'purpose-privilege' => '目的意識を明確にすることで、現在の環境や待遇の強みを最大限に活かせます。目的に沿った環境づくりが、さらなる満足度向上につながります。環境が整っているからこそ、目的に集中できる余裕が生まれ、目的を実現するための行動を起こしやすくなります。この組み合わせは、物質的な環境だけでなく、目的に沿った働き方ができる環境を意識的に作っていくことの重要性を示しています。目的が明確になれば、環境の価値もより深く実感でき、環境を活かした働き方ができるようになります。',
            'purpose-progress' => '目的意識を明確にすることで、成長実感の強みを活かした前向きな取り組みができます。目的に向かう成長が、より大きなやりがいを生みます。成長を実感できているからこそ、その成長を目的に結びつけることで、単なるスキルアップを超えた、意味のある成長が可能になります。この組み合わせは、あなたが成長の方向性を目的に沿って調整し、より充実したキャリアを築く可能性を示しています。目的が明確になれば、成長の意味も深まり、継続的な学習への意欲も高まります。',
            
            // profession（伸ばしどころ）の組み合わせ
            'profession-purpose' => '職業スキルを高めることで、目的意識の強みを実現する手段が増えます。専門性を目的に結びつけることで、より意味のある仕事ができます。目的が明確だからこそ、スキル向上の方向性も見えてきて、目的を実現するための具体的な手段として専門性を高めることができます。この組み合わせは、あなたが専門性を目的に貢献させる形で発揮し、より充実した仕事ができる可能性を示しています。スキルが高まれば、目的を実現する力も強まり、仕事へのやりがいも深まります。',
            'profession-people' => '職業スキルを高めることで、人間関係の強みを活かした協力ができます。専門性をチームで共有することで、より良い成果が生まれます。良好な人間関係があるからこそ、専門性を共有し、互いに学び合うことができます。この組み合わせは、あなたが専門性をチームの力として発揮し、協力してより大きな成果を生み出す可能性を示しています。スキルが高まれば、チーム内での役割も明確になり、人間関係もさらに深まります。専門性を共有することで、チーム全体のレベルも向上します。',
            'profession-privilege' => '職業スキルを高めることで、現在の環境や待遇の強みを活かした働き方ができます。専門性が認められる環境で、さらなる成長が期待できます。環境が整っているからこそ、スキル向上に集中でき、専門性を発揮しやすい環境で働くことができます。この組み合わせは、あなたが専門性を高めることで、環境の価値をさらに活かし、より充実した働き方ができる可能性を示しています。スキルが認められれば、環境もさらに整い、専門性を発揮できる場も広がります。',
            'profession-progress' => '職業スキルを高めることで、成長実感の強みを活かした継続的な学習ができます。専門性の向上が、さらなる成長への意欲を生みます。成長を実感できているからこそ、スキル向上のモチベーションも高く、継続的な学習が可能になります。この組み合わせは、あなたが専門性を高めることで、成長のサイクルを加速させ、さらなる高みを目指せる可能性を示しています。スキルが高まれば、成長の実感も深まり、学習への意欲もさらに高まります。',
            
            // people（伸ばしどころ）の組み合わせ
            'people-purpose' => '人間関係を整えることで、目的意識の強みをチームで共有できます。共通の目的に向かう関係性が、より良い職場環境を作ります。目的が明確だからこそ、人間関係を目的に沿った形で整えることができ、チームで共通の目的に向かうことができます。この組み合わせは、あなたが個人の目的をチームの目的と結びつけ、協力して価値を創造する可能性を示しています。人間関係が整えば、目的を共有しやすくなり、チーム全体で目的に向かう力が生まれます。',
            'people-profession' => '人間関係を整えることで、職業スキルの強みを活かした協力ができます。専門性をチームで共有することで、より良い成果が生まれます。専門性が高いからこそ、人間関係を整えることで、専門性を共有し、互いに学び合うことができます。この組み合わせは、あなたが専門性をチームの力として発揮し、協力してより大きな成果を生み出す可能性を示しています。人間関係が整えば、専門性を共有しやすくなり、チーム全体のレベルも向上します。',
            'people-privilege' => '人間関係を整えることで、現在の環境や待遇の強みを活かした働きやすさが向上します。良好な関係性が、職場の満足度を高めます。環境が整っているからこそ、人間関係を整えることで、物質的な環境と心理的な環境の両方が整い、より働きやすい職場になります。この組み合わせは、働きやすさの本質が「何があるか」だけでなく「誰と働くか」にもあることを示しています。人間関係が整えば、環境の価値もより深く実感でき、働きやすさが向上します。',
            'people-progress' => '人間関係を整えることで、成長実感の強みを活かした相互学習ができます。チームで成長を共有することで、より大きなやりがいが生まれます。成長を実感できているからこそ、人間関係を整えることで、成長を共有し、互いに学び合うことができます。この組み合わせは、あなたが個人の成長をチームの成長と結びつけ、協力してより大きな成果を生み出す可能性を示しています。人間関係が整えば、成長を共有しやすくなり、チーム全体の成長も加速します。',
            
            // privilege（伸ばしどころ）の組み合わせ
            'privilege-purpose' => '待遇や環境を改善することで、目的意識の強みを活かした働き方ができます。目的に沿った環境づくりが、さらなる満足度向上につながります。目的が明確だからこそ、環境を目的に沿った形で整えることができ、目的を実現しやすい環境を作ることができます。この組み合わせは、あなたが物質的な環境だけでなく、目的に沿った働き方ができる環境を意識的に作っていくことの重要性を示しています。環境が整えば、目的に集中できる余裕が生まれ、目的を実現する力も強まります。',
            'privilege-profession' => '待遇や環境を改善することで、職業スキルの強みを活かした働き方ができます。専門性が認められる環境で、さらなる成長が期待できます。専門性が高いからこそ、環境を整えることで、専門性を発揮しやすい環境で働くことができます。この組み合わせは、あなたが専門性を高めることで、環境の価値をさらに活かし、より充実した働き方ができる可能性を示しています。環境が整えば、スキル向上に集中でき、専門性を発揮できる場も広がります。',
            'privilege-people' => '待遇や環境を改善することで、人間関係の強みを活かした働きやすさが向上します。良好な関係性と環境の両立が、職場の満足度を高めます。良好な人間関係があるからこそ、環境を整えることで、物質的な環境と心理的な環境の両方が整い、より働きやすい職場になります。この組み合わせは、働きやすさの本質が「何があるか」と「誰と働くか」の両方にあることを示しています。環境が整えば、人間関係もさらに深まり、職場の満足度が向上します。',
            'privilege-progress' => '待遇や環境を改善することで、成長実感の強みを活かした前向きな取り組みができます。成長を実感できる環境が、さらなる意欲を生みます。成長を実感できているからこそ、環境を整えることで、成長を実感しやすい環境で働くことができます。この組み合わせは、あなたが成長の質を高めることで、環境の価値をさらに活かし、より充実した働き方ができる可能性を示しています。環境が整えば、成長に集中できる余裕が生まれ、成長の実感も深まります。',
            
            // progress（伸ばしどころ）の組み合わせ
            'progress-purpose' => '成長実感を得ることで、目的意識の強みを活かした前向きな取り組みができます。目的に向かう成長が、より大きなやりがいを生みます。目的が明確だからこそ、成長の方向性も見えてきて、目的を実現するための成長を意識的に進めることができます。この組み合わせは、あなたが成長の質を高めることで、目的を実現する力も強まり、より充実したキャリアを築く可能性を示しています。成長を実感できれば、目的への意欲も高まり、継続的な学習が可能になります。',
            'progress-profession' => '成長実感を得ることで、職業スキルの強みを活かした継続的な学習ができます。専門性の向上が、さらなる成長への意欲を生みます。専門性が高いからこそ、成長を実感することで、専門性をさらに高めるモチベーションも生まれ、継続的な学習が可能になります。この組み合わせは、あなたが成長のサイクルを加速させることで、専門性をさらに高め、さらなる高みを目指せる可能性を示しています。成長を実感できれば、スキル向上への意欲も高まり、専門性もさらに深まります。',
            'progress-people' => '成長実感を得ることで、人間関係の強みを活かした相互学習ができます。チームで成長を共有することで、より大きなやりがいが生まれます。良好な人間関係があるからこそ、成長を実感することで、成長を共有し、互いに学び合うことができます。この組み合わせは、あなたが個人の成長をチームの成長と結びつけ、協力してより大きな成果を生み出す可能性を示しています。成長を実感できれば、人間関係もさらに深まり、チーム全体の成長も加速します。',
            'progress-privilege' => '成長実感を得ることで、現在の環境や待遇の強みを活かした前向きな取り組みができます。成長を実感できる環境が、さらなる意欲を生みます。環境が整っているからこそ、成長を実感することで、環境を活かした働き方ができ、成長に集中できる余裕が生まれます。この組み合わせは、あなたが成長の質を高めることで、環境の価値をさらに活かし、より充実した働き方ができる可能性を示しています。成長を実感できれば、環境の価値もより深く実感でき、働きやすさも向上します。',
        ];
        
        // 総評コメントを取得（組み合わせに応じて）
        $summaryComment = 'この領域の組み合わせを意識して行動することで、バランスの取れた成長が期待できます。';
        $isDiagnosisPending = false; // 診断前の状態かどうか
        
        // すべてのpillarで満足度 > 重要度の場合の特別なコメント
        if ($isAllPositive) {
            $summaryComment = 'すべての領域で満足度が重要度を上回っており、全体的に非常に良好な状態です。この状態を維持しながら、さらなる成長や新しい挑戦に取り組むことで、より充実したキャリアを築くことができます。現在の良好な状態は、あなたがこれまでに築いてきた基盤の証です。この基盤を活かして、次のステップへと進んでいきましょう。';
        } elseif ($focusKey && $strongKey) {
            // 両方とも取得できた場合：組み合わせコメントを表示
            $combinationKey = $focusKey . '-' . $strongKey;
            if (isset($summaryComments[$combinationKey])) {
                $summaryComment = $summaryComments[$combinationKey];
            }
        } elseif ($focusKey || $strongKey) {
            // 片方しか取得できない場合のデフォルトコメント
            $summaryComment = 'この領域に取り組むことで、満足度の向上が期待できます。';
        } else {
            // 両方とも取得できない場合
            $isDiagnosisPending = true;
            
            // 診断が完了しているかどうかでメッセージを分ける
            if ($diagnosis->is_completed) {
                // 診断は完了しているが、01・02が未計測の場合
                // 重要度を回答していない可能性が高い
                if (!$hasImportance) {
                    $summaryComment = '重要度を回答すると、「フォーカス領域」と「活かしたい強み」の組み合わせに基づいた、より具体的な総評が表示されます。重要度を回答することで、あなたの成長の方向性をより明確にすることができます。';
                } else {
                    $summaryComment = 'データが不足しているため、総評を表示できません。診断を再度実施するか、管理者にお問い合わせください。';
                }
            } else {
                // 診断が未完了の場合
                $summaryComment = '診断を受けると、あなたの「フォーカス領域」と「活かしたい強み」の組み合わせに基づいた、より具体的な総評が表示されます。診断を通じて、あなたの成長の方向性を一緒に見つけていきましょう。';
            }
        }
        
        $balanceDelta = $workScore - $lifeScore;
        $absDelta = abs($balanceDelta);
        
        // 差分に応じたコメントを生成
        if ($balanceDelta === 0) {
            $balanceCopy = '満足度と重要度がバランスよく整っています';
        } elseif ($balanceDelta > 0) {
            // 満足度が重要度より高い場合（ポジティブ傾向）
            if ($absDelta >= 30) {
                $balanceCopy = '満足度が重要度よりかなり高いです。重要度の高い領域に意識を向けましょう。';
            } elseif ($absDelta >= 20) {
                $balanceCopy = '満足度が重要度より高いです。重要度の高い領域を意識すると更に充実します。';
            } elseif ($absDelta >= 10) {
                $balanceCopy = '満足度が重要度よりやや高いです。重要度を意識して取り組むとバランスが整います。';
            } else {
                $balanceCopy = '満足度が重要度よりわずかに高いです。現状は良好です。';
            }
        } else {
            // 重要度が満足度より高い場合（満足度が低い、ややネガティブだがフラットに）
            if ($absDelta >= 30) {
                $balanceCopy = '重要度が満足度よりかなり高いです。重要度の高い領域から優先的に改善を進めましょう。';
            } elseif ($absDelta >= 20) {
                $balanceCopy = '重要度が満足度より高いです。重要度の高い領域に重点的に取り組みましょう。';
            } elseif ($absDelta >= 10) {
                $balanceCopy = '重要度が満足度よりやや高いです。重要度の高い領域を意識して改善しましょう。';
            } else {
                $balanceCopy = '重要度が満足度よりわずかに高いです。重要度を意識して満足度を上げましょう。';
            }
        }
    @endphp

    <div class="w-full max-w-6xl mx-auto space-y-10">
        <!-- hero -->
        <div class="card-refined p-10 bg-gradient-to-br from-[#f8fbff] via-white to-[#e0edff]">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
                <div>
                    <p class="body-small uppercase tracking-[0.2em] text-[#4B7BB5] mb-2">
                        Current Position
                    </p>
                    <h1 class="heading-2 text-3xl md:text-4xl mb-3">
            あなたの現在地レポート
        </h1>
                    <p class="body-large text-[#1E3A5F]">
                        「いまの仕事」と「いまの暮らし」の凸凹を俯瞰して、次の一歩に使えるヒントをまとめました。
        </p>
    </div>
                <div class="flex flex-wrap gap-3">
                    <span class="px-4 py-2 rounded-full bg-white text-[#2E5C8A] body-small font-semibold soft-shadow-refined">
                        {{ now()->format('Y.m.d') }} 更新
                    </span>
                    <span class="px-4 py-2 rounded-full bg-[#2E5C8A] text-white body-small font-semibold soft-shadow-refined">
                        診断ID：#{{ str_pad($diagnosis->id, 4, '0', STR_PAD_LEFT) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- score cards -->
        <div class="grid grid-cols-1 {{ $hasImportance ? 'lg:grid-cols-3' : 'lg:grid-cols-2' }} gap-6">
            <div class="card-refined p-8 space-y-4">
                <div class="body-small font-medium text-[#4B7BB5]">満足度</div>
                <div class="heading-1 text-5xl text-[#1E3A5F]">
                    {{ $workScore }}<span class="text-2xl font-semibold"> /100</span>
                </div>
                <div class="w-full h-2.5 bg-[#E3ECF9] rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-[#5B8DCC] to-[#2563EB]" style="width: {{ $workScore }}%;"></div>
                </div>
                <p class="body-small text-[#4A5A73]">
                    ビジョン共感・仕事の楽しさ・チームの相性・待遇など、働く場そのものへの納得度。
                </p>
            </div>

            @if($hasImportance)
                <div class="card-refined p-8 space-y-4">
                    <div class="body-small font-medium text-[#4B7BB5]">重要度</div>
                    <div class="heading-1 text-5xl text-[#1E3A5F]">
                        {{ $lifeScore }}<span class="text-2xl font-semibold"> /100</span>
                    </div>
                    <div class="w-full h-2.5 bg-[#E3ECF9] rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-[#8FBEDC] to-[#4F9EDB]" style="width: {{ $lifeScore }}%;"></div>
                    </div>
                    <p class="body-small text-[#4A5A73]">
                        各領域への重要度の評価。満足度と比較することで、優先的に取り組むべき領域が明確になり、より効果的な行動計画を立てられます。
                    </p>
                </div>
            @else
                <div class="card-refined p-8 space-y-4 bg-gradient-to-br from-[#E3ECF9] to-[#F0F7FF]">
                    <div class="body-small font-medium text-[#4B7BB5] mb-2">重要度を入力すると、より深く理解できます</div>
                    <p class="body-text text-[#1E3A5F] mb-4">
                        現在は満足度のみの診断結果です。重要度を入力することで、満足度と重要度を比較し、優先的に取り組むべき領域が明確になります。
                    </p>
                    <a href="{{ route('diagnosis.importance', $diagnosis->id) }}" class="inline-block px-6 py-3 bg-[#6BB6FF] text-white rounded-lg font-semibold hover:bg-[#5B8DCC] transition-colors text-center">
                        重要度を入力する
                    </a>
                </div>
            @endif

            @if($hasImportance)
                <div class="card-refined p-8 space-y-4">
                    <div class="body-small font-medium text-[#4B7BB5]">満足度ー重要度</div>
                    <div class="heading-1 text-4xl text-[#1E3A5F]">
                        @if($balanceDelta === 0)
                            ±0
                        @else
                            {{ $balanceDelta > 0 ? '+' : '' }}{{ $balanceDelta }}
                        @endif
                    </div>
                    <p class="body-text text-[#1E3A5F]">
                        {{ $balanceCopy }}
                    </p>
                    <div class="flex flex-wrap gap-2 pt-2">
                        <span class="px-3 py-1 rounded-full bg-[#E3ECF9] text-[#2E5C8A] body-small">
                            強み：{{ $strongLabel }}
                        </span>
                        <span class="px-3 py-1 rounded-full bg-[#FFEED9] text-[#B45309] body-small">
                            伸ばしどころ：{{ $focusLabel }}
                        </span>
                    </div>
                </div>
            @endif
        </div>

        <!-- radar + insights -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="card-refined p-8">
                <div class="flex flex-col gap-2 mb-6">
                    <div class="heading-3 text-xl">
                        バランスチェック（レーダーチャート）
                    </div>
                    <p class="body-small text-[#4A5A73]">
                        凸は「安心・満足している領域」、凹は「これから呼吸を合わせたい領域」。重要度と重ねて眺めると、行動に移す順番が見えてきます。
                    </p>
                </div>

                <div class="w-full max-w-sm md:max-w-md mx-auto mb-6">
            <canvas id="radarChart" width="400" height="400"></canvas>
        </div>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3 text-xs text-[#4A5A73]">
                        <span class="inline-flex items-center gap-1">
                            <span class="w-3 h-3 rounded-full bg-[#2563EB]"></span> 満足度
                        </span>
                        <span class="inline-flex items-center gap-1">
                            <span class="w-3 h-3 rounded-full bg-[#F59E0B]"></span> 重要度
                        </span>
                    </div>
                    @if(!$hasImportance)
                    <a href="{{ route('diagnosis.importance', ['id' => $diagnosis->id]) }}" class="btn-primary text-sm px-5 py-2">
                        今度は重要度を確認する
                    </a>
                    @endif
        </div>
    </div>

            <div class="card-refined p-8 flex flex-col gap-6">
                <div>
                    <div class="heading-3 text-xl mb-2">次に整えたいポイント</div>
                    <p class="body-small text-[#4A5A73]">
                        満足度と重要度の差分から、優先的に取り組むべき領域と活用できる強みをピックアップしました。
                    </p>
        </div>
                <div class="space-y-4">
                    <div class="border border-[#2E5C8A]/15 rounded-2xl p-5 flex items-start gap-4">
                        <span class="w-12 h-12 rounded-2xl bg-[#F4F7FF] text-[#2E5C8A] flex items-center justify-center font-semibold">
                            01
                        </span>
                        <div>
                            <p class="body-small font-semibold text-[#2E5C8A] mb-1">フォーカス領域</p>
                            <p class="body-small text-[#4A5A73] mb-2">重要度が高いのに満足度が低い領域。優先的に改善すべきポイントです。</p>
                            <p class="heading-3 text-lg mb-2">{{ $focusLabel }}</p>
                            <p class="body-small text-[#4A5A73]">
                                {{ $focusComment }}
                            </p>
                        </div>
                    </div>
                    <div class="border border-[#2E5C8A]/15 rounded-2xl p-5 flex items-start gap-4 bg-[#FDF7EE]">
                        <span class="w-12 h-12 rounded-2xl bg-white text-[#B45309] flex items-center justify-center font-semibold">
                            02
                        </span>
                        <div>
                            <p class="body-small font-semibold text-[#B45309] mb-1">活かしたい強み</p>
                            <p class="body-small text-[#72441A] mb-2">満足度が高く、余力がある領域。行動の下支えとして活用できる資産です。</p>
                            <p class="heading-3 text-lg mb-2">{{ $strongLabel }}</p>
                            <p class="body-small text-[#72441A]">
                                {{ $strengthComment }}
                            </p>
                        </div>
                    </div>
                    <div class="border border-[#2E5C8A]/15 rounded-2xl p-5 flex items-start gap-4 {{ $isDiagnosisPending ? 'bg-gray-50 opacity-75' : 'bg-[#F0F9FF]' }}">
                        <span class="w-12 h-12 rounded-2xl {{ $isDiagnosisPending ? 'bg-gray-200 text-gray-400' : 'bg-white text-[#0369A1]' }} flex items-center justify-center font-semibold">
                            03
                        </span>
                        <div class="flex-1">
                            <p class="body-small font-semibold {{ $isDiagnosisPending ? 'text-gray-500' : 'text-[#0369A1]' }} mb-1">総評</p>
                            <p class="body-small {{ $isDiagnosisPending ? 'text-gray-500' : 'text-[#0C4A6E]' }} mb-2">
                                @if($isDiagnosisPending)
                                    診断を受けると、フォーカス領域と活かしたい強みの組み合わせから見える、あなたの成長の方向性が表示されます。
                                @else
                                    フォーカス領域と活かしたい強みの組み合わせから見える、あなたの成長の方向性です。
                                @endif
                            </p>
                            <p class="body-small {{ $isDiagnosisPending ? 'text-gray-600 italic' : 'text-[#0C4A6E]' }}">
                                {{ $summaryComment }}
                            </p>
                            @if($isDiagnosisPending)
                                <div class="mt-3">
                                    <a href="{{ route('diagnosis.start') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-[#0369A1] hover:text-[#0C4A6E] transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                        </svg>
                                        診断を始める
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- comments / reflection -->
        @if(!empty($answerNotes))
        <div class="card-refined p-8 space-y-6">
            <div>
                <div class="heading-3 text-xl mb-2">あなたのメモ</div>
                <p class="body-small text-[#4A5A73]">
                    セッションで深めたいキーワードを置き場として保存しています。読み返しながら、DiaryやMilestoneにも転記しておくと会話が滑らかになります。
                </p>
            </div>
            <div class="flex flex-col divide-y divide-[#2E5C8A]/10 gap-4">
                @foreach ($answerNotes as $note)
                    <div class="pt-4 first:pt-0">
                        <div class="body-small font-semibold text-[#2E5C8A] mb-1">
                            {{ $note['label'] }}
                        </div>
                        <div class="body-text whitespace-pre-line text-[#1E3A5F]">
                            {{ $note['comment'] }}
                        </div>
                    </div>
                @endforeach
        </div>
    </div>
    @endif

    <!-- actions -->
        <div class="flex flex-col md:flex-row gap-4">
            <a href="/diagnosis/start" class="btn-secondary flex-1 text-center">
            もう一度チェックする
        </a>
            <a href="/dashboard" class="btn-primary flex-1 text-center">
            ダッシュボードに戻る
        </a>
        </div>
    </div>
    </flux:main>
</x-layouts.app.sidebar>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let radarChartInstance = null;

function initRadarChart() {
    // Chart.jsが読み込まれているか確認
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js is not loaded yet, retrying...');
        setTimeout(initRadarChart, 100);
        return;
    }

    // 既存のチャートがあれば破棄
    if (radarChartInstance) {
        radarChartInstance.destroy();
        radarChartInstance = null;
    }

    const canvas = document.getElementById('radarChart');
    if (!canvas) {
        console.error('Radar chart canvas not found');
        return;
    }

    const ctx = canvas.getContext('2d');
    if (!ctx) {
        console.error('Could not get 2d context');
        return;
    }

    const radarLabels = @json($radarLabels ?? []);
    const workData = @json($radarWorkData ?? []);
    const lifeEdgeLeft = @json($lifeEdgeLeftData ?? []);
    const lifeEdgeRight = @json($lifeEdgeRightData ?? []);
    const lifePoint = @json($lifePointData ?? []);
    const lifeFill = @json($lifeFillData ?? []);
    const importanceData = @json($importanceDataset ?? []);
    const importanceLifeAvg = @json($importanceLifeAvg ?? null);

    console.log('Radar chart data:', {
        labels: radarLabels,
        workData: workData,
        lifeEdgeLeft: lifeEdgeLeft,
        lifeEdgeRight: lifeEdgeRight,
        lifePoint: lifePoint,
        lifeFill: lifeFill,
        importanceData: importanceData,
        importanceLifeAvg: importanceLifeAvg,
        importanceDataLength: importanceData.length,
        labelsLength: radarLabels.length
    });

    // データが空の場合はチャートを作成しない
    if (!radarLabels || radarLabels.length === 0) {
        console.warn('No radar chart labels found');
        return;
    }

    try {
        radarChartInstance = new Chart(ctx, {
    type: 'radar',
    data: {
        labels: radarLabels,
        datasets: [
            {
                label: '満足度',
                data: workData,
                borderWidth: 3,
                borderColor: '#2563EB',
                backgroundColor: 'rgba(37,99,235,0.2)',
                pointBackgroundColor: '#2563EB',
                pointBorderColor: '#FFFFFF',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
            },
            // Life 左↔点 の線
            {
                label: 'Life-Link-L',
                data: lifeEdgeLeft,
                borderWidth: 2,
                borderColor: '#2563EB',
                backgroundColor: 'transparent',
                pointRadius: 0,
                spanGaps: true,
            },
            // Life 右↔点 の線
            {
                label: 'Life-Link-R',
                data: lifeEdgeRight,
                borderWidth: 2,
                borderColor: '#2563EB',
                backgroundColor: 'transparent',
                pointRadius: 0,
                spanGaps: true,
            },
            // Life の塗り（ワーク色と同系）
            {
                label: 'Life-Fill',
                data: lifeFill,
                borderWidth: 0,
                backgroundColor: 'rgba(37,99,235,0.15)',
                pointRadius: 0,
                spanGaps: true,
            },
            // 重要度（オレンジ系で温かみとコントラスト）
            {
                label: '重要度',
                data: importanceData,
                borderWidth: 3,
                borderColor: '#F59E0B',
                backgroundColor: 'rgba(245,158,11,0.15)',
                pointBackgroundColor: '#F59E0B',
                pointBorderColor: '#FFFFFF',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
                spanGaps: true,
            },
            // Life の点のみ
            {
                label: 'Life-Point',
                data: lifePoint,
                borderWidth: 0,
                showLine: false,
                backgroundColor: 'transparent',
                pointBackgroundColor: '#2563EB',
                pointBorderColor: '#FFFFFF',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
            },
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            r: {
                suggestedMin: 0,
                suggestedMax: 100,
                grid: { color: 'rgba(46,92,138,0.2)', lineWidth: 1 },
                angleLines: { color: 'rgba(46,92,138,0.2)', lineWidth: 1 },
                pointLabels: {
                    color: '#2E5C8A',
                    font: { size: 11 }
                },
                ticks: {
                    backdropColor: 'transparent',
                    color: '#1E3A5F',
                    font: { size: 10 },
                    stepSize: 20
                }
            }
        },
        plugins: {
            legend: {
                labels: {
                    color: '#1E3A5F',
                    font: { size: 11 },
                    // 「満足度」「重要度」だけを凡例に表示
                    filter: function(item) {
                        return item.text === '満足度' || item.text === '重要度';
                    }
                }
            }
        }
    }
    });
    } catch (error) {
        console.error('Error creating chart:', error);
    }
}

// Chart.jsの読み込み完了を待つ
function waitForChartJS(callback) {
    if (typeof Chart !== 'undefined') {
        callback();
    } else {
        // Chart.jsのスクリプトタグのonloadイベントを待つ
        const script = document.querySelector('script[src*="chart.js"]');
        if (script) {
            script.addEventListener('load', callback);
        } else {
            // フォールバック: 定期的にチェック
            setTimeout(() => waitForChartJS(callback), 50);
        }
    }
}

// DOMContentLoadedとLivewireナビゲーションの両方に対応
function initChartWhenReady() {
    waitForChartJS(() => {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initRadarChart);
    } else {
            // DOMが既に読み込まれている場合は少し遅延させてから実行
            setTimeout(initRadarChart, 50);
    }
    });
}

// Livewireのナビゲーション後にも実行
document.addEventListener('livewire:navigated', () => {
    waitForChartJS(() => {
        setTimeout(initRadarChart, 50);
    });
});

// 初回読み込み時にも実行
initChartWhenReady();
</script>
 