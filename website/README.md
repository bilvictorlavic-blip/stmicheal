# ST. MICHAEL SENIOR SECONDARY SCHOOL NAAMA-MITYANA

A responsive school website with an enquiry service for a local XAMPP installation.

## Run locally with XAMPP

1. Copy this project folder into `C:\xampp\htdocs\` and start Apache and MySQL from the XAMPP Control Panel.
2. In phpMyAdmin, import `database/schema.sql`.
3. Configure the PHP environment variables listed below, then restart Apache.
4. Open `http://localhost/website/` (replace `website` with the folder name you used).
5. The admissions form emails each enquiry to `stmichaelsssn@gmail.com` and saves a copy to MySQL. Administrators can sign in at `/admin/login.php`.

The public pages can also be opened as `index.html`, but PHP forms and the administrator dashboard require Apache, PHP and MySQL.

## Server configuration

Set these variables in the environment used by Apache, then restart Apache:

| Variable | Local development default / requirement |
| --- | --- |
| `DB_HOST` | `127.0.0.1` |
| `DB_NAME` | `stmichael_school` |
| `DB_USER` | `root` |
| `DB_PASSWORD` | Your MySQL password (blank only if the local XAMPP root account has no password) |
| `ADMIN_USERNAME` | A new administrator username |
| `ADMIN_PASSWORD_HASH` | A hash generated with PHP `password_hash()` |

Generate a password hash from a terminal with PHP installed:

```text
php -r "echo password_hash('replace-this-with-a-unique-password', PASSWORD_DEFAULT), PHP_EOL;"
```

Set `ADMIN_PASSWORD_HASH` to the generated output, not to the plain-text password. Use a unique password and protect access to the server environment. Do not deploy with an unprotected database account.

## Outgoing email

The enquiry endpoint uses PHP `mail()` to send each visitor message to `stmichaelsssn@gmail.com`. Configure an outbound SMTP relay for PHP before using the form. In XAMPP, set the SMTP server, port and authorized sender credentials in `C:\xampp\sendmail\sendmail.ini`, then configure `sendmail_path` and `sendmail_from` in `php.ini` and restart Apache. Use an app password or other provider-approved SMTP credentials; never put credentials in the website files. If email delivery fails, the endpoint reports the failure and keeps the database copy when available.

## Updating school information

- Replace the photographs in `images/` with school-approved images, keeping the filenames or updating their references in `index.html`.
- The school crest is `images/logo.png`; update its references in `index.html` and the admin pages if it changes.
- Edit the school introduction, approved news, confirmed facilities, contacts and official social links in `index.html`.
- Keep the phone and email links in the contact section and footer in sync when official details change.
- The admission form, email delivery, database schema and administrator enquiry view are in `php/`, `database/` and `admin/`.

School history, leadership, achievements, admissions dates and individual facilities remain placeholders until verified by the school. The proposed vision and mission statements need school approval. The administrator area currently provides secure sign-in and enquiry viewing; it does not yet provide a content editor or photo-upload interface.
