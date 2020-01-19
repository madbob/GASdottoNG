<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use Log;

use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use Aws\Sns\Exception\InvalidSnsMessageException;

use App\InnerLog;

class MailController extends Controller
{
    public function postStatus(Request $request)
    {
        $message = Message::fromRawPostData();
        $validator = new MessageValidator();

        try {
            $validator->validate($message);
        }
        catch (InvalidSnsMessageException $e) {
            Log::error('SNS Message Validation Error: ' . $e->getMessage());
            abort(404);
        }

        if ($message['Type'] === 'SubscriptionConfirmation') {
            file_get_contents($message['SubscribeURL']);
        }
        else if ($message['Type'] === 'Notification') {
            $data = json_decode($message['Message']);

            if ($data->notificationType == 'Bounce') {
                try {
                    $email = $data->bounce->bouncedRecipients[0]->emailAddress;
                    $message = $data->bounce->bouncedRecipients[0]->diagnosticCode;
                    $message = sprintf(_i('Impossibile inoltrare mail a %s: %s', $email, $message));
                    $message = addslashes($message);

                    if (global_multi_installation()) {
                        $instances = get_instances();
                        $now = date('Y-m-d G:i:s');

                        foreach($instances as $i) {
                            try {
                                $db = get_instance_db($i);
                                $db_emails = $db->select("SELECT COUNT(*) as count FROM contacts WHERE type = 'email' and value = '$email'");
                                if ($db_emails[0]->count != 0) {
                                    $db->insert("INSERT INTO inner_logs (level, type, message, created_at, updated_at) VALUES ('error', 'mail', '$message', '$now', '$now')");
                                }
                            }
                            catch(\Exception $e) {
                                Log::error('Impossibile accedere a istanza per controllo SNS: ' . $e->getMessage());
                            }
                        }
                    }
                    else {
                        InnerLog::error('mail', $message);
                    }
                }
                catch(\Exception $e) {
                    Log::error('Notifica SNS illeggibile: ' . $e->getMessage() . ' - ' . print_r($data, true));
                }
            }
        }
    }
}
