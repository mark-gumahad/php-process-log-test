# PHP Log Processing Application

This is a PHP command-line application that processes log files according to specific formatting requirements. The application reads a log file, processes the data according to defined specifications, and generates an output file with formatted results.

## Requirements

- PHP 7.4 or higher (prefer version: 8.3.6)
- Composer (PHP package manager)

## Installation

1. Clone the repository:
    ```bash
    git clone https://github.com/mark-gumahad/php-process-log-test.git
    cd php-process-log-test
    ```

2. Install dependencies using Composer:
    ```bash
    composer install
    ```

## Project Structure
    - `process_logs.php` - Main script for processing log files
    - `sample-log.txt` - Sample input log file
    - `sample-output.txt` - Example of expected output format
    - `composer.json` - Project dependencies and configuration

## Usage

1. Place your input log file in the project directory (or use the sample-log.txt provided)

2. Run the script:
    ```bash
    php process_logs.php
    ```

The script will:
    - Process the input log file
    - Generate an output.txt file containing:
    - Pipe-delimited log entries
    - Sorted list of IDs
    - Sorted list of unique UserIDs with result IDs

## Output Format

The generated output.txt will contain:

1. Pipe-delimited log entries in the format:
    ```
    <UserID>|<BytesTX>|<BytesRX>|<DateTime>|<ID>
    ```

2. Sorted list of IDs in ascending order

3. Sorted list of unique UserIDs with result IDs in the format:
    ```
    [1] <UserID>
    ```

## Field Specifications

The log file contains the following fields:
- ID (Position: 1, Length: 12)
- UserID (Position: 13, Length: 6)
- BytesTX (Position: 19, Length: 8)
- BytesRX (Position: 27, Length: 8)
- DateTime (Position: 35, Length: 17)

## Formatting Rules

- Whitespaces are removed from field values
- BytesTX and BytesRX fields are formatted with commas as thousand separators
- DateTime field is formatted as: `Tue, 04 March 2025 02:03:20`
