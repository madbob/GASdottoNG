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
                        $message = _i('Rimosso indirizzo email ' . $email);
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
        $message = sprintf(_i('Impossibile inoltrare mail a %s: %s', [$email, $message]));
        $message = addslashes($message);

        if (global_multi_installation()) {
            $this->saveInstances($event, $email, $message);
        }
        else {
            InnerLog::error('mail', $message);
        }
    }

    public function postStatusSendinblue(Request $request)
    {
        if (env('MAIL_MAILER') == 'sendinblue') {
            /*
                Nota bene: qui arrivano tutte le segnalazioni webhook generate
                da SendInBlue, incluse quelle non generate da GASdotto.
                Il tag "gasdotto" viene aggiunto dal listener CustomMailTag
            */
            $tags = $request->input('tags');
            if (is_null($tags) || empty($tags)) {
                return;
            }

            if (in_array('gasdotto', $tags)) {
                $event = $request->input('event', '');

                if (in_array($event, ['hard_bounce', 'soft_bounce', 'complaint', 'blocked', 'error'])) {
                    try {
                        $email = $request->input('email');
                        $message = $request->input('reason', '???');
                        $this->registerBounce($event, $email, $message);
                    }
                    catch (\Exception $e) {
                        Log::error('Notifica SendInBlue illeggibile: ' . $e->getMessage() . ' - ' . print_r($request->all(), true));
                    }
                }
            }
        }
    }

    public function postStatusScaleway(Request $request)
    {
        if (env('MAIL_MAILER') == 'scaleway') {
            $api_endpoint = 'https://api.scaleway.com/transactional-email/v1alpha1/regions/fr-par/webhooks';

            $message = Message::fromRawPostData();
            $validator = new MessageValidator();

            try {
                $validator->validate($message);
            }
            catch (InvalidSnsMessageException $e) {
                Log::error('SNS Message Validation Error: ' . $e->getMessage());
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
                        $message = $payload->email_error;
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
