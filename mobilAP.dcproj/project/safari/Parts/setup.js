/* 
 This file was generated by Dashcode and is covered by the 
 license.txt included in the project.  You may edit this file, 
 however it is recommended to first turn off the Dashcode 
 code generator otherwise the changes will be lost.
 */
var dashcodePartSpecs = {
    "adminContentHeader": { "text": "Content", "view": "DC.Text" },
    "adminContentWebClipIconImage": { "view": "DC.ImageLayout" },
    "adminContentWebClipUpload": { "initialHeight": 30, "initialWidth": 82, "leftImageWidth": 5, "onclick": "adminWebclipIconUpload", "rightImageWidth": 5, "text": "Upload", "view": "DC.PushButton" },
    "adminEvaluationQuestionsAddButton": { "initialHeight": 30, "initialWidth": 121, "leftImageWidth": 5, "onclick": "evaluationQuestionAddQuestion", "rightImageWidth": 5, "text": "Add Question", "view": "DC.PushButton" },
    "adminEvaluationQuestionsCancel": { "initialHeight": 25, "initialWidth": 70, "leftImageWidth": 5, "onclick": "evaluationQuestionCancel", "rightImageWidth": 5, "text": "Cancel", "view": "DC.PushButton" },
    "adminEvaluationQuestionsDelete": { "initialHeight": 25, "initialWidth": 70, "leftImageWidth": 5, "onclick": "evaluationQuestionDelete", "rightImageWidth": 5, "text": "Delete", "view": "DC.PushButton" },
    "adminEvaluationQuestionsHeader": { "text": "Evaluation Questions", "view": "DC.Text" },
    "adminEvaluationQuestionsList": { "allowsEmptySelection": true, "dataArray": ["Item 1", "Item 2", "Item 3"], "labelElementId": "adminEvaluationQuestionsListQuestionText", "listStyle": "List.DESKTOP_LIST", "propertyValues": { "dataArrayBinding": { "keypath": "evaluation.content" } }, "sampleRows": 3, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "adminEvaluationQuestionsListQuestionText": { "propertyValues": { "textBinding": { "keypath": "*.question_text" } }, "text": "Item", "view": "DC.Text" },
    "adminEvaluationQuestionsQuestionResponsesAddButton": { "initialHeight": 25, "initialWidth": 65, "leftImageWidth": 5, "onclick": "evaluationQuestionAdminAddResponse", "rightImageWidth": 5, "text": "Add", "view": "DC.PushButton" },
    "adminEvaluationQuestionsQuestionResponsesList": { "allowsEmptySelection": true, "dataArray": ["Item 1", "Item 2", "Item 3"], "labelElementId": "adminEvaluationQuestionsQuestionResponseText", "listStyle": "List.DESKTOP_LIST", "sampleRows": 3, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "adminEvaluationQuestionsQuestionResponsesRemoveButton": { "initialHeight": 25, "initialWidth": 65, "leftImageWidth": 5, "rightImageWidth": 5, "text": "Remove", "view": "DC.PushButton" },
    "adminEvaluationQuestionsQuestionResponseText": { "text": "Item", "view": "DC.Text" },
    "adminEvaluationQuestionsQuestionTextLabel": { "text": "Question Text", "view": "DC.Text" },
    "adminEvaluationQuestionsSave": { "initialHeight": 25, "initialWidth": 70, "leftImageWidth": 5, "onclick": "evaluationQuestionSave", "rightImageWidth": 5, "text": "Save", "view": "DC.PushButton" },
    "adminHomeAnnouncementsLabel": { "text": "Announcements", "view": "DC.Text" },
    "adminHomeDirectoryLabel": { "text": "Directory", "view": "DC.Text" },
    "adminHomeHeader": { "text": "Home List", "view": "DC.Text" },
    "adminHomeSaveButton": { "initialHeight": 30, "initialWidth": 71, "leftImageWidth": 5, "onclick": "adminHomeSave", "rightImageWidth": 5, "text": "Save", "view": "DC.PushButton" },
    "adminHomeScheduleLabel": { "text": "Schedule", "view": "DC.Text" },
    "adminHomeWelcomeLabel": { "text": "Welcome", "view": "DC.Text" },
    "adminSessionsAddButton": { "initialHeight": 25, "initialWidth": 40, "leftImageWidth": 5, "onclick": "sessionAdminAddSession", "rightImageWidth": 5, "text": "+", "view": "DC.PushButton" },
    "adminSessionsDeleteButton": { "initialHeight": 20, "initialWidth": 60, "leftImageWidth": 5, "rightImageWidth": 5, "text": "Delete", "view": "DC.PushButton" },
    "adminSessionsEditButton": { "initialHeight": 25, "initialWidth": 64, "leftImageWidth": 5, "onclick": "adminSessionsToggleEdit", "rightImageWidth": 5, "text": "Edit", "view": "DC.PushButton" },
    "adminSessionsHeader": { "text": "Sessions", "view": "DC.Text" },
    "adminSessionsList": { "allowsEmptySelection": true, "dataArray": ["Item 1", "Item 2", "Item 3"], "dataSourceName": "MobilAP.DataSourceStub", "labelElementId": "adminSessionsTitle", "listStyle": "List.DESKTOP_LIST", "sampleRows": 3, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "adminSessionsTitle": { "text": "Item", "view": "DC.Text" },
    "adminSettingsHeader": { "text": "Settings", "view": "DC.Text" },
    "adminSettingsSaveButton": { "initialHeight": 30, "initialWidth": 111, "leftImageWidth": 5, "onclick": "adminSettingsSave", "rightImageWidth": 5, "text": "Save", "view": "DC.PushButton" },
    "adminTabbar": { "allowsEmptySelection": true, "dataArray": [["Settings", "settings"], ["Evaluation Questions", "evaluation_questions"], ["Something", "something"]], "dataSourceName": "admin_tabs", "labelElementId": "adminTabTitle", "listStyle": "List.DESKTOP_LIST", "sampleRows": 3, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "adminTabs": { "subviewsTransitions": [{ "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "none" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "none" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "none" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "none" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "none" }], "view": "DC.StackLayout" },
    "adminTabTitle": { "text": "Item", "view": "DC.Text" },
    "announcementAdminCancelButton": { "initialHeight": 30, "initialWidth": 82, "leftImageWidth": 5, "onclick": "announcementAdminCancel", "rightImageWidth": 5, "text": "Cancel", "view": "DC.PushButton" },
    "announcementAdminSaveButton": { "initialHeight": 30, "initialWidth": 71, "leftImageWidth": 5, "onclick": "announcementSave", "rightImageWidth": 5, "text": "Save", "view": "DC.PushButton" },
    "announcementAdminTextLabel": { "text": "Text", "view": "DC.Text" },
    "announcementAdminTitleLabel": { "text": "Title", "view": "DC.Text" },
    "announcementDeleteButton": { "initialHeight": 30, "initialWidth": 79, "leftImageWidth": 5, "onclick": "announcementAdminDelete", "rightImageWidth": 5, "text": "Delete", "view": "DC.PushButton" },
    "announcementEditButton": { "initialHeight": 30, "initialWidth": 64, "leftImageWidth": 5, "onclick": "announcementAdminEdit", "rightImageWidth": 5, "text": "Edit", "view": "DC.PushButton" },
    "announcementPosted": { "text": "Posted By", "view": "DC.Text" },
    "announcementsAddButton": { "initialHeight": 25, "initialWidth": 145, "leftImageWidth": 5, "onclick": "addAnnouncement", "rightImageWidth": 5, "text": "Add Announcement", "view": "DC.PushButton" },
    "announcementsList": { "allowsEmptySelection": true, "dataArray": ["Item 1", "Item 2", "Item 3"], "labelElementId": "announcementsListTitle", "listStyle": "List.DESKTOP_LIST", "propertyValues": { "dataArrayBinding": { "keypath": "announcements.content" } }, "sampleRows": 3, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "announcementsListTitle": { "propertyValues": { "textBinding": { "keypath": "*.announcement_title" } }, "text": "Item", "view": "DC.Text" },
    "announcementsNotice": { "text": "No announcements have been posted", "view": "DC.Text" },
    "announcementText": { "propertyValues": { "textBinding": { "keypath": "announcementsList.selection.announcement_text" } }, "text": "Announcement Text", "view": "DC.Text" },
    "announcementTitle": { "propertyValues": { "textBinding": { "keypath": "announcementsList.selection.announcement_title" } }, "text": "Announcement Title", "view": "DC.Text" },
    "configALLOW_SELF_CREATED_USERS_Label": { "text": "Allow self-created users", "view": "DC.Text" },
    "configCONTENT_PRIVATE_Label": { "text": "Require Login to view content", "view": "DC.Text" },
    "configSINGLE_SESSION_MODE": { "view": "DC.Text" },
    "configSINGLE_SESSION_MODE_Label": { "text": "Use simple schedule", "view": "DC.Text" },
    "configSITE_TITLE": { "propertyValues": { "valueBinding": { "keypath": "config.content.SITE_TITLE" } }, "view": "DC.TextField" },
    "configSITE_TITLE_Label": { "text": "Site Title", "view": "DC.Text" },
    "configTIMEZONE_Label": { "text": "Time Zone", "view": "DC.Text" },
    "configUSE_ADMIN_PASSWORDS_Label": { "text": "Require Password for admins", "view": "DC.Text" },
    "configUSE_PASSWORDS_Label": { "text": "Use Passwords", "view": "DC.Text" },
    "configUSE_PRESENTER_PASSWORDS_Label": { "text": "Require password for presenters", "view": "DC.Text" },
    "detailStack": { "subviewsTransitions": [{ "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "dissolve" }, { "direction": "right-left", "duration": "0.1", "timing": "ease-in-out", "type": "dissolve" }, { "direction": "right-left", "duration": "0.1", "timing": "ease-in-out", "type": "dissolve" }, { "direction": "right-left", "duration": "0.1", "timing": "ease-in-out", "type": "dissolve" }, { "direction": "right-left", "duration": "0.1", "timing": "ease-in-out", "type": "dissolve" }, { "direction": "right-left", "duration": "0.1", "timing": "ease-in-out", "type": "dissolve" }, { "direction": "right-left", "duration": "0.1", "timing": "ease-in-out", "type": "dissolve" }, { "direction": "right-left", "duration": "0.1", "timing": "ease-in-out", "type": "dissolve" }, { "direction": "right-left", "duration": "0.1", "timing": "ease-in-out", "type": "dissolve" }, { "direction": "right-left", "duration": "0.1", "timing": "ease-in-out", "type": "dissolve" }, { "direction": "right-left", "duration": "0.1", "timing": "ease-in-out", "type": "dissolve" }, { "direction": "right-left", "duration": "0.1", "timing": "ease-in-out", "type": "dissolve" }, { "direction": "right-left", "duration": "0.1", "timing": "ease-in-out", "type": "dissolve" }, { "direction": "right-left", "duration": "0.1", "timing": "ease-in-out", "type": "dissolve" }, { "direction": "right-left", "duration": "0.1", "timing": "ease-in-out", "type": "dissolve" }, { "direction": "right-left", "duration": "0.1", "timing": "ease-in-out", "type": "dissolve" }, { "direction": "right-left", "duration": "0.1", "timing": "ease-in-out", "type": "dissolve" }], "view": "DC.StackLayout" },
    "directoryAdminAddButton": { "initialHeight": 25, "initialWidth": 49, "leftImageWidth": 5, "onclick": "directoryAdminAdd", "rightImageWidth": 5, "text": "+", "view": "DC.PushButton" },
    "directoryAdminImportButton": { "initialHeight": 25, "initialWidth": 80, "leftImageWidth": 5, "onclick": "directoryAdminImport", "rightImageWidth": 5, "text": "Import", "view": "DC.PushButton" },
    "directoryFirstName": { "text": "Item", "view": "DC.Text" },
    "directoryImportAddAllButton": { "initialHeight": 25, "initialWidth": 83, "leftImageWidth": 5, "onclick": "directoryImportAddAll", "rightImageWidth": 5, "text": "Add All", "view": "DC.PushButton" },
    "directoryImportExplain": { "text": "Use this utility to import users using a tab-delimited file", "view": "DC.Text" },
    "directoryImportList": { "allowsEmptySelection": true, "dataArray": ["Item 1", "Item 2", "Item 3"], "dataSourceName": "MobilAP.DataSourceStub", "labelElementId": "importFirstName", "listStyle": "List.DESKTOP_LIST", "sampleRows": 3, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "directoryImportNote": { "text": "Text", "view": "DC.Text" },
    "directoryImportUploadButton": { "initialHeight": 30, "initialWidth": 82, "leftImageWidth": 5, "onclick": "directoryImportUpload", "rightImageWidth": 5, "text": "Upload", "view": "DC.PushButton" },
    "directoryLastName": { "text": "Text", "view": "DC.Text" },
    "directoryList": { "allowsEmptySelection": true, "dataArray": ["Item 1", "Item 2", "Item 3"], "dataSourceName": "MobilAP.DataSourceStub", "labelElementId": "directoryFirstName", "listStyle": "List.DESKTOP_LIST", "sampleRows": 9, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "directoryOrganization": { "text": "Text", "view": "DC.Text" },
    "directoryProfileAdminCancelButton": { "initialHeight": 25, "initialWidth": 80, "leftImageWidth": 5, "onclick": "directoryAdminCancelEdit", "rightImageWidth": 5, "text": "Don't Save", "view": "DC.PushButton" },
    "directoryProfileAdminDeleteButton": { "initialHeight": 25, "initialWidth": 79, "leftImageWidth": 5, "onclick": "directoryAdminDelete", "rightImageWidth": 5, "text": "Delete", "view": "DC.PushButton" },
    "directoryProfileAdminEditButton": { "initialHeight": 25, "initialWidth": 64, "leftImageWidth": 5, "onclick": "directoryAdminToggleEdit", "rightImageWidth": 5, "text": "Edit", "view": "DC.PushButton" },
    "directoryProfileEmail": { "text": "email", "view": "DC.Text" },
    "directoryProfileFirstName": { "text": "First Name", "view": "DC.Text" },
    "directoryProfileImageContainer": { "view": "DC.ImageLayout" },
    "directoryProfileLastName": { "text": "Last Name", "view": "DC.Text" },
    "directoryProfileOrganization": { "text": "Organization", "view": "DC.Text" },
    "directoryProfileResetPasswordButton": { "initialHeight": 25, "initialWidth": 136, "leftImageWidth": 5, "onclick": "directoryAdminResetPassword", "rightImageWidth": 5, "text": "Reset Password", "view": "DC.PushButton" },
    "evaluationQuestionResponsesText": { "text": "Item", "view": "DC.Text" },
    "homeList": { "dataArray": ["Item 1", "Item 2", "Item 3"], "labelElementId": "homeTitle", "listStyle": "List.DESKTOP", "propertyValues": { "dataArrayBinding": { "keypath": "homeData.content" } }, "sampleRows": 10, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "homeTitle": { "propertyValues": { "textBinding": { "keypath": "*.title" } }, "text": "Title", "view": "DC.Text" },
    "importAddButton": { "initialHeight": 20, "initialWidth": 65, "leftImageWidth": 5, "rightImageWidth": 5, "text": "Add", "view": "DC.PushButton" },
    "importEmail": { "text": "Email", "view": "DC.Text" },
    "importFirstName": { "text": "FirstName", "view": "DC.Text" },
    "importLastName": { "text": "LastName", "view": "DC.Text" },
    "importOrganization": { "text": "Organization", "view": "DC.Text" },
    "login_pword_label": { "text": "password", "view": "DC.Text" },
    "login_userID_label": { "text": "email", "view": "DC.Text" },
    "loginCreateNewUserButton": { "initialHeight": 30, "initialWidth": 149, "leftImageWidth": 5, "onclick": "createNewUser", "rightImageWidth": 5, "text": "Create an Account", "view": "DC.PushButton" },
    "loginSubmit": { "initialHeight": 30, "initialWidth": 73, "leftImageWidth": 5, "onclick": "loginSubmit", "rightImageWidth": 5, "text": "Login", "view": "DC.PushButton" },
    "loginText": { "text": "You are not logged in. Please login to participate", "view": "DC.Text" },
    "logoutSubmit": { "initialHeight": 30, "initialWidth": 81, "leftImageWidth": 5, "onclick": "logoutSubmit", "rightImageWidth": 5, "text": "Logout", "view": "DC.PushButton" },
    "logoutText": { "text": "Do you wish to logout?", "view": "DC.Text" },
    "logoutUserText": { "text": "You are logged in as", "view": "DC.Text" },
    "mobilAP_heading": { "propertyValues": { "textBinding": { "keypath": "config.content.SITE_TITLE" } }, "view": "DC.Text" },
    "newSessionAdminCancelButton": { "initialHeight": 30, "initialWidth": 80, "leftImageWidth": 5, "onclick": "sessionAdminCancel", "rightImageWidth": 5, "text": "Cancel", "view": "DC.PushButton" },
    "newSessionAdminDescriptionField": { "view": "DC.TextField" },
    "newSessionAdminHeader": { "text": "Add new Session", "view": "DC.Text" },
    "newSessionAdminSaveButton": { "initialHeight": 30, "initialWidth": 80, "leftImageWidth": 5, "onclick": "sessionAdminSave", "rightImageWidth": 5, "text": "Save", "view": "DC.PushButton" },
    "newSessionAdminTitleField": { "view": "DC.TextField" },
    "profileCreateDescription": { "text": "Welcome to mobilAP. Please enter the following information to create your account", "view": "DC.Text" },
    "profileCreateEmailLabel": { "text": "Email Address", "view": "DC.Text" },
    "profileCreateFirstNameLabel": { "text": "First Name", "view": "DC.Text" },
    "profileCreateHeader": { "text": "Profile Create", "view": "DC.Text" },
    "profileCreateLastNameLabel": { "text": "Last Name", "view": "DC.Text" },
    "profileCreateOrganizationLabel": { "text": "Organization", "view": "DC.Text" },
    "profileCreatePasswordLabel": { "text": "Password", "view": "DC.Text" },
    "profileCreateSubmitButton": { "initialHeight": 30, "initialWidth": 132, "leftImageWidth": 5, "onclick": "profileCreateSubmit", "rightImageWidth": 5, "text": "Create Account", "view": "DC.PushButton" },
    "profileCreateVerifyPasswordLabel": { "text": "Verify Password", "view": "DC.Text" },
    "profileHeader": { "text": "Profile", "view": "DC.Text" },
    "profilePasswordChangeButton": { "initialHeight": 30, "initialWidth": 146, "leftImageWidth": 5, "onclick": "changePassword", "rightImageWidth": 5, "text": "Change Password", "view": "DC.PushButton" },
    "profilePasswordLabel": { "text": "New Password", "view": "DC.Text" },
    "profilePasswordVerifyLabel": { "text": "Verify new password", "view": "DC.Text" },
    "scheduleAddButton": { "initialHeight": 25, "initialWidth": 50, "leftImageWidth": 5, "onclick": "scheduleAdminAdd", "rightImageWidth": 5, "text": "+", "view": "DC.PushButton" },
    "scheduleAdminAddSessionButton": { "initialHeight": 20, "initialWidth": 130, "leftImageWidth": 5, "onclick": "scheduleAdminAddSession", "rightImageWidth": 5, "text": "Add new session", "view": "DC.PushButton" },
    "scheduleAdminCancelButton": { "initialHeight": 30, "initialWidth": 82, "leftImageWidth": 5, "onclick": "scheduleAdminCancel", "rightImageWidth": 5, "text": "Cancel", "view": "DC.PushButton" },
    "scheduleAdminDateLabel": { "text": "Date:", "view": "DC.Text" },
    "scheduleAdminDeleteButton": { "initialHeight": 30, "initialWidth": 79, "leftImageWidth": 5, "onclick": "scheduleAdminDelete", "rightImageWidth": 5, "text": "Delete", "view": "DC.PushButton" },
    "scheduleAdminDetailLabel": { "text": "Detail Label:", "view": "DC.Text" },
    "scheduleAdminEndTimeLabel": { "text": "End Time:", "view": "DC.Text" },
    "scheduleAdminHeader": { "text": "Schedule Administration", "view": "DC.Text" },
    "scheduleAdminRoomLabel": { "text": "Room/Location", "view": "DC.Text" },
    "scheduleAdminSaveButton": { "initialHeight": 30, "initialWidth": 82, "leftImageWidth": 5, "onclick": "scheduleAdminSave", "rightImageWidth": 5, "text": "Save", "view": "DC.PushButton" },
    "scheduleAdminSessions": { "allowsEmptySelection": true, "dataArray": ["Item 1", "Item 2", "Item 3"], "labelElementId": "scheduleAdminSessionsTitle", "listStyle": "List.DESKTOP_LIST", "propertyValues": { "dataArrayBinding": { "keypath": "sessions.content" } }, "sampleRows": 3, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "scheduleAdminSessionsTitle": { "propertyValues": { "textBinding": { "keypath": "*.session_title" } }, "text": "Item", "view": "DC.Text" },
    "scheduleAdminStartTimeLabel": { "text": "Start Time:", "view": "DC.Text" },
    "scheduleEditButton": { "initialHeight": 25, "initialWidth": 50, "leftImageWidth": 5, "onclick": "scheduleAdminToggleEdit", "rightImageWidth": 5, "text": "Edit", "view": "DC.PushButton" },
    "scheduleList": { "allowsEmptySelection": true, "dataArray": ["Item 1", "Item 2", "Item 3"], "dataSourceName": "MobilAP.DataSourceStub", "labelElementId": "scheduleListStart", "listStyle": "List.DESKTOP_LIST", "sampleRows": 3, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "scheduleListDate": { "text": "Day", "view": "DC.Text" },
    "scheduleListDetail": { "text": "Detail", "view": "DC.Text" },
    "scheduleListEditButton": { "initialHeight": 25, "initialWidth": 50, "leftImageWidth": 5, "rightImageWidth": 5, "text": "Edit", "view": "DC.PushButton" },
    "scheduleListStart": { "text": "Start", "view": "DC.Text" },
    "scheduleListTitle": { "text": "Title", "view": "DC.Text" },
    "scheduleTypeList": { "dataArray": ["Item 1", "Item 2", "Item 3"], "labelElementId": "scheduleTypeListLabel", "listStyle": "List.DESKTOP_LIST", "sampleRows": 3, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "scheduleTypeListLabel": { "text": "Item", "view": "DC.Text" },
    "session_admin_options_discussion": { "propertyValues": { "checkedBinding": { "keypath": "session.content.session_flags_discussion" } }, "view": "DC.ToggleButton" },
    "session_admin_options_evalution": { "propertyValues": { "checkedBinding": { "keypath": "session.content.session_flags_evaluation" } }, "view": "DC.ToggleButton" },
    "session_admin_options_links": { "propertyValues": { "checkedBinding": { "keypath": "session.content.session_flags_links" } }, "view": "DC.ToggleButton" },
    "session_admin_options_user_links": { "propertyValues": { "checkedBinding": { "keypath": "session.content.session_flags_user_links" } }, "view": "DC.ToggleButton" },
    "sessionAdminDescription": { "propertyValues": { "valueBinding": { "keypath": "session.content.session_description" } }, "view": "DC.TextField" },
    "sessionAdminPresentersAddButton": { "initialHeight": 25, "initialWidth": 65, "leftImageWidth": 5, "rightImageWidth": 5, "text": "Add", "view": "DC.PushButton" },
    "sessionAdminPresentersAddLegend": { "text": "Add presenter (user must already exist)", "view": "DC.Text" },
    "sessionAdminPresentersAddList": { "allowsEmptySelection": true, "dataArray": ["Item 1", "Item 2", "Item 3"], "labelElementId": "sessionAdminPresentersAddName", "listStyle": "List.DESKTOP_LIST", "sampleRows": 3, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "sessionAdminPresentersAddName": { "text": "Name (email)", "view": "DC.Text" },
    "sessionAdminPresentersEmail": { "propertyValues": { "textBinding": { "keypath": "*.email" } }, "text": "email", "view": "DC.Text" },
    "sessionAdminPresentersFirstName": { "propertyValues": { "textBinding": { "keypath": "*.FirstName" } }, "text": "First", "view": "DC.Text" },
    "sessionAdminPresentersLabel": { "text": "Presenters", "view": "DC.Text" },
    "sessionAdminPresentersLastName": { "propertyValues": { "textBinding": { "keypath": "*.LastName" } }, "text": "Last", "view": "DC.Text" },
    "sessionAdminPresentersList": { "allowsEmptySelection": true, "dataArray": ["Item 1", "Item 2", "Item 3"], "labelElementId": "sessionAdminPresentersFirstName", "listStyle": "List.DESKTOP_LIST", "propertyValues": { "dataArrayBinding": { "keypath": "session.content.session_presenters" } }, "sampleRows": 3, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "sessionAdminPresentersRemoveButton": { "initialHeight": 25, "initialWidth": 90, "leftImageWidth": 5, "onclick": "removeSessionPresenter", "rightImageWidth": 5, "text": "Remove", "view": "DC.PushButton" },
    "sessionAdminSaveButton": { "initialHeight": 30, "initialWidth": 300, "leftImageWidth": 5, "onclick": "sessionSave", "rightImageWidth": 5, "text": "Save", "view": "DC.PushButton" },
    "sessionAdminTitle": { "propertyValues": { "valueBinding": { "keypath": "session.content.session_title" } }, "view": "DC.TextField" },
    "sessionDiscussionClearButton": { "initialHeight": 30, "initialWidth": 140, "leftImageWidth": 5, "onclick": "sessionClearDiscussion", "rightImageWidth": 5, "text": "Clear Discussion", "view": "DC.PushButton" },
    "sessionDiscussionList": { "allowsEmptySelection": true, "dataArray": ["Item 1", "Item 2", "Item 3"], "labelElementId": "sessionDiscussionListText", "listStyle": "List.DESKTOP_LIST", "propertyValues": { "dataArrayBinding": { "keypath": "session.content.session_discussion" } }, "sampleRows": 3, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "sessionDiscussionListText": { "propertyValues": { "textBinding": { "keypath": "*.post_text" } }, "text": "Text", "view": "DC.Text" },
    "sessionDiscussionListTime": { "propertyValues": { "textBinding": { "keypath": "*.post_timestamp", "transformer": "timestampTransformer" } }, "text": "Time", "view": "DC.Text" },
    "sessionDiscussionListUser": { "propertyValues": { "textBinding": { "keypath": "*.post_user", "transformer": "mobilAP_UserTransformer" } }, "text": "User", "view": "DC.Text" },
    "sessionDiscussionPostButton": { "initialHeight": 30, "initialWidth": 68, "leftImageWidth": 5, "onclick": "post_discussion", "rightImageWidth": 5, "text": "Post", "view": "DC.PushButton" },
    "sessionEvaluationQuestionFinishButton": { "initialHeight": 30, "initialWidth": 76, "leftImageWidth": 5, "onclick": "sessionEvaluationFinish", "rightImageWidth": 5, "text": "Finish", "view": "DC.PushButton" },
    "sessionEvaluationQuestionNextButton": { "initialHeight": 30, "initialWidth": 69, "leftImageWidth": 1, "onclick": "sessionEvaluationNext", "rightImageWidth": 15, "text": "Next", "view": "DC.PushButton" },
    "sessionEvaluationQuestionPreviousButton": { "initialHeight": 30, "initialWidth": 93, "leftImageWidth": 15, "onclick": "sessionEvaluationPrevious", "rightImageWidth": 1, "text": "Previous", "view": "DC.PushButton" },
    "sessionEvaluationQuestionResponses": { "allowsEmptySelection": true, "dataArray": ["Item 1", "Item 2", "Item 3"], "labelElementId": "evaluationQuestionResponsesText", "listStyle": "List.DESKTOP_LIST", "sampleRows": 3, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "sessionEvaluationQuestionText": { "text": "Evaluation", "view": "DC.Text" },
    "sessionEvaluationThanksText": { "text": "Thanks for your evaluation", "view": "DC.Text" },
    "sessionHeader": { "propertyValues": { "textBinding": { "keypath": "session.content.session_title" } }, "text": "Session Title", "view": "DC.Text" },
    "sessionInfoDate": { "text": "Date", "view": "DC.Text" },
    "sessionInfoDescription": { "propertyValues": { "textBinding": { "keypath": "session.content.session_description", "nullValuePlaceholder": "No Description" } }, "text": "Session Description", "view": "DC.Text" },
    "sessionInfoEnd": { "text": "12:00", "view": "DC.Text" },
    "sessionInfoPresentersFirstName": { "propertyValues": { "textBinding": { "keypath": "*.FirstName" } }, "text": "First", "view": "DC.Text" },
    "sessionInfoPresentersLabel": { "text": "Presenters", "view": "DC.Text" },
    "sessionInfoPresentersLastName": { "propertyValues": { "textBinding": { "keypath": "*.LastName" } }, "text": "Last", "view": "DC.Text" },
    "sessionInfoPresentersList": { "allowsEmptySelection": true, "dataArray": ["Item 1", "Item 2", "Item 3"], "labelElementId": "sessionInfoPresentersLastName", "listStyle": "List.DESKTOP_LIST", "propertyValues": { "dataArrayBinding": { "keypath": "session.content.session_presenters" } }, "sampleRows": 3, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "sessionInfoRoom": { "text": "Room", "view": "DC.Text" },
    "sessionInfoStart": { "text": "12:00", "view": "DC.Text" },
    "sessionInfoTimeDash": { "text": "-", "view": "DC.Text" },
    "sessionLinksAddButton": { "initialHeight": 30, "initialWidth": 100, "leftImageWidth": 5, "onclick": "sessionLinkButton", "rightImageWidth": 5, "text": "Add Link", "view": "DC.PushButton" },
    "sessionLinksAddSubmitButton": { "initialHeight": 30, "initialWidth": 100, "leftImageWidth": 5, "onclick": "sessionAddLink", "rightImageWidth": 5, "text": "Save", "view": "DC.PushButton" },
    "sessionLinksAddTitleLabel": { "text": "Title:", "view": "DC.Text" },
    "sessionLinksAddURLLabel": { "text": "URL:", "view": "DC.Text" },
    "sessionLinksList": { "allowsEmptySelection": true, "dataArray": ["Item 1", "Item 2", "Item 3"], "labelElementId": "sessionLinksListTitle", "listStyle": "List.DESKTOP_LIST", "sampleRows": 3, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "sessionLinksListDeleteButton": { "initialHeight": 20, "initialWidth": 55, "leftImageWidth": 5, "onclick": "sessionDeleteLink", "rightImageWidth": 5, "text": "Delete", "view": "DC.PushButton" },
    "sessionLinksListTitle": { "text": "Title", "view": "DC.Text" },
    "sessionLinksListURL": { "text": "URL", "view": "DC.Text" },
    "sessionLinksNotice": { "text": "No links have been posted", "view": "DC.Text" },
    "sessionQuestionAdminCancelButton": { "initialHeight": 30, "initialWidth": 82, "leftImageWidth": 5, "onclick": "sessionQuestionAdminCancel", "rightImageWidth": 5, "text": "Cancel", "view": "DC.PushButton" },
    "sessionQuestionAdminDeleteButton": { "initialHeight": 30, "initialWidth": 79, "leftImageWidth": 5, "onclick": "sessionQuestionAdminDelete", "rightImageWidth": 5, "text": "Delete", "view": "DC.PushButton" },
    "sessionQuestionAdminHeader": { "text": "Add new Question", "view": "DC.Text" },
    "sessionQuestionAdminQuestionActiveLabel": { "text": "Active", "view": "DC.Text" },
    "sessionQuestionAdminQuestionMaxChoicesLabel": { "text": "Maximum choices the responder may select", "view": "DC.Text" },
    "sessionQuestionAdminQuestionMinChoicesLabel": { "text": "Minimum choices the responder must select", "view": "DC.Text" },
    "sessionQuestionAdminQuestionTextLabel": { "text": "Question Title", "view": "DC.Text" },
    "sessionQuestionAdminResponsesAddButton": { "initialHeight": 25, "initialWidth": 65, "leftImageWidth": 5, "onclick": "sessionQuestionAdminAddResponse", "rightImageWidth": 5, "text": "Add", "view": "DC.PushButton" },
    "sessionQuestionAdminResponsesList": { "allowsEmptySelection": true, "dataArray": ["Item 1", "Item 2", "Item 3"], "labelElementId": "sessionQuestionAdminResponseTitle", "listStyle": "List.DESKTOP_LIST", "sampleRows": 3, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "sessionQuestionAdminResponsesRemoveButton": { "initialHeight": 25, "initialWidth": 65, "leftImageWidth": 5, "rightImageWidth": 5, "text": "Remove", "view": "DC.PushButton" },
    "sessionQuestionAdminResponseTitle": { "text": "Item", "view": "DC.Text" },
    "sessionQuestionAdminSaveButton": { "initialHeight": 30, "initialWidth": 71, "leftImageWidth": 5, "onclick": "sessionQuestionAdminSave", "rightImageWidth": 5, "text": "Save", "view": "DC.PushButton" },
    "sessionQuestionAnswersClearButton": { "initialHeight": 30, "initialWidth": 120, "leftImageWidth": 5, "onclick": "clearQuestionAnswers", "rightImageWidth": 5, "text": "Clear Results", "view": "DC.PushButton" },
    "sessionQuestionAskQuestionResponseText": { "text": "Item", "view": "DC.Text" },
    "sessionQuestionAskResponsesList": { "allowsEmptySelection": true, "dataArray": ["Item 1", "Item 2", "Item 3"], "dataSourceName": "question_responses", "labelElementId": "sessionQuestionAskQuestionResponseText", "listStyle": "List.DESKTOP_LIST", "sampleRows": 3, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "sessionQuestionAskSelectMessage": { "text": "Please select...", "view": "DC.Text" },
    "sessionQuestionAskSubmitButton": { "initialHeight": 30, "initialWidth": 150, "leftImageWidth": 5, "onclick": "submit_question", "rightImageWidth": 5, "text": "Submit Response", "view": "DC.PushButton" },
    "sessionQuestionAskViewResultsButton": { "initialHeight": 30, "initialWidth": 116, "leftImageWidth": 5, "onclick": "sessionQuestionViewResults", "rightImageWidth": 5, "text": "View Results", "view": "DC.PushButton" },
    "sessionQuestionQuestionText": { "text": "Question", "view": "DC.Text" },
    "sessionQuestionResultsAnswerCount": { "text": "Text", "view": "DC.Text" },
    "sessionQuestionResultsAnswerText": { "text": "Item", "view": "DC.Text" },
    "sessionQuestionResultsList": { "allowsEmptySelection": true, "dataArray": ["Item 1", "Item 2", "Item 3"], "dataSourceName": "question_answers", "labelElementId": "sessionQuestionResultsAnswerText", "listStyle": "List.DESKTOP_LIST", "sampleRows": 3, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "sessionQuestionsAddButton": { "initialHeight": 25, "initialWidth": 50, "leftImageWidth": 5, "onclick": "sessionQuestionsAddQuestion", "rightImageWidth": 5, "text": "+", "view": "DC.PushButton" },
    "sessionQuestionsEditButton": { "initialHeight": 25, "initialWidth": 50, "leftImageWidth": 5, "onclick": "sessionQuestionsToggleEdit", "rightImageWidth": 5, "text": "Edit", "view": "DC.PushButton" },
    "sessionQuestionsList": { "allowsEmptySelection": true, "dataArray": ["Item 1", "Item 2", "Item 3"], "labelElementId": "sessionQuestionsListQuestionText", "listStyle": "List.DESKTOP_LIST", "sampleRows": 3, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "sessionQuestionsListEditButton": { "initialHeight": 25, "initialWidth": 50, "leftImageWidth": 5, "rightImageWidth": 5, "text": "Edit", "view": "DC.PushButton" },
    "sessionQuestionsListQuestionText": { "text": "Item", "view": "DC.Text" },
    "sessionQuestionsNotice": { "text": "No questions have been posted", "view": "DC.Text" },
    "sessionQuestionStack": { "subviewsTransitions": [{ "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "dissolve" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "dissolve" }], "view": "DC.StackLayout" },
    "sessionsAdminSessionsLabel": { "text": "Session:", "view": "DC.Text" },
    "sessionStack": { "subviewsTransitions": [{ "direction": "right-left", "duration": "0.1", "timing": "ease-in-out", "type": "none" }, { "direction": "right-left", "duration": "0.1", "timing": "ease-in-out", "type": "none" }, { "direction": "right-left", "duration": "0.1", "timing": "ease-in-out", "type": "none" }, { "direction": "right-left", "duration": "0.1", "timing": "ease-in-out", "type": "none" }, { "direction": "right-left", "duration": "0.1", "timing": "ease-in-out", "type": "none" }, { "direction": "right-left", "duration": "0.1", "timing": "ease-in-out", "type": "none" }, { "direction": "right-left", "duration": "0.1", "timing": "ease-in-out", "type": "none" }, { "direction": "right-left", "duration": "0.1", "timing": "ease-in-out", "type": "none" }, { "direction": "right-left", "duration": "0.1", "timing": "ease-in-out", "type": "none" }], "view": "DC.StackLayout" },
    "sessionTabbar": { "allowsEmptySelection": true, "dataArray": ["Item 1", "Item 2", "Item 3"], "dataSourceName": "session_tabs", "labelElementId": "sessionTabbarTitle", "listStyle": "List.DESKTOP_LIST", "sampleRows": 5, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "sessionTabbarTitle": { "text": "Item", "view": "DC.Text" },
    "setupHeader": { "text": "mobilAP Setup", "view": "DC.Text" },
    "setupLoading": { "text": "Setup is loading. If this does not work, you might not be running on a webserver with PHP installed.", "view": "DC.Text" },
    "splitLayout": { "flexibleViewIndex": 1, "initialSize": 728, "initialSplitterSize": 1, "isVertical": true, "splitterPosition": 171, "view": "DC.SplitLayout" },
    "welcomeText": { "text": "Welcome", "view": "DC.Text" }
};






















































