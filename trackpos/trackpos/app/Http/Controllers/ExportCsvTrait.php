<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;

trait ExportCsvTrait
{
    /**
     * Export data to CSV
     */
    protected function exportToCsv($filename, $headers, $rows)
    {
        $csvData = [];
        
        // Add headers
        $csvData[] = $headers;
        
        // Add data rows
        foreach ($rows as $row) {
            $csvData[] = $row;
        }
        
        $callback = function() use ($csvData) {
            $handle = fopen('php://output', 'w');
            foreach ($csvData as $line) {
                fputcsv($handle, $line);
            }
            fclose($handle);
        };
        
        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
    
    /**
     * Format number for CSV (remove currency symbols, commas)
     */
    protected function formatForCsv($value)
    {
        if (is_numeric($value)) {
            return $value;
        }
        // Remove currency symbols and formatting
        return preg_replace('/[^0-9.-]/', '', $value);
    }
}