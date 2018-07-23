<?php
require_once __DIR__ . '/vendor/autoload.php';

use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\Constant\HTTPHeader;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use \LINE\LINEBot\MessageBuilder\LocationMessageBuilder;
use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use \LINE\LINEBot\MessageBuilder\VideoMessageBuilder;
use \LINE\LINEBot\MessageBuilder\AudioMessageBuilder;
use \LINE\LINEBot\MessageBuilder\ImagemapMessageBuilder;
use \LINE\LINEBot\Event\MessageEvent;
use \LINE\LINEBot\Event\PostbackEvent;
use \LINE\LINEBot\Event\MessageEvent\TextMessage;
use \LINE\LINEBot\Event\MessageEvent\StickerMessage;
use \LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use \LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;
use \LINE\LINEBot\TemplateActionBuilder\DatetimePickerTemplateActionBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ImageCarouselColumnTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ImageCarouselTemplateBuilder;
use LINE\LINEBot\ImagemapActionBuilder\AreaBuilder;
use LINE\LINEBot\ImagemapActionBuilder\ImagemapMessageActionBuilder;
use LINE\LINEBot\ImagemapActionBuilder\ImagemapUriActionBuilder;
use LINE\LINEBot\MessageBuilder\Imagemap\BaseSizeBuilder;

/**
* liinbotのapiを使いやすくまとめたクラス
*/
class LineBotClass extends LINEBot
{
	
	private $bot;
	private $reply_token;
	private $events;
	private $builder_stok = array();
	private $error_stok = array();

