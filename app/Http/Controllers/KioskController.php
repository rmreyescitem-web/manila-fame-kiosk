<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booth;

class KioskController extends Controller
{
    /**
     * Display the kiosk map view.
     */
    public function index()
    {
        $booths = Booth::all();
        return view('kiosk.index', compact('booths'));
    }

    /**
     * Search endpoint for real-time booth lookups.
     */
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:30',
        ]);

        $query = strtoupper(trim($request->input('query')));

        $booths = Booth::where('booth_code', 'LIKE', "%{$query}%")
                    ->orWhere('section', 'LIKE', "%{$query}%")
                    ->get();

        if ($booths->isEmpty()) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Booth not found.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $booths
        ]);
    }
}