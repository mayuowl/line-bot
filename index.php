<?php
// HTTPヘッダを設定
$channelToken = 'I4DUva3zqGl3FPqr+GczGkw5m7kozWBB+mV5IGQEgRix810iNudegfBB2Keps5Xin/hOM4IIe4DPAQXj7GFLS0rmx2coAxwJBjyyY+dEtJgs2pTbDKMVCgU83DHQYN8/Koh8g15HsmSaiAmmj+2dHAdB04t89/1O/w1cDnyilFU=';
$headers = [
	'Authorization: Bearer ' . $channelToken,
	'Content-Type: application/json; charset=utf-8',
];

// POSTデータを設定してJSONにエンコード
$post = [
	'to' => 'メモした Your userId の文字列',
	'messages' => [
		[
			'type' => 'text',
			'text' => 'hello world',
		],
	],
];
$post = json_encode($post);

// HTTPリクエストを設定
$ch = curl_init('https://api.line.me/v2/bot/message/push');
$options = [
	CURLOPT_CUSTOMREQUEST => 'POST',
	CURLOPT_HTTPHEADER => $headers,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_BINARYTRANSFER => true,
	CURLOPT_HEADER => true,
	CURLOPT_POSTFIELDS => $post,
];
curl_setopt_array($ch, $options);

// 実行
$result = curl_exec($ch);

// エラーチェック
$errno = curl_errno($ch);
if ($errno) {
	return;
}

// HTTPステータスを取得
$info = curl_getinfo($ch);
$httpStatus = $info['http_code'];

$responseHeaderSize = $info['header_size'];
$body = substr($result, $responseHeaderSize);

// 200 だったら OK
echo $httpStatus . ' ' . $body;