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

if ($db_type != 'default') {
    $data = mobilAP_db::testConnection(mobilAP::getDBConfig('db_type'), mobilAP::getDBConfig('db_host'), mobilAP::getDBConfig('db_username'), mobilAP::getDBConfig('db_password'), mobilAP::getDBConfig('db_database'));
    if (!mobilAP_Error::isError($data)) {
        $result = mobilAP_db::createTables();
    }
}

?>

<p>Welcome to mobilAP setup. This will lead you through the initial configuration of mobilAP.</p>

<?php
if (!mobilAP::canSaveDBConfigFile()) { ?>
    <p><b>Error:</b> The webserver cannot save the database configuration file. You will need to allow the webserver to write to <b><?php echo mobilAP::dbConfigFolder(); ?></b>.</p>
<?php
die();

}
?>
<div id="setupStack">
    <div id="setupDB">
        <h2>Step 1: Database configuration</h2>
        <p>mobilAP uses a database to store its data. It currently supports <?php echo count($db_types); ?> database types:</p>
        <ul>
        <?php 
        foreach ($db_types as $db_type=>$data) { 
        ?><li><?php if ($data['supported']) { ?><input type="radio" name="db_type" value="<?php echo $db_type; ?>" onclick="mobilAP.setupController.setDBType('<?php echo $db_type; ?>')"><?php } else { ?> - <?php } ?> <b><?php echo $data['title']; ?></b>. <?php echo $data['description']; ?> Availability: <span class="<?php echo $data['supported'] ? 'db_supported' : 'db_notsupported'; ?>"><?php echo $data['supported_message']; ?></span></li>
        <?php } ?>
        </ul>

        <div id="db_host_info">
       <p>Please enter your database connection information</p>
            <label>Host:</label>
            <input type="text" id="db_host" value="<?php echo mobilAP::getDBConfig('db_host'); ?>">
            <label>Username:</label>
            <input type="text" id="db_username" value="<?php echo mobilAP::getDBConfig('db_username'); ?>">
            <label>Password:</label>
            <input type="password" id="db_password" value="">
            <label>Database:</label>
            <input type="text" id="db_database" value="<?php echo mobilAP::getDBConfig('db_database'); ?>">
            <div id="db_test_results"></div>
            <input type="button" id="db_test" onclick="mobilAP.setupController.dbtest()" value="Validate Connection">
        </div>

    </div>
    <div id="setupUser">
        <h2>Step 2: User configuration</h2>
        <p>Now you need to create an initial administrative user. mobilAP users email addresses to uniquely identify users and requires all administrative users to use a password. Please create an initial account by entering the values below:</p>


        <label>Name:</label> 
        <input type="text" id="admin_FirstName"> <input type="text" id="admin_LastName">
        <label>email address:</label>
        <input type="text" id="admin_email">
        <label>Password:</label>
        <input type="password" id="admin_password">
        <label>Verify Password:</label>
        <input type="password" id="admin_verify_password">
    </div>
    <div id="setupOptions">
        <h2>Step 3: Options</h2>
        <p>Now it's time to set a few options that affect the behavior of mobilAP. You can change these and other options later using the admin tools.</p>

<div class="mobilAP_label">Site Title</div>
<input type="text" id="admin_site_title" value="mobilAP" size="50">

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

<div class="mobilAP_label">Don't use schedule</div>
<p class="mobilAP_explanation">For simpler events where there is only 1 session, you can turn off the schedule</p>
<div id="admin_single_session_mode" class="mobilAP_switch"></div>        
        
    </div>
    <div id="setupFinished">
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
