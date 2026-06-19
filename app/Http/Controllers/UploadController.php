<?php
namespace App\Http\Controllers;

use App\Models\{Store, UploadLog};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UploadController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $storeQuery = Store::query()->where('is_active', true);
        if ($user->isPic() && $user->pic_id) {
            $storeQuery->where('pic_id', $user->pic_id);
        }
        $stores = $storeQuery->get();
        $logs = UploadLog::with('store')->orderByDesc('uploaded_at')->limit(20)->get();
        return view('upload.index', compact('stores', 'logs'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'report_type' => 'required|in:orders,financials,ads,metrics',
            'source_platform' => 'required|in:Shopee,TikTok Shop,Meta Ads',
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240',
        ]);

        $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        UploadLog::create([
            'pic_id' => Auth::user()->pic_id,
            'store_id' => $request->store_id,
            'report_type' => $request->report_type,
            'source_platform' => $request->source_platform,
            'filename' => $filename,
            'rows_parsed' => 0,
            'status' => 'success',
        ]);

        return back()->with('success', "File \"$filename\" berhasil diupload!");
    }
}
