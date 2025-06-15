<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use Aws\Sns\Exception\InvalidSnsMessageException;

use App\InnerLog;

class MailController extends Controller
{
    private function saveInstances($event, $email, $message)
    {
        $instances = get_instances();
        $now = date('Y-m-d G:i:s');

        foreach ($instances as $i) {
            try {
                $db = get_instance_db($i);
                $db_emails = $db->select("SELECT COUNT(*) as count FROM contacts WHERE type = 'email' and value = '$email'");
                if ($db_emails[0]->count != 0) {
                    $db->insert("INSERT INTO inner_logs (level, type, message, created_at, updated_at) VALUES ('error', 'mail', '$message', '$now', '$now')");
                    $db_failures = $db->select("SELECT COUNT(*) as count FROM inner_logs WHERE type = 'mail' and message = '$message'");

                    if ($db_failures[0]->count >= 3 || $event == 'blocked') {
                        $db->delete("UPDATE contacts SET type = 'skip_email' WHERE type = 'email' and value = '$email'");
                        $message = __('mail.help.removed_email_log', ['address' => $email]);
                        $db->insert("INSERT INTO inner_logs (level, type, message, created_at, updated_at) VALUES ('error', 'mailsuppression', '$message', '$now', '$now')");
                        Log::info($message);
                    }
                }
            }
            catch (\Exception $e) {
                // dummy
            }
        }
    }

    private function registerBounce($event, $email, $message)
    {
        $message = sprintf(__('mail.help.send_error', [
            'email' => $email,
            'message' => $message,
        ]));

        $message = addslashes($message);

        if (global_multi_installation()) {
            $this->saveInstances($event, $email, $message);
        }
        else {
            InnerLog::error('mail', $message);
        }
    }

    public function postStatusScaleway(Request $request)
    {
        if (env('MAIL_MAILER') == 'scaleway') {
            $message = Message::fromRawPostData();
            $validator = new MessageValidator(null, '/\.scw\.cloud$/');

            try {
                $validator->validate($message);
            }
            catch (InvalidSnsMessageException $e) {
                Log::error('SNS Message Validation Error: ' . $e->getMessage() . "\n" . print_r($message, true));
                abort(404);
            }

            try {
                $body = json_decode($request->getContent());

                if ($body) {
                    /*
                        Per automatizzare la procedura di conferma della
                        registrazione del webhook
                    */
                    if (isset($body->SubscribeURL)) {
                        @file_get_contents($body->SubscribeURL);

                        return;
                    }
                    else {
                        $payload = json_decode($body->Message);
                        $event = $payload->type;
                        $email = $payload->email_to;
                        $message = $payload->email_response_message;
                        $this->registerBounce($event, $email, $message);
                    }
                }
            }
            catch (\Exception $e) {
                Log::error('Notifica Scaleway illeggibile: ' . $e->getMessage() . ' - ' . print_r($request->all(), true));
            }
        }
    }
}
