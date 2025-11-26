<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Resume;
use Illuminate\Support\Facades\Auth;

class ResumeController extends Controller
{
    /**
     * 履歴書PDFを表示
     */
    public function view($id)
    {
        $document = Resume::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $filePath = storage_path('app/public/' . $document->file_path);

        if (!file_exists($filePath)) {
            abort(404, 'ファイルが見つかりません。');
        }

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
