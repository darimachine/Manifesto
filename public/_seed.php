<?php
$EXPECTED_TOKEN = 'serhi-seed';

if (($_GET['token'] ?? '') !== $EXPECTED_TOKEN) {
    http_response_code(403);
    exit('forbidden');
}

header('Content-Type: text/plain; charset=utf-8');

$host = getenv('DB_HOST') ?: '';
$port = getenv('DB_PORT') ?: '3306';
$db   = getenv('DB_NAME') ?: '';
$user = getenv('DB_USER') ?: '';
$pass = getenv('DB_PASS') ?: '';

if ($host === '' || $db === '' || $user === '') {
    echo "ERROR: липсват DB_* env променливи.\n";
    echo "DB_HOST={$host}\nDB_PORT={$port}\nDB_NAME={$db}\nDB_USER={$user}\n";
    echo "Провери в HSS Edit Container че те са с тип ENV (не ARG).\n";
    exit(1);
}

echo "Connecting to {$host}:{$port}/{$db} as {$user}\n";

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES   => true,
            PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
        ]
    );
} catch (Throwable $e) {
    echo "CONNECT FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Connected.\n\n";

$root = dirname(__DIR__);

foreach (['schema.sql', 'seed.sql'] as $file) {
    $path = $root . '/db/' . $file;

    if (!is_file($path)) {
        echo "SKIP: {$path} не съществува\n";
        continue;
    }

    $sql = file_get_contents($path);
    if ($sql === '' || $sql === false) {
        echo "SKIP: {$file} е празен\n";
        continue;
    }

    // schema.sql/seed.sql имат hardcoded `CREATE DATABASE manifesto` и
    // `USE manifesto`. На HSS user-а няма права върху име 'manifesto' —
    // вече сме закачени за {$db} през DSN-а, така че тези редове не ни трябват.
    $sql = preg_replace('/^\s*CREATE\s+DATABASE[^;]*;/im', '', $sql);
    $sql = preg_replace('/^\s*USE\s+[^;]*;/im',           '', $sql);

    echo "Running {$file} ... ";

    try {
        // Multi-statement execution
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        // Изчерпай всички result sets за да се освободи връзката
        do {
            $stmt->fetchAll(PDO::FETCH_ASSOC);
        } while ($stmt->nextRowset());

        echo "OK\n";
    } catch (Throwable $e) {
        echo "FAILED\n  " . $e->getMessage() . "\n";
        exit(1);
    }
}

echo "\nDone. !!! DELETE public/_seed.php NOW !!!\n";
