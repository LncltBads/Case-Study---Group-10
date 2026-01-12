<?php

class Profiler {
    private $timers = [];
    private $enabled = true;

    public function __construct($enabled = true) {
        $this->enabled = $enabled;
    }

    public function start($label) {
        if (!$this->enabled) return;
        
        $this->timers[$label] = [
            'start' => microtime(true),
            'memory_start' => memory_get_usage()
        ];
    }

    public function stop($label) {
        if (!$this->enabled || !isset($this->timers[$label])) return;

        $this->timers[$label]['end'] = microtime(true);
        $this->timers[$label]['memory_end'] = memory_get_usage();
        $this->timers[$label]['duration'] = 
            $this->timers[$label]['end'] - $this->timers[$label]['start'];
        $this->timers[$label]['memory_used'] = 
            $this->timers[$label]['memory_end'] - $this->timers[$label]['memory_start'];
    }

    public function report() {
        if (!$this->enabled) return;
        
        echo "\n" . str_repeat("=", 80) . "\n";
        echo "PERFORMANCE PROFILE\n";
        echo str_repeat("=", 80) . "\n";
        printf("%-35s %12s %15s\n", "Operation", "Time (s)", "Memory (MB)");
        echo str_repeat("-", 80) . "\n";

        $totalTime = 0;
        foreach ($this->timers as $label => $data) {
            if (isset($data['duration'])) {
                printf("%-35s %12.4f %15.2f\n", 
                    $label, 
                    $data['duration'],
                    $data['memory_used'] / 1024 / 1024
                );
                $totalTime += $data['duration'];
            }
        }
        
        echo str_repeat("-", 80) . "\n";
        printf("%-35s %12.4f\n", "TOTAL", $totalTime);
        echo str_repeat("=", 80) . "\n\n";
    }

    public function getTimers() {
        return $this->timers;
    }
}
?>
