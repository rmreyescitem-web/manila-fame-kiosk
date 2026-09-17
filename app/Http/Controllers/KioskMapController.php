<?php

namespace App\Http\Controllers;

use App\Models\Booth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KioskMapController extends Controller
{
    public function index()
    {
        // Fetch booth assignments for fair code MFIO2026 joined with their spatial layout details
        $booths = DB::table('booth_assignments')
            ->join('booth_spaces', 'booth_assignments.booth_space_id', '=', 'booth_spaces.id')
            ->select(
                'booth_assignments.id as assignment_id',
                'booth_assignments.exhibitor_id',
                'booth_assignments.fair_code',
                'booth_assignments.tag',
                'booth_spaces.id as booth_space_id',
                'booth_spaces.name', 
                'booth_spaces.description',
                'booth_spaces.size',
                'booth_spaces.dimension',
                'booth_spaces.status',
                'booth_spaces.x', 
                'booth_spaces.y',
                'booth_spaces.start_x',
                'booth_spaces.start_y',
                'booth_spaces.cols',
                'booth_spaces.rows',
                'booth_spaces.width', 
                'booth_spaces.height', 
                'booth_spaces.color_inHex'
            )
            ->where('booth_assignments.fair_code', 'MFIO2026')
            ->get();

        // Debug line: uncomment this if you want to inspect what data is coming back
        // dd($booths->count(), $booths->take(5));

        return view('kiosk.map', compact('booths'));
    }
}