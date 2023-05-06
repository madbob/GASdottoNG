<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use Log;

use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use Aws\Sns\Exception\InvalidSnsMessageException;

use App\Contact;
use App\InnerLog;

class MailController extends Controller
{
    private function saveInstances($email, $message)
    {
        $instances = get_instances();
        $now = date('Y-m-d G:i:s');

        foreach($instances as $i) {
            try {
                $db = get_instance_db($i);
                $db_emails = $db->select("SELECT COUNT(*) as count FROM contacts WHERE type = 'email' and value = '$email'");
                if ($db_emails[0]->count != 0) {
                    $db->insert("INSERT INTO inner_logs (level, type, message, created_at, updated_at) VALUES ('error', 'mail', '$message', '$now', '$now')");
                    $db_failures = $db->select("SELECT COUNT(*) as count FROM inner_logs WHERE type = 'email' and message like '%$email%'");
                    if ($db_failures[0]->count >= 3) {
                        $db->delete("DELETE FROM contacts WHERE type = 'email' and value = '$email'");
                        $message = _i('Rimosso indirizzo email ' . $email);
                        $db->insert("INSERT INTO inner_logs (level, type, message, created_at, updated_at) VALUES ('error', 'mailsuppression', '$message', '$now', '$now')");
                    }
                }
            }
            catch(\Exception $e) {
                // dummy
            }
        }
    }

    private function registerBounce($email, $message)
    {
        $message = sprintf(_i('Impossibile inoltrare mail a %s: %s', [$email, $message]));
        $message = addslashes($message);

        if (global_multi_installation()) {
            $this->saveInstances($email, $message);
        }
        else {
            InnerLog::error('mail', $message);
        }
    }

    public function postStatusSES(Request $request)
    {
		if (env('MAIL_MAILER') == 'ses') {
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
	            $dummy = file_get_contents($message['SubscribeURL']);
	        }
	        else if ($message['Type'] === 'Notification') {
	            $data = json_decode($message['Message']);
	            if ($data->notificationType == 'Bounce') {
					try {
						$email = $data->bounce->bouncedRecipients[0]->emailAddress;
			            $message = $data->bounce->bouncedRecipients[0]->diagnosticCode ?? '???';
		                $this->registerBounce($email, $message);
					}
					catch(\Exception $e) {
						Log::error('Notifica SNS illeggibile: ' . $e->getMessage() . ' - ' . print_r($data, true));
					}
	            }
	        }
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
		                $this->registerBounce($email, $message);

                        /*
                            Se l'indirizzo mail è stato bloccato, è inutile
                            inoltrare altri messaggi: qui ne cambio il tipo per
                            evitare di generare altre mail a vuoto
                        */
                        if ($event == 'blocked') {
                            Contact::where('type', 'email')->where('value', $email)->update([
                                'type' => 'skip_email',
                            ]);
                        }
					}
					catch(\Exception $e) {
						Log::error('Notifica SendInBlue illeggibile: ' . $e->getMessage() . ' - ' . print_r($request->all(), true));
					}
				}
			}
		}
	}
}
