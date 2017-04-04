<?php
$config = include('config.php');
$command = $_POST['command'];
$text = $_POST['text'];
$token = $_POST['token'];

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
    if (isset($rates[$from])) {
        $result = $amount / $rates[$from];
        $message_text = sprintf('%s %s entspricht %s EUR', number_format($amount, 2, ',', '.'), $from, number_format($result, 2, ',', '.'));
    }
    else {
        $message_text = sprintf('Leider kenne ich die Währung %s nicht. @larsborn kann vielleicht helfen', $from);
    }
}
else {
    $message_text = 'Ich habe dich nicht verstanden, bitte gibt etwas in der art "123 usd" an';
}

$data = array(
    "icon_emoji" => ":moneybag:",
    "username" => "MoneyBot",
    "channel" => $_POST['channel_id'],
    "text" => $message_text,
);
$json_string = json_encode($data);
$slack_call = curl_init($config['webhookUrl']);
curl_setopt($slack_call, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($slack_call, CURLOPT_POSTFIELDS, $json_string);
curl_setopt($slack_call, CURLOPT_CRLF, true);
curl_setopt($slack_call, CURLOPT_RETURNTRANSFER, true);
curl_setopt($slack_call, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Content-Length: " . strlen($json_string)
]);
$result = curl_exec($slack_call);
curl_close($slack_call);
