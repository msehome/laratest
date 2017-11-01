<?php
header('Content-Type: text/html; charset=utf-8');
ob_start();
define('BOT_TOKEN', '325403094:AAEaGtBVcmbDKgsRtjzJLyAS3IB1A0riqQU');
define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN.'/');


define('SERVER_NAME', 'localhost');
define('USER_NAME','m_sadegh');
define('PASSWORD','@Msadegh2016!');
define('DBNAME','poem');

function updateBotState($chat_id,$user_id,$user_name, $username, $current_state,$message_id,$last_message,$page_index) {
    try {
        if ($page_index < 0) $page_index = 1;
        $sql = "INSERT INTO State (chat_id, user_id, user_name, username, current_state, message_id,last_message,page_index) VALUES (" . $chat_id . "," . $user_id . ",'" . $user_name . "'," . "'" . $username . "'," . $current_state . "," . $message_id . ",'" . $last_message . "'," . $page_index . ")";
        /*$file = 'insert.log';
        $current = file_get_contents($file);
        $current .= $sql;
        file_put_contents($file, $current);*/
        $conn = new mysqli(SERVER_NAME, USER_NAME, PASSWORD, DBNAME);
        mysqli_set_charset($conn,"utf8");
        if ($conn->connect_errno) {
            //log connection problem
        }
        else if ($conn->query($sql) === TRUE) {
            //log success
        } else {
            //log failure
        }
        $conn->close();
    }
    catch (mysqli_sql_exception $er)
    {
        $file = 'insert.log';
        $current = file_get_contents($file);
        $current .= $er->getMessage();
        file_put_contents($file, $current);
        return $er->getMessage();
    }
}

function appendLog($chat_id,$user_id,$user_name,$username,$current_state,$message_id,$text) {
    try {

        $sql = "INSERT INTO User_activity_log (chat_id, user_id, user_name, username, current_state, message_id,text) VALUES (" . $chat_id . "," . $user_id . ",'" . $user_name . "'," . "'" . $username . "'," . $current_state . "," . $message_id . ",'" . $text . "')";
        $conn = new mysqli(SERVER_NAME, USER_NAME, PASSWORD, DBNAME);
        mysqli_set_charset($conn,"utf8");
        if ($conn->connect_errno) {
            //log connection problem
        }
        else if ($conn->query($sql) === TRUE) {
            //log success
        } else {
            //log failure
        }
        $conn->close();
    }
    catch (mysqli_sql_exception $er)
    {
        $file = 'append.log';
        $current = file_get_contents($file);
        $current .= $er->getMessage();
        file_put_contents($file, $current);
        return $er->getMessage();
    }
}


function getBotState($chat_id){
    try {
        $sql = "SELECT * FROM State Where chat_id = ".$chat_id ." Order by activity_datetime DESC LIMIT 1";
        /*$file = 'get.log';
        $current = file_get_contents($file);
        $current .= $sql;
        file_put_contents($file, $current);*/
        $conn = new mysqli(SERVER_NAME, USER_NAME, PASSWORD, DBNAME);
        mysqli_set_charset($conn,"utf8");
        $result = $conn->query($sql);
        if ($conn->connect_errno) {
            //log connection problem
        }
        $rowset = null;
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $rowset = $row;
            }
            return $rowset;
        } else {
            return null;
        }
    }
    catch (mysqli_sql_exception $er)
    {
        $file = 'get.log';
        $current = file_get_contents($file);
        $current .= $er->getMessage();
        file_put_contents($file, $current);
        return $er->getMessage();
    }
}

function getStatistics()
{
    try {
        $sql = "SELECT COUNT(DISTINCT user_name) as cnt FROM State";
        $conn = new mysqli(SERVER_NAME, USER_NAME, PASSWORD, DBNAME);
        mysqli_set_charset($conn,"utf8");
        $result = $conn->query($sql);
        $cnt = "";
        while ($row = $result->fetch_assoc()) {
            $cnt = $row['cnt'];
        }
        $cnt = " تعداد کاربران: " . $cnt .'
';
        $sql = "SELECT current_state,COUNT(current_state) as cnt FROM State GROUP BY current_state";
        mysqli_set_charset($conn,"utf8");
        $result = $conn->query($sql);
        $state = [];
        $count = [];
        $i = 0;
        while ($row = $result->fetch_assoc()) {
            if ($row['current_state'] == "0")
                $state[] = "بازگشت";
            else if ($row['current_state'] == "1")
                $state[] = "مشاعره";
            else if ($row['current_state'] == "2")
                $state[] = "تفال";
            else if ($row['current_state'] == "3")
                $state[] = "جستجو";
            else if ($row['current_state'] == "31")
                $state[] = "ادامه جستجو";
            else if ($row['current_state'] == "4")
                $state[] = "نمایش کتب";
            else if ($row['current_state'] == "41")
                $state[] = "نمایش محتوای کتب";
            $count[] = $row['cnt'];
            $i++;
        }
        $cnt .= '
';
        for ($j=1;$j<$i;$j++)
        {
            $cnt .= $state[$j] . " : " . $count[$j].'
';
        }
        return $cnt;

    }
    catch (mysqli_sql_exception $er)
    {
        $file = 'get.log';
        $current = file_get_contents($file);
        $current .= $er->getMessage();
        file_put_contents($file, $current);
        return $er->getMessage();
    }
}

