<?php
require_once __DIR__ . '/linebot.php';

$bot = new LineBotClass();

try {
	// 画像url
	$photo_url = "https://www.pakutaso.com/shared/img/thumb/cat126IMGL6488_TP_V.jpg";
	// 動画url
	$video_url = "https://hoge/hoge.mp4";
	// 音声url
	$audeo_url = "https://hoge/hoge.m4a";
	// 位置情報
	$title = "ゲームフリーク";
	$address = "東京都世田谷区太子堂4丁目1番1号 キャロットタワー22階";
	$lat = 35.643656;
	$lon = 139.669046;

	// テキストを取得
	$text = $bot->get_text();
	// メッセージタイプを取得
	$messeage_type = $bot->get_message_type();
	// イベントタイプを取得
	$event_type = $bot->get_event_type();
	// $bot->add_text_builder("イベントタイプ:" . $event_type);

	// オウム返し
	if ($text !== false) {
		$bot->add_text_builder($text);
	}

	if ($messeage_type !== false) {
		// $bot->add_text_builder("メッセージタイプ:" . $messeage_type);
	}

	// 画像取得
	// if ($messeage_type === "image") {
	// 	file_put_contents("image/test.jpg", $bot->get_content());
	// }

	// ポストバックのイベントなら
	if ($event_type === "postback") {
		$post_data = $bot->get_post_data();
		$post_params = $bot->get_post_params();
		$post_text = "post_data:" . $post_data . "\n";
		foreach ((array)$post_params as $key => $value) {
			$post_text .= $key . ":" . $value . "\n";
		}
		$bot->add_text_builder($post_text);
	}

	// スタンプなら
	if ($messeage_type == "sticker") {
		$stame_id = $bot->get_stamp_id();
		$id_text = "";
		foreach ($stame_id as $key => $value) {
			$id_text .= $key . ":" . $value . "\n";
		}
		$bot->add_text_builder($id_text);
	}

	// 画像メッセージの追加
	if ($text == "イメージ") {
		$bot->add_image_builder($photo_url,$photo_url);
	}

	// 位置情報メッセージの追加
	if ($text == "位置情報") {
		$bot->add_location_builder($title,$address,$lat,$lon);
	}

	// スタンプメッセージの追加
	if ($text == "スタンプ") {
		$bot->add_stamp_builder(141,2);
	}

	// 動画メッセージの追加
	if ($text == "動画") {
		$bot->add_vido_builder($video_url,$photo_url);
	}

	// 音声メッセージの追加
	if ($text == "音声") {
		$bot->add_audeo_builder($audeo_url,60000);
	}

	// ボタンテンプレート
	if ($text == "ボタン") {
		// アクションボタンの作成
		$action_button = array();
		$action_button[] = $bot->create_action_builder("text","TypeText","test_text");
		$action_button[] = $bot->create_action_builder("post","TypePost","post_text");
		$action_button[] = $bot->create_action_builder("url","TypeUrl","https://developers.line.me/ja/reference/messaging-api/");
		$action_button[] = $bot->create_action_builder("date","Typedate","date_text","datetime");
		$default_action = $bot->create_action_builder("text","TypeText","デフォルトアクション");
		$result = $bot->add_button_template_builder("代替テキスト","アクションボタンのテストもかねて",$action_button,"テンプレートボタンテスト",$photo_url,$default_action);
	}

	// 確認テンプレート
	if ($text == "確認") {
		// 確認テンプレートの作成
		$action_button = array();
		$text = "テスト";
		$text = urlencode($text);

		$action_button[] = $bot->create_action_builder("url","押せ","line://msg/text/?" . $text);
		$action_button[] = $bot->create_action_builder("url","タイムライン","line://home/public/main?id=sah1718q");
		$result = $bot->add_confirm_template_builder("代替テキスト","確認テンプレートのテスト\nurlスキームのテストもかねて",$action_button);
	}

	// カルーセルテンプレート
	if ($text == "カルーセル") {
		// カルーセルテンプレートの作成
		$column_builders = array();
		for ($i=0; $i < 10; $i++) {
			// アクションボタンの作成 1~3まで有効
			$action_button = array();
			$action_button[] = $bot->create_action_builder("text","TypeText","test_text");
			$action_button[] = $bot->create_action_builder("post","TypePost","post_text");
			$action_button[] = $bot->create_action_builder("url","TypeUrl","line://msg/text/?test_text");
			// デフォルトアクションの作成
			$data_text = "デフォルトアクションtest" . ($i+1);
			$default_action = $bot->create_action_builder("text","TypeText",$data_text);

			// 本文
			$text = ($i+1) . "ページ";
			// タイトル
			$title = "カルーセルテンプレートテスト";
			// カラムテンプレートビルダーの作成
			$result = $bot->create_carousel_column_template_builder($text,$action_button,$title,$photo_url,$default_action);
			if ($result !== false) {
				$column_builders[] = $result;
			}
		}
		// カルーセルテンプレートビルダーの追加
		$bot->add_carousel_template_builder("代替テキスト",$column_builders);
	}

	// イメージカルーセルテンプレート
	if ($text == "イメージカルーセル") {
		// イメージカルーセルテンプレートの作成
		$image_column_builders = array();
		for ($i=1; $i <= 10; $i++) {
			// アクションビルダーを作成
			$action_builder = $bot->create_action_builder("text","","イメージ" . $i);
			// イメージカラムビルダーを作成
			$image_column_builders[] = $bot->create_image_column_template_builder($photo_url,$action_builder);
		}
		// イメージカルーセルテンプレートの追加
		$bot->add_image_carousel_template_builder("代替テキスト",$image_column_builders);
	}

	// イメージマップ
	if ($text == "イメージマップ") {
		// // イメージマップの作成
		$action_area_builders = array();
		// アクションエリアビルダーの作成
		$action_area_builders[] = $bot->create_imagemap_action_area_builder(0,0,520,1040,"text","左");
		$action_area_builders[] = $bot->create_imagemap_action_area_builder(520,0,520,1040,"text","右");
		// ベースurl
		$base_url = "https://hoge.com/hoge";
		// イメージマップビルダーを追加
		$result = $bot->add_imagemap_buildr("代替テキスト",$base_url,1040,$action_area_builders);
		// 追加失敗ならエラーをスロー
		if ($result === false) {
			throw new Exception("イメージマップの追加失敗");
		}
	}


	// 返信実行
	$bot->reply();

} catch (Exception $e) {
	$error = $e->getMessage();
	$bot->add_text_builder("エラーキャッチ:" . $error);
	// 返信実行
	$bot->reply();
}

	
?>