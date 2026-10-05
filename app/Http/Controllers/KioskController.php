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
        // Fetch regular booths, facilities, entrances, and aisles
        $booths = Booth::where('type', '!=', 'wall')->get();

        // Fetch structural walls separately to populate the wall layer
        $walls = Booth::where('type', 'wall')->get();

        return view('kiosk.index', compact('booths', 'walls'));
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

        $booths = Booth::where('type', '!=', 'wall')
                    ->where(function($q) use ($query) {
                        $q->where('booth_code', 'LIKE', "%{$query}%")
                          ->orWhere('section', 'LIKE', "%{$query}%");
                    })
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