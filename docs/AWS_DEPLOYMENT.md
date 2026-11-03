# AWS Deployment Guide

This guide prepares the Ashford University portal for AWS. **No AWS
resources have been created and no deployment has been performed or
verified** — these are instructions and configuration for when you are
ready to deploy.

## Recommended architecture (simplest secure option)

```
Browser --HTTPS--> Application Load Balancer / EC2 (Apache + PHP 8)
                          |
                          +--> Amazon RDS for MySQL (private subnet)
                          +--> Amazon SES or external SMTP (PHPMailer)
                          +--> AWS Secrets Manager or SSM Parameter Store
                          +--> Amazon CloudWatch (logs + metrics)
```

- **Compute:** one EC2 instance (or Elastic Beanstalk PHP platform) is
  enough for a university portal. Elastic Beanstalk is the lowest-effort
  option because it manages the Apache + PHP stack for you.
- **Database:** Amazon RDS for MySQL in a private subnet, reachable only
  from the application security group. Never publicly accessible.
- **Email:** Amazon SES (or any SMTP provider) for PHPMailer.
- **Secrets:** AWS Secrets Manager or SSM Parameter Store (SecureString).
  The application reads everything from environment variables / `.env`,
  so secrets never enter the repository.

## 1. Package the application

The app is plain PHP — copyable as-is:

```bash
# From the project root (exclude local artifacts)
zip -r ashford-portal.zip . \
	-x ".env" "*.log" ".git/*" "docs/*" "*.md"
```

PHPMailer ships in `lib/PHPMailer/` (no Composer step needed on the server).

## 2. Environment configuration

Create `.env` on the server and set production values:

```ini
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=America/New_York
APP_BASE_PATH=

DB_HOST=<rds-endpoint>
DB_PORT=3306
DB_NAME=ashford_university
DB_USER=<rds-username>
DB_PASS=<rds-password>

AUTH_MAX_ATTEMPTS=3
AUTH_RESET_TTL=3600
AUTH_RESET_COOLDOWN=60

MAIL_TRANSPORT=phpmailer
MAIL_HOST=email-smtp.us-east-1.amazonaws.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=<ses-smtp-username>
MAIL_PASSWORD=<ses-smtp-password>
MAIL_FROM_ADDRESS=no-reply@ashford.edu
MAIL_FROM_NAME=Ashford University
```

Restrict file permissions: `chmod 640 .env` owned by the web user.

Better still, inject these as Elastic Beanstalk environment properties or
store them in Secrets Manager and read them at boot; never commit them.

## 3. Database setup

Run on the RDS instance, in order:

```sql
SOURCE database/schema.sql;
SOURCE database/migrations/0002_registration_windows.sql;
SOURCE database/migrations/0003_semester_start.sql;
SOURCE database/migrations/0004_master_schedule.sql;
```

Then create the first administrator by visiting `/install.php` once. The
installer disables itself once any user exists; all further accounts are
created from **Admin → Users**.

## 4. Web server

Point the document root at the project root and require HTTPS:

```apache
<VirtualHost *:80>
	ServerName portal.ashford.edu
	Redirect permanent / https://portal.ashford.edu/
</VirtualHost>

<VirtualHost *:443>
	ServerName portal.ashford.edu
	DocumentRoot /var/www/ashford

	SSLEngine on
	SSLCertificateFile    /etc/ssl/certs/ashford.crt
	SSLCertificateKeyFile /etc/ssl/private/ashford.key

	<Directory /var/www/ashford>
		AllowOverride All
		Require all granted
	</Directory>

	ErrorLog  /var/log/apache2/ashford-error.log
	CustomLog /var/log/apache2/ashford-access.log combined
</VirtualHost>
```

Use an AWS Certificate Manager certificate when fronting with a load
balancer. Session cookies are marked `Secure` automatically when the
request arrives over HTTPS (`includes/session.php`).

## 5. Production error handling

With `APP_DEBUG=false` in `.env` and PHP's `display_errors=Off` +
`log_errors=On`, users see generic messages while details go to the error
log (already wired through `error_log()` calls in the application).

## 6. PHPMailer in production

`includes/mail.php` uses PHPMailer over SMTP. Production values:

- `MAIL_TRANSPORT=phpmailer`
- Amazon SES SMTP host/port (e.g. `email-smtp.us-east-1.amazonaws.com`,
  port 587, STARTTLS) or your provider's host,
- credentials from Secrets Manager / Parameter Store, injected as
  environment variables.

Verify sending before go-live from `/forgot_password.php`; the reset link
also appears in the PHP error log when `MAIL_TRANSPORT=null`.

## 7. Backups and recovery

- Enable RDS automated backups (retention 7-35 days) and a maintenance
  window.
- Add a manual snapshot before each release.
- Restore drill: create a new instance from a snapshot, point a staging
  environment at it, and verify the portal end to end (sign in, register
  a student for a section, submit a grade).

## 8. Rollback

- Elastic Beanstalk: redeploy the previous application version.
- Manual EC2: keep the previous release directory and swap the document
  root, or restore from AMI snapshot.
- Database: roll forward with migrations only; use the pre-release
  snapshot for a full rollback.

## 9. Monitoring

- CloudWatch agent on the instance shipping Apache error/access logs.
- Alarm on HTTP 5xx rate and RDS CPU/storage.
- The portal writes operational events to PHP error log and the
  `Audit_Log` table (`admin/audit.php`).

## 10. Production-readiness checklist

- [ ] `.env` present, permissions 640, not in source control
- [ ] `APP_ENV=production`, `APP_DEBUG=false`, `display_errors=Off`
- [ ] HTTPS enforced; HSTS header configured at the load balancer
- [ ] RDS private, security group limited to the app instances
- [ ] SMTP credentials verified with a live reset email
- [ ] `install.php` visited once, then confirm it refuses to re-run
- [ ] First administrator password documented in the secret store
- [ ] Automated backups and a restore drill completed
- [ ] CloudWatch alarms and log shipping confirmed
