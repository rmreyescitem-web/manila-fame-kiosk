<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class BoothSeeder extends Seeder
{
    public function run(): void
    {
        Booth::truncate();

        $filePath = storage_path('app/imports/booth.xlsx');
        if (!file_exists($filePath)) {
            $this->command->error("Excel file not found at: {$filePath}");
            return;
        }

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

        $records = [];
        $processedCells = [];

        // 1. Process Merged Cells First
        foreach ($sheet->getMergeCells() as $range) {
            [$startCell, $endCell] = explode(':', $range);

            // Mark all cells inside the merged range as processed
            $rangeCells = Coordinate::extractAllCellReferencesInRange($range);
            foreach ($rangeCells as $c) {
                $processedCells[$c] = true;
            }

            // Fetch calculated value from the top-left cell of the merged block
            $cellValue = trim((string) $sheet->getCell($startCell)->getCalculatedValue());
            $upperVal = strtoupper($cellValue);

            // Classify content (handles both labeled and unlabeled merged blocks)
            [$type, $section, $cleanedCode] = $this->classifyCellContent($upperVal, $startCell, true);

            $records[] = [
                'booth_code' => $cleanedCode,
                'section'    => $section,
                'type'       => $type,
                'start_cell' => $startCell,
                'end_cell'   => $endCell,
                'is_merged'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // 2. Process Standard Single Cells
        for ($row = 1; $row <= $highestRow; $row++) {
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $colLetter = Coordinate::stringFromColumnIndex($col);
                $cellCoordinate = "{$colLetter}{$row}";

                if (isset($processedCells[$cellCoordinate])) {
                    continue;
                }

                // Fetch calculated value
                $cellValue = trim((string) $sheet->getCell($cellCoordinate)->getCalculatedValue());

                if ($cellValue !== '') {
                    $upperVal = strtoupper($cellValue);
                    [$type, $section, $cleanedCode] = $this->classifyCellContent($upperVal, $cellCoordinate, false);

                    $records[] = [
                        'booth_code' => $cleanedCode,
                        'section'    => $section,
                        'type'       => $type,
                        'start_cell' => $cellCoordinate,
                        'end_cell'   => $cellCoordinate,
                        'is_merged'  => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        // Insert records in chunks for optimum database performance
        foreach (array_chunk($records, 200) as $chunk) {
            Booth::insert($chunk);
        }

        $this->command->info("Successfully imported " . count($records) . " layout elements from booth.xlsx!");
    }

    /**
     * Classifies cell content into appropriate type, section, and booth code.
     */
    private function classifyCellContent(string $upperVal, string $cellCoordinate, bool $isMerged): array
    {
        $type = 'booth';
        $section = 'Exhibition Booth';
        $boothCodeValue = $upperVal;

        // If merged and empty, mark as walkway/aisle structure
        if ($upperVal === '') {
            return ['aisle', 'Walkway Aisle', ''];
        }

        // Explicit Booth Code Overrides (e.g., L-54, L-55, L-56, L-57)
        $explicitBooths = ['L-54', 'L-55', 'L-56', 'L-57'];
        if (in_array($upperVal, $explicitBooths)) {
            $colLetter = preg_replace('/[0-9]+/', '', $cellCoordinate);
            return ['booth', "Column {$colLetter}", $upperVal];
        }

        // Entrance Detection
        if (str_contains($upperVal, 'ENTRANCE') || str_contains($upperVal, 'MAIN ENTRANCE')) {
            $type = 'entrance';
            $section = 'Main Entrance';
            $boothCodeValue = $upperVal !== '' ? $upperVal : 'MAIN ENTRANCE';
        } 
        // Structural Wall & Safety Exit Detection
        elseif (str_contains($upperVal, 'WALL')) {
            $type = 'wall';
            $section = 'Structure';
            $boothCodeValue = ''; 
        } 
        elseif (str_contains($upperVal, 'FIRE EXIT') || str_contains($upperVal, 'EXIT')) {
            $type = 'facility';
            $section = 'Safety & Exit';
        }
        // Walkway & Aisle Detection
        elseif (str_contains($upperVal, 'AISLE') || str_contains($upperVal, 'CORRIDOR') || str_contains($upperVal, 'WALKWAY')) {
            $type = 'aisle';
            $section = 'Walkway Aisle';
            $boothCodeValue = '';
        } 
        // Artisans Village Sections
        elseif (str_contains($upperVal, 'ARTISANS VILLAGE')) {
            $type = 'facility';
            $section = 'Artisans Village';
        }
        // Food, Refreshments & Concessions
        elseif ($this->anyMatch($upperVal, ['CONCESSIONAIRE', 'FOOD KIOSK', 'FOOD', 'DINING', 'CAFETERIA'])) {
            $type = 'facility';
            $section = 'Food & Dining Zone';
        }
        // Stage, Seminars & Activity Hubs
        elseif ($this->anyMatch($upperVal, ['FAME TALKS', 'TALKS', 'STAGE', 'SEMINAR'])) {
            $type = 'facility';
            $section = 'Stage & Event Zone';
        }
        // Lounge & Media Studios
        elseif ($this->anyMatch($upperVal, ['LOUNGE STUDIO', 'STUDIO BG', 'LOUNGE', 'VIP LOUNGE', 'EXHIBITOR LOUNGE'])) {
            $type = 'facility';
            $section = 'Lounge & Studio Zone';
        }
        // Special Event Facilities, Brands & Feature Pavilions
        elseif ($this->anyMatch($upperVal, [
            'BAY', 'ROOM', 'FAME', 'COMMUNE', 'VILLAGE', 'FILIPINO', 
            'COMPONENTS', 'FASHION', 'SETTING', 'DCP', 'OBC', 'CREATE LAB', 
            'LUMI CANDLES', 'LIKHANG FILIPINO'
        ])) {
            $type = 'facility';
            $section = 'Feature Area';
        } 
        // Standard Exhibition Booths
        else {
            $colLetter = preg_replace('/[0-9]+/', '', $cellCoordinate);
            $section = "Column {$colLetter}";
        }

        return [$type, $section, $boothCodeValue];
    }

    /**
     * Helper to check if string contains any item from array
     */
    private function anyMatch(string $string, array $array): bool 
    {
        foreach ($array as $val) {
            if (str_contains($string, $val)) {
                return true;
            }
        }
        return false;
    }
}