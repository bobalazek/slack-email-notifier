# Slack Email Notifier
A simple script, that sends notifications to you channel, after you get an email.

## Setup ##
* Download the script
* Extract it on your server
* Configure settings (under app/config.php)
* Setup a cron job (`curl -X GET 'http://your-domain.com/slack-email-notifier/execute?token=YOUR-TOKEN'`)
* It's done

## How does it work? ##
The script connects to your IMAP mail account and finds posts the unread mails at slack. Because of that, you will NEED to set all other devices / clients to IMAP (instead of POP3). Workaround soon!

## FAQ ##
### Do the fetched emails get flagged as read? ###
Yes, at this point all the fetched emails get flagged as read. I'll do a workaround soon!

## License ##
Slack Email Notifier is licensed under the MIT license.
