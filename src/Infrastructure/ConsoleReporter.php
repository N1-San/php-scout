<?php

namespace PhpScout\Infrastructure;

use PhpScout\Analysis\ProjectReport;

class ConsoleReporter {
    private array $options;

    public function __construct(array $options = []) {
        $this->options = $options;
    }

    public function render(ProjectReport $report): string {
        $output = "\n" . str_repeat("-", 45) . "\n";
        $output .= "🚀 PHP Scout: Codebase Analysis\n";
        $output .= str_repeat("-", 45) . "\n";

        $output .= sprintf("Laravel Detected: %s\n", $report->isLaravel ? "✅ YES" : "❌ NO");
        
        $output .= "\n[Filesystem]\n";
        $output .= sprintf("Total Files:     %d\n", $report->totalFiles);
        $output .= sprintf("PHP Files:       %d\n", $report->phpFiles);
        $output .= sprintf("Total Lines:     %s\n", number_format($report->totalLines));

        $output .= "\n[Structure]\n";
        $output .= sprintf("Classes Found:   %d\n", count($report->classes));
        
        $methodCount = 0;
        foreach ($report->classes as $methods) {
            $methodCount += count($methods);
        }
        $output .= sprintf("Total Methods:   %d\n", $methodCount);

        if (isset($this->options['verbose']) && !empty($report->classes)) {
            $output .= "\n[Class Map]\n";
            foreach (array_slice($report->classes, 0, 10) as $class => $methods) {
                $output .= " - $class (" . count($methods) . " methods)\n";
            }
            if (count($report->classes) > 10) {
                $output .= " ... and " . (count($report->classes) - 10) . " more.\n";
            }
        }

        $output .= str_repeat("-", 45) . "\n";

        return $output;
    }
}