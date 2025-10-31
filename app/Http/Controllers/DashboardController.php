<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Diagnosis;
use App\Models\LifeEvent;
use App\Models\WcmSheet;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 診断の状態を取得
        $latestDiagnosis = Diagnosis::where('user_id', $user->id)
            ->where('is_completed', true)
            ->latest()
            ->first();
        
        $draftDiagnosis = Diagnosis::where('user_id', $user->id)
            ->where('is_draft', true)
            ->where('is_completed', false)
            ->latest()
            ->first();

        // 人生史のイベント数を取得
        $lifeEventCount = LifeEvent::where('user_id', $user->id)->count();
        $hasLifeHistory = $lifeEventCount > 0;

        // WCMシートの最新バージョンを取得
        $latestWcmSheet = WcmSheet::where('user_id', $user->id)
            ->where('is_draft', false)
            ->latest('updated_at')
            ->first();

        return view('dashboard', [
            'latestDiagnosis' => $latestDiagnosis,
            'draftDiagnosis' => $draftDiagnosis,
            'hasLifeHistory' => $hasLifeHistory,
            'lifeEventCount' => $lifeEventCount,
            'latestWcmSheet' => $latestWcmSheet,
        ]);
    }
}

