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
            $cellValue = trim((string) $sheet->getCell($startCell)->getValue());

            if ($cellValue !== '') {
                $upperVal = strtoupper($cellValue);
                [$type, $section, $cleanedCode] = $this->classifyCellContent($upperVal, $startCell);

                $records[] = [
                    'booth_code' => $cleanedCode,
                    'section' => $section,
                    'type' => $type,
                    'start_cell' => $startCell,
                    'end_cell' => $endCell,
                    'is_merged' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $rangeCells = [];
                Coordinate::extractAllCellReferencesInRange($range, $rangeCells);
                foreach ($rangeCells as $c) {
                    $processedCells[$c] = true;
                }
            }
        }

        // 2. Process Standard Single Cells
        for ($row = 1; $row <= $highestRow; $row++) {
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $colLetter = Coordinate::stringFromColumnIndex($col);
                $cellCoordinate = "{$colLetter}{$row}";

                if (isset($processedCells[$cellCoordinate])) {
                    continue;
                }

                $cellValue = trim((string) $sheet->getCell($cellCoordinate)->getValue());

                if ($cellValue !== '') {
                    $upperVal = strtoupper($cellValue);
                    [$type, $section, $cleanedCode] = $this->classifyCellContent($upperVal, $cellCoordinate);

                    $records[] = [
                        'booth_code' => $cleanedCode,
                        'section' => $section,
                        'type' => $type,
                        'start_cell' => $cellCoordinate,
                        'end_cell' => null,
                        'is_merged' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        // Insert in chunks for performance
        foreach (array_chunk($records, 200) as $chunk) {
            Booth::insert($chunk);
        }

        $this->command->info("Successfully imported " . count($records) . " layout elements from booth.xlsx!");
    }

    private function classifyCellContent(string $upperVal, string $cellCoordinate): array
    {
        $type = 'booth';
        $section = 'Exhibition Booth';
        $boothCodeValue = $upperVal;

        if (str_contains($upperVal, 'WALL')) {
            $type = 'wall';
            $section = 'Structure';
            $boothCodeValue = ''; 
        } elseif (str_contains($upperVal, 'AISLE') || str_contains($upperVal, 'CORRIDOR') || str_contains($upperVal, 'WALKWAY')) {
            $type = 'aisle';
            $section = 'Walkway Aisle';
            $boothCodeValue = ''; // Clear text so it acts as an open grid node without cluttering UI
        } elseif ($this->anyMatch($upperVal, ['BAY', 'ROOM', 'LOUNGE', 'FAME', 'COMMUNE', 'EXHIBITOR', 'VILLAGE', 'FILIPINO', 'COMPONENTS', 'FASHION', 'SETTING', 'DCP', 'ENTRANCE', 'OBC'])) {
            $type = 'facility';
            $section = 'Feature Area';
        } else {
            $colLetter = preg_replace('/[0-9]+/', '', $cellCoordinate);
            $section = "Column {$colLetter}";
        }

        return [$type, $section, $boothCodeValue];
    }

    private function anyMatch(string $string, array $array): bool {
        foreach ($array as $val) {
            if (str_contains($string, $val)) return true;
        }
        return false;
    }
}