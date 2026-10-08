<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booth;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class KioskController extends Controller
{
    /**
     * Display the kiosk map view.
     */
    public function index(): View
    {
        $columns = ['id', 'booth_code', 'section', 'start_cell', 'end_cell', 'type', 'is_merged'];

        $booths = Booth::where('type', '!=', 'wall')->get($columns);
        $walls  = Booth::where('type', 'wall')->get($columns);

        return view('kiosk.index', compact('booths', 'walls'));
    }

    /**
     * Search endpoint for real-time booth lookups.
     */
    public function search(Request $request): JsonResponse
    {
        $searchTerm = trim($request->input('query', ''));

        // Prevent short queries without throwing a 422/302 validation exception
        if (mb_strlen($searchTerm) < 2) {
            return response()->json([
                'status'  => 'success',
                'data'    => [],
                'message' => 'Query too short.'
            ]);
        }

        $query = strtoupper($searchTerm);
        $escapedQuery = str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $query);

        $booths = Booth::whereNotIn('type', ['wall', 'aisle'])
            ->where(function ($q) use ($escapedQuery) {
                $q->where('booth_code', 'LIKE', "%{$escapedQuery}%")
                  ->orWhere('section', 'LIKE', "%{$escapedQuery}%");
            })
            ->select(['id', 'booth_code', 'section', 'start_cell', 'end_cell', 'type'])
            ->limit(15)
            ->get();

        if ($booths->isEmpty()) {
            // Return 200 OK with empty array so JS fetch handles it without crashing
            return response()->json([
                'status'  => 'not_found',
                'message' => 'No matching booth or feature area found.',
                'data'    => []
            ], 200);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $booths
        ]);
    }
}