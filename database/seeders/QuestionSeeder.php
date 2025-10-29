<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $questions = [
            [
                'question_id' => 'work_purpose',
                'type' => 'work',
                'pillar' => 'purpose',
                'weight' => 20,
                'text' => 'あなたが務める会社の大義・ビジョン・目的に共感できますか？',
                'helper' => '会社がめざしているものは、あなたにとって『一緒に叶えたい』と思える内容でしょうか？',
                'options' => [
                    ['value' => 5, 'label' => 'とても共感できる', 'desc' => '強く誇りを感じる'],
                    ['value' => 4, 'label' => '共感できる', 'desc' => 'わりと納得している'],
                    ['value' => 3, 'label' => 'どちらともいえない', 'desc' => '良い面と疑問の両方がある'],
                    ['value' => 2, 'label' => 'あまり共感できない', 'desc' => '少しズレを感じている'],
                    ['value' => 1, 'label' => 'まったく共感できない', 'desc' => '自分の価値観とは合わない'],
                ],
                'order' => 1,
            ],
            [
                'question_id' => 'work_job_meaning',
                'type' => 'work',
                'pillar' => 'profession',
                'weight' => 20,
                'text' => 'あなたの仕事にやりがい・価値を感じていますか？',
                'helper' => '誰のために何をどう良くしている仕事だと感じますか？心をこめて取り組めていますか？',
                'options' => [
                    ['value' => 5, 'label' => '強く感じる', 'desc' => '誇りと喜びがある'],
                    ['value' => 4, 'label' => '感じる', 'desc' => 'わりと満足している'],
                    ['value' => 3, 'label' => 'どちらともいえない', 'desc' => '悪くはないが特別でもない'],
                    ['value' => 2, 'label' => 'あまり感じない', 'desc' => '作業的になっている'],
                    ['value' => 1, 'label' => '全く感じない', 'desc' => 'やる意味を見失っている'],
                ],
                'order' => 2,
            ],
            [
                'question_id' => 'work_strength_fit',
                'type' => 'work',
                'pillar' => 'profession',
                'weight' => 20,
                'text' => 'あなたの強み・適性は今の仕事で活かせていますか？',
                'helper' => '得意なことを発揮できている／スキルが育っている、と実感できるか？',
                'options' => [
                    ['value' => 5, 'label' => '十分に活かせている', 'desc' => '伸びている実感がある'],
                    ['value' => 4, 'label' => 'まあ活かせている', 'desc' => '手応えはある'],
                    ['value' => 3, 'label' => 'どちらともいえない', 'desc' => '一部だけ活きている'],
                    ['value' => 2, 'label' => 'あまり活かせていない', 'desc' => 'ズレていると感じる'],
                    ['value' => 1, 'label' => '全く活かせていない', 'desc' => '本来の力とは違う仕事をしている'],
                ],
                'order' => 3,
            ],
            [
                'question_id' => 'work_people',
                'type' => 'work',
                'pillar' => 'people',
                'weight' => 20,
                'text' => '一緒に働く人たちは『この人たちと働きたい』と思える相手ですか？',
                'helper' => '信頼・尊敬・安心感はありますか？ロールモデルはいますか？',
                'options' => [
                    ['value' => 5, 'label' => 'とてもそう思う', 'desc' => '尊敬・信頼できる仲間が多い'],
                    ['value' => 4, 'label' => 'そう思う', 'desc' => '良い関係を築けている'],
                    ['value' => 3, 'label' => 'どちらともいえない', 'desc' => '良い人もいれば合わない人もいる'],
                    ['value' => 2, 'label' => 'あまり思えない', 'desc' => '孤立/合わなさが気になる'],
                    ['value' => 1, 'label' => '全く思えない', 'desc' => '安心して働けない'],
                ],
                'order' => 4,
            ],
            [
                'question_id' => 'work_privilege_wlb',
                'type' => 'work',
                'pillar' => 'privilege',
                'weight' => 20,
                'text' => '今の働き方はあなたの生活リズムを守れていますか？',
                'helper' => '働く時間をある程度コントロールできている／WLBは取れている？',
                'options' => [
                    ['value' => 5, 'label' => 'かなり守れている', 'desc' => '自分のペースを保てている'],
                    ['value' => 4, 'label' => 'まあ守れている', 'desc' => '少し無理はあるが許容範囲'],
                    ['value' => 3, 'label' => 'どちらともいえない', 'desc' => '波が激しい'],
                    ['value' => 2, 'label' => 'あまり守れていない', 'desc' => '生活が仕事中心になりすぎている'],
                    ['value' => 1, 'label' => '全く守れていない', 'desc' => '常にいっぱいいっぱい'],
                ],
                'order' => 5,
            ],
            [
                'question_id' => 'work_privilege_money',
                'type' => 'work',
                'pillar' => 'privilege',
                'weight' => 20,
                'text' => '今の収入や待遇に、金銭的な満足感はありますか？',
                'helper' => 'いまの努力・責任と比べて、フェアだと思える？',
                'options' => [
                    ['value' => 5, 'label' => 'とても満足', 'desc' => '十分に見合っている'],
                    ['value' => 4, 'label' => '満足', 'desc' => '大きな不満はない'],
                    ['value' => 3, 'label' => 'ふつう', 'desc' => '納得も不満も半々'],
                    ['value' => 2, 'label' => 'やや不満', 'desc' => 'もっと欲しい/上げたい'],
                    ['value' => 1, 'label' => 'かなり不満', 'desc' => '正直割に合わない'],
                ],
                'order' => 6,
            ],
            [
                'question_id' => 'work_privilege_status',
                'type' => 'work',
                'pillar' => 'privilege',
                'weight' => 20,
                'text' => '今の仕事や肩書に対して、社会的な評価・信頼を感じますか？',
                'helper' => 'ネームバリューや社会的信用は得られている？',
                'options' => [
                    ['value' => 5, 'label' => '強く感じる', 'desc' => '誇れる肩書やブランドがある'],
                    ['value' => 4, 'label' => '感じる', 'desc' => 'ある程度の信用がある'],
                    ['value' => 3, 'label' => 'どちらともいえない', 'desc' => '強みもあるが弱さもある'],
                    ['value' => 2, 'label' => 'あまり感じない', 'desc' => '無名・不安定さが気になる'],
                    ['value' => 1, 'label' => '全く感じない', 'desc' => '評価されにくいと感じる'],
                ],
                'order' => 7,
            ],
            [
                'question_id' => 'work_progress',
                'type' => 'work',
                'pillar' => 'progress',
                'weight' => 20,
                'text' => '成長実感はありますか？',
                'helper' => '昨日より今日の自分が、少しでも進んでいると思えるか？',
                'options' => [
                    ['value' => 5, 'label' => '強くある', 'desc' => '明確に伸びている感覚がある'],
                    ['value' => 4, 'label' => 'ある', 'desc' => 'ゆっくりだが進んでいる'],
                    ['value' => 3, 'label' => 'どちらともいえない', 'desc' => '停滞と前進を行き来している'],
                    ['value' => 2, 'label' => 'あまりない', 'desc' => '同じ場所で足踏みしている感覚'],
                    ['value' => 1, 'label' => 'まったくない', 'desc' => '後退している気さえする'],
                ],
                'order' => 8,
            ],
            [
                'question_id' => 'life_family',
                'type' => 'life',
                'pillar' => 'family',
                'weight' => null,
                'text' => '結婚・家族・子育て（または今の家族との関係）に満足できていますか？',
                'helper' => null,
                'options' => [
                    ['value' => 5, 'label' => '満足', 'desc' => '安心感がある'],
                    ['value' => 4, 'label' => 'おおむね満足', 'desc' => '大きな不満はない'],
                    ['value' => 3, 'label' => 'ふつう', 'desc' => '良い面としんどい面が同じくらい'],
                    ['value' => 2, 'label' => 'やや不満', 'desc' => '負担/孤独を感じることがある'],
                    ['value' => 1, 'label' => '不満', 'desc' => 'かなりしんどい'],
                ],
                'order' => 9,
            ],
            [
                'question_id' => 'life_friends',
                'type' => 'life',
                'pillar' => 'friends',
                'weight' => null,
                'text' => '友人・人間関係に満足できていますか？',
                'helper' => null,
                'options' => [
                    ['value' => 5, 'label' => '満足', 'desc' => '支え合える関係がある'],
                    ['value' => 4, 'label' => 'おおむね満足', 'desc' => '気軽に話せる人がいる'],
                    ['value' => 3, 'label' => 'ふつう', 'desc' => 'つながりはあるが浅い'],
                    ['value' => 2, 'label' => 'やや不満', 'desc' => '孤立感がある'],
                    ['value' => 1, 'label' => '不満', 'desc' => 'ほぼ頼れる人がいない'],
                ],
                'order' => 10,
            ],
            [
                'question_id' => 'life_leisure',
                'type' => 'life',
                'pillar' => 'leisure',
                'weight' => null,
                'text' => '自分のための時間（休息・趣味）をちゃんと取れていますか？',
                'helper' => null,
                'options' => [
                    ['value' => 5, 'label' => '十分取れている', 'desc' => '心身がリセットできている'],
                    ['value' => 4, 'label' => 'まあ取れている', 'desc' => 'ある程度確保できている'],
                    ['value' => 3, 'label' => 'どちらともいえない', 'desc' => '波がある'],
                    ['value' => 2, 'label' => 'あまり取れていない', 'desc' => '常に後回しになっている'],
                    ['value' => 1, 'label' => '全く取れていない', 'desc' => '休む暇がない'],
                ],
                'order' => 11,
            ],
            [
                'question_id' => 'life_sidejob',
                'type' => 'life',
                'pillar' => 'sidejob',
                'weight' => null,
                'text' => '本業以外の活動（副業/事業/挑戦）に満足できていますか？',
                'helper' => null,
                'options' => [
                    ['value' => 5, 'label' => '満足', 'desc' => '自分の可能性を広げられている'],
                    ['value' => 4, 'label' => 'おおむね満足', 'desc' => 'やりたいことに手をつけられている'],
                    ['value' => 3, 'label' => 'ふつう', 'desc' => 'まだ探り中'],
                    ['value' => 2, 'label' => 'やや不満', 'desc' => 'やりたいのに動けていない'],
                    ['value' => 1, 'label' => '不満', 'desc' => '何もできていない'],
                ],
                'order' => 12,
            ],
            [
                'question_id' => 'life_health',
                'type' => 'life',
                'pillar' => 'health',
                'weight' => null,
                'text' => '体調・メンタル・睡眠など、健康的な生活は送れていますか？',
                'helper' => null,
                'options' => [
                    ['value' => 5, 'label' => 'とても良い', 'desc' => '安定して元気'],
                    ['value' => 4, 'label' => '良い', 'desc' => '多少の疲れはあるが回復できている'],
                    ['value' => 3, 'label' => 'ふつう', 'desc' => '波が大きい'],
                    ['value' => 2, 'label' => 'あまり良くない', 'desc' => '慢性的な疲れや不調がある'],
                    ['value' => 1, 'label' => 'かなり良くない', 'desc' => '日常に支障があるレベル'],
                ],
                'order' => 13,
            ],
            [
                'question_id' => 'life_finance',
                'type' => 'life',
                'pillar' => 'finance',
                'weight' => null,
                'text' => '将来に対するお金の安心感はありますか？（貯蓄・資産形成・収支）',
                'helper' => null,
                'options' => [
                    ['value' => 5, 'label' => '安心できる', 'desc' => '今後の計画にも見通しがある'],
                    ['value' => 4, 'label' => 'わりと安心', 'desc' => '大きな不安はない'],
                    ['value' => 3, 'label' => 'どちらともいえない', 'desc' => '時々グラつく'],
                    ['value' => 2, 'label' => 'やや不安', 'desc' => 'このままで大丈夫か心配'],
                    ['value' => 1, 'label' => 'とても不安', 'desc' => '生活そのものが不安定'],
                ],
                'order' => 14,
            ],
        ];

        foreach ($questions as $question) {
            Question::updateOrCreate(
                ['question_id' => $question['question_id']],
                $question
            );
        }
    }
}