function apiRequestWebhook($method, $parameters) {
    if (!is_string($method)){
        error_log("Method name must be a string\n");
        return false;
    }

    if (!$parameters) {
        $parameters = array();
    } else if (!is_array($parameters)) {
        error_log("Parameters must be an array\n");
        return false;
    }

    $parameters["method"] = $method;

    header("Content-Type: application/json");
    echo json_encode($parameters);
    return true;
}

function exec_curl_request($handle) {
    $response = curl_exec($handle);

    if ($response === false) {
        $errno = curl_errno($handle);
        $error = curl_error($handle);
        error_log("Curl returned error $errno: $error\n");
        curl_close($handle);
        return false;
    }

    $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
    curl_close($handle);

    if ($http_code >= 500) {
        // do not wat to DDOS server if something goes wrong
        sleep(10);
        return false;
    } else if ($http_code != 200) {
        $response = json_decode($response, true);
        error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
        if ($http_code == 401) {
            throw new Exception('Invalid access token provided');
        }
        return false;
    } else {
        $response = json_decode($response, true);
        if (isset($response['description'])) {
            error_log("Request was successfull: {$response['description']}\n");
        }
        $response = $response['result'];
    }

    return $response;
}

function apiRequest($method, $parameters) {
    if (!is_string($method)) {
        error_log("Method name must be a string\n");
        return false;
    }

    if (!$parameters) {
        $parameters = array();
    } else if (!is_array($parameters)) {
        error_log("Parameters must be an array\n");
        return false;
    }

    foreach ($parameters as $key => &$val) {
        // encoding to JSON array parameters, for example reply_markup
        if (!is_numeric($val) && !is_string($val)) {
            $val = json_encode($val);
        }
    }
    $url = API_URL.$method.'?'.http_build_query($parameters);

    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($handle, CURLOPT_TIMEOUT, 60);

    return exec_curl_request($handle);
}

function apiRequestJson($method, $parameters) {
    if (!is_string($method)) {
        error_log("Method name must be a string\n");
        return false;
    }

    if (!$parameters) {
        $parameters = array();
    } else if (!is_array($parameters)) {
        error_log("Parameters must be an array\n");
        return false;
    }

    $parameters["method"] = $method;

    $handle = curl_init(API_URL);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($handle, CURLOPT_TIMEOUT, 60);
    curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
    curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

    return exec_curl_request($handle);
}

function apiHTTPRequest($method,$datas=[]){
    $url = API_URL."/".$method;
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($datas));
    $res = curl_exec($ch);
    if(curl_error($ch)){
        $file = 'curl-error.log';
        $current = file_get_contents($file);
        $current .= print_r(curl_error($ch),true).'
       ';
        file_put_contents($file, $current);
    }else{
        return json_decode($res);
    }
}

function farsinumbers($str)
{
    $farsi_eastern = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩');
    $farsi_western = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
    return str_replace($farsi_western, $farsi_eastern, $str);
}

function showMainMenu($chat_id)
{
    apiRequestJson('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"لطفاً یک مورد را انتخاب کنید:",
        'reply_markup'=>json_encode([
            'inline_keyboard'=>[

                [['text'=>"مشاعره",'callback_data'=>'1'],
                    ['text'=>"تفأل",'callback_data'=>'2']],
                [['text'=>"جستجو",'callback_data'=>'3'],
                    ['text'=>"فهرست شعرا",'callback_data'=>'4']],
                [['text'=>"ارسال شعر تصادفی",'callback_data'=>'5']],
                [['text'=>"کانال راهنما",'url'=>'https://t.me/poemdirectory_bot']],
                [['text'=>"درباره كنترل اعداد",'url'=>'http://controladad.com/about.html']]
            ]])]);
}

