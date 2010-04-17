/* 
 This file was generated by Dashcode and is covered by the 
 license.txt included in the project.  You may edit this file, 
 however it is recommended to first turn off the Dashcode 
 code generator otherwise the changes will be lost.
 */
var dashcodePartSpecs = {
    "announcementPosted": { "text": "Posted By", "view": "DC.Text" },
    "announcementsList": { "allowsEmptySelection": true, "dataArray": ["Item 1", "Item 2", "Item 3"], "labelElementId": "announcementsTitle", "listStyle": "List.EDGE_TO_EDGE", "propertyValues": { "dataArrayBinding": { "keypath": "announcements.content" } }, "sampleRows": 3, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "announcementsTitle": { "propertyValues": { "textBinding": { "keypath": "*.announcement_title" } }, "text": "Announcement Title", "view": "DC.Text" },
    "announcementText": { "propertyValues": { "textBinding": { "keypath": "announcementsList.selection.announcement_text" } }, "text": "Announcement Text", "view": "DC.Text" },
    "announcementTitle": { "propertyValues": { "textBinding": { "keypath": "announcementsList.selection.announcement_title" } }, "text": "Announcement Title", "view": "DC.Text" },
    "back_button": { "initialHeight": 30, "initialWidth": 50, "leftImageWidth": 16, "rightImageWidth": 5, "text": "Back", "view": "DC.PushButton" },
    "browser": { "clearSelectionOnBack": true, "view": "DC.Browser" },
    "dicussion_text": { "text": "Text", "view": "DC.Text" },
    "directoryFirstName": { "text": "First", "view": "DC.Text" },
    "directoryLastName": { "text": "Last", "view": "DC.Text" },
    "directoryList": { "allowsEmptySelection": true, "dataArray": ["Item 1", "Item 2", "Item 3"], "dataSourceName": "MobilAP.DataSourceStub", "labelElementId": "directoryLastName", "listStyle": "List.EDGE_TO_EDGE", "sampleRows": 3, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "directoryOrganization": { "text": "Organization", "view": "DC.Text" },
    "directoryProfileFirstName": { "propertyValues": { "textBinding": { "keypath": "profile.content.FirstName" } }, "text": "John", "view": "DC.Text" },
    "directoryProfileImage": { "view": "DC.ImageLayout" },
    "directoryProfileLabel": { "text": "label", "view": "DC.Text" },
    "directoryProfileLastName": { "propertyValues": { "textBinding": { "keypath": "profile.content.LastName" } }, "text": "Appleseed", "view": "DC.Text" },
    "directoryProfileList": { "allowsEmptySelection": true, "dataArray": ["organization", "department", "phone"], "labelElementId": "directoryProfileLabel", "listStyle": "List.ROUNDED_RECTANGLE", "sampleRows": 3, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "directoryProfileValue": { "text": "Item", "view": "DC.Text" },
    "directorySearch": { "view": "DC.SearchField" },
    "evaluationQuestionFinishButton": { "initialHeight": 30, "initialWidth": 76, "leftImageWidth": 5, "onclick": "sessionEvaluationFinish", "rightImageWidth": 5, "text": "Finish", "view": "DC.PushButton" },
    "evaluationQuestionNextButton": { "initialHeight": 30, "initialWidth": 60, "leftImageWidth": 5, "onclick": "sessionEvaluationNext", "rightImageWidth": 16, "text": "Next", "view": "DC.PushButton" },
    "evaluationQuestionPreviousButton": { "initialHeight": 30, "initialWidth": 60, "leftImageWidth": 16, "onclick": "sessionEvaluationPrevious", "rightImageWidth": 5, "text": "Back", "view": "DC.PushButton" },
    "evaluationQuestionResponses": { "allowsEmptySelection": true, "dataArray": ["Item 1", "Item 2", "Item 3"], "labelElementId": "evaluationQuestionResponsesText", "listStyle": "List.ROUNDED_RECTANGLE", "sampleRows": 3, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "evaluationQuestionResponsesText": { "text": "Response", "view": "DC.Text" },
    "evaluationQuestionText": { "text": "Evaluation Question", "view": "DC.Text" },
    "header": { "rootTitle": "mobilAP", "view": "DC.Header" },
    "homeList": { "allowsEmptySelection": true, "labelElementId": "homeListTitle", "listStyle": "DC.List.EDGE_TO_EDGE", "propertyValues": { "dataArrayBinding": { "keypath": "homeData.content" } }, "sampleRows": 5, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "homeListTitle": { "propertyValues": { "textBinding": { "keypath": "*.title" } }, "text": "Item", "view": "DC.Text" },
    "loginCreateNewUserButton": { "initialHeight": 30, "initialWidth": 282, "leftImageWidth": 5, "onclick": "createNewUser", "rightImageWidth": 5, "text": "Create an Account", "view": "DC.PushButton" },
    "loginSubmit": { "initialHeight": 30, "initialWidth": 282, "leftImageWidth": 5, "onclick": "loginSubmit", "rightImageWidth": 5, "text": "Login", "view": "DC.PushButton" },
    "logoutHeader": { "text": "Do you wish to logout?", "view": "DC.Text" },
    "logoutSubmit": { "initialHeight": 30, "initialWidth": 301, "leftImageWidth": 5, "onclick": "logoutSubmit", "rightImageWidth": 5, "text": "Logout", "view": "DC.PushButton" },
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
    "scheduleDayDate": { "text": "Oct 28, 2009", "view": "DC.Text" },
    "scheduleDayDay": { "text": "Wednesday", "view": "DC.Text" },
    "scheduleDayDetail": { "text": "Detail", "view": "DC.Text" },
    "scheduleDayList": { "allowsEmptySelection": true, "dataArray": ["Item 1", "Item 2", "Item 3"], "labelElementId": "scheduleDayTime", "listStyle": "List.ROUNDED_RECTANGLE", "sampleRows": 3, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "scheduleDayPrev": { "view": "DC.ImageView" },
    "scheduleDaysList": { "allowsEmptySelection": true, "dataArray": ["Item 1", "Item 2", "Item 3"], "labelElementId": "schedule_days_listDay", "listStyle": "List.EDGE_TO_EDGE", "propertyValues": { "dataArrayBinding": { "keypath": "schedule.content" } }, "sampleRows": 3, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "scheduleDayTime": { "text": "Time", "view": "DC.Text" },
    "scheduleDayTitle": { "text": "Title", "view": "DC.Text" },
    "scheduleListDetail": { "text": "Detail", "view": "DC.Text" },
    "scheduleListList": { "allowsEmptySelection": true, "dataArray": ["Item 1", "Item 2", "Item 3"], "dataSourceName": "schedule_list", "labelElementId": "scheduleListTime", "listStyle": "List.EDGE_TO_EDGE", "sampleRows": 3, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "scheduleListTime": { "text": "Time", "view": "DC.Text" },
    "scheduleListTitle": { "text": "Title", "view": "DC.Text" },
    "scheduleMonthDetail": { "text": "Detail", "view": "DC.Text" },
    "scheduleMonthList": { "allowsEmptySelection": true, "dataArray": ["Item 1", "Item 2", "Item 3"], "labelElementId": "scheduleMonthTime", "listStyle": "List.EDGE_TO_EDGE", "sampleRows": 3, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "scheduleMonthTime": { "text": "Time", "view": "DC.Text" },
    "scheduleMonthTitle": { "text": "Title", "view": "DC.Text" },
    "scheduleStack": { "subviewsTransitions": [{ "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "none" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "none" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "none" }], "view": "DC.StackLayout" },
    "scheduleTypeList": { "dataArray": [["List", "scheduleList"], ["Day", "scheduleDay"], ["Month", "scheduleMonth"]], "labelElementId": "scheduleTypeListLabel", "listStyle": "List.EDGE_TO_EDGE", "sampleRows": 3, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "scheduleTypeListLabel": { "text": "Item", "view": "DC.Text" },
    "session_admin_options_discussion": { "propertyValues": { "checkedBinding": { "keypath": "session.content.session_flags_discussion" } }, "view": "DC.ToggleButton" },
    "session_admin_options_evalution": { "propertyValues": { "checkedBinding": { "keypath": "session.content.session_flags_evaluation" } }, "view": "DC.ToggleButton" },
    "session_admin_options_links": { "propertyValues": { "checkedBinding": { "keypath": "session.content.session_flags_links" } }, "view": "DC.ToggleButton" },
    "session_admin_options_user_links": { "propertyValues": { "checkedBinding": { "keypath": "session.content.session_flags_user_links" } }, "view": "DC.ToggleButton" },
    "session_days_header": { "propertyValues": { "textBinding": { "keypath": "schedule_days_menu.selection.date_str" } }, "text": "Day", "view": "DC.Text" },
    "sessionAdminDescription": { "propertyValues": { "valueBinding": { "keypath": "session.content.session_description" } }, "view": "DC.TextField" },
    "sessionAdminSaveButton": { "initialHeight": 30, "initialWidth": 300, "leftImageWidth": 5, "onclick": "sessionSaveAdmin", "rightImageWidth": 5, "text": "Save", "view": "DC.PushButton" },
    "sessionAdminTitle": { "propertyValues": { "valueBinding": { "keypath": "session.content.session_title" } }, "view": "DC.TextField" },
    "sessionDiscussionClearButton": { "initialHeight": 30, "initialWidth": 302, "leftImageWidth": 5, "onclick": "sessionClearDiscussion", "rightImageWidth": 5, "text": "Ckear Discussion", "view": "DC.PushButton" },
    "sessionDiscussionList": { "allowsEmptySelection": true, "dataArray": ["Item 1", "Item 2", "Item 3"], "labelElementId": "sessionDiscussionListText", "listStyle": "List.ROUNDED_RECTANGLE", "propertyValues": { "dataArrayBinding": { "keypath": "session.content.session_discussion" } }, "sampleRows": 3, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "sessionDiscussionListText": { "propertyValues": { "textBinding": { "keypath": "*.post_text" } }, "text": "Text", "view": "DC.Text" },
    "sessionDiscussionListTime": { "propertyValues": { "textBinding": { "keypath": "*.post_timestamp", "transformer": "timestampTransformer" } }, "text": "Time", "view": "DC.Text" },
    "sessionDiscussionListUser": { "propertyValues": { "textBinding": { "keypath": "*.post_user", "transformer": "mobilAP_UserTransformer" } }, "text": "User", "view": "DC.Text" },
    "sessionDiscussionPostButton": { "initialHeight": 30, "initialWidth": 301, "leftImageWidth": 5, "onclick": "post_discussion", "rightImageWidth": 5, "text": "Post", "view": "DC.PushButton" },
    "sessionEvaluationThanksText": { "text": "Thank you for your feedback", "view": "DC.Text" },
    "sessionInfoDate": { "text": "Date", "view": "DC.Text" },
    "sessionInfoDescription": { "propertyValues": { "textBinding": { "keypath": "session.content.session_description" } }, "text": "Description", "view": "DC.Text" },
    "sessionInfoEnd": { "text": "12:00p", "view": "DC.Text" },
    "sessionInfoPresentersFirstName": { "propertyValues": { "textBinding": { "keypath": "*.FirstName" } }, "text": "First", "view": "DC.Text" },
    "sessionInfoPresentersLastName": { "propertyValues": { "textBinding": { "keypath": "*.LastName" } }, "text": "Last", "view": "DC.Text" },
    "sessionInfoPresentersList": { "allowsEmptySelection": true, "dataArray": ["Item 1", "Item 2", "Item 3"], "labelElementId": "sessionInfoPresentersLastName", "listStyle": "List.ROUNDED_RECTANGLE", "propertyValues": { "dataArrayBinding": { "keypath": "session.content.session_presenters" } }, "sampleRows": 3, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "sessionInfoPresentersOrganization": { "propertyValues": { "textBinding": { "keypath": "*.organization" } }, "text": "Organization", "view": "DC.Text" },
    "sessionInfoRoom": { "text": "Room", "view": "DC.Text" },
    "sessionInfoStart": { "text": "12:00a", "view": "DC.Text" },
    "sessionLinksAddButton": { "initialHeight": 30, "initialWidth": 302, "leftImageWidth": 15, "onclick": "sessionLinkButton", "rightImageWidth": 15, "text": "Add Link", "view": "DC.PushButton" },
    "sessionLinksAddSubmitButton": { "initialHeight": 30, "initialWidth": 302, "leftImageWidth": 15, "onclick": "sessionAddLink", "rightImageWidth": 15, "text": "Save", "view": "DC.PushButton" },
    "sessionLinksAddTitleLabel": { "text": "Title:", "view": "DC.Text" },
    "sessionLinksAddURLLabel": { "text": "URL:", "view": "DC.Text" },
    "sessionLinksList": { "allowsEmptySelection": true, "dataArray": ["Item 1", "Item 2", "Item 3"], "labelElementId": "sessionLinksTitle", "listStyle": "List.ROUNDED_RECTANGLE", "propertyValues": { "dataArrayBinding": { "keypath": "session.content.session_links" } }, "sampleRows": 3, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "sessionLinksTitle": { "propertyValues": { "textBinding": { "keypath": "*.link_text" } }, "text": "Link text", "view": "DC.Text" },
    "sessionLinksURL": { "propertyValues": { "textBinding": { "keypath": "*.link_url" } }, "text": "url", "view": "DC.Text" },
    "sessionQuestionAnswersClearButton": { "initialHeight": 30, "initialWidth": 302, "leftImageWidth": 15, "onclick": "clearQuestionAnswers", "rightImageWidth": 15, "text": "Clear Results", "view": "DC.PushButton" },
    "sessionQuestionResponsesList": { "allowsEmptySelection": true, "dataArray": ["Item 1", "Item 2", "Item 3"], "dataSourceName": "question_responses", "labelElementId": "sessionQuestionResponseText", "listStyle": "List.ROUNDED_RECTANGLE", "sampleRows": 3, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "sessionQuestionResponseText": { "text": "Item", "view": "DC.Text" },
    "sessionQuestionResultsAnswerCount": { "text": "0", "view": "DC.Text" },
    "sessionQuestionResultsAnswerText": { "text": "Item", "view": "DC.Text" },
    "sessionQuestionResultsList": { "allowsEmptySelection": true, "dataArray": ["Item 1", "Item 2", "Item 3"], "dataSourceName": "question_answers", "labelElementId": "sessionQuestionResultsAnswerText", "listStyle": "List.ROUNDED_RECTANGLE", "sampleRows": 3, "useDataSource": true, "view": "DC.List" },
    "sessionQuestionSelectMessage": { "text": "Please select...", "view": "DC.Text" },
    "sessionQuestionsList": { "allowsEmptySelection": true, "dataArray": ["Item 1", "Item 2", "Item 3"], "labelElementId": "sessionQuestionsText", "listStyle": "List.ROUNDED_RECTANGLE", "sampleRows": 3, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "sessionQuestionsNotice": { "text": "No questions have been posted", "view": "DC.Text" },
    "sessionQuestionStack": { "subviewsTransitions": [{ "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "none" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "none" }], "view": "DC.StackLayout" },
    "sessionQuestionsText": { "text": "Item", "view": "DC.Text" },
    "sessionQuestionSubmitButton": { "initialHeight": 30, "initialWidth": 302, "leftImageWidth": 15, "onclick": "submit_question", "rightImageWidth": 15, "text": "Submit Response", "view": "DC.PushButton" },
    "sessionQuestionText": { "text": "Question", "view": "DC.Text" },
    "sessionQuestionViewResultsButton": { "initialHeight": 30, "initialWidth": 302, "leftImageWidth": 15, "onclick": "sessionQuestionViewResults", "rightImageWidth": 15, "text": "View Results", "view": "DC.PushButton" },
    "sessionStack": { "subviewsTransitions": [{ "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "none" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "none" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "none" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "none" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "push" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "none" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "push" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "none" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "none" }], "view": "DC.StackLayout" },
    "sessionTabbar": { "dataArray": ["Info", "Evaluation", "Links", "Questions", "Discussion"], "dataSourceName": "session_tabs", "labelElementId": "sessionTabbarTitle", "listStyle": "List.EDGE_TO_EDGE", "sampleRows": 5, "selectionEnabled": true, "useDataSource": true, "view": "DC.List" },
    "sessionTabbarImage": { "view": "DC.ImageLayout" },
    "sessionTabbarTitle": { "text": "Label", "view": "DC.Text" },
    "sessionTitle": { "propertyValues": { "textBinding": { "keypath": "session.content.session_title" } }, "text": "Session Title", "view": "DC.Text" },
    "setupHeader": { "text": "mobilAP Setup", "view": "DC.Text" },
    "setupLoading": { "text": "Please run mobilAP Setup from a desktop browser.", "view": "DC.Text" },
    "stackLayout": { "subviewsTransitions": [{ "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "push" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "push" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "push" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "push" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "push" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "push" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "push" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "push" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "push" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "push" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "push" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "push" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "push" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "push" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "push" }], "view": "DC.StackLayout" }
};













































