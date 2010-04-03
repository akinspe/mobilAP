<?php

require_once('../mobilAP.php');
$content_type = 'application/json';
$user_session = new mobilAP_UserSession();
$_user = new mobilAP_user(true);

if (isset($_POST['post'])) {
    $post_action = $_POST['post'];
    switch ($post_action)
    {
        case 'deleteUser':
            $userID = isset($_POST['userID']) ? $_POST['userID'] : '';
            if ($user = mobilAP_User::getUserByID($userID)) {
                $data = $user->deleteUser($_user->getUserID());
            } else {
                $data = mobilAP_Error::throwError("Unable to find user for userID " . $userID,-2, $userID);
                break;
            }
            break;
        case 'resetPassword':
            $userID = isset($_POST['userID']) ? $_POST['userID'] : '';
            if (!$user = mobilAP_User::getUserByID($userID)) {
                $data = mobilAP_Error::throwError("Unable to find user for userID " . $userID,-2, $userID);
                break;
            }
            $data = $user->resetPassword();
            break;
        case 'setPassword':
            if ($user = new mobilAP_user(true)) {
                $password_md5 = isset($_POST['password_md5']) ? $_POST['password_md5'] : '';
                $data = $user->setMD5Password($password_md5);
            } else {
                $data = mobilAP_Error::throwError("Not logged in");
            }
            break;
        case 'updateUserImage':
            $userID = isset($_POST['userID']) ? $_POST['userID'] : '';
            if ($user = mobilAP_User::getUserByID($userID)) {
                $file = isset($_FILES['directoryProfileImageFile']) ? $_FILES['directoryProfileImageFile'] : array();
                $data = $user->uploadImage($file);
            } else {
                $data = mobilAP_Error::throwError("Unable to find user for userID " . $userID,-2, $userID);
            }
            
            $content_type = 'text/html';
            break;
        case 'updateUser':
            $userID = isset($_POST['userID']) ? $_POST['userID'] : '';
            if (!$user = mobilAP_User::getUserByID($userID)) {
                $data = mobilAP_Error::throwError("Unable to find user for userID " . $userID,-2, $userID);
                break;
            }
        case 'uploadImportFile':
			$file = isset($_FILES['directoryImportFile']) ? $_FILES['directoryImportFile'] : array();
			$data = mobilAP_User::uploadImportFile($file);
            $content_type = 'text/html';
        	break;
        case 'addUser':
            if ($post_action == 'addUser') {
                $user = new mobilAP_user();
            }
            
            $FirstName = isset($_POST['FirstName']) ? $_POST['FirstName'] : $user->FirstName;
            $LastName = isset($_POST['LastName']) ? $_POST['LastName'] : $user->LastName;
            $organization = isset($_POST['organization']) ? $_POST['organization'] : $user->organization;
            $email = isset($_POST['email']) ? $_POST['email'] : $user->email;
            $admin = isset($_POST['admin']) ? $_POST['admin'] : $user->admin;
            $user->setName($FirstName, $LastName);
            $user->setOrganization($organization);
            $user->setEmail($email);
            $user->setAdmin($admin);

            if ($post_action =='addUser') {
                $data = $user->addUser($_user->getUserID());
            } else {
                $data = $user->updateUser($_user->getUserID());
            }

            if (!mobilAP_Error::isError($data)) {
                if (isset($_POST['md5_password'])) {
                    $user->setMD5Password($_POST['md5_password']);
                }
            }
            break;
    }
} elseif (isset($_GET['userID'])) {
    $data = mobilAP_User::getUserByID($_GET['userID']);
} else {
    $data = new mobilAP_user(true);
    $data->userData = $data->getUserData();
}

header("Content-type: $content_type; charset=" . MOBILAP_CHARSET);
echo json_encode($data);

?>