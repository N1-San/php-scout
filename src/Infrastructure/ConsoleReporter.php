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

        $output .= "\n[Top Dependencies (External/Internal)]\n";
        $allDeps = [];
        foreach ($report->dependencies as $deps) {
            foreach ($deps as $d) {
                $allDeps[$d] = ($allDeps[$d] ?? 0) + 1;
            }
        }
        arsort($allDeps); // Sort by most used
        foreach (array_slice($allDeps, 0, 10) as $name => $count) {
            $output .= sprintf(" - %-40s (%d hits)\n", $name, $count);
        }

        if ($report->isLaravel && !empty($report->laravelRoutes)) {
            $output .= "\n[Laravel Routes (Sample)]\n";
            foreach (array_slice($report->laravelRoutes, 0, 5) as $route) {
                $output .= sprintf(" %-6s | %-20s -> %s\n", 
                    $route['http_method'], 
                    $route['uri'], 
                    $route['action']
                );
            }
        }

        if ($report->isLaravel) {
            $output .= "\n[Laravel Components]\n";
            $metrics = $report->laravelMetrics;
            
            // Chunk the metrics into two columns for a cleaner CLI look
            $chunks = array_chunk(array_keys($metrics), 2);
            foreach ($chunks as $pair) {
                $left = $pair[0];
                $right = $pair[1] ?? '';
                
                $output .= sprintf(
                    " %-15s: %-5d | %-15s: %-5d\n",
                    $left, $metrics[$left],
                    $right, ($right ? $metrics[$right] : 0)
                );
            }
        }

        return $output;
    }
}