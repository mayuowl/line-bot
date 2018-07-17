<?php
// sdkを読み込み
require_once __DIR__ . '/vendor/autoload.php';

//アクセストークンを使いCurlHTTPClientをインスタンス化
$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(getenv('LINE_CHANNEL_TOKEN'));

// CulHTTPClientとシークレットを使いLINEBotをインスタンス化
$bot = new \LINE\LINEBot($httpClient,['channelSecret' => getenv('LINE_CHANNEL_SECRET')]);

// LINE Messaging APIがリクエストに付与した署名を取得
$signature = $_SERVER['HTTP_' . \LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];

// 署名が正当かチェック。正当であればリクエストをパースし配列へ
$events = $bot->parseEventRequest(file_get_contents('php://input'),$signature);

// 配列に格納された各イベントをループで処理
foreach ($events as $event) {
    // テキストを返信し次のイベントの処理へ
    replyTextMessage($bot, $event->getReplyToken(), '頑張れ！');
}

/**
     * テキスト返信
     * @param  $bot         LINEBot
     *         $replyToken  返信先 
     *         $text        テキスト
     */
function replyTextMessage($bot, $replyToken, $text) {
    // 返信を行いレスポンスを取得
    // TextMessageBuilderの引数はテキスト
    $response = $bot->replyMessage($replyToken, new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($text));
    
    // レスポンスが異常な場合
    if (!$response->isSucceeded()) {
        // エラー内容を出力
        error_log('Failed!'.$response->getHTTPStatus.''.$response->getRawBody());
    }
}

?>