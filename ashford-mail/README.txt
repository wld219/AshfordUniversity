ASHFORD MAIL — LOCAL DEMO INBOX

FILES
  dev/mailbox.php       Outlook-style local demo inbox
  includes/dev_mail.php Helper to append mock messages

INSTALL
  Copy the dev and includes files into matching folders in Ashford-New.
  No database changes are required just to view the inbox.
  Start PHP using: php -S localhost:8001
  Visit: http://localhost:8001/dev/mailbox.php

TEST MESSAGE (optional)
  Set APP_ENV=local in the shell running PHP.
  From a PHP script, require includes/dev_mail.php and call:
  dev_mail_deliver('liam.walker@ashford.edu', 'Password Reset Verification', 'Your code is 482913.');
  This example code is for preview only and does not reset any password.

NEXT STEP
  Integrate the helper with the password reset request handler. Generate a real
  random code, store only its hash in Password_Reset, enforce expiry/attempt
  limits, and do not reveal the code on the reset form. The current reset flow
  still sends a link; this package does not silently modify it.

SECURITY
  Mailbox denies non-loopback HTTP access; helper requires APP_ENV=local.
  Codes are plaintext in the demo inbox by design. Never deploy these files
  or the storage/dev_mail.json file to AWS or a public web server.
  For production, use independently verified recovery email delivery.
