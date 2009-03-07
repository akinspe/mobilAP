<?php

/*

* Copyright (c) 2008, University of Cincinnati
* All rights reserved.
* See LICENSE file for important license information

*/

require_once('inc/app_classes.php');
$PAGE_TITLE = 'Attendee Administration';

$App = new Application();

if (!$App->is_LoggedIn()) {
	include("templates/not_logged_in.tpl");
	exit();
}

$user = $App->getUser();

if (!$user->isAdmin()) {
	include("templates/access_error.tpl");
	exit();
}

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'main';

if (isset($_POST['cancel_attendee'])) {
	$action='main';
}

switch ($action)
{
	case 'import':
		$template_file = 'import_attendees.tpl';

		if (isset($_POST['import_file'])) {
		
			if (isset($_FILES['file_upload']) && $_FILES['file_upload']['error']==0) {
				$delimiter = isset($_POST['delimiter']) ? $_POST['delimiter'] : 'tab';
				switch ($delimiter)
				{
					case 'csv':
						$delimiter = ",";
						$enclosure = '"';
						break;

					case 'tab':
					default:
						$delimiter = "\t";
						$enclosure = null;
						break;
				}			
								
					
				$import_data = mobilAP_attendee::importDelimitedFile($_FILES['file_upload']['tmp_name'], $delimiter, $enclosure);
				if (mobilAP_Error::isError($import_data)) {
					$App->addErrorMessage("Error importing data: " . $result->getNessage());
					$import_data = array();
				}
								
			} else {
				$App->addErrorMessage("Error uploading file");
			
			}

		} elseif (isset($_REQUEST['delete_import'])) {
			$result = mobilAP_attendee::deleteImport(intval($_REQUEST['delete_import']));
			
		} elseif (isset($_REQUEST['commit_import'])) {
			$result = mobilAP_attendee::commitImport(intval($_REQUEST['commit_import']));
			if (mobilAP_Error::isError($result)) {
				$App->addErrorMessage("Error adding attendee: " . $result->getMessage());
			}
		} elseif (isset($_REQUEST['edit_import'])) {
			if ($attendee = mobilAP_attendee::editImport(intval($_REQUEST['edit_import']))) {
				$import_id = $_REQUEST['edit_import'];
				$action='add';
				$salutations = mobilAP_attendee::getSalutations();
				$template_file = 'add_attendee.tpl';
			}
		}
	
		$import_data = mobilAP_attendee::getImportData();
		break;
	case 'add':
		$template_file = 'add_attendee.tpl';
		$attendee = new mobilAP_attendee();	
		$salutations = mobilAP_attendee::getSalutations();

		if (isset($_POST['add_attendee'])) {
			$ok = true;
			$salutation = isset($_POST['salutation']) ? $_POST['salutation'] : ''; 
			$FirstName = isset($_POST['FirstName']) ? $_POST['FirstName'] : ''; 
			$LastName = isset($_POST['LastName']) ? $_POST['LastName'] : ''; 
			$organization = isset($_POST['organization']) ? $_POST['organization'] : ''; 
			$title = isset($_POST['title']) ? $_POST['title'] : ''; 
			$dept = isset($_POST['dept']) ? $_POST['dept'] : ''; 
			$email = isset($_POST['email']) ? $_POST['email'] : '';
			$password = isset($_POST['password']) ? $_POST['password'] : getConfig('default_password');

			$city = isset($_POST['city']) ? $_POST['city'] : '';
			$state = isset($_POST['state']) ? $_POST['state'] : '';
			$country = isset($_POST['country']) ? $_POST['country'] : '';
			$phone = isset($_POST['phone']) ? $_POST['phone'] : '';
			$bio = isset($_POST['bio']) ? $_POST['bio'] : '';


			$admin = isset($_POST['admin']) ? $_POST['admin'] : 0;
			
			if (!$attendee->setName($salutation, $FirstName, $LastName)) {
				$ok = false;
				$App->addErrorMessage("Please include your full name");
			}
			$attendee->setOrganization($organization);
			$attendee->setTitle($title);
			$attendee->setDepartment($dept);
			$attendee->setLocation($city, $state, $country);
			$attendee->setPhone($phone);
			$attendee->setBio($bio);
			$attendee->setAdmin($admin);

			if (!$attendee->setEmail($email)) {
				$ok = false;
				$App->addErrorMessage("Please include a valid email");
			}
			
			if ($ok) {
				$result = $attendee->createAttendeeFromObj();
				if (mobilAP_Error::isError($result)) {
					$App->addErrorMessage("There was an error creating the attendee: " . $result->getMessage());
					break;
				} else {
					$attendee->setPassword($password);
					$result = $attendee->updateAttendee();
					$attendee_id = $attendee->attendee_id;
					$App->addMessage("Attendee created");
					if (isset($_POST['import_id'])) {
						mobilAP_attendee::deleteImport(intval($_POST['import_id']));
						$import_data = mobilAP_attendee::getImportData();
						$template_file = 'import_attendees.tpl';
						break;
					} else {
						$action = 'edit';
					}
				}
			} else {
				break;
			}
		} else {
			break;
		}
	
	case 'edit':
		if (!isset($attendee_id)) {
			$attendee_id = isset($_REQUEST['attendee_id']) ? $_REQUEST['attendee_id'] : '';
		}
	
		if ($attendee = mobilAP_attendee::getAttendeeByID($attendee_id)) {
			if (isset($_POST['delete_photo'])) {
				$result = $attendee->deleteDirectoryImage();

			}
		
			if (isset($_POST['update_attendee'])) {
			
				$ok = true;
				$salutation = isset($_POST['salutation']) ? $_POST['salutation'] : ''; 
				$FirstName = isset($_POST['FirstName']) ? $_POST['FirstName'] : ''; 
				$LastName = isset($_POST['LastName']) ? $_POST['LastName'] : ''; 
				$organization = isset($_POST['organization']) ? $_POST['organization'] : ''; 
				$title = isset($_POST['title']) ? $_POST['title'] : ''; 
				$dept = isset($_POST['dept']) ? $_POST['dept'] : ''; 
				$email = isset($_POST['email']) ? $_POST['email'] : '';
				$password = isset($_POST['password']) ? $_POST['password'] : '';
				$city = isset($_POST['city']) ? $_POST['city'] : '';
				$state = isset($_POST['state']) ? $_POST['state'] : '';
				$country = isset($_POST['country']) ? $_POST['country'] : '';
				$phone = isset($_POST['phone']) ? $_POST['phone'] : '';
				$bio = isset($_POST['bio']) ? $_POST['bio'] : '';
				$admin = isset($_POST['admin']) ? $_POST['admin'] : 0;
				
				if (!$attendee->setName($salutation, $FirstName, $LastName)) {
					$ok = false;
					$App->addErrorMessage("Please include your full name");
				}
				$attendee->setOrganization($organization);
				$attendee->setTitle($title);
				$attendee->setDepartment($dept);
				$attendee->setLocation($city, $state, $country);
				$attendee->setPhone($phone);
				$attendee->setAdmin($admin);
				$attendee->setBio($bio);

				if (!$attendee->setEmail($email)) {
					$ok = false;
					$App->addErrorMessage("Please include a valid email");
				}

				if (getConfig('use_passwords') && $password != '') {
					$attendee->setPassword($password);
				}
				
				$result = $attendee->uploadDirectoryImage();
				if (mobilAP_Error::isError($result)) {
					$ok = false;
					$App->addErrorMessage("Error uploading image: " . $result->getMessage());
				}
				
				
				if ($ok) {
					$result = $attendee->updateAttendee();
					if (mobilAP_Error::isError($result)) {
						$App->addErrorMessage("There was an error saving the attendee data: " . $result->getMessage());
					} else {
						$App->addMessage("Attendee data updated");
						$action = 'main';
					}
				}
	
			}
			
			if (isset($_POST['delete_attendee'])) {
				$result = $attendee->deleteAttendee();
				$App->addMessage("Attendee deleted");
				$action='main';
				break;
			}
			
			if (isset($_POST['check_in'])) {
				$attendee->check_in();
			}

			if (isset($_POST['rotate'])) {
				$dir = key($_POST['rotate']);
				$result = $attendee->rotatePhoto($dir);
							
			}
			
			if (isset($_POST['directory_inactive'])) {
				$attendee->setDirectoryActive(false);
				$attendee->updateAttendee();
			}

			if (isset($_POST['directory_active'])) {
				$attendee->setDirectoryActive(true);
				$attendee->updateAttendee();
			}
			
			$salutations = mobilAP_attendee::getSalutations();
			$template_file = 'edit_attendee.tpl';
		} else {
			$App->addErrorMessage("Invalid attendee");
			$action='main';
		}
		break;
		
	case 'main':
	default:
		$action='main';

}

if ($action=='main') 
{

	$sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'LastName';
	
	$template_file = 'attendee_admin_main.tpl';
	
	$attendees = mobilAP_attendee::getAttendees(array('order'=>$sort));

	$usedLetters = array();
	$attendee_total = 0;
	$checked_in = 0;
	$letters = utils::getLetters();
	
	foreach ($attendees as $attendee) {
		$attendee_total++;
		if ($attendee->checked_in) {
			$checked_in++;
		}
		$letter = strtoupper($attendee->LastName[0]);
		if (!in_array($letter, $usedLetters)) {
			$usedLetters[] = $letter;
		}
	}
}

include('templates/header.tpl');
include("templates/nav.tpl");
include("templates/admin/$template_file");
include('templates/footer.tpl');

?>