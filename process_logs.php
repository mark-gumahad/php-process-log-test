<?php

require_once __DIR__ . '/vendor/autoload.php';

class LogProcessor {
    private const ID_POSITION = 0;
    private const ID_LENGTH = 12;
    private const USER_ID_POSITION = 12;
    private const USER_ID_LENGTH = 6;
    private const BYTES_TX_POSITION = 18;
    private const BYTES_TX_LENGTH = 8;
    private const BYTES_RX_POSITION = 26;
    private const BYTES_RX_LENGTH = 8;
    private const DATETIME_POSITION = 34;
    private const DATETIME_LENGTH = 17;

    private array $processedData = [];
    private array $uniqueUserIds = [];
    private array $ids = [];

    public function processFile(string $inputFile, string $outputFile): void {
        if (!file_exists($inputFile)) {
            throw new RuntimeException("Input file not found: {$inputFile}");
        }

        $handle = fopen($inputFile, 'r');
        if ($handle === false) {
            throw new RuntimeException("Could not open input file: {$inputFile}");
        }

        while (($line = fgets($handle)) !== false) {
            $this->processLine($line);
        }

        fclose($handle);
        $this->generateOutput($outputFile);
    }

    private function processLine(string $line): void {
        $id = trim(substr($line, self::ID_POSITION, self::ID_LENGTH));
        $userId = trim(substr($line, self::USER_ID_POSITION, self::USER_ID_LENGTH));
        $bytesTx = $this->formatNumber(trim(substr($line, self::BYTES_TX_POSITION, self::BYTES_TX_LENGTH)));
        $bytesRx = $this->formatNumber(trim(substr($line, self::BYTES_RX_POSITION, self::BYTES_RX_LENGTH)));
        $datetime = $this->formatDateTime(trim(substr($line, self::DATETIME_POSITION, self::DATETIME_LENGTH)));

        $this->processedData[] = [
            'userId' => $userId,
            'bytesTx' => $bytesTx,
            'bytesRx' => $bytesRx,
            'datetime' => $datetime,
            'id' => $id
        ];

        if (!in_array($userId, $this->uniqueUserIds)) {
            $this->uniqueUserIds[] = $userId;
        }

        $this->ids[] = $id;
    }

    private function formatNumber(string $number): string {
        return number_format((int)$number, 0, '.', ',');
    }

    private function formatDateTime(string $datetime): string {
        $date = DateTime::createFromFormat('Y-m-d H:i', $datetime);
        if ($date === false) {
            throw new RuntimeException("Invalid datetime format: {$datetime}");
        }
        return $date->format('D, d F Y H:i:s');
    }

    private function generateOutput(string $outputFile): void {
        $output = [];

        foreach ($this->processedData as $entry) {
            $output[] = implode('|', [
                $entry['userId'],
                $entry['bytesTx'],
                $entry['bytesRx'],
                $entry['datetime'],
                $entry['id']
            ]);
        }

        $output[] = '';
        sort($this->ids, SORT_STRING);
        $output = array_merge($output, $this->ids);

        $output[] = '';
        sort($this->uniqueUserIds, SORT_STRING);
        foreach ($this->uniqueUserIds as $index => $userId) {
            $output[] = '[' . ($index + 1) . '] ' . $userId;
        }

        file_put_contents($outputFile, implode("\n", $output));
    }
}


try {
    $processor = new LogProcessor();
    $processor->processFile('sample-log.txt', 'output.txt');
    echo "Log processing completed successfully. Output saved to output.txt\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 
