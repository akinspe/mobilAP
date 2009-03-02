/* 
 This file was generated by Dashcode and is covered by the 
 license.txt included in the project.  You may edit this file, 
 however it is recommended to first turn off the Dashcode 
 code generator otherwise the changes will be lost.
 */
var dashcodePartSpecs = {
    "announcement_list_title": { "creationFunction": "CreateText", "text": "Item" },
    "announcement_text": { "creationFunction": "CreateText", "text": "Announcement Text" },
    "announcement_timestamp": { "creationFunction": "CreateText", "text": "Posted: x" },
    "announcement_title": { "creationFunction": "CreateText", "text": "Announcement Title" },
    "announcements_list": { "creationFunction": "CreateList", "dataArray": ["Item 1", "Item 2", "Item 3"], "dataSourceName": "announcement_controller", "labelElementId": "announcement_list_title", "listStyle": "List.EDGE_TO_EDGE", "sampleRows": 3, "useDataSource": true },
    "announcements_title": { "creationFunction": "CreateText", "text": "Announcements" },
    "back_button": { "creationFunction": "CreatePushButton", "initialHeight": 30, "initialWidth": 60, "leftImageWidth": 16, "rightImageWidth": 5, "text": "Back" },
    "back_to_questions": { "creationFunction": "CreatePushButton", "initialHeight": 30, "initialWidth": 302, "leftImageWidth": 15, "onclick": "back_to_questions", "rightImageWidth": 15, "text": "Back to Questions" },
    "browser": { "creationFunction": "CreateBrowser" },
    "demo_text": { "creationFunction": "CreateText" },
    "detail_name": { "creationFunction": "CreateText", "text": "Detail Name" },
    "detail_text": { "creationFunction": "CreateText", "text": "Text" },
    "directory_bio": { "creationFunction": "CreateText" },
    "directory_detail_dept": { "creationFunction": "CreateText", "text": "dept" },
    "directory_detail_email": { "creationFunction": "CreateText", "text": "email" },
    "directory_detail_name": { "creationFunction": "CreateText", "text": "Name" },
    "directory_detail_organization": { "creationFunction": "CreateText", "text": "organization" },
    "directory_detail_title": { "creationFunction": "CreateText", "text": "title" },
    "directoryList": { "creationFunction": "CreateList", "dataArray": ["Item 1", "Item 2", "Item 3"], "dataSourceName": "directoryController", "labelElementId": "name", "listStyle": "List.EDGE_TO_EDGE", "sampleRows": 5, "useDataSource": true },
    "evaluation_intro": { "creationFunction": "CreateText", "text": "We appreciate your feedback. Please answer the following brief questions and include additional comments if you wish." },
    "evaluation_thanks_text": { "creationFunction": "CreateText", "text": "Thank you for your response. We appreciate your feedback." },
    "generic_list_list": { "creationFunction": "CreateList", "dataArray": ["Item 1", "Item 2", "Item 3"], "dataSourceName": "genericListController", "labelElementId": "genericList_label", "listStyle": "List.EDGE_TO_EDGE", "sampleRows": 3, "useDataSource": true },
    "genericList_label": { "creationFunction": "CreateText", "text": "Item" },
    "header": { "creationFunction": "CreateHeader", "rootTitle": "mobilAP" },
    "homeList": { "creationFunction": "CreateList", "dataSourceName": "browserController", "labelElementId": "listTitle", "listStyle": "List.EDGE_TO_EDGE", "sampleRows": 4, "useDataSource": true },
    "link_title_label": { "creationFunction": "CreateText", "text": "Title:" },
    "link_url_label": { "creationFunction": "CreateText", "text": "URL:" },
    "links_save_button": { "creationFunction": "CreatePushButton", "initialHeight": 30, "initialWidth": 302, "leftImageWidth": 15, "onclick": "session_links.submit", "rightImageWidth": 15, "text": "Add Link" },
    "listTitle": { "creationFunction": "CreateText", "text": "Item" },
    "login_button": { "creationFunction": "CreatePushButton", "initialHeight": 30, "initialWidth": 60, "leftImageWidth": 5, "onclick": "mobilAP.login_button_handler", "rightImageWidth": 5, "text": "Login" },
    "login_result": { "creationFunction": "CreateText" },
    "login_submit": { "creationFunction": "CreatePushButton", "initialHeight": 25, "initialWidth": 60, "leftImageWidth": 5, "onclick": "mobilAP.login_submit", "rightImageWidth": 5, "text": "Login" },
    "name": { "creationFunction": "CreateText", "text": "Name" },
    "organization": { "creationFunction": "CreateText", "text": "organization" },
    "post_submit": { "creationFunction": "CreatePushButton", "initialHeight": 30, "initialWidth": 128, "leftImageWidth": 15, "onclick": "session_chat.submit", "rightImageWidth": 15, "text": "Submit Post" },
    "post_text": { "creationFunction": "CreateText", "text": "Item" },
    "post_timestamp": { "creationFunction": "CreateText", "text": "time" },
    "post_user": { "creationFunction": "CreateText", "text": "First Last" },
    "post_view": { "creationFunction": "CreatePushButton", "initialHeight": 30, "initialWidth": 131, "leftImageWidth": 15, "onclick": "session_chat.view_posts", "rightImageWidth": 15, "text": "View Posts" },
    "presenter_name": { "creationFunction": "CreateText", "text": "Name" },
    "presenter_organization": { "creationFunction": "CreateText", "text": "Organization" },
    "programList_detail": { "creationFunction": "CreateText", "text": "Event detail" },
    "programList_room": { "creationFunction": "CreateText", "text": "Room" },
    "programList_time": { "creationFunction": "CreateText", "text": "07:00" },
    "programList_title": { "creationFunction": "CreateText", "text": "Event Title" },
    "programs_list": { "creationFunction": "CreateList", "dataArray": ["Item 1", "Item 2", "Item 3"], "dataSourceName": "programSchedule", "labelElementId": "programList_time", "listStyle": "List.ROUNDED_RECTANGLE", "sampleRows": 3, "useDataSource": true },
    "question_answers": { "creationFunction": "CreateList", "dataArray": ["Item 1", "Item 2", "Item 3"], "dataSourceName": "session_question_answers", "labelElementId": "question_response_text_label", "listStyle": "List.ROUNDED_RECTANGLE", "sampleRows": 3, "useDataSource": true },
    "question_response_count": { "creationFunction": "CreateText", "text": "20" },
    "question_response_index": { "creationFunction": "CreateText", "text": "1." },
    "question_response_label": { "creationFunction": "CreateText", "text": "Response" },
    "question_response_text": { "creationFunction": "CreateText", "text": "Question Text" },
    "question_response_text_label": { "creationFunction": "CreateText", "text": "Item" },
    "question_response_total": { "creationFunction": "CreateText", "text": "X Responses" },
    "question_responses": { "creationFunction": "CreateList", "dataArray": ["Item 1", "Item 2", "Item 3"], "dataSourceName": "session_question", "labelElementId": "question_response_label", "listStyle": "List.ROUNDED_RECTANGLE", "sampleRows": 3, "useDataSource": true },
    "question_text": { "creationFunction": "CreateText", "text": "Question Text" },
    "session_abstract": { "creationFunction": "CreateText", "text": "Loading..." },
    "session_add_link_button": { "creationFunction": "CreatePushButton", "initialHeight": 30, "initialWidth": 302, "leftImageWidth": 15, "onclick": "session.show_add_link", "rightImageWidth": 15, "text": "Add Link" },
    "session_data_stack": { "creationFunction": "CreateStackLayout", "subviewsTransitions": [{ "direction": "right-left", "duration": ".55", "timing": "ease-in-out", "type": "cube" }, { "direction": "right-left", "duration": ".55", "timing": "ease-in-out", "type": "cube" }, { "direction": "bottom-top", "duration": ".55", "timing": "ease-in-out", "type": "push" }, { "direction": "right-left", "duration": ".55", "timing": "ease-in-out", "type": "cube" }, { "direction": "right-left", "duration": ".55", "timing": "ease-in-out", "type": "cube" }, { "direction": "right-left", "duration": ".55", "timing": "ease-in-out", "type": "push" }, { "direction": "right-left", "duration": ".55", "timing": "ease-in-out", "type": "cube" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "push" }] },
    "session_day": { "creationFunction": "CreateText", "text": "Loading Schedule..." },
    "session_discussion_count": { "creationFunction": "CreateText" },
    "session_discussion_description": { "creationFunction": "CreateText", "text": "Talk about this session here" },
    "session_discussion_list": { "creationFunction": "CreateList", "dataArray": ["Item 1", "Item 2", "Item 3"], "dataSourceName": "session_chat", "labelElementId": "post_timestamp", "listStyle": "List.ROUNDED_RECTANGLE", "sampleRows": 3, "useDataSource": true },
    "session_discussion_next": { "creationFunction": "CreatePushButton", "initialHeight": 30, "initialWidth": 128, "leftImageWidth": 15, "onclick": "session_chat.nextPage", "rightImageWidth": 15, "text": "Previous Posts" },
    "session_discussion_post": { "creationFunction": "CreatePushButton", "initialHeight": 30, "initialWidth": 301, "leftImageWidth": 15, "rightImageWidth": 15, "text": "New Post" },
    "session_discussion_prev": { "creationFunction": "CreatePushButton", "initialHeight": 30, "initialWidth": 127, "leftImageWidth": 15, "onclick": "session_chat.prevPage", "rightImageWidth": 15, "text": "Recent Posts" },
    "session_group_list": { "creationFunction": "CreateList", "dataArray": ["Item 1", "Item 2", "Item 3"], "dataSourceName": "session_group", "labelElementId": "sessionGroupList_time", "listStyle": "List.ROUNDED_RECTANGLE", "sampleRows": 3, "useDataSource": true },
    "session_group_title": { "creationFunction": "CreateText", "text": "Session Group" },
    "session_links_description": { "creationFunction": "CreateText", "text": "Here you can find and post links that are relevant to this session." },
    "session_links_label": { "creationFunction": "CreateText", "text": "Item" },
    "session_links_list": { "creationFunction": "CreateList", "dataArray": ["Link 1", "Link 2", "Link 3"], "dataSourceName": "session_links", "labelElementId": "session_links_label", "listStyle": "List.ROUNDED_RECTANGLE", "sampleRows": 3, "useDataSource": true },
    "session_menu_label": { "creationFunction": "CreateText", "text": "Item" },
    "session_menu_list": { "creationFunction": "CreateList", "dataArray": ["Wednesday", "Thursday", "Friday"], "dataSourceName": "session_days", "labelElementId": "session_menu_label", "listStyle": "List.ROUNDED_RECTANGLE", "sampleRows": 3, "useDataSource": true },
    "session_presenters_list": { "creationFunction": "CreateList", "dataArray": ["Item 1", "Item 2", "Item 3"], "dataSourceName": "session_presenters", "labelElementId": "presenter_name", "listStyle": "List.ROUNDED_RECTANGLE", "sampleRows": 3, "useDataSource": true },
    "session_question_num": { "creationFunction": "CreateText", "text": "1." },
    "session_question_text": { "creationFunction": "CreateText", "text": "Question" },
    "session_questions_list": { "creationFunction": "CreateList", "dataArray": ["Question 1", "Question 2", ["Question 3", "Question 3"]], "dataSourceName": "session_questions", "labelElementId": "session_question_num", "listStyle": "List.ROUNDED_RECTANGLE", "sampleRows": 3, "useDataSource": true },
    "session_rate_button": { "creationFunction": "CreatePushButton", "initialHeight": 30, "initialWidth": 302, "leftImageWidth": 15, "onclick": "session.start_evaluation", "rightImageWidth": 15, "text": "Rate this session" },
    "session_title": { "creationFunction": "CreateText", "text": "Session Page" },
    "sessionGroupList_detail": { "creationFunction": "CreateText", "text": "Text" },
    "sessionGroupList_room": { "creationFunction": "CreateText", "text": "room" },
    "sessionGroupList_title": { "creationFunction": "CreateText", "text": "Event Title" },
    "sessions_current_detail": { "creationFunction": "CreateText", "text": "Text" },
    "sessions_current_heading": { "creationFunction": "CreateText", "text": "Current Sessions" },
    "sessions_current_list": { "creationFunction": "CreateList", "dataArray": ["Item 1", "Item 2", "Item 3"], "dataSourceName": "current_sessions", "labelElementId": "sessions_current_time", "listStyle": "List.ROUNDED_RECTANGLE", "sampleRows": 2, "useDataSource": true },
    "sessions_current_room": { "creationFunction": "CreateText", "text": "Text" },
    "sessions_current_time": { "creationFunction": "CreateText", "text": "Item" },
    "sessions_current_title": { "creationFunction": "CreateText", "text": "Text" },
    "stackLayout": { "creationFunction": "CreateStackLayout", "subviewsTransitions": [{ "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "push" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "push" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "push" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "push" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "push" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "push" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "push" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "push" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "push" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "push" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "push" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "push" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "push" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "push" }, { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "push" }] },
    "submit_evaluation_next": { "creationFunction": "CreatePushButton", "customImagePosition": "PushButton.IMAGE_POSITION_NONE", "initialHeight": 30, "initialWidth": 45, "leftImageWidth": 15, "onclick": "session_evaluation.next", "rightImageWidth": 15, "text": "Next" },
    "submit_evaluation_prev": { "creationFunction": "CreatePushButton", "initialHeight": 30, "initialWidth": 45, "leftImageWidth": 15, "onclick": "session_evaluation.previous", "rightImageWidth": 15, "text": "Previous" },
    "submit_response": { "creationFunction": "CreatePushButton", "initialHeight": 30, "initialWidth": 45, "leftImageWidth": 15, "onclick": "session_question.submit", "rightImageWidth": 15, "text": "Submit Response" },
    "view_results": { "creationFunction": "CreatePushButton", "initialHeight": 30, "initialWidth": 45, "leftImageWidth": 15, "onclick": "session_question.view_results", "rightImageWidth": 15, "text": "View Results" },
    "welcomeHeading": { "creationFunction": "CreateText", "text": "Welcome" },
    "welcomeText": { "creationFunction": "CreateText", "text": "Welcome placeholder text" }
};
