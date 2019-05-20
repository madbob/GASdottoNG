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
                            $db_emails = DB::table($i . '.contacts')->where('type', 'email')->where('value', $email)->count();
                            if ($db_emails != 0) {
                                DB::insert("INSERT INTO ${i}.inner_logs (level, type, message, created_at, updated_at) VALUES ('error', 'mail', '$message', '$now', '$now')");
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
