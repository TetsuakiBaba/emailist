# emailist
A system that makes mailing list management as simple as possible, running on php and sqlite.

![screenshot](./images/screenshot.png)
## Dependencies
* php
* sqlite

## Getting Started

### 1. Install and initialize database and admin acount on a local server
```
git clone https://github.com/TetsuakiBaba/emailist.git
cd emailist
php -S localhost:8000

// macOS
open http://localhost:8000/init_db.php

// windows OS
start "" "http://localhost:8000/init_db.php"
```
<!-- teaser.gifを挿入 -->
![teaser](images/teaser.gif)

### 2. Customize settings.php according to your environment

### 3. manage email list
Basic operations can be performed from dashboard.php.
* add email
  * This operation can also be performed by other users using subscribe.php.
* remove email
  * This operation can also be performed by other users using unsubscribe.php.
* download a csv of the email list
  * Download all email lists in the database table as csv files.
* batch update of email list by csv list
  * This is a csv file with the email address on a new line, in the same format as the downloaded csv file.
* batch deletion of email lists
  * Delete all email lists in the database table. Since the database is not deleted, IDs are not initialized.
* delete database
  * This will delete the database and create a new one. This is useful when you want to delete all email addresses and start over.If you delete the database, be sure to run it again from init_db.php.

### 4. send email
* send.php (send email to all email addresses)

### 5. Subscribe and Unsubscribe from annoymus users
* subscribe.php (subscribe to email list)
* unsubscribe.php (unsubscribe from email list)

## Deployment
* You can deploy this system on a web server that supports php and sqlite. In that case, please place emailist.db in a location that is not accessible to general users. The location of emailist.db can be specified at once from settings.php.