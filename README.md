# EventLog
# EventLog  EventLog is a event based logging system for amateur radio clubs. It is designed for managing radio events, recording QSOs, and tracking participants and their activity during club competitions and events.

## Description
EventLog is a web-based amateur radio logging system designed for recording, managing and checking QSOs. It supports manual QSO entry, ADIF import, searching and checking previous contacts, event management and QSO scoring. The system includes user accounts, permissions, private logs, login tracking and statistics. It also features a diploma system with customizable PDF templates, where callsigns, names and points can be positioned precisely in millimeters. The project combines logging, event management and automated diploma generation in one platform.

## Futures
* **QSO Logging**

  * Manual QSO entry
  * ADIF import
  * QSO search and filtering
  * Previous QSO checking
  * Support for multiple bands and operating modes

* **Events**

  * Event creation and management
  * Event-specific QSO tracking
  * Event scoring
  * Event result checking
  * Event controller management

* **Diplomas**
  * Automatic diploma qualification checking
  * Custom diploma templates
  * PDF diploma backgrounds
  * Custom positioning of callsign, name and points
  * Show/hide individual diploma elements
  * Multiple diploma designs

* **Statistics**

  * QSO statistics
  * Event statistics
  * Points calculation
  * Result overview

* **User Management**

  * User accounts
  * Login system
  * User permissions
  * Private logs
  * Access control

* **Security**

  * Login tracking
  * IP logging
  * Permission-based access

* **Additional Features**

  * QSO verification
  * Searchable event results
  * Responsive web interface


## Requirements
* Web server with PHP support
* **PHP 8.2.12 or newer**
* **MySQL/MariaDB** database server
* PHP **MySQLi** extension
* Web browser with JavaScript support
* Sufficient storage space for the application and database

## Installation
First, you need to create an SQL database. After creating the database, create a user for the database and grant the user access to it.

Then, copy the contents of the `src` folder and upload them to your web server.

## Setup
After completing the installation, open the `db.php` file and enter all required values in the `mysqli_connect()` function:

```php
mysqli_connect(Host name, Username, Password, Database name)
```

Then open the website and click **Sign In**. Use `ADMIN` as the username and choose a password.

If the sign-in is successful, open the database and find the `users` table. Find the row with the username `ADMIN`, edit the `RIGHTS` field, and set it to:

```text
111111
```

You now have an **ADMIN account with full permissions** and can invite other users.

## How do it work

### Users

### Events

### CALC file

### Diplomas
