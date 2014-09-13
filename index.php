<?php
require('config.php');

$requestPath = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$format = $requestPath[0];
$username = $requestPath[1];

$dbh = new PDO(
    "{$config['database']['type']}:host={$config['database']['hostname']};dbname={$config['database']['database']};charset=utf8",
    $config['database']['username'],
    $config['database']['password']
);

$query = <<<QUERY
SELECT `keys`.`KEY_TEXT`
FROM
    `sta_normal_user` AS `user`
    LEFT JOIN `AO_FB71B4_SSH_PUBLIC_KEY` AS `keys` ON `user`.`user_id` = `keys`.`user_id`
WHERE `user`.`name` = :username
LIMIT 0,50;
QUERY;

if ($stmt = $dbh->prepare($query)) {
    $stmt->bindParam(":username", $username, PDO::PARAM_STR);
    $stmt->execute();
    $stmt->bindColumn(1, $key);

    $buffer = [];

    while ($row = $stmt->fetch(PDO::FETCH_BOUND)) {
        $buffer[] = trim($key);
    }
    $stmt->closeCursor();

    if ($format == 'json') {
        header('Content-Type: application/json');
        $obj_buffer = [];
        foreach ($buffer as $key => $value) {
            $obj_buffer[] = [
                'id' => $key + 1,
                'key' => $value
            ];
        }
        echo json_encode($obj_buffer) . "\n";
    } else {
        header('Content-Type: text/plain');
        echo join(array_values($buffer), "\n") . "\n";
    }

}