function processMessage($message) {

    try {
        // process incoming message
        $message_id = $message['message_id'];
        $chat_id = $message['chat']['id'];
        $user_id = $message['from']['id'];
        $username = $message['from']['username'];
        $user_name = $message['from']['first_name'].' '.$message['from']['last_name'];
        /*$file = 'message.log';
        $current = file_get_contents($file);
        $current .= print_r($message,true).'
        ';
        file_put_contents($file, $current);*/

        if (isset($message['text'])) {
            // incoming text message
            $text = $message['text'];
            $text = trim($text);
            $text = str_replace('ك','ک',$text);
            $text = str_replace('ي','ی',$text);
            $state = getBotState($chat_id);
            appendLog($chat_id,$user_id,$user_name,$username,$state['current_state'],$message_id,$text);
            if ($text=== "/start") {
                showMainMenu($chat_id);
            }
            else if ($text == "/stats")
            {
                $stats = getStatistics();
                apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => $stats, 'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [
                            ['text' => "بازگشت", 'callback_data' => '0'],
                        ]
                    ]])));
            }
            else if ($state['current_state'] == 1) {

                mb_internal_encoding('UTF-8');
                $fst = "";
                $text = trim($text);

                $lst = mb_substr($text, -1);

                if ($state['last_message'] != "مشاعره") {
                    $fst = mb_substr($text, 0,1);
                    $lst_msg = $state['last_message'];
                    $lst_msg = str_replace("?","",$lst_msg);
                    $lst_msg = str_replace("؟","",$lst_msg);
                    $lst_msg = str_replace("!","",$lst_msg);
                    $lst_msg = str_replace("»","",$lst_msg);
                    $lst_msg = str_replace("&#8204;","",$lst_msg);
                    $lst_msg = trim($lst_msg);
                    $lst_msg = preg_replace( '/[\x{200B}-\x{200D}]/u', '', $lst_msg );
                    $llst = mb_substr($lst_msg, -1);
                    if ($llst == " " || $llst == "")
                        $llst = trim(mb_substr($lst_msg, -2));
                    if ($fst != $llst) {
                        /*$current = "";
                        $file = 'verse_problem.log';
                        file_get_contents($file, $current);
                        $current .= 'fst: '.$fst . '
llst: '.$llst.'
lst msg: '.$lst_msg.'
txt: '.$text;
                        file_put_contents($file, $current);
*/
                        apiRequestJson("sendMessage", array('chat_id' => $chat_id, "reply_to_message_id" => $message_id, "text" => " بیت ارسال شده مورد قبول نیست این بیت باید با حرف '" . $llst . "' آغاز شود. بیت ارسالی شما با '" . $fst . "' آغاز شده است."));
                        return;
                    }
                }
                if (preg_match('/^([\x{0600}-\x{06FF}| |\x{200C}])+$/u',$lst)) {

                    $verse = file_get_contents('https://poem.adad.ws/poem.php?func=mosh&key=' . $lst);
                    $verse_arr = json_decode($verse, true);
                    if ($verse_arr) {
                        $len = count($verse_arr['items']);
                        $i = 0;
                        foreach ($verse_arr['items'] as $item) {
                            $i++;
                            apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => $item['poem']['verse-r'] . '
' . $item['poem']['verse-l'] . '

#' . $item['poem']['poet'] . '
#' . $item['poem']['book'] . ' - #' . $item['poem']['parent'] . '
 ', 'reply_markup' => json_encode([
                                'inline_keyboard' => [
                                    [['text' => "نمایش متن/شعر کامل", 'callback_data' => 'poem'.$item['poem_id'].'']],
                                    [['text' => "بازگشت", 'callback_data' => "0"]]
                                ]
                            ])));
                            if ($verse != "لطفاً شعر را با حروف فارسی تایپ نمایید." && $text != "ورودی نامناسب") {
                                updateBotState($chat_id, $user_id, $user_name, $username, 1, $message_id,$item['poem']['verse-l'] , 0);
                            }
                        }
                    } else
                    {
                        if ($verse == false)
                        {
                            apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => "با عرض پوزش، به دلیل ترافیک بالای سرور وقفه ای در پاسخگویی پیش آمده است، لطفاً بیت خود را مجدداً ارسال نمایید.", 'reply_markup' => json_encode([
                                'inline_keyboard' => [
                                    [
                                        ['text' => "بازگشت", 'callback_data' => '0'],
                                    ]
                                ]])));
                            exit;

                        }
                        else {
                            apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => $verse, 'reply_markup' => json_encode([
                                'inline_keyboard' => [
                                    [
                                        ['text' => "بازگشت", 'callback_data' => '0'],
                                    ]
                                ]])));
                            exit;
                        }
                    }

                }
                else
                {
                    apiRequestWebhook("sendMessage", array('chat_id' => $chat_id, "reply_to_message_id" => $message_id, "text" => "متن ارسالی مناسب نیست "));
                }

            }
            else if ($state['current_state'] == 3){
                try {
                    $text = str_replace("٪","%",$text);
                    $text = str_replace("&","%",$text);
                    $verse = file_get_contents('https://poem.adad.ws/poem.php?func=search&page=0&key=' . urlencode($text));
                    $verse_arr = json_decode($verse, true);
                    if (!$verse_arr) {
                        apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => $verse, 'reply_markup' => json_encode([
                            'inline_keyboard' => [
                                [
                                    ['text' => "بازگشت", 'callback_data' => '0'],
                                ]
                            ]])));
                        exit;
                    }
                    $len = count($verse_arr['items']);
                    $i = 0;
                    foreach ($verse_arr['items'] as $item) {
                        $i++;
                        if ($i < $len) {
                            apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => farsinumbers($item['item']) . '-'
                                . $item['poem']['verse-r'] . '
' . $item['poem']['verse-l'] . '

#' . $item['poem']['poet'] . '
#' . $item['poem']['book'] . ' - #' . $item['poem']['parent'] . '
 ', 'reply_markup' => json_encode([
                                'inline_keyboard' => [
                                    [
                                        ['text' => "نمایش متن/شعر کامل", 'callback_data' =>  'poem'.$item['poem_id'].'']]
                                ]
                            ])));
                        }
                        else{
                            apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => farsinumbers($item['item']) . '-'
                                . $item['poem']['verse-r'] . '
' . $item['poem']['verse-l'] . '

#' . $item['poem']['poet'] . '
#' . $item['poem']['book'] . ' - #' . $item['poem']['parent'] . '
 ', 'reply_markup' => json_encode([
                                'inline_keyboard' => [
                                    [['text' => "نمایش متن/شعر کامل", 'callback_data' => 'poem'.$item['poem_id'].'']],
                                    [['text' => "بازگشت", 'callback_data' => "0"],['text' => "موارد بعدی", 'callback_data' => "31"]]
                                ]
                            ])));
                        }
                        /*$current = "";
                        $file = 'keyboard.log';
                        file_get_contents($file, $current);
                        $current .= json_encode([
                            'inline_keyboard' => [
                                [['text' => "نمایش متن/شعر کامل", 'callback_data' => ''.$item['poem_id'].'']],
                                [['text' => "بازگشت", 'callback_data' => "0"],['text' => "موارد بعدی", 'callback_data' => "31"]]
                            ]
                        ]).'
                        ';
                        file_put_contents($file, $current);*/
                    }
                    updateBotState($chat_id,$user_id,$user_name, $username, 3, $message_id, $text, 0);
                }
                catch (Exception $err)
                {
                    $current = "";
                    $file = 'error.log';
                    file_get_contents($file, $current);
                    $current .= $err . '
                    ';
                    file_put_contents($file, $current);
                }
            }
            else if ($state['current_state'] == 51)
            {
                if (preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/',$text)) {
                    $parts = explode(":", $text);
                    $hour = (int)$parts[0];
                    $min = (int)$parts[1];
                    if ($hour < 0 || $hour > 24 || $min < 0 || $min > 59 || ($min % 10) != 0)
                        apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => "زمان وارد شده قابل قبول نیست، لطفاً دقت کنید ساعت صحیح باشد و دقیقه مضربی از ده باشد."));
                    else {
                        if ($hour < 10)
                            $shour = $hour;
                        else
                            $shour = $hour;
                        if ($min < 10)
                            $smin = $min;
                        else
                            $smin = $min;
                        $time = $shour . ":" . $smin;
                        file_get_contents("https://poem.adad.ws/subscriptions.php?subscribe=1&chat_id=" . $chat_id . "&time=" . $time);
                        apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => "درخواست شما ثبت شد، هر روز در زمان مقرر شعری بصورت تصادفی برای شما ارسال خواهد شد."));
                        updateBotState($chat_id,$user_id,$user_name, $username, 0, $message_id, $text, 0);
                        showMainMenu($chat_id);
                    }
                }
                else
                    apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => "زمان وارد شده قابل قبول نیست."));

            }
            else if (strpos($text, "/stop") === 0) {
                // stop now
            }
            else
            {
                //showMainMenu($chat_id);
            }
        }
        else {
            // apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'پیغام شما قابل پردازش نیست.'));
        }

    } catch (Exception $e) {
        apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => ($e->getMessage())));
    }
}


