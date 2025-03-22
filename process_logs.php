<?php

require_once __DIR__ . '/vendor/autoload.php';

class LogProcessor {
    // Define constant variables for the log file format
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

    // Define constant variables for the log file path and output file path
    private const LOG_FILE_PATH = 'sample-log.txt';
    private const OUTPUT_FILE_PATH = 'output.txt';

    // Initialize arrays to store processed data, unique UserIDs, and IDs
    private array $processedData = [];
    private array $uniqueUserIds = [];
    private array $ids = [];

    // Process the log file and generate the output
    public function processFile(string $inputFile, string $outputFile): void {
        // Check if the input file exists
        if (!file_exists($inputFile)) {
            throw new RuntimeException("Input file not found: {$inputFile}");
        }

        // Open the input file for reading
        $handle = fopen($inputFile, 'r');
        if ($handle === false) {
            throw new RuntimeException("Could not open input file: {$inputFile}");
        }

        // Read each line of the log file
        while (($line = fgets($handle)) !== false) {
            $this->processLine($line);
        }

        // Close the input file
        fclose($handle);

        // Generate the output file
        $this->generateOutput($outputFile);
    }

    // Process each line of the log file
    private function processLine(string $line): void {
        // Extract the ID from the line
        $id = trim(substr($line, self::ID_POSITION, self::ID_LENGTH));

        // Extract the UserID from the line
        $userId = trim(substr($line, self::USER_ID_POSITION, self::USER_ID_LENGTH));

        // Format the BytesTX and BytesRX numbers with commas as thousands separators
        $bytesTx = $this->formatNumber(trim(substr($line, self::BYTES_TX_POSITION, self::BYTES_TX_LENGTH)));
        $bytesRx = $this->formatNumber(trim(substr($line, self::BYTES_RX_POSITION, self::BYTES_RX_LENGTH)));
        $datetime = $this->formatDateTime(trim(substr($line, self::DATETIME_POSITION, self::DATETIME_LENGTH)));

        // Add the processed data to the array
        $this->processedData[] = [
            'userId' => $userId,
            'bytesTx' => $bytesTx,
            'bytesRx' => $bytesRx,
            'datetime' => $datetime,
            'id' => $id
        ];

        // Add the UserID to the unique UserIDs array if it's not already present
        if (!in_array($userId, $this->uniqueUserIds)) {
            $this->uniqueUserIds[] = $userId;
        }

        // Add the ID to the IDs array
        $this->ids[] = $id;
    }

    // Format numbers with commas as thousands separators
    private function formatNumber(string $number): string {
        return number_format((int)$number, 0, '.', ',');
    }

    // Format the datetime string to a readable format
    private function formatDateTime(string $datetime): string {
        // Create a DateTime object from the datetime string
        $date = DateTime::createFromFormat('Y-m-d H:i', $datetime);
    
        // Check if the datetime string is invalid
        if ($date === false) {
            throw new RuntimeException("Invalid datetime format: {$datetime}");
        }
    
        // Format the DateTime object to the desired format: "F d Y, H:i"
        return $date->format('F d Y, H:i');
    }

    // Generate the output file with pipe-delimited entries
    private function generateOutput(string $outputFile): void {
        // Initialize the output array
        $output = [];

        // Add the processed data to the output array
        foreach ($this->processedData as $entry) {
            $output[] = implode('|', [
                $entry['userId'],
                $entry['bytesTx'],
                $entry['bytesRx'],
                $entry['datetime'],
                $entry['id']
            ]);
        }

        // Add an empty line to separate the processed data from the sorted IDs
        $output[] = '';

        // Sort the IDs array
        sort($this->ids, SORT_STRING);

        // Add the sorted IDs to the output array
        $output = array_merge($output, $this->ids);

        // Add an empty line to separate the processed data from the sorted UserIDs
        $output[] = '';

        // Sort the UserIDs array
        sort($this->uniqueUserIds, SORT_STRING);

        // Add the sorted UserIDs to the output array
        foreach ($this->uniqueUserIds as $index => $userId) {
            $output[] = '[' . ($index + 1) . '] ' . $userId;
        }

        // Write the output array to the output file
        file_put_contents($outputFile, implode("\n", $output));
    }
}

// Main script execution
try {
    $processor = new LogProcessor();
    $processor->processFile(LogProcessor::LOG_FILE_PATH, LogProcessor::OUTPUT_FILE_PATH);
    echo "Log processing completed successfully. Output saved to " . LogProcessor::OUTPUT_FILE_PATH . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 
