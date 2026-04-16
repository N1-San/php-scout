<?php

namespace PhpScout\Infrastructure;

use PhpScout\Analysis\ProjectReport;

class JsonReporter {
    public function render(ProjectReport $report): string {
        return json_encode([
            'files' => [
                'total' => $report->totalFiles,
                'php' => $report->phpFiles,
                'lines' => $report->totalLines,
            ],
            'structure' => [
                'classes' => count($report->classes),
                'is_laravel' => $report->isLaravel
            ],
            'class_map' => $report->classes,
            'dependencies' => $report->dependencies
        ], JSON_PRETTY_PRINT);
    }
}