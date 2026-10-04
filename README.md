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

After that, copy the SQL commands from the `SQL` folder into the SQL command prompt. Before starting the website, two tables need to be created: `users` and `login_log`.

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
All notifications about new users will be sent to the email address associated with the **admin account**.

## How does it work?

### Users
When a new user registers, they initially have no permissions and are placed on the waiting list. The administrator receives a notification about the new registration and can either invite or delete the user from the **EDIT USERS** page.

Each user can have different permissions assigned by an administrator. The available permissions are:

* View and edit their own log
* Edit other users' logs
* Manage users
* View other users' logs
* Create and manage events

Any user with permission to manage users can modify these permissions.

### Events
Users with event management permission can create and edit events. Every QSO must be associated with an event, so the standard `EventLog` should not be used as a personal log unless an event specifically intended for this purpose has been created.

When creating an event, you can define its name and duration. The end date also determines when diplomas become available.

An event can optionally require users to submit their log before they are allowed to download a diploma.

QSOs can also be recorded in another user's log, for example when all event contacts are made under a club callsign. In this case, event leaders can be selected. These users are allowed to enter QSOs into the selected log.

After creating an event, up to **three different diplomas** can be added to it.

### CALC file
A CALC file is a PHP file used to search for QSOs and calculate points according to the rules of a specific event.

A CALC file has four input variables:

* `$sc` – callsign that is being searched for
* `$se` – event ID that is being searched for
* `$il` – identifies the type of search
* `$_SESSION['log_name']` – name of the currently logged-in user's log

At the beginning of the CALC file, the database connection must be included:

```php
require_once("db.php");
```

This provides the database connection through the `$con` variable.

If information about all users and their logs is required, include:

```php
require_once("get_users.php");
```

The `get_users.php` file returns two arrays:

* `$log_names` – contains the names of all available logs
* `$usernames` – contains the usernames or callsigns associated with the logs

The two arrays use the same indexes. For example, `$log_names[0]` belongs to `$usernames[0]`.

The CALC file returns found QSOs through the `$event_search_res` array. Each QSO must be added using the following structure:

```php
$event_search_res[] = [
    'callsign',
    'date',
    'time',
    'band',
    'mode',
    'rst_r',
    'rst_s',
    'tocke'
];
```

The `tocke` value determines how many points the QSO is worth.

#### Example 1 – Search through all logs

This CALC file searches all available logs for the specified callsign and event.

When `$il` is set to `log`, it performs an additional check using the logged-in user's log. It compares QSOs between the user's log and other logs and can find confirmed contacts between users.

When `$il` has another value, it simply searches all available logs for QSOs matching the specified callsign and event.

This makes the CALC file suitable for events where QSOs can be recorded in multiple users' logs and need to be checked against each other.

```php
<?php       
    require_once("db.php");
    require_once("get_users.php");
    $search_call_20 = $sc;
    $event_id_20 = $se;
    $event_search_res = [];

	if(!isset($il)) $il = "";

    //Prijavljen upoirabnik - potrjene zveze
    if($il == "log"){
        $my_log_name_20 = $_SESSION['log_name'];
        for($i_20 = 0; $i_20 < count($log_names); $i_20++){
            $search_log_name_20 = $log_names[$i_20];
            $search_log_callsign_20 = $usernames[$i_20];
            if($search_log_name_20 != $my_log_name_20){
                $query = "
                    SELECT DISTINCT  m.* 
                    FROM $my_log_name_20 AS m 
                    INNER JOIN $search_log_name_20 AS s 
                    ON s.callsign = '$search_call_20' 
                    AND m.DOGODEK = s.DOGODEK 
                    WHERE m.callsign = '$search_log_callsign_20'
                        AND s.DOGODEK = '$event_id_20' 
                        AND m.DOGODEK = '$event_id_20' "; // ujemanje mode: AND m.mode = s.mode   ujemanje raporta: AND m.rst_s = s.rst_r AND m.
                $res_20 = mysqli_query($con, $query);
                while($sked_row = mysqli_fetch_assoc($res_20)){
                    $event_search_res[] = [
                        'callsign' => strtoupper($sked_row['callsign']),
                        'date'     => $sked_row['date'],
                        'time'     => $sked_row['time'],
                        'band'     => $sked_row['band'],
                        'mode'     => $sked_row['mode'],
                        'rst_r'    => $sked_row['rst_r'],
                        'rst_s'    => $sked_row['rst_s'],
                        'tocke'    => 1
                    ];
                }
            }
        }
    }
    else{
        for($i_20 = 0; $i_20 < count($log_names); $i_20++){
            $search_log_name_20 = $log_names[$i_20];
            $query = "
                SELECT * FROM `$search_log_name_20` WHERE 
                `callsign` = '$search_call_20'
                AND `DOGODEK` = '$event_id_20';
            "; // ujemanje mode: AND m.mode = s.mode   ujemanje raporta: AND m.rst_s = s.rst_r AND m.
            $res_20 = mysqli_query($con, $query);
            while($sked_row = mysqli_fetch_assoc($res_20)){
                $event_search_res[] = [
                    'callsign' => strtoupper($sked_row['callsign']),
                    'date'     => $sked_row['date'],
                    'time'     => $sked_row['time'],
                    'band'     => $sked_row['band'],
                    'mode'     => $sked_row['mode'],
                    'rst_r'    => $sked_row['rst_r'],
                    'rst_s'    => $sked_row['rst_s'],
                    'tocke'    => 1
                ];
            }
        }
    }

?>
```

#### Example 2 – Fixed S59EKL log

This CALC file searches only the `s59ekl_log` log for the specified callsign and event.

Every matching QSO is returned with `S59EKL` as the callsign, regardless of the callsign stored in the QSO. Each matching QSO is worth one point.

This type of CALC file is useful for events where all relevant QSOs are recorded under one specific callsign or log.

```php
<?php
	require_once("db.php");
	$search_call_test = $sc;
	$search_event_test = $se;

	$query = "SELECT * FROM `s59ekl_log` WHERE `callsign` = '$search_call_test' AND `DOGODEK` = '$search_event_test';";
	$res_20 = mysqli_query($con, $query);
	while($sked_row = mysqli_fetch_assoc($res_20)){
		$event_search_res[] = [
			'callsign' => "S59EKL",
			'date'     => $sked_row['date'],
			'time'     => $sked_row['time'],
			'band'     => $sked_row['band'],
			'mode'     => $sked_row['mode'],
			'rst_r'    => $sked_row['rst_r'],
			'rst_s'    => $sked_row['rst_s'],
			'tocke'    => 1
		];
	}
?>
```

### Diplomas
Each event can have up to three diplomas. To create a diploma, upload a PDF template with a size of **14 × 9 cm**.

After uploading the template, the diploma editor opens. The required text elements can be moved with the mouse to their correct positions on the diploma. Once the positions are correct, they can be saved.

The diploma can contain automatically generated information such as the participant's callsign, name and achieved points. Different diplomas can use different requirements and CALC files, allowing each event to have its own diploma system.