define('WEBHOOK_URL', 'https://poem.adad.ws/325403094:AAEaGtBVcmbDKgsRtjzJLyAS3IB1A0riqQU/poembot.php');

if (php_sapi_name() == 'cli') {
    // if run from console, set or delete webhook
    apiRequest('setWebhook', array('url' => isset($argv[1]) && $argv[1] == 'delete' ? '' : WEBHOOK_URL));
    exit;
}


if ($_GET["func"] == 'cron_call')
{
    $subscribers = json_decode(urldecode($_GET["subscribers"]), true);
    $verse = file_get_contents('https://poem.adad.ws/poem.php?func=tafaol&allpoets=1');
    foreach ($subscribers as $subscriber)
    {
        $chat_id = $subscriber;
        if ($verse != false)
            apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => $verse,'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [
                        ['text' => "بازگشت", 'callback_data' => '0'],
                    ]
                ]])));
    }

    exit;
}


$content = file_get_contents("php://input");
$update = json_decode($content, true);


if (!$update) {
    // receive wrong update, must not happen
    exit;
}
else
{
    /* $file = 'update.log';
     $current = file_get_contents($file);
     $current .= print_r($update,true).'
     ';
     file_put_contents($file, $current);*/
}



if (isset($update["callback_query"]))
{
    $chat_id = $update['callback_query']['message']['chat']['id'];
    try {
        $callbackMessage = 'لطفاً کمی صبر کنید';
        apiRequestJson('answerCallbackQuery', [
            'callback_query_id' => $update->callback_query->id,
            'text' => $callbackMessage]);
        $message_id = $update['callback_query']['message']['message_id'];
        $user_id = $update['callback_query']['from']['id'];
        $user_name = $update['callback_query']['from']['first_name'] .' '.$update['callback_query']['from']['last_name'];
        $state = getBotState($chat_id);
        $username = $update['callback_query']['from']['username'];

        if (strpos($update['callback_query']['data'],'poem') !== false ) {
            $theverse = file_get_contents('https://poem.adad.ws/poem.php?func=poem&pid='. trim(str_replace("poem","",$update['callback_query']['data'])));
			$verses = explode("!br!", $theverse);
			foreach ($verses as $verse) {
                if( !next( $verses)) {
                    apiRequestJson('sendMessage', [
                        'chat_id' => $chat_id,
                        'text' => $verse,
                        'reply_markup' => json_encode([
                            'inline_keyboard' => [
                                [
                                    ['text' => "بازگشت", 'callback_data' => '0']
                                ]
                            ]])]);
                }
                else
                {
                    apiRequestJson('sendMessage', [
                        'chat_id' => $chat_id,
                        'text' => $verse]);
                }
            }
        }
        else if (strpos($update['callback_query']['data'],'poet') !== false ) {
            mb_internal_encoding('UTF-8');
            $verse = file_get_contents('https://poem.adad.ws/poem.php?func=listpoets&key='.str_replace("poet","",$update['callback_query']['data']));
            //apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => $verse));
            $crumb = "";
            $verse_arr = json_decode($verse, true);
            if ($verse_arr) {
                $inlinebutton = [];
                $i = 0;
                foreach ($verse_arr['items'] as $item) {
                    $crumb = $item['crumb'];
                    $inlinebutton[$i] = ['text' => '' . $item['text'] . '' , 'callback_data' => '' . $item['pref'] . $item['id'] .'' ];
                    $i++;
                }
                $inlinebutton[$i] = ['text' => 'بازگشت' , 'callback_data' => '0' ];
                $keyboard = json_encode(['inline_keyboard' => [$inlinebutton]]) ;
                $keyboard = str_replace("},{","|",$keyboard);
                $keyboard_segments = explode('|',$keyboard);
                $keyboard_double = "";
                $i =0;
                //$keyboard = str_replace("},{","}],[{",$keyboard);
                foreach($keyboard_segments as $segment)
                {
                    if ($i==0)
                        $keyboard_double .= $segment;
                    else if ($i % 2 == 0)
                        $keyboard_double .= '}],[{'. $segment;
                    else
                        $keyboard_double .= '},{'. $segment;
                    $i++;
                }
                apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => "فهرست:" . $crumb . "          ", 'reply_markup' => $keyboard_double ));
            }
            else
            {
                apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => "بروز اشکال در خواندن لیست، لطفاً مجددا تلاش فرمایید.".$verse));
            }
        }
        else if (strpos($update['callback_query']['data'],'book') !== false ) {
            mb_internal_encoding('UTF-8');
            $verse = file_get_contents('https://poem.adad.ws/poem.php?func=listpoems&page=0&key='.str_replace("book","",$update['callback_query']['data']));
            updateBotState($chat_id,$user_id,$user_name, $username, 41, $message_id,($update['callback_query']['data']),0);
            //apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => $verse));
            $verse_arr = json_decode($verse, true);
            if ($verse_arr) {
                //apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => $verse_arr));
                $i = 0;
                $len = count($verse_arr['items']);
                foreach ($verse_arr['items'] as $item) {
                    $i++;
                    if ($i<$len) {
                        apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => '' . farsinumbers($item['item']) . ' - ' . $item['title'] . ' 
' . $item['first_verse'] .
                            '
                                          ', 'reply_markup' => json_encode([
                            'inline_keyboard' =>

                                [[['text' => "نمایش شعر/متن", 'callback_data' => 'poem' . $item['id'] . '']],
                                    [['text' => "بازگشت", 'callback_data' => '0']]]
                        ])));
                    }
                    else
                    {
                        apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => '' . farsinumbers($item['item']) . ' - ' . $item['title'] . ' 
' . $item['first_verse'] .
                            '
                                          ', 'reply_markup' => json_encode([
                            'inline_keyboard' =>

                                [[['text' => "نمایش شعر/متن", 'callback_data' => 'poem' . $item['id'] . '']],
                                    [['text' => "موارد بعد", 'callback_data' => '41']],
                                    [['text' => "بازگشت", 'callback_data' => '0']]]
                        ])));
                    }
                }
            }
            else
            {
                apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => "بروز اشکال در خواندن لیست، لطفاً مجددا تلاش فرمایید."));
            }
        }
        else if (strpos($update['callback_query']['data'],'remove_time') !== false ) {
            mb_internal_encoding('UTF-8');
            file_get_contents('https://poem.adad.ws/subscriptions.php?remove=1&key='.str_replace("remove_time","",$update['callback_query']['data']));
            apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => "زمان مورد نظر حذف شد."));
            updateBotState($chat_id,$user_id,$user_name, $username, 52, $message_id,$text,0);
            $items = file_get_contents("https://poem.adad.ws/subscriptions.php?subscriptions=1&chat_id=" . $chat_id);
            $items_arr = json_decode($items, true);
            $len = count($items_arr);
            $i = 0;
            foreach ($items_arr as $item) {
                $i++;
                $parts = explode(":", $item);
                $hour = "".$parts[1];
                $min = "".$parts[2];

                if ((int)$hour < 10)
                    $hour = "0" . $hour;

                if ((int)$min < 1)
                    $min = "0" . $min;

                apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => farsinumbers(" زمان تنظیم شده: " . $hour . ":" . $min ) , 'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [
                            ['text' => "حذف", 'callback_data' => 'remove_time'.$parts[0].'']]
                    ]
                ])));
            }
        }
        else if ($update['callback_query']['data'] == 0) {
            showMainMenu($chat_id);
        }
        else if ($update['callback_query']['data'] == 1) {
            updateBotState($chat_id,$user_id,$user_name, $username, 1, $message_id,"مشاعره",0);
            apiRequestJson('editMessageText',[
                'chat_id'=>$chat_id,
                'message_id'=>$message_id,
                'text'=>   "لطفاً برای شروع مشاعره یک بیت ارسال نمایید. بین دو مصرع بوسیله کلید Enter یا * فاصله بگذارید.",
                'reply_markup'=>json_encode([
                    'inline_keyboard'=>[
                        [
                            ['text'=>"بازگشت",'callback_data'=>'0']
                        ]
                    ]])]);
        }
        else if ($update['callback_query']['data'] == 2) {
            updateBotState($chat_id,$user_id,$user_name, $username, 2, $message_id, $text, 0);
            apiRequestJson('editMessageText',[
                'chat_id'=>$chat_id,
                'message_id'=>$message_id,
                'text'=> "لطفاً ابتدا نیت کنید و بعد کلید تفأل را بزنید.",
                'reply_markup'=>json_encode([
                    'inline_keyboard'=>[
                        [
                            ['text'=>"بازگشت",'callback_data'=>'0'],
                            ['text'=>"تفأل",'callback_data'=>'21']
                        ]
                    ]])]);
        }
        else if ($update['callback_query']['data'] == 21) {
            updateBotState($chat_id,$user_id,$user_name, $username, 2, $message_id, $text, 0);
            $verse = file_get_contents('https://poem.adad.ws/poem.php?func=tafaol');
            apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => $verse, 'reply_markup'=>json_encode([
                'inline_keyboard'=>[
                    [
                        ['text'=>"بازگشت",'callback_data'=>'0'],
                        ['text'=>"تفأل",'callback_data'=>'21']
                    ]
                ]])));
        }
        else if ($update['callback_query']['data'] == 3) {
            updateBotState($chat_id,$user_id,$user_name, $username, 3, $message_id, $text, 0);
            apiRequestJson('editMessageText',[
                'chat_id'=>$chat_id,
                'message_id'=>$message_id,
                'text'=>  "جستجو به چند روش ممکن است:

الف) جستجوي ساده براي يك كلمه  یا عبارت: فقط آن كلمه یا عبارت را وارد كنيد،
مثال : توانا

ب ) جستجوی کلمات ابتدای مصرع : بعد از چند كلمه ابتدايي مصرع، علامت % يا & بگذاريد،
مثال : توانا بود&

ج) جستجوي كلمات انتهاي مصرع : قبل از چند كلمه انتهايي مصرع، علامت % يا & بگذاريد،
مثال : &هر كه دانا بود

