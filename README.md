# emailist
A system that makes mailing list management as simple as possible, running on php and sqlite.

![screenshot](./screenshot.png)
## Dependencies
* php
* sqlite

## Getting Started

### 1. Install and initialize database and admin acount
```
git clone https://github.com/TetsuakiBaba/emailist.git
php -S localhost:8000

// macOS
open http://localhost:8000/init_db.php

// windows OS
start "" "http://localhost:8000/init_db.php"
```
<!-- teaser.gifを挿入 -->
![teaser](teaser.gif)

### 2. Customize settings.php according to your environment

### 3. manage email list
Basic operations can be performed from dashboard.php.
* add email
* delete email
* download a csv of the email list
* batch update of email list by csv list
* batch deletion of email lists
* delete database

### 4. send email
* send.php (send email to all email addresses)

### 5. Subscribe and Unsubscribe from annoymus users
* subscribe.php (subscribe to email list)
* unsubscribe.php (unsubscribe from email list)