	function __construct($default=true)
	{
		$accessToken = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(getenv('LINE_CHANNEL_TOKEN')); // アクセストークン
		$channelSecret = new \LINE\LINEBot($httpClient,['channelSecret' => getenv('LINE_CHANNEL_SECRET')]); // シークレット

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
				$this->set_error("テキストメッセージではありません");
				return false;
			}
		}
	}

	/**
	 * postされたdataを取得
	 * @return string 
	 */
	public function get_post_data()
	{
		foreach ($this->events as $key => $event) {
			// イベントがPostbackEventのclassかチェック
			if ($event instanceof PostbackEvent) {
				return $event->getPostbackData();
			}else{
				$this->set_error("ポストバックイベントではありません");
				return false;
			}
		}
	}

	/**
	 * postされたdeteの情報を取得
	 * @return array 
	 */
	public function get_post_params()
	{
		foreach ($this->events as $key => $event) {
			// イベントがPostbackEventのclassかチェック
			if ($event instanceof PostbackEvent) {
				return $event->getPostbackParams();
			}else{
				$this->set_error("ポストバックイベントではありません");
				return false;
			}
		}
	}

	/**
	 * スタンプのステッカーidとパッケージidを取得
	 * @return array 
	 */
	public function get_stamp_id()
	{
		foreach ($this->events as $key => $event) {
			// イベントがStickerMessageのclassかチェック
			if ($event instanceof StickerMessage) {
				$id_data = array();
				$id_data['sticker_id'] = $event->getStickerId();
				$id_data['package_id'] = $event->getPackageId();
				return $id_data;
			}else{
				$this->set_error("スタンプメッセージではありません");
				return false;
			}
		}
	}

	/**
	 * メッセージタイプを取得
	 * @return string メッセージタイプ
	 *
	 * text  テキスト
	 * image 画像
	 * video 動画
	 * audio 音声
	 * file  ファイル
	 */
	public function get_message_type()
	{
		foreach ($this->events as $key => $event) {
			if ($event instanceof MessageEvent) {
				return $event->getMessageType();
			}else{
				$this->set_error("メッセージイベントではありません");
				return false;
			}
		}
	}

	/**
	 * メッセージidを取得
	 * @return string メッセージid
	 */
	public function get_message_id()
	{
		foreach ($this->events as $key => $event) {
			if ($event instanceof MessageEvent) {
				return $event->getMessageId();
			}else{
				$this->set_error("メッセージイベントではありません");
				return false;
			}
		}
	}

	/**
	 * イベントタイプを取得
	 * @return string イベントタイプ
	 * message  メッセージ
	 * follow   友達追加
	 * unfollow 友達ブロック
	 * join     グループまたはルーム参加
	 * leave    グループまたはルームからの退会
	 * postback ポストバック
	 */
	public function get_event_type()
	{
		foreach ($this->events as $key => $event) {
			return $event->getType();
		}
	}

	/**
	 * 送信元タイプを取得
	 * @return string user room group
	 */
	public function get_event_sonrce_type()
	{
		foreach ($this->events as $key => $event) {
			return $event->getEventSourceType();
		}
	}

	/**
	 * 送信元のユーザーidを取得
	 * @return string ユーザーid
	 */
	public function get_user_id()
	{
		foreach ($this->events as $key => $event) {
			return $event->getUserId();
		}
	}

	/**
	 * 送信元のグループidを取得
	 * @return string グループid
	 */
	public function get_group_id()
	{
		foreach ($this->events as $key => $event) {
			return $event->getGroupId();
		}
	}

	/**
	 * 送信元のルームidを取得
	 * @return string ルームid
	 */
	public function get_room_id()
	{
		foreach ($this->events as $key => $event) {
			return $event->getRoomId();
		}
	}

	/**
	 * 送信元のidを取得
	 * 個人ユーザーからならuser_id (get_user_id()と同等)
	 * グループからならgroup_id (get_group_id()と同等)
	 * ルームからならroom_id (get_room_id()と同等)
	 * @return string id
	 */
	public function get_event_source_id()
	{
		foreach ($this->events as $key => $event) {
			return $event->getEventSourceId();
		}
	}

	/**
	 * ユーザーのプロフィールを取得
	 * @param  string $user_id ユーザーid
	 * @return array           ユーザーデータ
	 *
	 * データ構造
	 * array[
	 * 	"displayName" =>   "表示名",
	 * 	"pictureUrl"  =>   "画像url",
	 * 	"statusMessage" => "ステータスメッセージ"
	 * ]
	 */
	public function get_profile($user_id)
	{
		$user_profile = $this->getProfile($user_id);
		if ($user_profile->isSucceeded()) {
			return $user_profile->getJSONDecodedBody();
		}else{
			$this->set_error("取得できませんでした");
			return false;
		}
	}

	/**
	 * 送信されたコンテンツのバイナリデータを取得
	 * @return  成功ならバイナリデータ 失敗ならfalse
	 */
	public function get_content()
	{
		$response  = $this->getMessageContent($this->get_message_id());
		if ($response->isSucceeded()) {
			return $response->getRawBody();
		}else{
			$this->set_error("取得できませんでした");
			return false;
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
			$this->set_error("一度に送信できるメッセージは5件までです");
			return false;
		}

		// 空チェック
		if (isset($text) && $text !== "") {
			$this->builder_stok[] = new TextMessageBuilder($text);
			return true;
		}else{
			$this->set_error("テキストは必須です");
			return false;
		}
	}

	/**
	 * 画像のビルダーを追加
	 * @param  string $original_image_url 画像url
	 * @param  string $preview_image_url  サムネイル画像url
	 * @return bool                       成功ならtrue 失敗ならfalse
	 *
	 * 必須条件
	 * HTTPS で始まるurl
	 * JPEG 拡張子
	 *
	 * 画像
	 * 最大画像サイズ：1024×1024
	 * 最大ファイルサイズ：1MB
	 *
	 * サムネイル画像
	 * 最大画像サイズ：240×240
	 * 最大ファイルサイズ：1MB
	 */
	public function add_image_builder($original_image_url,$preview_image_url)
	{
		// ビルダーストックの数が既に５つ以上ならエラー
		if (count($this->builder_stok) >= 5) {
			$this->set_error("一度に送信できるメッセージは5件までです");
			return false;
		}

		// 空チェック
		if (!empty($original_image_url) && !empty($preview_image_url)) {
			$this->builder_stok[] = new ImageMessageBuilder($original_image_url, $preview_image_url);
			return true;
		}else{
			$this->set_error("画像urlとサムネイル画像urlは必須です");
			return false;
		}
	}

	/**
	 * 位置情報のビルダーを追加
	 * @param  string $title   タイトル
	 * @param  string $address 住所
	 * @param  double $lat     緯度(十進数)
	 * @param  double $lon     経度(十進数)
	 * @return bool           成功ならtrue 失敗ならfalse
	 */
	public function add_location_builder($title,$address,$lat,$lon)
	{
		// ビルダーストックの数が既に５つ以上ならエラー
		if (count($this->builder_stok) >= 5) {
			$this->set_error("一度に送信できるメッセージは5件までです");
			return false;
		}

		// 空チェック
		if (!empty($title) && !empty($address)) {
			$this->builder_stok[] = new LocationMessageBuilder($title, $address, $lat, $lon);
			return true;
		}else{
			$this->set_error("タイトルと住所は必須です");
			return false;
		}
	}

	/**
	 * スタンプのビルダーを追加
	 * @param  int $sticker_id ステッカーid
	 * @param  int $package_id パッケージid
	 * @return bool            成功ならtrue 失敗ならfalse
	 *
	 * ステッカーidとパッケージidはLINEBot公式リファレンス参照
	 */
	public function add_stamp_builder($sticker_id,$package_id)
	{
		// ビルダーストックの数が既に５つ以上ならエラー
		if (count($this->builder_stok) >= 5) {
			$this->set_error("一度に送信できるメッセージは5件までです");
			return false;
		}

		// 空チェック
		if (!empty($package_id) && !empty($sticker_id)) {
			$this->builder_stok[] = new StickerMessageBuilder($package_id, $sticker_id);
			return true;
		}else{
			$this->set_error("ステッカーidとパッケージidは必須です");
			return false;
		}
	}

	/**
	 * 動画のビルダーを追加
	 * @param  string $original_content_url 動画url
	 * @param  string $preview_image_url    サムネイル画像url
	 * @return bool                         成功ならtrue 失敗ならfalse
	 *
	 * 必須条件
	 * HTTPS で始まるurl
	 *
	 * 動画
	 * 最大長：1分
	 * 最大ファイルサイズ：10MB
	 * 拡張子:mp4
	 *
	 * サムネイル画像
	 * 最大画像サイズ：240×240
	 * 最大ファイルサイズ：1MB
	 */
	public function add_vido_builder($original_content_url,$preview_image_url)
	{
		// ビルダーストックの数が既に５つ以上ならエラー
		if (count($this->builder_stok) >= 5) {
			$this->set_error("一度に送信できるメッセージは5件までです");
			return false;
		}

		// 空チェック
		if (!empty($original_content_url) && !empty($preview_image_url)) {
			$this->builder_stok[] = new VideoMessageBuilder($original_content_url, $preview_image_url);
			return true;
		}else{
			$this->set_error("動画urlと画像urlは必須です");
			return false;
		}
	}

	/**
	 * 音声のビルダーを追加
	 * @param  string $original_content_url 音声ファイルurl
	 * @param  int    $audio_length         音声ファイルの長さ（ミリ秒）
	 * @return bool                         成功ならtrue 失敗ならfalse
	 *
	 * 必須条件
	 * HTTPS で始まるurl
	 *
	 * 音声ファイル
	 * 最大長：1分
	 * 最大ファイルサイズ：10MB
	 * 拡張子:m4a
	 */
	public function add_audeo_builder($original_content_url,$audio_length)
	{
		// ビルダーストックの数が既に５つ以上ならエラー
		if (count($this->builder_stok) >= 5) {
			$this->set_error("一度に送信できるメッセージは5件までです");
			return false;
		}

		// 空チェック
		if (!empty($original_content_url) && !empty($audio_length)) {
			$this->builder_stok[] = new AudioMessageBuilder($original_content_url, $audio_length);
			return true;
		}else{
			$this->set_error("音声ファイルurlと音声ファイルの長さは必須です");
			return false;
		}
	}

	/**
	 * ボタンテンプレートのビルダーを追加
	 * @param  string $alternative_text       代替テキスト
	 * @param  string $text                   本文
	 * @param  array  $action_buttons         アクションボタン (create_action_builder()のアクションビルダーの配列 ４つまで)
	 * @param  string $title                  タイトル
	 * @param  string $image_url              画像url
	 * @param  class  $default_action_builder デフォルトアクション(create_action_builder()のアクションビルダー)
	 * @return bool                           成功ならtrue 失敗ならfalse
	 */
	public function add_button_template_builder($alternative_text,$text,$action_buttons,$title="",$image_url="",$default_action_builder="")
	{
		// ビルダーストックの数が既に５つ以上ならエラー
		if (count($this->builder_stok) >= 5) {
			$this->set_error("一度に送信できるメッセージは5件までです");
			return false;
		}

		// 代替テキストが空ならエラー
		if (empty($alternative_text)) {
			$this->set_error("代替テキストは必須です");
			return false;
		}

		// 本文が空ならエラー
		if (empty($text)) {
			$this->set_error("本文は必須です");
			return false;
		}

		// エラー対策
		$action_button_array = array();
		foreach ((array)$action_buttons as $key => $value) {
			// アクションクラスじゃなければエラー
			if ($this->check_action_class($value) === false) {
				$this->set_error("アクションビルダーじゃないものが含まれています");
				return false;
			}
			$action_button_array[] = $value;
		}

		// アクションビルダーがないならエラー
		if (count($action_button_array) === 0) {
			$this->set_error("アクションビルダーは必須です");
			return false;
		}

		// アクションビルダーが４つより多いならエラー
		if (count($action_button_array) > 4) {
			$this->set_error("アクションビルダーは4個までです");
			return false;
		}

		// デフォルトアクションが空じゃなくアクションクラスじゃなければエラー
		if (!empty($default_action_builder) && $this->check_action_class($default_action_builder) === false) {
			$this->set_error("アクションビルダーではありません");
			return false;
		}
		
		// ビルダーを追加
		$this->builder_stok[] = new TemplateMessageBuilder($alternative_text,new ButtonTemplateBuilder($title, $text, $image_url, $action_button_array,$default_action_builder));
		return true;
	}

	/**
	 * 確認テンプレートのビルダーを追加
	 * @param  string $alternative_text 代替テキスト
	 * @param  string $text             本文
	 * @param  array  $action_buttons   アクションボタン (create_action_builder()のアクションビルダーの配列 ２つ)
	 * @return bool                     成功ならtrue 失敗ならfalse
	 */
	public function add_confirm_template_builder($alternative_text,$text,$action_buttons)
	{
		// ビルダーストックの数が既に５つ以上ならエラー
		if (count($this->builder_stok) >= 5) {
			$this->set_error("一度に送信できるメッセージは5件までです");
			return false;
		}

		// 代替テキストが空ならエラー
		if (empty($alternative_text)) {
			$this->set_error("代替テキストは必須です");
			return false;
		}

		// 本文が空ならエラー
		if (empty($text)) {
			$this->set_error("本文は必須です");
			return false;
		}
		// ボタンアクション
		$action_button_array = array();
		foreach ($action_buttons as $key => $value) {
			// アクションクラスじゃなければエラー
			if ($this->check_action_class($value) === false) {
				$this->set_error("アクションクラスじゃないものが含まれています");
				return false;
			}
			$action_button_array[] = $value;
		}

		// ボタンアクションが２つじゃなければエラー
		if (count($action_button_array) !== 2) {
			$this->set_error("ボタンアクションは2個でなくてはいけない");
			return false;
		}

		// ビルダーを追加
		$this->builder_stok[] = new TemplateMessageBuilder($alternative_text,new ConfirmTemplateBuilder($text, $action_button_array));
		return true;
	}

	/**
	 * カルーセルテンプレートのカラムビルダーを作成
	 * @param  string $text                   本文
	 * @param  array  $action_buttons         アクションボタン(create_action_builder()のアクションビルダーの配列 1~3つ)
	 * @param  string $title                  タイトル
	 * @param  string $image_url              画像url
	 * @param  class  $default_action_builder デフォルトアクション(create_action_builder()のアクションビルダー)
	 * @return bool                           成功ならカラムビルダーのインスタンス 失敗ならfalse
	 */
	public function create_carousel_column_template_builder($text,$action_buttons,$title="",$image_url="",$default_action_builder="")
	{
		// 本文が空ならエラー
		if (empty($text)) {
			$this->set_error("本文は必須です");
			return false;
		}

		// ボタンアクション
		$action_button_array = array();
		foreach ($action_buttons as $key => $value) {
			// アクションクラスじゃなければエラー
			if ($this->check_action_class($value) === false) {
				$this->set_error("アクションビルダーじゃないものが含まれています");
				return false;
			}
			$action_button_array[] = $value;
		}

		// アクションビルダーがないならエラー
		if (count($action_button_array) === 0) {
			$this->set_error("アクションビルダーは必須です");
			return false;
		}

		// アクションビルダーが3つより多いならエラー
		if (count($action_button_array) > 3) {
			$this->set_error("アクションビルダーは3個までです");
			return false;
		}

		// デフォルトアクションが空じゃなくアクションクラスじゃなければエラー
		if (!empty($default_action_builder) && $this->check_action_class($default_action_builder) === false) {
			$this->set_error("アクションビルダーではありません");
			return false;
		}

		return new CarouselColumnTemplateBuilder($title,$text,$image_url,$action_button_array,$default_action_builder);
	}

	/**
	 * カルーセルテンプレートを追加
	 * @param string $alternative_text         代替テキスト
	 * @param array  $column_template_builders カラムビルダー (create_carousel_column_template_builder()の配列 1~10つまで)
	 */
	public function add_carousel_template_builder($alternative_text,$column_template_builders)
	{
		// ビルダーストックの数が既に５つ以上ならエラー
		if (count($this->builder_stok) >= 5) {
			$this->set_error("一度に送信できるメッセージは5件までです");
			return false;
		}

		// 代替テキストが空ならエラー
		if (empty($alternative_text)) {
			$this->set_error("代替テキストは必須です");
			return false;
		}

		// カラムビルダーチェック
		$column_template_builder_array = array();
		foreach ((array)$column_template_builders as $key => $value) {
			if ($value instanceof CarouselColumnTemplateBuilder) {
				$column_template_builder_array[] = $value;
			}else{
				$this->set_error("カラムビルダーじゃないものが含まれています");
				return false;
			}
		}

		// カラムビルダーがないならエラー
		if (count($column_template_builder_array) === 0) {
			$this->set_error("カラムビルダーは必須です");
			return false;
		}
		// カラムビルダーが10個より多いならエラー
		if (count($column_template_builder_array) > 10) {
			$this->set_error("カラムビルダーは10個までです");
			return false;
		}

		// ビルダーを追加
		$this->builder_stok[] = new TemplateMessageBuilder($alternative_text,new CarouselTemplateBuilder($column_template_builder_array));
		return true;
	}

	/**
	 * イメージカルーセルテンプレートのカラムビルダー作成
	 * @param  string $image_url      画像url
	 * @param  class  $action_builder アクション (create_action_builder()のアクションビルダー)
	 * @return                        成功ならカラムビルダーのインスタンス 失敗ならfalse
	 */
	public function create_image_column_template_builder($image_url,$action_builder)
	{
		// 空ならエラー
		if (empty($image_url)) {
			$this->set_error("画像rulは必須です");
			return false;
		}

		// 空ならエラー
		if (empty($action_builder)) {
			$this->set_error("アクションビルダーは必須です");
			return false;
		}

		// アクションクラスじゃなければエラー
		if ($this->check_action_class($action_builder) === false) {
			$this->set_error("アクションクラスじゃありません");
			return false;
		}

		// イメージカラム作成
		return new ImageCarouselColumnTemplateBuilder($image_url,$action_builder);
	}

	/**
	 * イメージカルーセルテンプレートの追加
	 * @param string $alternative_text      代替テキスト
	 * @param array  $image_column_builders カラムビルダー (create_image_column_template_builder()の配列 1~10個まで)
	 */
	public function add_image_carousel_template_builder($alternative_text,$image_column_builders)
	{
		// ビルダーストックの数が既に５つ以上ならエラー
		if (count($this->builder_stok) >= 5) {
			$this->set_error("一度に送信できるメッセージは5件までです");
			return false;
		}

		// 代替テキストが空ならエラー
		if (empty($alternative_text)) {
			$this->set_error("代替テキストは必須です");
			return false;
		}

		// イメージカラムビルダーチェック
		$image_column_builder_array = array();
		foreach ((array)$image_column_builders as $key => $value) {
			// イメージカラムビルダーのクラスかチェック
			if ($value instanceof ImageCarouselColumnTemplateBuilder) {
				$image_column_builder_array[] = $value;
			}else{
				$this->set_error("イメージカラムビルダーじゃないものが含まれています");
				return false;
			}
		}

		// イメージカラムがないならエラー
		if (count($image_column_builder_array) === 0) {
			$this->set_error("イメージカラムは必須です");
			return false;
		}

		// イメージカラムが10個より多いならエラー
		if (count($image_column_builder_array) > 10) {
			$this->set_error("イメージカラムは10個までです");
			return false;
		}
		// ビルダーを追加
		$this->builder_stok[] = new TemplateMessageBuilder($alternative_text,new ImageCarouselTemplateBuilder($image_column_builder_array));
		return true;
	}

	/**
	 * イメージマップのアクションエリアビルダーの作成
	 * @param  int    $x           アクションエリア支点のx座標
	 * @param  int    $y           アクションエリア支点のｙ座標
	 * @param  int    $width       アクションエリアの幅
	 * @param  int    $height      アクションエリアの高さ
	 * @param  string $action_type アクションタイプ text url
	 * @param  string $content     アクションした時に使用するデータ
	 * @return                     成功ならアクションエリアビルダーのインスタンス 失敗ならfalse
	 */
	public function create_imagemap_action_area_builder(int $x,int $y,int $width,int $height,$action_type,$content)
	{
		// エリアビルダーを作成
		$area_builder = new AreaBuilder($x,$y,$width,$height);

		// アクションタイプをチェック
		if ($action_type !== "text" && $action_type !== "url") {
			$this->set_error("存在しないアクションタイプです");
			return false;
		}

		// アクションタイプを判別
		switch ($action_type) {
			case 'text':
				return new ImagemapMessageActionBuilder($content,$area_builder);
				break;
			case 'url':
				return new ImagemapUriActionBuilder($content,$area_builder);
				break;
			
			default:
				$this->set_error("存在しないアクションタイプです");
				return false;
				break;
		}
	}

	/**
	 * イメージマップのビルダーを追加
	 * @param string $alternative_text     代替テキスト
	 * @param string $image_base_url       画像ベースurl
	 * @param int    $width                画像の高さ
	 * @param array  $action_area_builders アクションエリアビルダー (create_imagemap_action_area_builder()の配列 50個まで)
	 */
	public function add_imagemap_buildr($alternative_text,$image_base_url,int $width,$action_area_builders)
	{
		// ビルダーストックの数が既に５つ以上ならエラー
		if (count($this->builder_stok) >= 5) {
			$this->set_error("一度に送信できるメッセージは5件までです");
			return false;
		}

		// 代替テキストが空ならエラー
		if (empty($alternative_text)) {
			$this->set_error("代替テキストは必須です");
			return false;
		}

		// 画像ベースurlが空ならエラー
		if (empty($image_base_url)) {
			$this->set_error("画像ベースurlは必須です");
			return false;
		}

		// アクションエリアビルダーのチェック
		$action_area_builder_array = array();
		foreach ($action_area_builders as $key => $value) {
			if ($this->check_action_area_class($value)) {
				$action_area_builder_array[] = $value;
			}else{
				$this->set_error("アクションエリアビルダーではないものが含まれています");
				return false;
			}
		}

		// ベースサイズビルダーを作成
		$base_size_builder = new BaseSizeBuilder(1040,$width);

		// ビルダーを追加
		$this->builder_stok[] = new ImagemapMessageBuilder($image_base_url,$alternative_text,$base_size_builder,$action_area_builder_array);
		return true;
	}

	/**
	 * アクションのビルダーを作成
	 * @param  string $action_type アクションタイプ  text post url date
	 * @param  string $label       表示するテキスト
	 * @param  string $content     アクションした時に使用するデータ
	 * @param  string $date_mode   アクションタイプがdateの時、必須 date time datetime
	 * @param  string $initial     アクションタイプがdateの時 日時の初期値
	 * @param  string $limit_max   アクションタイプがdateの時 日時の上限
	 * @param  string $limit_min   アクションタイプがdateの時 日時の下限
	 * @return                     各アクションタイプのビルダークラス 失敗時はfalse
	 */
	public function create_action_builder($action_type,$label,$content,$date_mode="",$initial="",$limit_max="",$limit_min="")
	{
		// アクションタイプが空ならエラー
		if (empty($action_type)) {
			$this->set_error("アクションタイプは必須です");
			return false;
		}

		// ラベルが空なら
		if (empty($label)) {
			$label = null;
		}

		// アクションタイプ判別
		switch ($action_type) {
			case 'text':
				return new MessageTemplateActionBuilder($label,$content);
				break;
			case 'post':
				return new PostbackTemplateActionBuilder($label,$content);
				break;
			case 'url':
				return new UriTemplateActionBuilder($label,$content);
				break;
			case 'date':
				if (empty($date_mode)) {
					$this->set_error("アクションタイプがdateの時、date_modeは必須です");
					return false;
				}
				// date_modeが正しいかチェック
				if ($date_mode !== "date" && $date_mode !== "time" && $date_mode !== "datetime") {
					$this->set_error("存在しないdate_modeです");
					return false;
				}
				return new DatetimePickerTemplateActionBuilder($label,$content,$date_mode,$initial,$limit_max,$limit_min);
				break;
			default:
				$this->set_error("存在しないアクションタイプです");
				return false;
				break;
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
				$this->set_error("深刻な返信エラー" . $response->getHTTPStatus() . ' ' . $response->getRawBody());
				return false;
			}else{
				return true;
			}
		}else{
			$this->set_error("返信するビルダーがありません");
			return false;
		}
	}

	/**
	 * 送信先を指定してメッセージを送る
	 * @param  [type] $to_id 送信先id
	 * @return [type]        [description]
	 */
	public function push($to_id)
	{
		// 送信先がなければエラー
		if (empty($to_id)) {
			$this->set_error("送信先は必須です");
			return false;
		}

		if (count($this->builder_stok) > 0) {
			$builder = new MultiMessageBuilder();
			foreach ($this->builder_stok as $key => $row) {
				$builder -> add($row);
			}
			$response = $this -> pushMessage($to_id, $builder);
			if ($response -> isSucceeded() == false) {
				error_log("深刻な返信エラー" . $response->getHTTPStatus() . ' ' . $response->getRawBody());
				$this->set_error("深刻な返信エラー" . $response->getHTTPStatus() . ' ' . $response->getRawBody());
				return false;
			}else{
				return true;
			}
		}else{
			$this->set_error("送信するビルダーがありません");
			return false;
		}
	}

	/**
	 * ビルダーストックを削除(初期化)
	 * @param  string $delete_type 何も指定しなければ全て削除 lastを指定すれば最後の要素を削除
	 * @return 
	 */
	public function delete_builder_stok($delete_type="default")
	{
		if ($delete_type === "default") {
			$this->builder_stok = array();
		}

		if ($delete_type === "last") {
			array_pop($this->builder_stok);
		}
	}

	/**
	 * 返信するjsonオブジェクトを取得
	 * @return json 
	 */
	public function get_reply_json_data()
	{
		$builder = new MultiMessageBuilder();
		foreach ($this->builder_stok as $key => $row) {
			$builder -> add($row);
		}

		$data = array();
		$data['replyToken'] = $this->reply_token;
		$data['messages'] = $builder->buildMessage();
		$json_data = json_encode($data);
		
		return $json_data;
	}

	/**
	 * テキストメッセージを返信
	 * @param  string $text 返信するテキスト
	 * @return bool         成功ならtrue 失敗ならfalse
	 *
	 * 返信は一度しか行えません
	 */
	public function reply_text($text)
	{
		// 返信とそのレスポンス
		$response = $this -> replyMessage($this->reply_token, new TextMessageBuilder($text));
		if ($response -> isSucceeded() == false) {
			error_log("深刻な返信エラー" . $response->getHTTPStatus() . ' ' . $response->getRawBody());
			$this->set_error("深刻な返信エラー" . $response->getHTTPStatus() . ' ' . $response->getRawBody());
			return false;
		}else{
			return true;
		}
	}

	/**
	 * create_action_builder()で作成されるアクションクラスかチェック
	 * @param         $action_class チェックする変数
	 * @return bool   アクションクラスならtrue 違うならfalse
	 */
	public function check_action_class($action_class)
	{
		// textactionのclass
		if ($action_class instanceof MessageTemplateActionBuilder) {
			return true;
		}
		// postactionのclass
		if ($action_class instanceof PostbackTemplateActionBuilder) {
			return true;
		}
		// urlactionのclass
		if ($action_class instanceof UriTemplateActionBuilder) {
			return true;
		}
		// urlactionのclass
		if ($action_class instanceof DatetimePickerTemplateActionBuilder) {
			return true;
		}
		// どれにも当てはまらないならfalse
		return false;
	}

	/**
	 * アクションエリアビルダーのクラスかチェック
	 * @param       $action_class アクションクラス
	 * @return bool               アクションエリアビルダーのクラスならtrue 違うならfalse
	 */
	public function check_action_area_class($action_class)
	{
		// textactionのclass
		if ($action_class instanceof ImagemapMessageActionBuilder) {
			return true;
		}
		// postactionのclass
		if ($action_class instanceof ImagemapUriActionBuilder) {
			return true;
		}
		
		// どれにも当てはまらないならfalse
		return false;
	}

	/**
	 * エラーメッセージを追加する
	 * @param string $error_message エラーメッセージ
	 */
	private function set_error($error_message)
	{
		$this->error_stok[] = $error_message;
	}

	/**
	 * エラーメッセージを取得
	 * @return array エラーメッセージの配列
	 */
	public function get_error()
	{
		return $this->error_stok;
	}
}
?>