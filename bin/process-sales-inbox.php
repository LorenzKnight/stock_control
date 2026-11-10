<?php
declare(strict_types=1);

use App\AiSales\SalesMailboxClient;
use App\AiSales\SalesMailboxProcessor;
use App\AiSales\SalesMessageRepository;
use App\AiSales\SalesConversationRepository;
use App\AiSales\SalesLeadRepository;
use App\AiSales\SalesContactRepository;
use App\AiSales\SalesMessageService;

chdir(__DIR__ . '/../public/api');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../public/inc/env.php';
require_once __DIR__ . '/../public/logic/stock_be.php';


$lockFile = fopen(
    sys_get_temp_dir() . '/allstockcontrol-ai-sales-inbox.lock',
    'c'
);

if ($lockFile === false) {
    throw new RuntimeException(
        'Could not open AI Sales inbox lock file.'
    );
}

if (!flock($lockFile, LOCK_EX | LOCK_NB)) {
    echo "AI Sales inbox synchronization already running." .
        PHP_EOL;
    fclose($lockFile);
    exit(0);
}


try {
    $messages = new SalesMessageRepository();
    $conversations = new SalesConversationRepository();
    $leads = new SalesLeadRepository();
    $contacts = new SalesContactRepository();

    $service = new SalesMessageService(
        $messages,
        $conversations,
        $leads
    );

    $processor = new SalesMailboxProcessor(
        new SalesMailboxClient(),
        $messages,
        $conversations,
        $contacts,
        $service
    );

    $dryRun = in_array(
        '--dry-run',
        $argv,
        true
    );

    $result = $processor->process(
        50,
        $dryRun
    );

    echo json_encode(
        $result,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    ) . PHP_EOL;

    if (!empty($result['errors'])) {
        exit(1);
    }

    exit(0);

} catch (Throwable $e) {
    error_log(
        '[AI Sales Inbox] ' . $e->getMessage()
    );

    exit(1);
}