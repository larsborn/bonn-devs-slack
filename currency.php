<?php
$config = include('config.php');
$command = $_POST['command'];
$text = $_POST['text'];
$token = $_POST['token'];
$sender = $_POST['user_name'];

if($token !== $config['currencyToken']) exit();

$exp = explode(' ', $text);
if (count($exp) == 2) {
    list($amount, $from) = $exp;
    $amount = floatval($amount);
    $exchanges = json_decode(file_get_contents(__DIR__ . '/exchanges/exchange.json'), $assoc = true);
    $rates = $exchanges['rates'];
    foreach(['btc', 'etc', 'eth'] as $filename) {
        $json = json_decode(file_get_contents(__DIR__ . '/exchanges/' . $filename . '.json'), $assoc = true);
        $rates[$json['ticker']['base']] = 1/floatval($json['ticker']['price']);
    }
    if ($from === 'EUR') {
        $message_text = sprintf('Today\'s smartarse award geht an %s: ein Euro ist ein Euro!', $sender);
    }
    elseif (isset($rates[$from])) {
        $result = $amount / $rates[$from];
        $message_text = sprintf('@%s: %s %s entspricht %s EUR', $sender, number_format($amount, 2, ',', '.'), $from, number_format($result, 2, ',', '.'));
    }
    else {
        $message_text = sprintf('@%s: Leider kenne ich die Währung %s nicht. @larsborn kann vielleicht helfen', $sender, $from);
    }
}
else {
    $message_text = sprintf('Ich habe dich nicht verstanden @%s, bitte gibt etwas in der art "123 usd" an', $sender);
}

$data = array(
    "icon_emoji" => ":moneybag:",
    "username" => "MoneyBot",
    "channel" => $_POST['channel_id'],
    "response_type" => "in_channel",
    "text" => $message_text,
);
$json_string = json_encode($data);
header("Content-Type: application/json"); 
echo json_encode($data);
