<?php
namespace PhpScout\Service;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use PhpScout\Analysis\ProjectReport;
use PhpScout\Parsers\StructureVisitor;
use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;

class AnalyzerEngine {
    private string $root;
    private $parser;
    // Define folders we should never look into
    private const IGNORED_DIRS = ['.git', 'vendor', 'node_modules', 'storage'];

    public function __construct(string $path) {
        $this->root = realpath($path);
        $this->parser = (new ParserFactory())->createForNewestSupportedVersion();
    }

    public function run(): ProjectReport {
        $report = new ProjectReport();
        $this->detectLaravel($report);

        if ($report->isLaravel) {
            $this->parseLaravelRoutes($report);
        }
        $directory = new RecursiveDirectoryIterator($this->root, RecursiveDirectoryIterator::SKIP_DOTS);
        
        // Filter out the junk before we even start iterating
        $iterator = new RecursiveIteratorIterator(
            new \RecursiveCallbackFilterIterator($directory, function ($current, $key, $iterator) {
                if ($iterator->hasChildren() && in_array($current->getFilename(), self::IGNORED_DIRS)) {
                    return false;
                }
                return true;
            })
        );
        
        foreach ($iterator as $file) {
            // Skip directories, non-readable files, or broken symlinks
            if ($file->isDir() || !$file->isReadable() || $file->isLink()) {
                continue;
            }
            
            $lineCount = $this->countLines($file->getPathname());
            // if ($file->isDir()) continue;
            $filePath = $file->getPathname();
            $report->addFileStats($this->countLines($filePath));
            // Use our new memory-safe line counter
            $lineCount = $this->countLines($file->getPathname());
            $report->addFileStats($lineCount);
            
            if ($file->getExtension() === 'php') {
                $report->phpFiles++;
                if ($report->isLaravel) {
                    $this->classifyLaravelFile($filePath, $report);
                }
                $this->analyzePhpFile($file->getPathname(), $report);
            }
        }

        return $report;
    }

    /**
     * Memory-efficient line counting using a stream buffer
     */
    private function countLines(string $filePath): int {
        $lineCount = 0;
        $handle = fopen($filePath, "r");
        if ($handle) {
            while (!feof($handle)) {
                fgets($handle);
                $lineCount++;
            }
            fclose($handle);
        }
        return $lineCount;
    }

    private function analyzePhpFile(string $path, ProjectReport $report): void {
        try {
            $code = file_get_contents($path);
            if (strlen($code) > 1024 * 1024) return;

            $stmts = $this->parser->parse($code);
            
            $traverser = new NodeTraverser();
            
            // 1. Add NameResolver first to populate namespacedName
            $traverser->addVisitor(new NameResolver()); 
            
            // 2. Then add your custom visitor
            $visitor = new StructureVisitor();
            $traverser->addVisitor($visitor);
            
            $traverser->traverse($stmts);

            foreach ($visitor->classes as $class) {
                $report->classes[$class['name']] = $class['methods'];
                $report->dependencies[$class['name']] = $visitor->imports;
            }
        } catch (\Exception $e) {
            // Log or skip
        }
    }

    private function detectLaravel(ProjectReport $report): void {
        $report->isLaravel = file_exists($this->root . '/artisan');
    }

    private function parseLaravelRoutes(ProjectReport $report): void {
        $routeFiles = ['/routes/web.php', '/routes/api.php'];
        
        foreach ($routeFiles as $file) {
            $path = $this->root . $file;
            if (!file_exists($path)) continue;

            $code = file_get_contents($path);
            $stmts = $this->parser->parse($code);

            $traverser = new \PhpParser\NodeTraverser();
            $visitor = new \PhpScout\Parsers\LaravelRouteVisitor();
            $traverser->addVisitor($visitor);
            $traverser->traverse($stmts);

            $report->laravelRoutes = array_merge($report->laravelRoutes, $visitor->routes);
        }
    }

    private function classifyLaravelFile(string $path, ProjectReport $report): void {
        $map = [
            'app/Http/Controllers' => 'Controllers',
            'app/Models'           => 'Models',
            'database/migrations'  => 'Migrations',
            'database/seeders'     => 'Seeders',
            'database/factories'   => 'Factories',
            'app/Http/Middleware'  => 'Middleware',
            'app/Jobs'             => 'Jobs',
            'app/Events'           => 'Events',
            'app/Listeners'        => 'Listeners',
            'app/Mail'             => 'Mailables',
            'app/Policies'         => 'Policies',
            'app/Console/Commands' => 'Commands',
            'app/Services'         => 'Services',
            'app/Repositories'     => 'Repositories',
        ];

        foreach ($map as $dir => $key) {
            if (str_contains($path, $dir)) {
                $report->laravelMetrics[$key]++;
                return;
            }
        }
    }
}