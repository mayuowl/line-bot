<?php
 
$accessToken = 'I4DUva3zqGl3FPqr+GczGkw5m7kozWBB+mV5IGQEgRix810iNudegfBB2Keps5Xin/hOM4IIe4DPAQXj7GFLS0rmx2coAxwJBjyyY+dEtJgs2pTbDKMVCgU83DHQYN8/Koh8g15HsmSaiAmmj+2dHAdB04t89/1O/w1cDnyilFU=';
 
//ユーザーからのメッセージ取得
$json_string = file_get_contents('php://input');
$json_object = json_decode($json_string);
 
//取得データ
$replyToken   = $json_object->{"events"}[0]->{"replyToken"};         //返信用トークン
$message_type = $json_object->{"events"}[0]->{"message"}->{"type"};  //メッセージタイプ
$message_text = $json_object->{"events"}[0]->{"message"}->{"text"};  //メッセージ内容
 
//メッセージタイプが「text」以外のときは何も返さず終了
if($message_type != "text") exit;
 
//返信メッセージ
$return_message_text = "下を向いていたら、虹を見つけることは出来ないよ。";
 
//返信実行
sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
?>

<?php
//メッセージの送信
function sending_messages($accessToken, $replyToken, $message_type, $return_message_text){
    //レスポンスフォーマット
    $response_format_text = [
        "type" => $message_type,
        "text" => $return_message_text
    ];
 
    //ポストデータ
    $post_data = [
        "replyToken" => $replyToken,
        "messages" => [$response_format_text]
    ];
 
    //curl実行
    $ch = curl_init("https://api.line.me/v2/bot/message/reply");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charser=UTF-8',
        'Authorization: Bearer ' . $accessToken
    ));
    $result = curl_exec($ch);
    curl_close($ch);
}
?>