د) جستجوي چند كلمه در يك مصرع : بعد از هر كلمه علامت % يا & بگذاريد،
مثال : توانا& دانا&
",
                'reply_markup'=>json_encode([
                    'inline_keyboard'=>[
                        [
                            ['text'=>"بازگشت",'callback_data'=>'0'],
                        ]
                    ]])]);
        }
        else if ($update['callback_query']['data'] == 31) {

            try {
                $text = str_replace("٪","%",$text);
                $verse = file_get_contents('https://poem.adad.ws/poem.php?func=search&page='.($state['page_index'] + 1).'&key=' . urlencode($state['last_message']));
                $verse_arr = json_decode($verse, true);
                if (!$verse_arr) {
                    apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => $verse, 'reply_markup' => json_encode([
                        'inline_keyboard' => [
                            [
                                ['text' => "بازگشت", 'callback_data' => '0'],
                            ]
                        ]])));
                    exit;
                }
                $len = count($verse_arr['items']);
                $i = 0;
                foreach ($verse_arr['items'] as $item) {
                    $i++;
                    if ($i < $len) {
                        apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => farsinumbers($item['item']) . '-'
                            . $item['poem']['verse-r'] . '
' . $item['poem']['verse-l'] . '

#' . $item['poem']['poet'] . '
#' . $item['poem']['book'] . ' - #' . $item['poem']['parent'] . '
 ', 'reply_markup' => json_encode([
                            'inline_keyboard' => [
                                [
                                    ['text' => "نمایش متن/شعر کامل", 'callback_data' => 'poem'.$item['poem_id'].'']]
                            ]
                        ])));
                    }
                    else{
                        apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => farsinumbers($item['item']) . '-'
                            . $item['poem']['verse-r'] . '
' . $item['poem']['verse-l'] . '

#' . $item['poem']['poet'] . '
#' . $item['poem']['book'] . ' - #' . $item['poem']['parent'] . '
 ', 'reply_markup' => json_encode([
                            'inline_keyboard' => [
                                [
                                    ['text' => "نمایش متن/شعر کامل", 'callback_data' => 'poem'.$item['poem_id'].'']],
                                [['text' => "بازگشت", 'callback_data' => "0"],['text' => "موارد بعدی", 'callback_data' => "31"]]
                            ]
                        ])));
                    }
                }
                updateBotState($chat_id,$user_id,$user_name, $username, 3, $message_id, $state['last_message'], ($state['page_index'] + 1));
            }
            catch (Exception $err)
            {
                $current = "";
                $file = 'error.log';
                file_get_contents($file, $current);
                $current .= $err . '
                    ';
                file_put_contents($file, $current);
            }
        }
        else if ($update['callback_query']['data'] == 4) {
            /*apiRequestJson('editMessageText',[
                'chat_id'=>$chat_id,
                'message_id'=>$message_id,
                'text'=> "این قسمت بزودی اضافه خواهد شد.",
                'reply_markup'=>json_encode([
                    'inline_keyboard'=>[
                        [
                            ['text'=>"بازگشت",'callback_data'=>'0']
                        ]
                    ]])]);
            exit;*/
            mb_internal_encoding('UTF-8');
            $verse = file_get_contents('https://poem.adad.ws/poem.php?func=listpoets&page=0&key=0');
            //apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => $verse));
            $verse_arr = json_decode($verse, true);
            if ($verse_arr) {
                $inlinebutton = [];
                $i = 0;
                foreach ($verse_arr['items'] as $item) {
                    $inlinebutton[$i] = ['text' => '' . $item['text'] . '' , 'callback_data' => 'poet' . $item['id'] .'' ];
                    $i++;
                }
                $inlinebutton[$i] = ['text' => 'بازگشت' , 'callback_data' => '0' ];
                $keyboard = json_encode(['inline_keyboard' => [$inlinebutton]]) ;
                $keyboard = str_replace("},{","|",$keyboard);
                $keyboard_segments = explode('|',$keyboard);
                $keyboard_double = "";
                $i =0;
                //$keyboard = str_replace("},{","}],[{",$keyboard);
                foreach($keyboard_segments as $segment)
                {
                    if ($i==0)
                        $keyboard_double .= $segment;
                    else if ($i % 2 == 0)
                        $keyboard_double .= '}],[{'. $segment;
                    else
                        $keyboard_double .= '},{'. $segment;
                    $i++;
                }
                updateBotState($chat_id,$user_id,$user_name, $username, 4, $message_id,"",0);
                apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => "فهرست:                                    ", 'reply_markup' => $keyboard_double ));
            }
            else
            {
                apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => "بروز اشکال در خواندن لیست، لطفاً مجددا تلاش فرمایید."));
            }
        }
        else if ($update['callback_query']['data'] == 41) {
            mb_internal_encoding('UTF-8');
            $verse = file_get_contents('https://poem.adad.ws/poem.php?func=listpoems&page=' . ($state['page_index'] + 1) . '&key='.str_replace("book","",$state['last_message']));
            updateBotState($chat_id,$user_id,$user_name, $username, 41, $message_id,($update['callback_query']['data']),($state['page_index'] + 1));
            //apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => $verse));
            $verse_arr = json_decode($verse, true);
            if ($verse_arr) {
                //apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => $verse_arr));
                $i = 0;
                $len = count($verse_arr['items']);
                foreach ($verse_arr['items'] as $item) {
                    $i++;
                    if ($i<$len) {
                        apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => '' . farsinumbers($item['item']) . ' - ' . $item['title'] . ' 
' . $item['first_verse'] .
                            '
                                          ', 'reply_markup' => json_encode([
                            'inline_keyboard' =>

                                [[['text' => "نمایش شعر/متن", 'callback_data' => 'poem' . $item['id'] . '']],
                                    [['text' => "بازگشت", 'callback_data' => '0']]]
                        ])));
                    }
                    else
                    {
                        apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => '' . farsinumbers($item['item']) . ' - ' . $item['title'] . ' 
' . $item['first_verse'] .
                            '
                                          ', 'reply_markup' => json_encode([
                            'inline_keyboard' =>

                                [[['text' => "نمایش شعر/متن", 'callback_data' => 'poem' . $item['id'] . '']],
                                    [['text' => "موارد بعد", 'callback_data' => '41']],
                                    [['text' => "بازگشت", 'callback_data' => '0']]]
                        ])));
                    }
                }
            }
            else
            {
                apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => "بروز اشکال در خواندن لیست، لطفاً مجددا تلاش فرمایید.".$verse));
            }
        }
        else if ($update['callback_query']['data'] == 5) {
            updateBotState($chat_id,$user_id,$user_name, $username, 5, $message_id, $text, 0);
            apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => " در این قسمت شما می توانید روبات را طوری تنظیم نمایید که هر روز در زمان (های) مقرر شعری را بصورت تصادفی ارسال نماید. لازم بذکر است اگر روبات در یک گروه اضافه شده باشد و در آنجا تنظیم شود شعر در همان گروه ارسال خواهد شد.", 'reply_markup' => json_encode([
                'inline_keyboard' =>

                    [[['text' => "اضافه کردن زمان جدید", 'callback_data' => '51']],
                        [['text' => "نمایش زمانهای قبلی", 'callback_data' => '52']],
                        [['text' => "بازگشت", 'callback_data' => '0']]]
            ])));
        }
        else  if ($update['callback_query']['data'] == 51) {
            updateBotState($chat_id,$user_id,$user_name, $username, 51, $message_id,$text,0);
            apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" =>  farsinumbers("لطفاً ساعت را بصورت hh:mm و 24 ساعته ارسال نمایید و دقیقه مضربی از 10 باشد."), 'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [
                        ['text' => "بازگشت", 'callback_data' => '0'],
                    ]
                ]])));
        }
        else  if ($update['callback_query']['data'] == 52) {
            updateBotState($chat_id,$user_id,$user_name, $username, 52, $message_id,$text,0);
            $items = file_get_contents("https://poem.adad.ws/subscriptions.php?subscriptions=1&chat_id=" . $chat_id);
            $items_arr = json_decode($items, true);
            $len = count($items_arr);
            $i = 0;
            foreach ($items_arr as $item) {
                $i++;
                $parts = explode(":", $item);
                apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => farsinumbers(" زمان تنظیم شده: " . $parts[1] . ":" . $parts[2]), 'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [
                            ['text' => "حذف", 'callback_data' => 'remove_time'.$parts[0].'']]
                    ]
                ])));
            }
        }
        exit;
    }
    catch (Exception $er)
    {
        $file = 'callback-error.log';
        $current = file_get_contents($file);
        $current .= $er;
        file_put_contents($file, $current);
    }
}
else
{
    $file = 'no-callback.log';
    $current = file_get_contents($file);
    $current .= print_r($update,true).'
       ';
    file_put_contents($file, $current);
}

if (isset($update["message"])) {
    processMessage($update["message"]);
}

?>