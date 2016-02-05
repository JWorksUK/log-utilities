<?php

namespace JWorksUK\LogUtilities;
 
class LogUtilities {
    protected $logs;

    protected $findme = '/findme';

    protected $datetime;

    public $ignore = ['.','..','.DS_Store', 'logreader.php', 'exports'];

    public $newlines = [];

    public $newArray = [];


    public function __construct() {
        $this->findLogs();
        $this->datetime = date('Y-m-d-His');
        $this->createExportFolder();
    }

    public function init() {
        foreach ($this->logs as $log) {
            $this->processLog($log);
        }
        $this->orderNewLines();
        $this->createNewLog();
    }

    public function findLogs() {
        $logs = scandir('.');

        foreach ($logs as $log) {
            if (in_array($log, $this->ignore)) {
                continue;
            }

            $this->logs[] = $log;
        }
    }
    
    public function processLog($log) {
        echo $log."\n";

        $handle = fopen($log, "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $this->processLine($line);   
            }
            fclose($handle);
        } else {
            // error opening the file.
        } 
    }

    public function processLine($line) {
        $pos = strpos($line, $this->findme);
        if ($pos === false) {
        } else {
            $this->newlines[] = $line;
        }
    }

    public function createExportFolder() {
        if (!file_exists('exports')) {
            mkdir('exports', 0777, true);
        }
    }

    public function createNewLog() {
        $newLog = "exports/".$this->datetime."-log.txt";
        $file = fopen($newLog, "w");

        foreach ($this->newArray as $line) {
            fwrite($file, $line);
        }

        fclose($file);
    }

    public function orderNewLines() {
        $expr = '/\d{2}\/(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\/\d{4}:\d{2}:\d{2}:\d{2} (?:-|\+)\d{4}/';

        foreach ($this->newlines as $line) {
            preg_match($expr ,$line, $matches);

            if (isset($matches[0])) {
                if (
                    strtotime($matches[0]) > strtotime('2016-01-01 00:00:00') &&
                    strtotime($matches[0]) < strtotime('2016-02-01 23:59:59')

                ) {
                    // echo date('Y-m-d H:i:s', strtotime($matches[0]))."\n";
                    $this->newArray[date('U', strtotime($matches[0]))] = $line;
                }
            }
        }

        ksort($this->newArray);
    }
}
