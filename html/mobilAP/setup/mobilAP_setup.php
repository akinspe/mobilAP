<div id="setupHeader" class="mobilAP_header">mobilAP Setup</div>
<style type="text/css">@import url(../mobilAP/setup/mobilAP_setup.css);</style>
<?php

require_once('../../mobilAP.php');
    
if (mobilAP::isSetup()) { 
    echo "<p>This site has already been setup</p>";
    //stop if the site has already been setup
    exit();   
} 
    
$db_types = mobilAP_db::db_types();

$db_type = mobilAP::getDBConfig('db_type');

$db_configured = 0;
$user_configured = 0;
$db_test = false;

if (array_key_exists($db_type, $db_types)) {
    $db_test = mobilAP_db::testConnection($_DBCONFIG);
    if (!mobilAP_Error::isError($db_test)) {
        $db_test = mobilAP_db::createTables();
		$db_configured = mobilAP::getConfig('MOBILAP_HASH') ? -1 : 0;
		$users = mobilAP::getUsers();
		$user_configured = count($users);
    }
}

?>

<p>Welcome to mobilAP setup. This will lead you through the initial configuration of mobilAP.</p>

<?php
if (mobilAP_Error::isError($db_test)) { ?>
    <p><b>Error with database:</b> <?php echo $db_test->getMessage(); ?>. Please check your configuration settings.
<?php 
die();
} elseif (!mobilAP::canSaveDBConfigFile() && !$db_configured) { ?>
    <p><b>Error:</b> The webserver cannot save the database configuration file. 
    You will need to allow the webserver to write to <b><?php echo mobilAP::dbConfigFile(); ?></b> or edit this file manually.</p>
<?php
die();

}
?>
<div id="setupStack">
    <div id="setupDB" class="setupStack">
		<input type="hidden" id="db_configured" value="<?php echo $db_configured; ?>">
        <h2>Step 1: Database configuration</h2>
<?php if ($db_test) { ?>
	<p>You have successfully manually configured your database settings. You can continue to the next step</p> 
<?php } else {?>
        <p>mobilAP uses a database to store its data. It currently supports <?php echo count($db_types); ?> database types:</p>
        <ul id="db_type_list">
        <?php 
        foreach ($db_types as $db_type=>$data) { 
        ?><li><?php if ($data['supported']) { ?><input type="radio" name="db_type" value="<?php echo $db_type; ?>"<?php if (mobilAP::getDBConfig('db_type')==$db_type) echo ' checked'; ?> onclick="mobilAP.setupController.setDBType('<?php echo $db_type; ?>')"><?php } else { ?> - <?php } ?> <b><?php echo $data['title']; ?></b>. <?php echo $data['description']; ?> Availability: <span class="<?php echo $data['supported'] ? 'db_supported' : 'db_notsupported'; ?>"><?php echo $data['supported_message']; ?></span></li>
        <?php } ?>
        </ul>

        <div id="db_mysql_info" class="db_info">
       <p>Please enter your database connection information</p>
            <label>Host:</label>
            <input type="text" size="20" id="db_host" value="<?php echo mobilAP::getDBConfig('db_host'); ?>">
            <label>Username:</label>
            <input type="text" size="20" id="db_username" value="<?php echo mobilAP::getDBConfig('db_username'); ?>">
            <label>Password:</label>
            <input type="password" size="20" id="db_password" value="">
            <label>Database:</label>
            <input type="text" size="20" id="db_database" value="<?php echo mobilAP::getDBConfig('db_database'); ?>">
        </div>
        <div id="db_sqlite_info" class="db_info">
       <p>By default, the SQLite database will be placed in the mobilAP data folder. If you wish, you can place it in an alternate location</p>
            <label>Database location (leave blank for default):</label>
            <input type="text" id="db_folder" size="50" value="<?php echo mobilAP::getDBConfig('db_folder'); ?>">

        </div>

        <div id="db_validate_info" class="db_info">
            <div id="db_test_results"></div>
            <p>
            <input type="button" id="db_test" onclick="mobilAP.setupController.dbtest()" value="Validate Connection">
            </p>
		</div>
<?php } ?>
    </div>
    <div id="setupUser" class="setupStack">
		<input type="hidden" id="user_configured" value="<?php echo $user_configured; ?>">
        <h2>Step 2: User configuration</h2>
