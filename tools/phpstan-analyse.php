<?php

declare(strict_types=1);

$command = [
    PHP_BINARY,
    __DIR__.'/../vendor/phpstan/phpstan/phpstan',
    'analyse',
    '--memory-limit=512M',
];

$passthroughArgs = array_slice($_SERVER['argv'], 1);

foreach ($passthroughArgs as $arg) {
    $command[] = $arg;
}

$descriptorSpec = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];

$process = proc_open($command, $descriptorSpec, $pipes, dirname(__DIR__));

if (! is_resource($process)) {
    fwrite(STDERR, "Failed to start PHPStan.\n");

    exit(1);
}

fclose($pipes[0]);

$filterNoise = static function (string $buffer): string {
    $lines = preg_split('/\\r\\n|\\n|\\r/', $buffer);

    if ($lines === false) {
        return $buffer;
    }

    $filtered = [];

    foreach ($lines as $line) {
        if ($line === 'Cannot create a file when that file already exists.') {
            continue;
        }

        $filtered[] = $line;
    }

    return implode(PHP_EOL, $filtered);
};

$stdout = stream_get_contents($pipes[1]);
$stderr = stream_get_contents($pipes[2]);

fclose($pipes[1]);
fclose($pipes[2]);

if ($stdout !== false) {
    $filteredStdout = $filterNoise($stdout);

    if ($filteredStdout !== '') {
        fwrite(STDOUT, $filteredStdout);

        if (! str_ends_with($filteredStdout, PHP_EOL)) {
            fwrite(STDOUT, PHP_EOL);
        }
    }
}

if ($stderr !== false) {
    $filteredStderr = $filterNoise($stderr);

    if ($filteredStderr !== '') {
        fwrite(STDERR, $filteredStderr);

        if (! str_ends_with($filteredStderr, PHP_EOL)) {
            fwrite(STDERR, PHP_EOL);
        }
    }
}

$exitCode = proc_close($process);

exit(is_int($exitCode) ? $exitCode : 1);
