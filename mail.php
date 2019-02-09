<?

/*
 * @param $params - Array (Параметры запроса)
 * @param $url - String (URL запроса)
 *
 */
function sendToAmoCRM ($params, $url) {
    $link = 'https://x5fitnessmarketing.amocrm.ru' . $url;
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
    curl_setopt($curl, CURLOPT_URL, $link);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/cookie.txt');
    curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/cookie.txt');
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

    $out = curl_exec($curl);
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);


    // echo $url;
    $code = (int)$code;
    $errors = array(
        301 => 'Moved permanently',
        400 => 'Bad request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not found',
        500 => 'Internal server error',
        502 => 'Bad gateway',
        503 => 'Service unavailable'
    );

    try {
        if($code!=200 && $code!=204)
            throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error',$code);
    } catch(Exception $E) {
        // die('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
        return array (
            'status' => false,
            'message' => 'Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode(),
            // '' => json_decode($out, true)
        );
    }

    // return json_decode($out, true);

    // echo $out;
    return array (
        'status' => true,
        'out' => json_decode($out, true),
    );
}



if( isset($_POST['email']) && $_POST['email'] != "") {



    ///////////////
    // amoCRM
    ///////////////

    $user = array(
        'USER_LOGIN' => 'tkatchnastenka@yandex.ru', #Ваш логин (электронная почта)
        'USER_HASH' => '3c298f1bf1c26830768196c24399567efe8fafb7' #Хэш для доступа к API (смотрите в профиле пользователя)
    );

    $subdomain = 'tkatchnastenka'; #Наш аккаунт - поддомен

    $manager = 3236818; // Идентификатор ответственного
    $pipelineId = 1543618;





    $leads = array(
        'name' => 'Новая заявка с сайта',
        'pipeline_id' => $pipelineId,
        'status_id' => 11536083, // ???
        'responsible_user_id' => $manager,
        'custom_fields' => array()
    );

    $contact = array(
        'responsible_user_id' => $manager,
        'custom_fields' => array()
    );





    //////////////////
    // Аутентификация amoCRM
    //////////////////

    sendToAmoCRM($user, '/private/api/auth.php?type=json');



    $contact['name'] = $_POST["name"];

    $contact['custom_fields'][] = array(
        'id' => 360921,
        'values' => array(
            array(
                'value' => $_POST["email"],
                'enum' => '563809'
            )
        )
    );

    //////////////////
    // Создание нового контакта
    //////////////////

    $contact_params = Array();
    $contact_params['add'][] = $contact;

    $response = sendToAmoCRM($contact_params, '/api/v2/contacts');

    $user_id = $response['out']['_embedded']['items'][0]['id'];


    if ($response['status']) {


        //////////////////
        // Создание новой сделки с присваиванием контакта
        //////////////////

        $leads['contacts_id'] = $user_id;
        // $leads['company_id'] = $company_id;
        $leads_params = Array();
        $leads_params['add'][0] = $leads;

        sendToAmoCRM($leads_params, '/api/v2/leads'); 


        // // Создание примечания в ленте контакта
        // $note['element_id'] = $user_id;

        // $note_params = Array();
        // $note_params['add'][0] = $note;

        // sendToAmoCRM($note_params, '/api/v2/notes'); 
    }



    echo json_encode(array('sended'=>true, 'type'=>$_POST['form-type'], 'message'=>''));

}



?>