<?php if ($user_configured) { ?>
	<p>You have successfully created an administrative user. You can continue to the next step</p>
<?php } else { ?>        
        <p>Now you need to create an initial administrative user. mobilAP users email addresses to uniquely identify users and requires all administrative users to use a password. Please create an initial account by entering the values below:</p>


        <label>Name:</label> 
        <input type="text" size="30" id="admin_FirstName"> 
        <input type="text" size="30" id="admin_LastName">
        <label>Organization (optional):</label> 
        <input type="text" size="30" id="admin_organization">
        <label>email address:</label>
        <input type="text" size="40" id="admin_email">
        <label>Password:</label>
        <input type="password" size="30" id="admin_password">
        <label>Verify Password:</label>
        <input type="password" size="30" id="admin_verify_password">
<?php } ?>        
    </div>
    <div id="setupOptions" class="setupStack">
        <h2>Step 3: Options</h2>
        <p>Now it's time to set a few options that affect the behavior of mobilAP. You can change these and other options later using the admin tools.</p>

<fieldset>
<legend>Site Metadata</legend>

<div class="mobilAP_label">Site Title</div>
<input type="text" id="admin_site_title" value="mobilAP" size="50">
</fieldset>

<fieldset>
<legend>Users and Passwords</legend>
<div class="mobilAP_label">Require Login to view content</div>
<p class="mobilAP_explanation">You can make all the content on the site private and require users to login in order to view the site</p>
<div id="admin_content_private" class="mobilAP_switch"></div>

<div class="mobilAP_label">Use Passwords</div>
<p class="mobilAP_explanation">You can require attendees to enter a password when logging in. You assign passwords either at import or by editing the user in the attendee administration.</p>
<div id="admin_use_passwords" class="mobilAP_switch"></div>

<div class="mobilAP_label">Require Password for admins</div>
<p class="mobilAP_explanation">You can require admins to enter a password when logging in. This will allow attendees to login without a password, but prevent unauthorized administration without a password.</p> 
<div id="admin_use_admin_passwords" class="mobilAP_switch"></div>

<div class="mobilAP_label">Require Password for presenters</div>
<p class="mobilAP_explanation">You can require presenters to enter a password when logging in. This will allow attendees to login without a password, but prevent unauthorized administration without a password.</p>
<div id="admin_use_presenter_passwords" class="mobilAP_switch"></div>        

<div class="mobilAP_label">Allow self-created users</div>
<p class="mobilAP_explanation">You can allow anyone to create an account in the system by supplying their email address. If off, you must administer all users before they can login.</p>
<div id="admin_allow_self_created_users" class="mobilAP_switch"></div>        
</fieldset>

<fieldset>
<legend>Schedule and Sessions</legend>

<div class="mobilAP_label">Use simple schedule</div>
<p class="mobilAP_explanation">For simpler events where there is only 1 session, you can turn off the schedule</p>
<div id="admin_single_session_mode" class="mobilAP_switch"></div>        

<div class="mobilAP_label">Time Zone</div>
<p class="mobilAP_explanation">Select the time zone to use for this event</p>
<input type="hidden" id="admin_timezone" value="">
<div id="admin_timezone_container"></div>
</fieldset>
        
    </div>
    <div id="setupFinished" class="setupStack">
        <h2>Setup completed</h2>
        <p>You have completed the mobilAP setup sucessfully.</p>
        <p><a href="../">Go to site</a></p>
    </div>
</div>
<div id="setupButtons">
<div id="setupPreviousButton" class="mobilAP_setupbutton" onclick="mobilAP.setupController.previousView()">Previous</div>
<div id="setupNextButton" class="mobilAP_setupbutton" onclick="mobilAP.setupController.nextView()">Next</div>
<div id="setupFinishButton" class="mobilAP_setupbutton" onclick="mobilAP.setupController.finishSetup()">Finish</div>
</div>
