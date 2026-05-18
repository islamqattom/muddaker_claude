<?php

namespace App\Console\Commands;

use App\Models\TajweedRule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ImportTajweedRules extends Command
{
    protected $signature = 'tajweed:import
                            {file? : Path to JSON file with tajweed rules}
                            {--reset : Clear existing rules before import}';

    protected $description = 'Import Tajweed rules from QUL JSON format or seed basic rules';

    public function handle(): int
    {
        if ($this->option('reset')) {
            TajweedRule::query()->delete();
            $this->info('Cleared existing Tajweed rules.');
        }

        $filePath = $this->argument('file');

        if ($filePath) {
            return $this->importFromFile($filePath);
        }

        $this->info('No file provided. Use --reset to clear or provide a JSON file path.');
        $this->info('Expected JSON format:');
        $this->line('[{"surah_number":1,"ayah_number":1,"word_position":1,"rule_type":"ghunna","start_char":0,"end_char":1,"rule_color":"#FF7F50"},...]');

        return 0;
    }

    private function importFromFile(string $filePath): int
    {
        $fullPath = base_path($filePath);
        if (!file_exists($fullPath)) {
            $fullPath = $filePath;
            if (!file_exists($fullPath)) {
                $this->error("File not found: {$filePath}");
                return 1;
            }
        }

        $content = file_get_contents($fullPath);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Invalid JSON: ' . json_last_error_msg());
            return 1;
        }

        if (!is_array($data)) {
            $this->error('JSON must be an array of rule objects');
            return 1;
        }

        $colors = TajweedRule::getDefaultColors();
        $count = 0;

        foreach ($data as $rule) {
            try {
                $ruleType = $rule['rule_type'] ?? $rule['tajweed_rule'] ?? null;
                if (!$ruleType) continue;

                TajweedRule::create([
                    'surah_number' => (int) ($rule['surah_number'] ?? $rule['surah'] ?? 0),
                    'ayah_number' => (int) ($rule['ayah_number'] ?? $rule['ayah'] ?? 0),
                    'word_position' => (int) ($rule['word_position'] ?? $rule['word'] ?? 0),
                    'rule_type' => $ruleType,
                    'start_char' => $rule['start_char'] ?? null,
                    'end_char' => $rule['end_char'] ?? null,
                    'rule_color' => $rule['rule_color'] ?? $colors[$ruleType] ?? '#666666',
                ]);
                $count++;
            } catch (\Exception $e) {
                Log::warning('Failed to import tajweed rule', ['rule' => $rule, 'error' => $e->getMessage()]);
            }
        }

        $this->info("Imported {$count} Tajweed rules successfully.");
        return 0;
    }
}
