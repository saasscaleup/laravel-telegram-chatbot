<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TelegramController extends Controller
{
    public function inbound(Request $request){
        \Log::info($request->all());

        // get telegram chat_id and reply to
        $chat_id            = $request->message['from']['id'];
        $reply_to_message   = $request->message['message_id'];

        \Log::info("chat_id: {$chat_id}");
        \Log::info("reply_to_message: {$reply_to_message}");

        // If first time -> send first time message
        if(!cache()->has("chat_id_{$chat_id}")){

            $text = "Welcome to ImageDetectTextBOT 🤖 \r\n";
            $text.= "Please upload a IMAGE and enjoy the magic 🪄";

            cache()->put("chat_id_{$chat_id}",true,now()->addMinute(60));


        }else if(isset($request->message['photo'])){ // If chat is photo -> Extract text from photo
            
            // Get image_url...
            $image_url = app('telegram_bot')->getImageUrl($request->message['photo']);

            // Extract text from image
            $text = app('image_detect_text')->getTextFromImage($image_url);

        }else{        // Else -> Send default message

            $text = "ImageDetectTextBOT 🤖\r\nPlease upload an IMAGE!";
        }

        // telegram service - > sendMessage($text,$chat_id,$reply_to_message)
        $result = app('telegram_bot')->sendMessage($text,$chat_id,$reply_to_message);

        return response()->json($result,200);
    }
}
