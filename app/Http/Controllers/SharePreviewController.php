<?php

namespace App\Http\Controllers;

use App\Models\WcmSheet;
use App\Models\LifeEvent;
use App\Models\CareerSatisfactionDiagnosis;
use App\Models\Diagnosis;
use App\Models\StrengthsReport;
use App\Models\CareerMilestone;
use App\Models\PersonalityAssessment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SharePreviewController extends Controller
{
    /**
     * WCMシートの共有確認画面
     */
    public function previewWcm($id)
    {
        $sheet = WcmSheet::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('share-preview.wcm', [
            'sheet' => $sheet,
            'type' => 'wcm',
            'id' => $id,
        ]);
    }

    /**
     * 人生史の共有確認画面
     */
    public function previewLifeHistory($id)
    {
        $event = LifeEvent::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('share-preview.life-history', [
            'event' => $event,
            'type' => 'life_history',
            'id' => $id,
        ]);
    }

    /**
     * 診断結果の共有確認画面
     */
    public function previewDiagnosis($id)
    {
        $diagnosis = Diagnosis::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('share-preview.diagnosis', [
            'diagnosis' => $diagnosis,
            'type' => 'diagnosis',
            'id' => $id,
        ]);
    }

    /**
     * 現職満足度診断結果の共有確認画面
     */
    public function previewCareerSatisfaction($id)
    {
        $diagnosis = CareerSatisfactionDiagnosis::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('share-preview.career-satisfaction', [
            'diagnosis' => $diagnosis,
            'type' => 'career_satisfaction',
            'id' => $id,
        ]);
    }

    /**
     * 持ち味診断の共有確認画面
     */
    public function previewStrengthsReport($id)
    {
        $report = StrengthsReport::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('share-preview.strengths-report', [
            'report' => $report,
            'type' => 'strengths_report',
            'id' => $id,
        ]);
    }

    /**
     * マイゴールの共有確認画面
     */
    public function previewMyGoal()
    {
        $user = Auth::user();
        
        if (!$user->goal_image) {
            return redirect()->route('my-goal')
                ->with('error', 'マイゴールが設定されていません。');
        }

        return view('share-preview.my-goal', [
            'user' => $user,
            'type' => 'my_goal',
        ]);
    }

    /**
     * マイルストーンの共有確認画面
     */
    public function previewMilestone($id)
    {
        $milestone = CareerMilestone::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('share-preview.milestone', [
            'milestone' => $milestone,
            'type' => 'milestone',
            'id' => $id,
        ]);
    }

    /**
     * 自己診断結果の共有確認画面
     */
    public function previewPersonalityAssessment($id)
    {
        $assessment = PersonalityAssessment::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('share-preview.personality-assessment', [
            'assessment' => $assessment,
            'type' => 'personality_assessment',
            'id' => $id,
        ]);
    }

    /**
     * 共有許可の確定処理
     */
    public function confirmShare(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:wcm,life_history,career_satisfaction,diagnosis,strengths_report,my_goal,milestone,personality_assessment',
            'id' => 'nullable|integer',
        ]);

        $type = $request->type;
        $id = $request->id;
        $userId = Auth::id();

        switch ($type) {
            case 'wcm':
                $item = WcmSheet::where('id', $id)
                    ->where('user_id', $userId)
                    ->firstOrFail();
                $item->update(['is_admin_visible' => true]);
                $redirectRoute = route('wcm.sheet', ['id' => $id]);
                break;

            case 'life_history':
                $item = LifeEvent::where('id', $id)
                    ->where('user_id', $userId)
                    ->firstOrFail();
                $item->update(['is_admin_visible' => true]);
                $redirectRoute = route('life-history.timeline');
                break;

            case 'career_satisfaction':
                $item = CareerSatisfactionDiagnosis::where('id', $id)
                    ->where('user_id', $userId)
                    ->firstOrFail();
                $item->update(['is_admin_visible' => true]);
                $redirectRoute = route('career-satisfaction-diagnosis.result', ['id' => $id]);
                break;

            case 'diagnosis':
                $item = Diagnosis::where('id', $id)
                    ->where('user_id', $userId)
                    ->firstOrFail();
                $item->update(['is_admin_visible' => true]);
                $redirectRoute = route('diagnosis.result', ['id' => $id]);
                break;

            case 'strengths_report':
                $item = StrengthsReport::where('id', $id)
                    ->where('user_id', $userId)
                    ->firstOrFail();
                $item->update(['is_admin_visible' => true]);
                $redirectRoute = route('onboarding.mini-manual');
                break;

            case 'my_goal':
                $user = User::findOrFail($userId);
                $user->update(['goal_is_admin_visible' => true]);
                $redirectRoute = route('my-goal');
                break;

            case 'milestone':
                $item = CareerMilestone::where('id', $id)
                    ->where('user_id', $userId)
                    ->firstOrFail();
                $item->update(['is_admin_visible' => true]);
                $redirectRoute = route('career.milestones');
                break;

            case 'personality_assessment':
                $item = PersonalityAssessment::where('id', $id)
                    ->where('user_id', $userId)
                    ->firstOrFail();
                $item->update(['is_admin_visible' => true]);
                $redirectRoute = route('assessments.index');
                break;

            default:
                return redirect()->back()
                    ->with('error', '無効なタイプです。');
        }

        return redirect($redirectRoute)
            ->with('success', '管理者への共有を許可しました。');
    }
}
