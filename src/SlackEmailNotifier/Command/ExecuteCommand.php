<?php

namespace SlackEmailNotifier\Command;

use PhpImap\Mailbox as ImapMailbox;
use PhpImap\IncomingMail;
use PhpImap\IncomingMailAttachment;

class ExecuteCommand
{
    public $app;

    public function __construct(\Silex\Application $app) {
        $this->app = $app;
    }

    public function execute() {
        $response = array(
            'ok' => true,
            'new_emails' => 0,
        );

        $emails = $this->app['emails'];

        if(isset($emails) && is_array($emails)) {
            $newEmails = 0;
            $errors = array();

            foreach($emails as $email => $emailData) {
                $result = $this->executeByMailbox($emailData, $email);

                if(isset($result['new_emails'])) {
                    $newEmails += $result['new_emails'];
                }

                if(isset($result['error'])) {
                    $errors[] = $result['error'];
                }
            }

            $response['new_emails'] = $newEmails;

            if(count($errors)) {
                $response['errors'] = $errors;
                $response['ok'] = false;
            }
        }

        return $response;
    }

    public function executeByMailbox($mailboxData, $email) {
        $output = array(
            'ok' => true,
            'new_emails' => 0,
        );

        try {
            $mailboxPath = '{'.$mailboxData['host'].':'.
                $mailboxData['port'].
                $mailboxData['flags'].'}INBOX'
            ;

            $mailbox = new \PhpImap\Mailbox(
                $mailboxPath,
                $mailboxData['username'],
                $mailboxData['password'],
                ROOT_DIR.'/var/attachments'
            );

            $mailIds = $mailbox->searchMailBox('UNSEEN');

            if($mailIds) {
                $newEmails = 0;

                foreach($mailIds as $mailIdKey => $mailId) {
                    $email = $mailbox->getMail($mailId);

                    $slackAttachment = array(
                        'color' => '#999999',
                        'fields' => array(
                            array(
                                'title' => 'Subject',
                                'value' => $email->subject,
                                'short' => true,
                            ),
                            array(
                                'title' => 'From',
                                'value' => $email->fromAddress,
                                'short' => true,
                            ),
                            array(
                                'title' => 'Date',
                                'value' => $email->date,
                                'short' => true,
                            ),
                            array(
                                'title' => 'Text',
                                'value' => $email->textPlain,
                                'short' => false,
                            ),
                        ),
                    );

                    $slackAttachments = array($slackAttachment);

                    $slackData = array(
                        'username' => $mailboxData['slack']['username'],
                        'icon_emoji' => $mailboxData['slack']['icon_emoji'],
                        'channel' => $mailboxData['slack']['channel'],
                        'attachments' => json_encode($slackAttachments),
                    );

                    if(isset($mailboxData['slack']['text']) &&
                        $mailboxData['slack']['text']) {
                        $slackData['text'] = $mailboxData['slack']['text'];
                    }

                    $slackResponse = $this->app['slack.commander']->execute(
                        'chat.postMessage',
                        $slackData
                    );

                    $newEmails++;
                }

                $output['new_emails'] = $newEmails;
            }
        } catch( \Exception $e ) {
            $output = array(
                'ok' => false,
                'error' => $email.' email error: '.$e->getMessage(),
            );
        }

        return $output;
    }
}
