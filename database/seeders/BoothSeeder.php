<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booth;
use PhpOffice\PhpSpreadsheet\IOFactory;

class BoothSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing records to prevent duplicates on re-seed
        Booth::truncate();

        // Path to your excel file in storage
        $filePath = storage_path('app/imports/booth.xlsx');

        if (!file_exists($filePath)) {
            $this->command->error("Excel file not found at: {$filePath}");
            return;
        }

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();

        $layouts = [];

        // 1. Process Merged Ranges first
        foreach ($sheet->getMergeCells() as $rangeString) {
            // Split range e.g. "D4:I4" into start and end
            [$startCell, $endCell] = explode(':', $rangeString);
            
            // Get the value from the top-left cell of the merged range
            $cellValue = $sheet->getCell($startCell)->getValue();

            if (!empty($cellValue)) {
                $layouts[] = [
                    'booth_code' => trim($cellValue),
                    'section' => 'Merged Block',
                    'start_cell' => $startCell,
                    'end_cell' => $endCell,
                    'is_merged' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Keep track of processed merged cells to avoid duplicates
        $mergedCoordinates = [];
        foreach ($sheet->getMergeCells() as $rangeString) {
            $cellIterator = $sheet->rangeToArray($rangeString, null, true, true, true);
            foreach ($cellIterator as $coord => $val) {
                $mergedCoordinates[] = $coord;
            }
        }

        // 2. Process Individual Non-Merged Cells
        for ($row = 1; $row <= $sheet->getHighestRow(); $row++) {
            for ($col = 1; $col <= \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestColumn()); $col++) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $coord = "{$colLetter}{$row}";
                
                $cellValue = $sheet->getCell($coord)->getValue();

                if (!empty($cellValue) && !in_array($coord, $mergedCoordinates)) {
                    $layouts[] = [
                        'booth_code' => trim($cellValue),
                        'section' => "Column {$colLetter}",
                        'start_cell' => $coord,
                        'end_cell' => null,
                        'is_merged' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        // Mass insert all layout objects safely in chunks
        foreach (array_chunk($layouts, 100) as $chunk) {
            Booth::insert($chunk);
        }

        $this->command->info("Successfully seeded " . count($layouts) . " booths/sections directly from booth.xlsx!");
    }
}