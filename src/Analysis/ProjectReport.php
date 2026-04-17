<?php
namespace PhpScout\Analysis;

class ProjectReport {
    public int $totalFiles = 0;
    public int $phpFiles = 0;
    public int $totalLines = 0;
    
    // Core structure
    public array $classes = [];
    public array $dependencies = [];

    // Laravel Specific Metrics
    public bool $isLaravel = false;
    public array $laravelRoutes = [];
    public array $laravelMetrics = [
        'Controllers' => 0,
        'Models'      => 0,
        'Migrations'  => 0,
        'Seeders'     => 0,
        'Factories'   => 0,
        'Policies'    => 0,
        'Middleware'  => 0,
        'Jobs'        => 0,
        'Events'      => 0,
        'Listeners'   => 0,
        'Mailables'   => 0,
        'Commands'    => 0,
        'Services'    => 0,    // Custom folder detection
        'Repositories'=> 0     // Custom folder detection
    ];

    public function addFileStats(int $lines): void {
        $this->totalFiles++;
        $this->totalLines += $lines;
    }
}