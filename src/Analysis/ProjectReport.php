<?php
namespace PhpScout\Analysis;

class ProjectReport {
    public int $totalFiles = 0;
    public int $phpFiles = 0;
    public int $totalLines = 0;
    public array $classes = [];
    public array $dependencies = [];
    public bool $isLaravel = false;
    public array $laravelRoutes = [];

    public function addFileStats(int $lines): void {
        $this->totalFiles++;
        $this->totalLines += $lines;
    }
}