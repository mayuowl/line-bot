<?php
// sdkを読み込み
require_once __DIR__ . '/vendor/autoload.php';

use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\Constant\HTTPHeader;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\Event\MessageEvent;
use \LINE\LINEBot\Event\MessageEvent\TextMessage;


// クラスをインスタンス化
$bot = new LineBotClass();
// テキストを取得
$text = $bot->get_text();
// テキストメッセージを作成
$bot->add_text_builder($text);
// 返信実行
$bot->reply();


/**
 * ラインボットクラス
 */
class LineBotClass extends LINEBot
{
    private $bot;
    private $reply_token;
    private $events;
    private $builder_stok = array();

    function __construct($default=true)
    {
        $accessToken = "I4DUva3zqGl3FPqr+GczGkw5m7kozWBB+mV5IGQEgRix810iNudegfBB2Keps5Xin/hOM4IIe4DPAQXj7GFLS0rmx2coAxwJBjyyY+dEtJgs2pTbDKMVCgU83DHQYN8/Koh8g15HsmSaiAmmj+2dHAdB04t89/1O/w1cDnyilFU="; // アクセストークン
        $channelSecret = "7a03d02427cd3dcac4e9ee14d40605c5"; // シークレット

        // アクセストークンでCurlHTTPClientをインスタンス化
        $http_client = new CurlHTTPClient($accessToken);
        parent::__construct($http_client, ['channelSecret' => $channelSecret]);
        if ($default) {
            // LINEAPIが付与した署名を取得
            $signature = $_SERVER['HTTP_' . HTTPHeader::LINE_SIGNATURE];
            // 署名が正当かチェック、正当ならリクエストをパースし配列に代入
            $this->events = $this->parseEventRequest(file_get_contents('php://input'),$signature);
            foreach ($this->events as $key => $event) {
                // 返信トークン取得
                $this->reply_token = $event -> getReplyToken();
            }
        }
    }

    /**
     * TextMessageのテキストを取得
     * @return string テキスト
     */
    public function get_text()
    {
        foreach ($this->events as $key => $event) {
            // イベントがTextMessageのclassかチェック
            if ($event instanceof TextMessage) {
                return $event->getText();
            }else{
                error_log("テキストメッセージではありません");
                return false;
            }
        }
    }

    /**
     * テキストのビルダーを追加
     * @param  string $text 送信するテキストメッセージ
     * @return bool         成功ならtrue 失敗ならfalse
     */
    public function add_text_builder($text)
    {
        // ビルダーストックの数が既に５つ以上ならエラー
        if (count($this->builder_stok) >= 5) {
            error_log("一度に送信できるメッセージは5件までです");
            return false;
        }

        // 空チェック
        if (isset($text) && $text !== "") {
            $this->builder_stok[] = new TextMessageBuilder($text);
            return true;
        }else{
            error_log("テキストは必須です");
            return false;
        }
    }

    /**
     * 返信を実行
     * @return  成功ならtrue 失敗ならfalse
     *
     * 返信は一度しか行えません
     */
    public function reply()
    {
        if (count($this->builder_stok) > 0) {
            $builder = new MultiMessageBuilder();
            foreach ($this->builder_stok as $key => $row) {
                $builder -> add($row);
            }
            $response = $this -> replyMessage($this->reply_token, $builder);
            if ($response -> isSucceeded() == false) {
                error_log("深刻な返信エラー" . $response->getHTTPStatus() . ' ' . $response->getRawBody());
                return false;
            }else{
                return true;
            }
        }else{
            error_log("返信するビルダーがありません");
            return false;
        }
    }
}
?>