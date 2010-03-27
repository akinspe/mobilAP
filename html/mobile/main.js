/* This script handles items only dealt with by the mobile side */

/* this function called by the dashcode engine */
function load()
{
    if (!navigator.userAgent.match(new RegExp('AppleWebKit/.*Mobile/'))) {
        var body = document.getElementsByTagName('body')[0];
        body.className += ' desktop';
    }

    dashcode.setupParts();
    
    //instantiate controllers    
    window.mobilAP = new MobilAP.MobileApplicationController({
        browser: document.getElementById('browser').object,
        header: document.getElementById('header').object,
        homeList: document.getElementById('homeList').object
    });

    mobilAP.loginController = new MobilAP.MobileLoginController({
        userID_field: document.getElementById('loginUserID'),
        password_field: document.getElementById('loginPassword'),
        password_fields: document.getElementById('loginPasswordFields'), 
        login_result: document.getElementById('loginResult'),
        createNewUserButton: document.getElementById('loginCreateNewUserButton').object
    });
    mobilAP.addViewController('login', mobilAP.loginController);

    mobilAP.scheduleController = new MobilAP.MobileScheduleController({
        stack: document.getElementById('scheduleStack').object,
        daysList: document.getElementById('scheduleDaysList').object
    });
    mobilAP.addViewController('schedule', mobilAP.scheduleController);

    mobilAP.scheduleTypeController = new MobilAP.MobileScheduleTypeController('scheduleTypeList', {
    	 scheduleController: mobilAP.scheduleController
    });

    mobilAP.welcomeController = {
        viewDidLoad: function() {
            MobilAP.loadContent('welcome', base_url + 'data/welcome.php');
        }
    }
    mobilAP.addViewController('welcome', mobilAP.welcomeController);

    mobilAP.sessionController = new MobilAP.MobileSessionController({
        session_tabbar: document.getElementById('sessionTabbar').object,
        stack: document.getElementById('sessionStack').object
    });
    mobilAP.addViewController('session', mobilAP.sessionController);

    mobilAP.scheduleListController = new MobilAP.MobileScheduleListListController('scheduleListList', {
    	 scheduleController: mobilAP.scheduleController,
    	 sessionController: mobilAP.sessionController,
        }
    );
    mobilAP.scheduleController.addViewController('scheduleList', mobilAP.scheduleListController);

    mobilAP.scheduleDayController = new MobilAP.MobileScheduleDayListController('scheduleDayList',{
    	 scheduleController: mobilAP.scheduleController,
    	 sessionController: mobilAP.sessionController,
         scheduleNextButton: document.getElementById('scheduleDayNext'),
         schedulePreviousButton: document.getElementById('scheduleDayPrev'),
         scheduleDaysDay: document.getElementById('scheduleDayDay'),
         scheduleDaysDate: document.getElementById('scheduleDayDate')
    });
    mobilAP.scheduleController.addViewController('scheduleDay', mobilAP.scheduleDayController);

    mobilAP.scheduleMonthController = new MobilAP.MobileScheduleMonthListController('scheduleMonthList',{
    	 scheduleController: mobilAP.scheduleController,
    	 sessionController: mobilAP.sessionController
    });
    mobilAP.scheduleController.addViewController('scheduleMonth', mobilAP.scheduleMonthController);

    scheduleMonthCalendarController = new MobilAP.MobileScheduleCalendarController('schedule_month_calendar',{
    	 scheduleController: mobilAP.scheduleController
    });

    mobilAP.profileController = new MobilAP.MobileProfileController({
        profileList: document.getElementById('directoryProfileList').object,
        profileImage: document.getElementById('directoryProfileImage').object,
        profileFirstName: document.getElementById('directoryProfileFirstName'),
        profileLastName: document.getElementById('directoryProfileLastName')
    });
    mobilAP.addViewController('directoryProfile', mobilAP.profileController);
    
    mobilAP.directoryController = new MobilAP.MobileDirectoryController('directoryList',{
    	directorySearch: document.getElementById('directorySearch'),
        profileController: mobilAP.profileController
    });
    mobilAP.addViewController('directory', mobilAP.directoryController);

    mobilAP.presenterController = new MobilAP.MobileDirectoryController('sessionInfoPresentersList', {
        sessionController: mobilAP.sessionController,
        profileController: mobilAP.profileController,
        viewDidLoad: function() {
            this.object.clearSelection();
            this.object.viewElement().style.display = this.object.rows.length>0 ? 'block' : 'none';
        }
    });
    mobilAP.sessionController.addViewController('info', mobilAP.presenterController);

    mobilAP.sessionInfoController = new MobilAP.MobileSessionInfoController({
        session_schedule_box: document.getElementById('sessionInfoScheduleBox'),
        session_date: document.getElementById('sessionInfoDate'),
        session_start: document.getElementById('sessionInfoStart'),
        session_end: document.getElementById('sessionInfoEnd'),
        session_room: document.getElementById('sessionInfoRoom'),
        session_description: document.getElementById('sessionInfoDescriptionBox'),
        sessionController: mobilAP.sessionController
    });
    mobilAP.sessionController.addViewController('info', mobilAP.sessionInfoController);

    mobilAP.sessionLinksController = new MobilAP.ListController('sessionLinksList', {
        sessionController: mobilAP.sessionController,
        viewDidLoad: function() {
            this.object.clearSelection();
            this.object.viewElement().style.display = this.object.rows.length>0 ? 'block' : 'none';
        }
    });
    mobilAP.sessionController.addViewController('links', mobilAP.sessionLinksController);

    mobilAP.sessionDiscussionController = new MobilAP.MobileDiscussionController('sessionDiscussionList', {
        profileController: mobilAP.profileController,
        sessionController: mobilAP.sessionController
    });
    mobilAP.sessionController.addViewController('discussion', mobilAP.sessionDiscussionController);

    mobilAP.announcementsController = new MobilAP.MobileAnnouncementController('announcementsList');
    mobilAP.addViewController('announcements', mobilAP.announcementsController);
    
    mobilAP.sessionEvaluationController = new MobilAP.MobileSessionEvaluationController({
        sessionController: mobilAP.sessionController,
        evaluationQuestionText: document.getElementById('evaluationQuestionText'),
        evaluationQuestionResponses: document.getElementById('evaluationQuestionResponses').object,
        evaluationQuestionTextResponse: document.getElementById('evaluationQuestionTextResponse'),
        evaluationQuestionPreviousButton: document.getElementById('evaluationQuestionPreviousButton'),
        evaluationQuestionNextButton: document.getElementById('evaluationQuestionNextButton'),
        evaluationQuestionFinishButton: document.getElementById('evaluationQuestionFinishButton')
    });
    mobilAP.sessionController.addViewController('evaluation', mobilAP.sessionEvaluationController);
    
    mobilAP.userProfileController = new MobilAP.MobileUserProfileController({
        passwordField: document.getElementById('profilePasswordField'),
        passwordVerifyField: document.getElementById('profilePasswordVerifyField')
    });
    mobilAP.addViewController('profile', mobilAP.userProfileController);

    mobilAP.questionsController = new MobilAP.MobileQuestionsController('sessionQuestionsList', {
        sessionQuestionsNotice: document.getElementById('sessionQuestionsNotice'), 
        sessionController: mobilAP.sessionController
    });
    mobilAP.sessionController.addViewController('questions', mobilAP.questionsController);

    mobilAP.questionController = new MobilAP.MobileQuestionController({ 
        stack: document.getElementById('sessionQuestionStack').object,
        sessionController: mobilAP.sessionController,
        responses_list: document.getElementById('sessionQuestionResponsesList').object,
        questionText: document.getElementById('sessionQuestionText'),
        questionSelectMessageText: document.getElementById('sessionQuestionSelectMessage'),
        results_canvas: document.getElementById('sessionQuestionResultsCanvas'),
        results_list: document.getElementById('sessionQuestionResultsList').object,
        results_chart: document.getElementById('sessionQuestionResultsChart')
        }
    );
    mobilAP.sessionController.addViewController('question', mobilAP.questionController);
    
    mobilAP.directoryAdminController = new MobilAP.MobileDirectoryAdminController({
        directoryController: mobilAP.directoryController
    });
    
    mobilAP.serialController = new MobilAP.MobileSerialController({
    });
    mobilAP.serialController.setReloadTimer(5);
    
    
    window.scrollTo(0,1);
}

MobilAP.MobileApplicationController= Class.create(MobilAP.ApplicationController, {
    login: function() {
        mobilAP.loadView('login', 'Login');
    },
    logout: function() {
        return mobilAP.loginController.logout();
    },
    configUpdated: function(change,keyPath) {
    	this.base(change,keyPath);
    	this.updateHeader();
    },
    updateHeader: function() {
    	/* this may have to be changed if the private header part changes */
    	var stack = this.header._stack;
    	stack[0].title = mobilAP.getConfig('SITE_TITLE');
    	stack[0].titleElement.innerHTML = mobilAP.getConfig('SITE_TITLE');
    	this.header._sizeChanged();
    },
    openURL: function(url) {
        //load youtube and maps urls inside the window to launch the iPhone apps
        if (url.match('^(http://www.youtube.com/watch|http://maps.google.com)')) {
            window.location=url;
        } else {
            window.open(url);
        }
    },
    userUpdated: function(change,keyPath) {
        this.base(change,keyPath);
        dashcode.getDataSource('homeData').queryUpdated();
        dashcode.getDataSource('session').queryUpdated();
    },
    loadView: function(toView, title) {        
        if (this.getConfig('CONTENT_PRIVATE') && !this.isLoggedIn() && toView != 'login') {
            this.loadView('login', 'Login');
            this.loginController.setLoginResult('You must login to view content on this site');
            return;
        }

        // don't go forward if we're already at the view
        if (this.browser.getCurrentView().id != toView) {
            this.browser.goForward(toView, title, this.browserBackHandler.bind(this));
            this.viewDidLoad(toView);
        }
    },
    browserBackHandler: function()
    {
        // we need to use timers until a valid call back is made once the selection has been changed
        var startView = this.browser.getCurrentView().id;
      //  var browser = this.browser;
        var self = this;
        var changed = function(prevView) {
            var view = self.browser.getCurrentView().id;        
            if (startView==view) {
                setTimeout(changed, 20, prevView);
                return;
            }

            self.viewDidUnload(prevView);
            self.viewDidLoad(view);
        }
        
        changed(startView);
        return;        
    },
    goHome: function() {
        for (var i=this.browser._headerElement.object._stack.length; i>1; i--) {
        	this.goBack();
        }
    },
    goBack: function() {
		this.browser.goBack();
    },
    homeSelected: function() {
        var selectedObjects = this.homeList.selectedObjects();
        if (selectedObjects && selectedObjects.length==1) {
            this.loadView(selectedObjects[0].valueForKey('id'), selectedObjects[0].valueForKey('title'));
        }
    },
    error: function(error) {
        alert(error.error_message);
    },
    constructor: function(parameters)
    {
        this.product = 'mobile';
        this.base(parameters);
        if (!this.browser) {
        	throw('Browser element not set');
        }
        this.homeList.addObserverForKeyPath(this, this.homeSelected, "selectionIndexes");
    }
    
        
});

MobilAP.MobileSerialController = Class.create(MobilAP.SerialController, {
});

MobilAP.MobileProfileController = Class.create(MobilAP.ProfileController, {
    _labels: [ 'organization', 'email', 'phone' ],
    _profileLabels: [],
    viewDidLoad: function() {
        this.profileList.clearSelection();
    },
    setUser: function(user) {
    	this.base(user);
        this._profileLabels = [];
        for (var i=0; i<this._labels.length; i++) {
            if (this.user[this._labels[i]]) {
                this._profileLabels.push(this._labels[i]);
            }
        }

		this.profileFirstName.innerHTML = this.user.FirstName;
		this.profileLastName.innerHTML = this.user.LastName;
		this.profileImage.setSrc(this.user.imageThumbURL);
        this.profileList.reloadData();
    },
	prepareRow: function(rowElement, rowIndex, templateElements) {
        templateElements.directoryProfileLabel.innerHTML = this._profileLabels[rowIndex];
        templateElements.directoryProfileValue.innerHTML = this.user[this._profileLabels[rowIndex]];
        switch (this._profileLabels[rowIndex])
        {
            case 'email':
                rowElement.onclick = function() {
                    window.open('mailto:' + templateElements.directoryProfileValue.innerHTML);
                }
        }
	},
    numberOfRows: function() {
        return this._profileLabels.length;
    },
    constructor: function(params) {
        this.base(params);
        this.profileList.setDataSource(this);
    }
});

MobilAP.MobileScheduleTypeController = Class.create(MobilAP.ListController, {
    scheduleTypes: [ {label:'List',value:'scheduleList'},{label:'Day',value:'scheduleDay'},{label:'Month',value:'scheduleMonth'}],
    numberOfRows: function() {
        return this.scheduleTypes.length;
    },
	prepareRow: function(rowElement, rowIndex, templateElements) {
        templateElements.scheduleTypeListLabel.innerHTML = this.scheduleTypes[rowIndex].label;
        var self = this;
        rowElement.onclick = function() {
            self.scheduleController.setScheduleType(self.scheduleTypes[rowIndex].value);
        }
	},
    rowSelected: function(change, keyPath) {
        if (this.object.selectionIndexes().length==0) {
            this.setScheduleType(this.scheduleController.scheduleType());
        }
    },
    setScheduleType: function(scheduleType) {
        for (var i=0; i<this.scheduleTypes.length; i++) {
            if (this.scheduleTypes[i].value==scheduleType) {
                this.object.setSelectionIndexes([i]);
                return;
            }
        }
    },
    scheduleTypeUpdated: function(change, keyPath) {
        this.setScheduleType(change.newValue);
    },
    constructor: function(part_id, params) {
        this.base(part_id, params);
        this.object.setDataSource(this);
        this.setScheduleType(this.scheduleController.scheduleType());
        this.scheduleController.addObserverForKeyPath(this, this.scheduleTypeUpdated, "scheduleType");
    }
});

MobilAP.MobileScheduleController = Class.create(MobilAP.ScheduleController, {
    viewDidLoad: function() {
        this.subViewDidLoad(this.scheduleType());
    },
    viewControllers: {},
    addViewController: function(view_id, controller) {
        if (!(view_id in this.viewControllers)) {
            this.viewControllers[view_id] = [];
        }

        this.viewControllers[view_id].push(controller);
    },
    subViewDidLoad: function(toView) {
		if (toView in this.viewControllers) {
			for (var i=0; i<this.viewControllers[toView].length;i++) {
				try {
					this.viewControllers[toView][i].viewDidLoad(toView);
				} catch(e) {
				}
			}
		}
    },
    subViewDidUnload: function(toView) {
		if (toView in this.viewControllers) {
			for (var i=0; i<this.viewControllers[toView].length;i++) {
				try {
					this.viewControllers[toView][i].viewDidUnload(toView);
				} catch(e) {
				}
			}
		}
    },
    loadView: function(toView) {
        if (this.stack.getCurrentView().id != toView) {
            this.subViewDidUnload(this.stack.getCurrentView().id);
            this.stack.setCurrentView(toView);
            this.subViewDidLoad(toView);
        }
    },
    setScheduleType: function(schedule_type) {
        this.base(schedule_type);
        this.loadView(this.scheduleType());
    },
    setDate: function(date) {
        this.base(date);
        if (null === this.dateIndex()) {
            this.daysList.clearSelection();
        } else {
            this.daysList.setSelectionIndexes([this.dateIndex()]);
        }
    },
    constructor: function(params) {
        this.base(params);
        this.setScheduleType('scheduleDay');
    }
});

MobilAP.MobileScheduleListController = Class.create(MobilAP.ScheduleListController, {
    rowSelected: function(change, keyPath) {
    	this.base(change,keyPath);
        if (!this.selectedObject) {
            return;
        }
        
        switch (this.selectedObject.schedule_type)
        {
            case 'date':
                this.clearSelection();
                return;
                break;
            case 'session':
                this.sessionController.setTabIndex(0);
                this.sessionController.setScheduleData(this.selectedObject);
                this.sessionController.setSession(this.selectedObject.session_id);
                break;
            case 'session_group':
                throw('need to handle session group');
        }
        mobilAP.loadView(this.selectedObject.schedule_type, this.selectedObject.title);
    }
});

MobilAP.MobileScheduleListListController = Class.create(MobilAP.MobileScheduleListController, {

});

MobilAP.MobileScheduleSessionListController = Class.create(MobilAP.MobileScheduleListController, {
	numberOfRows: function() {
        try {
            return this.scheduleController.schedule().length;
        } catch(e) {
            return 0;
        }
	},
    // this method has to be there. For some reason Dashcode framework looks for this method, but actuallly CALLS representationForRow
    objectForRow: function(rowIndex) {
    },
    representationForRow: function(rowIndex) {
        return this.scheduleController.schedule()[(rowIndex)];
    },
	prepareRow: function(rowElement, rowIndex, templateElements) {
        var item = this.scheduleController.schedule()[rowIndex];
        var transformer = new timeTransformer();
        var self = this;
        templateElements['schedule'+this.scheduleType+'Time'].innerHTML = transformer.transformedValue(item.start_time);
        templateElements['schedule'+this.scheduleType+'Title'].innerHTML = item.title;
        templateElements['schedule'+this.scheduleType+'Detail'].innerHTML = item.detail;
	},
    viewDidLoad: function() {
        this.object.viewElement().style.display = this.object.rows.length>0 ? 'block' : 'none';
        this.clearSelection();
    },
    constructor: function(list, params) {
        this.base(list, params);
        this.object.setDataSource(this);
    }
});


MobilAP.MobileScheduleDayListController = Class.create(MobilAP.MobileScheduleSessionListController, {
    scheduleType: 'Day',
    updateElements: function() {
        this.scheduleDaysDay.innerHTML = new dayTransformer().transformedValue(this.scheduleController.date());
        this.scheduleDaysDate.innerHTML = new shortDateTransformer().transformedValue(this.scheduleController.date());
        this.scheduleNextButton.style.display = this.scheduleController.isAfterLastDay() ? 'none' : '';
        this.schedulePreviousButton.style.display = this.scheduleController.isBeforeFirstDay() ? 'none' : '';
    },
    dateUpdated: function(change, keyPath) {
        this.base(change, keyPath);
        this.updateElements();
    },
    scheduleUpdated: function(change, keyPath) {
        this.base(change, keyPath);
        this.updateElements();
    }
});

MobilAP.MobileScheduleMonthListController = Class.create(MobilAP.MobileScheduleSessionListController, {
    scheduleType: 'Month'
});


MobilAP.MobileScheduleCalendarController = Class.create(MobilAP.ScheduleCalendarController, {
});

MobilAP.MobileDiscussionController = Class.create(MobilAP.DiscussionController, {
    rowSelected: function(change, keyPath) {
    	this.base(change,keyPath);
        var selectedObjects = this.object.selectedObjects();
        if (selectedObjects && selectedObjects.length==1) {
            //mobilAP.loadView('directoryProfile', selectedObjects[0].post_name);
        }
    },
    viewDidLoad: function() {
        this.clearSelection();
        this.object.viewElement().style.display = this.object.rows.length>0 ? 'block' : 'none';
    }
});


MobilAP.MobileSessionInfoController = Class.create(MobilAP.SessionInfoController, {
        viewDidLoad: function() {
            if (this.getConfig('SINGLE_SESSION_MODE')) {
                this.session_schedule_box.style.display='none';
            } else {
                this.session_schedule_box.style.display='block';
                var _timeTransformer = new timeTransformer();
                var _dateTransformer = new shortDateTransformer();
                this.session_date.innerHTML = _dateTransformer.transformedValue(this.sessionController.scheduleData.start_time);
                this.session_start.innerHTML = _timeTransformer.transformedValue(this.sessionController.scheduleData.start_time);
                this.session_end.innerHTML = _timeTransformer.transformedValue(this.sessionController.scheduleData.end_time);
                this.session_room.innerHTML = this.sessionController.scheduleData.room;
            }
            this.session_description.style.display = this.sessionController.session.session_description ? 'block' : 'none';
        }
});

MobilAP.MobileDirectoryAdminController = Class.create(MobilAP.DirectoryAdminController, {

});

MobilAP.MobileDirectoryController = Class.create(MobilAP.DirectoryController, {
    viewDidLoad: function() {
        this.clearSelection();
    },
    content: function() {
    	return this._content;
    },
    reloadData: function() {
    	this._content = this._dataSource.content();
    	this.object.reloadData();
    },
    rowSelected: function(change, keyPath) {
    	this.base(change,keyPath);
        if (this.user) {
           mobilAP.loadView('directoryProfile', this.user.getFullName());
        }
 
    },
    objectForRow: function() {
    },
    representationForRow: function(rowIndex) {
        return this.content()[rowIndex];
    },
	prepareRow: function(rowElement, rowIndex, templateElements) {
		var user = this.representationForRow(rowIndex);
		templateElements.directoryFirstName.innerHTML = user.FirstName;
		templateElements.directoryLastName.innerHTML = user.LastName;
		templateElements.directoryOrganization.innerHTML = user.organization;
	},
	filter: function(searchValue) {
		searchValue = searchValue.toLowerCase();
		for (var i=0; i<this.object.rows.length;i++) {
			var row = this.object.rows[i];
            if (this._content[i].FirstName.toLowerCase().match(searchValue) ||
                this._content[i].LastName.toLowerCase().match(searchValue) ||
                this._content[i].organization.toLowerCase().match(searchValue)) {
                row.style.display='';
            } else {
                row.style.display='none';
            }
		}
	},
    numberOfRows: function() {
		return this.content().length
    },
    constructor: function(part_id,params) {
    	this.base(part_id,params);
		this._dataSource = dashcode.getDataSource('users');
		this._content = this._dataSource.content() || [];
		this._dataSource.addObserverForKeyPath(this, this.reloadData, "content");
        this.object.setDataSource(this);
    }
});

MobilAP.MobileLoginController= Class.create(MobilAP.LoginController, {
    loginHandler: function(json) {
        var result = this.base(json);
        this.createNewUserButton.viewElement().style.display='none';
        if (this.isError(result)) {
            this.setLoginResult(json.error_message);
            switch (result.error_code)
            {
                case this.CREATE_NEW_USER:
                    this.createNewUserButton.viewElement().style.display='block';
                    break;
                case this.ERROR_REQUIRES_PASSWORD:
                    this.password_fields.style.display='block';
                    break;
            }
        } else {
            mobilAP.goBack();
        }
    },
    logoutHandler: function(json) {
        var result = this.base(json);
        if (this.isError(result)) {
            this.setLoginResult(json.error_message);
        } else {
            mobilAP.goBack();
        }
    },
    in_progress: function(bool) {
        this.setLoginResult(bool ? 'Logging in...' : '');
    },
    setLoginResult: function(message) {
        this.login_result.innerHTML = message;
    },
    viewDidLoad: function() {
        this.userID_field.value = '';
        this.password_field.value = '';
        this.createNewUserButton.viewElement().style.display='none';
        this.setLoginResult('');
        if (!this.getConfig('USE_PASSWORDS')) {
        	this.password_fields.style.display = 'none';
        }
    }
});

MobilAP.MobileUserProfileController = Class.create(MobilAP.UserProfileController, {
    setUser: function(user) {
        this.base(user);
    },
    viewDidLoad: function() {
        this.passwordField.value='';
        this.passwordVerifyField.value='';
    }
});


MobilAP.MobileAnnouncementController = Class.create(MobilAP.AnnouncementController, {
    rowSelected: function(change, keyPath) {
        this.base(change, keyPath);
        var selectedObjects = this.object.selectedObjects();
        if (selectedObjects && (1 == selectedObjects.length)){
            mobilAP.loadView('announcement', selectedObjects[0].valueForKey('announcement_title'));
        }    
    }
});

MobilAP.MobileQuestionsController = Class.create(MobilAP.ListController, {
    viewDidLoad: function() {
        this.clearSelection();
        this.reloadData();
        this.sessionQuestionsNotice.style.display = this.content().length == 0 ? '' : 'none';
    },
    rowSelected: function(change, keyPath) {
        this.base(change, keyPath);
        var selectedObjects = this.object.selectedObjects();
        if (selectedObjects && (1 == selectedObjects.length)){
            mobilAP.questionController.setQuestion(new MobilAP.SessionQuestion(selectedObjects[0]));
            this.sessionController.loadView('question');
        }    
    },
    objectForRow: function() {
    },
    representationForRow: function(rowIndex) {
        var question = new MobilAP.SessionQuestion(this.sessionController.session.session_questions[rowIndex]);
        return question;
    },
	prepareRow: function(rowElement, rowIndex, templateElements) {
        var self = this;
        var question = this.representationForRow(rowIndex);
        MobilAP.setClassName(rowElement,'question_inactive', !question.question_active);
        templateElements.sessionQuestionsText.innerHTML = question.question_text;
	},
    numberOfRows: function() {
        return this.sessionController.session.session_questions.length;
    },
    constructor: function(part_id, params) {
        this.base(part_id, params);
        this.object.setDataSource(this);
        this.sessionController.addObserverForKeyPath(this, this.reloadData, "session");
    }
});

MobilAP.MobileQuestionController = Class.create(MobilAP.QuestionController, {
    viewDidLoad: function() {
        if (this.sessionController.questionAnswered(this.question.question_id)) {
            this.showResults();
        } else {
            this.showAsk();
        }
    },
    submitQuestion: function() {
        var result = this.base();
        if (this.isError(result)) {
            if (result.error_code==mobilAP.ERROR_NO_USER) {
                mobilAP.login();
            }
            alert(result.error_message);
        } else {
            this.showResults();
        }
    },
    getChartHeight: function() {
        return 150;
    },
    getChartWidth: function() {
        return 300;
    },
    selectResponse: function(response_index) {
        this.base(response_index);
        this.responses_list.setSelectionIndexes(this.selectedResponses);
    },
    showAsk: function() {
        this.stack.setCurrentView('sessionQuestionAsk');
    },
    showResults: function() {
        this.updateChart();
        this.stack.setCurrentView('sessionQuestionResults');
    },
    sessionUpdated: function(change, keyPath) {
        this.base(change, keyPath);
        this.responses_list.setSelectionIndexes(this.selectedResponses);
    },
    setQuestion: function(object) {
        this.base(object);
        this.questionText.innerHTML = this.question.question_text;
        this.questionSelectMessageText.innerHTML = this.question.selectMessage();
        this.responses_list.reloadData();
        this.results_list.reloadData();
        this.updateChart();
    }
});

MobilAP.MobileSessionEvaluationController = Class.create(MobilAP.SessionEvaluationController, {
    setQuestion: function(question) {
        this.base(question);
        this.evaluationQuestionText.innerHTML = this.question.question_text;
        this.evaluationQuestionResponses.reloadData();
        this.evaluationQuestionTextResponse.value = '';
        switch (this.question.question_response_type)
        {
            case 'M':
                this.evaluationQuestionResponses.viewElement().style.display = 'block';
                this.evaluationQuestionTextResponse.style.display = 'none';
                if (this.questionIndex in this.responses) {
                    var response = this.responseForResponseValue(this.responses[this.questionIndex]);
                    this.evaluationQuestionResponses.setSelectionIndexes([response.response_index]);
                }
                break;
            case 'T':
                this.evaluationQuestionResponses.viewElement().style.display = 'none';
                this.evaluationQuestionTextResponse.style.display = 'block';
                if (this.questionIndex in this.responses) {
                    this.evaluationQuestionTextResponse.value=this.responses[this.questionIndex];
                }
                break;
        }
        this.evaluationQuestionNextButton.style.display = this.questionIndex < (this.content().length-1) ? 'block' : 'none';
        this.evaluationQuestionPreviousButton.style.display = this.questionIndex > 0 ? 'block' : 'none';
        this.evaluationQuestionFinishButton.style.display = this.questionIndex == (this.content().length-1) ? 'block' : 'none';
    },
    numberOfRows: function() {
        return this.question ? this.question.responses.length : 0;
    },
	prepareRow: function(rowElement, rowIndex, templateElements) {
        templateElements.evaluationQuestionResponsesText.innerHTML = this.question.responses[rowIndex].response_text;
        var self = this;
        rowElement.onclick = function() {
            self.setResponse(self.questionIndex, self.question.responses[rowIndex].response_value);
        }
	},
    viewDidLoad: function() {
        if (this.sessionController.evaluationCompleted()) {
            this.sessionController.loadView('evaluation_thanks', false);
        } else {
            this.setQuestionIndex(0);
        }
    },
    dataSourceUpdated: function(change, keyPath) {
        this.base(change, keyPath);
        this.setQuestionIndex(0);
    },
    constructor: function(params) {
        this.base(params);
        this.evaluationQuestionResponses.setDataSource(this);
        var self = this;
        this.evaluationQuestionTextResponse.onchange = function() {
            self.setResponse(self.questionIndex, this.value);
        }
    }
    
});

MobilAP.MobileSessionController = Class.create(MobilAP.SessionController, {
    active_tab_id: 'info',
    active_tab_index: 0,
    active_view: 'info',
    viewControllers: {},
    tabIndexForId: function(tab_id) {
        var tabs = session_tabs.tabs();
        for (var i=0; i< tabs.length; i++) {
            if (tabs[i].tab_id==tab_id) {
                return i;
            }
        }
    },
    setTabID: function(tab_id) {
        var tab_index = this.tabIndexForId(tab_id);
        if (typeof tab_index != 'undefined') {
            return this.setTabIndex(tab_index);
        }
        
        throw ("Unable to get index for " + tab_id);
    },
    addViewController: function(view_id, controller) {
        if (!(view_id in this.viewControllers)) {
            this.viewControllers[view_id] = [];
        }

        this.viewControllers[view_id].push(controller);
    },
    sessionUpdated: function(change, keyPath) {
        this.base(change, keyPath);
        this.session_tabbar.reloadData();
        this.session_tabbar.setSelectionIndexes([this.tabIndexForId(this.active_tab_id)]);
        this.loadView(this.active_view);
    },
    subViewDidLoad: function(toView) {
		if (toView in this.viewControllers) {
			for (var i=0; i<this.viewControllers[toView].length;i++) {
				try {
					this.viewControllers[toView][i].viewDidLoad(toView);
				} catch(e) {
				}
			}
		}
    },
    subViewDidUnload: function(toView) {
		if (toView in this.viewControllers) {
			for (var i=0; i<this.viewControllers[toView].length;i++) {
				try {
					this.viewControllers[toView][i].viewDidUnload(toView);
				} catch(e) {
				}
			}
		}
    },
    loadView: function(view) {
        var toView = 'session_' + view;
        if (this.getCurrentView().id != toView) {
            this.subViewDidUnload(this.active_view);
            this.setCurrentView(toView);
            this.active_view = view
            this.subViewDidLoad(view);
        } else {
            this.active_view = view
        }
    },
    setTabIndex: function(tab_index) {
        var tab_id = session_tabs.tabs()[tab_index].tab_id;
        this.loadView(tab_id);
        
        this.active_tab_index = tab_index;
        this.active_tab_id = tab_id
        this.session_tabbar.reloadData();
        this.session_tabbar.setSelectionIndexes([tab_index]);
    },
    getCurrentView: function() {
        return this.stack.getCurrentView();
    },
    setCurrentView: function(view_id, animate) {
        this.stack.setCurrentView(view_id, animate);
    },
    viewDidUnload: function() {
        this.stopReloadTimer();
    },
    viewDidLoad: function(view_id) {
        this.subViewDidLoad(this.active_view);
        MobilAP.setClassName(view_id,'mobilAP_sessionadmin',this.isAdmin());
    },
    addLink: function(link_url, link_title) {
        var result = this.base(link_url, link_title);
        if (this.isError(result)) {
            if (result.error_code==mobilAP.ERROR_NO_USER) {
                mobilAP.login();
            }
            alert(result.error_message);
            return;
        }
        this.loadView('links');
    },
    postDiscussion: function(post_text) {
        var result = this.base(post_text);
        if (this.isError(result)) {
            if (result.error_code==mobilAP.ERROR_NO_USER) {
                mobilAP.login();
            }
            alert(result.error_message);
            return;
        }
    }
});

var question_responses = {
	
	numberOfRows: function() {
        try {
            return mobilAP.questionController.question.responses.length;
        } catch(e) {
            return 0;
        }
	},
	
	// The List calls this method once for every row.
	prepareRow: function(rowElement, rowIndex, templateElements) {
        var response = mobilAP.questionController.question.responses[rowIndex];
            var index = mobilAP.questionController.codeMap.charAt(rowIndex);
        templateElements.sessionQuestionResponseText.innerText = index + '. ' + response.response_text;
        rowElement.onclick = function() {
            mobilAP.questionController.selectResponse(rowIndex);
        }
	}
};

var question_answers = {
	
	numberOfRows: function() {
        try {
            return mobilAP.questionController.question.responses.length+1;
        } catch(e) {
            return 0;
        }
	},
	
	prepareRow: function(rowElement, rowIndex, templateElements) {
        var question = mobilAP.questionController.question;
        if (rowIndex < question.responses.length) {
            MobilAP.removeClassName(rowElement,'question_answer_total');
            var response = question.responses[rowIndex];
            var index = mobilAP.questionController.codeMap.charAt(rowIndex);
            templateElements.sessionQuestionResultsAnswerText.innerText = index + '. ' + response.response_text;
            templateElements.sessionQuestionResultsAnswerCount.innerText = question.answers[response.response_value];
        } else {
            MobilAP.addClassName(rowElement,'question_answer_total');
            templateElements.sessionQuestionResultsAnswerText.innerText = 'Total:';
            templateElements.sessionQuestionResultsAnswerCount.innerText = question.answers.total;
        }
	}
};


function sessionLinkButton(event)
{
    mobilAP.sessionController.loadView('links_add');
}

function sessionAddLink(event)
{
    var link_url = document.getElementById('sessionLinksAddURL').value;
    var link_title = document.getElementById('sessionLinksAddTitle').value;
    var result = mobilAP.sessionController.addLink(link_url, link_title);
}

function sessionLinkGo(event)
{
    var e = event || window.event;
    var target = e.srcElement || e.target;
    try {
        var url = target.objectValue.link_url;
    } catch(e) {
        this.log("Couldn't get proper object for sessionLinkGo");
        return;
    }
    
    mobilAP.openURL(url);
    try {
        target.parentNode.object.clearSelection(true);
    } catch(e) {}
}

function post_discussion(event)
{
    var post_text = document.getElementById('sessionDiscussionTextField').value;
    var result = mobilAP.sessionController.postDiscussion(post_text);
}

var session_tabs = {
	
	_alltabs: [
        {tab_id:"info",tab_title:"Info"},
        {tab_id:"evaluation",tab_title:"Evaluation"},
        {tab_id:"links",tab_title:"Links"},
        {tab_id:"questions",tab_title:"Questions"},
        {tab_id:"discussion",tab_title:"Discussion"},
        {tab_id:"admin",tab_title:"Admin"}
    ],
    tabs: function() {
        var tabs = [];

        if (mobilAP.sessionController) {
            for (var i=0; i<this._alltabs.length; i++) {
                if (mobilAP.sessionController['show'+this._alltabs[i].tab_id]()) {
                    tabs.push(this._alltabs[i]);
                }
            }
        }
        
        return tabs;
    },
        
    numberOfRows: function() {
        this._tabs = this.tabs();
		return this._tabs.length;
	},
    objectForRow: function() {
    },
    representationForRow: function(rowIndex) {
        return this._tabs[rowIndex];
    },
    /* figure out how wide the tab bar elements should be. */
    _getWidth: function(rowIndex) {
        if (100 % this._tabs.length == 0) {
            return (100/this._tabs.length);
        }

        switch (this._tabs.length) 
        {
            case 3:
                return (rowIndex==0 || rowIndex==2) ? 33 : 34;
            case 6:
                return (rowIndex==0 || rowIndex==5) ? 16 : 17;
        }
    },
    _getImageLeft: function(rowElement) {
        return 17;
    },
	
	prepareRow: function(rowElement, rowIndex, templateElements) {
        rowElement.style.width = "" + this._getWidth(rowIndex) + "%";
        if (mobilAP.sessionController.active_tab_index==rowIndex) {
            templateElements.sessionTabbarImage_img.src = 'Images/tab_' + this.tabs()[rowIndex].tab_id + '_selected.png';
        } else {
            templateElements.sessionTabbarImage_img.src = 'Images/tab_' + this.tabs()[rowIndex].tab_id + '.png';
        }
        templateElements.sessionTabbarTitle.innerHTML = this.tabs()[rowIndex].tab_title;
        rowElement.onclick = function(event) {
            
            mobilAP.sessionController.setTabIndex(rowIndex);
        };
	}
};

var schedule_list = {
	reloadData: function() {
        document.getElementById('scheduleListList').object.reloadData();
    },	
	numberOfRows: function() {
        if ('undefined'==typeof mobilAP.scheduleController) {
            return 0;
        }

		return mobilAP.scheduleController.flatSchedule().length;
	},
    objectForRow: function(rowIndex) {
    },
    representationForRow: function(rowIndex) {
        return mobilAP.scheduleController.flatSchedule()[rowIndex];
    },
	prepareRow: function(rowElement, rowIndex, templateElements) {
        var object = document.getElementById('scheduleListList').object;
        var item = mobilAP.scheduleController.flatSchedule()[rowIndex];
        rowElement.className += ' schedule_' + item.schedule_type;
        rowElement.object.value = item;
        rowElement.objectValue = item;
        var transformer = new timeTransformer();
        if (item.schedule_type=='date') {
            templateElements.scheduleListTitle.innerHTML = Date.daysShort[item.date.getDay()] + ' ' + Date.monthsShort[item.date.getMonth()] + ' ' + item.date.getDate() + ', ' + item.date.getFullYear();
            templateElements.scheduleListArrow.style.display='none';
            templateElements.scheduleListDetail.innerHTML = '';
            templateElements.scheduleListTime.innerHTML = '';
        } else {
            templateElements.scheduleListTitle.innerHTML = item.title;
            templateElements.scheduleListDetail.innerHTML = item.detail;
            templateElements.scheduleListTime.innerHTML = transformer.transformedValue(item.start_time);
        }

	}
};


function sessionSaveAdmin(event)
{
    mobilAP.sessionController.setTitle(document.getElementById('sessionAdminTitle').value);
    mobilAP.sessionController.setDescription(document.getElementById('sessionAdminDescription').value);
    var session_flags = 0;
    var _flags = [ 'Links', 'UserLinks', 'Discussion','Evaluation'];
    for (var i=0; i<_flags.length; i++) {
        if (document.getElementById('sessionAdminOptions' + _flags[i]).checked) {
            session_flags+=parseInt(document.getElementById('sessionAdminOptions'+_flags[i]).value);
        }
    }
    mobilAP.sessionController.setFlags(session_flags);

    var result = mobilAP.sessionController.saveSessionAdmin();
    if (mobilAP.isError(result)) {
        alert(result.error_message);
    }
}

function setScheduleMode(event)
{
    var selectedObjects = document.getElementById('scheduleTypeList').object.selectedObjects();
    var selectionIndexes = document.getElementById('scheduleTypeList').object.selectionIndexes();
    if (selectionIndexes && (1 == selectionIndexes.length)){
        mobilAP.scheduleController.setScheduleType(selectedObjects[0].value);
    }
}

function submit_question(event) {
    mobilAP.questionController.submitQuestion();
}

function scheduleDaysNext(event) {
    mobilAP.scheduleController.nextDay();
}

function scheduleDaysPrev(event) {
    mobilAP.scheduleController.previousDay();
}

function loginSubmit(event)
{
    var userID = document.getElementById('loginUserID').value;
    var password = document.getElementById('loginPassword').value;
    mobilAP.loginController.login(userID, password);
}

function logoutSubmit(event)
{
    mobilAP.loginController.logout();
}

function sessionEvaluationFinish(event)
{
    var result = mobilAP.sessionEvaluationController.submitEvaluation();
    if (mobilAP.isError(result)) {
        alert(result.error_message);
    } else {
        mobilAP.sessionController.loadView('evaluation_thanks');
    }
}

function sessionEvaluationPrevious(event)
{
    mobilAP.sessionEvaluationController.setPreviousQuestion();
}


function sessionEvaluationNext(event)
{
    mobilAP.sessionEvaluationController.setNextQuestion();
}


function changePassword(event)
{
    var newPassword = mobilAP.userProfileController.passwordField.value;
    var newPasswordVerify = mobilAP.userProfileController.passwordVerifyField.value;
    if (newPassword != newPasswordVerify) {
        alert("You did not verify your new password correctly");
        return;
    }
    var result = mobilAP.userProfileController.setPassword(newPassword);
    if (mobilAP.isError(result)) {
        alert(result.error_message);
    } else {
        mobilAP.userProfileController.passwordVerifyField.value='';
        mobilAP.userProfileController.passwordField.value='';
    }
}


function createNewUser(event)
{
    if (mobilAP.getConfig('ALLOW_SELF_CREATED_USERS')) {
        mobilAP.loadView('profileCreate');
    }
}


function profileCreateSubmit(event)
{
    if (document.getElementById('profileCreatePassword').value != document.getElementById('profileCreateVerifyPassword').value)      {
        alert("You did not verify your password");
    }

    if (document.getElementById('profileCreatePassword').value.length==0) {
        alert("Password should not be blank");
    }
    
    var user = new MobilAP.User({
        FirstName: document.getElementById('profileCreateFirstName').value,
        LastName: document.getElementById('profileCreateLastName').value,
        organization: document.getElementById('profileCreateOrganization').value,
        email: document.getElementById('profileCreateEmail').value,
        password: document.getElementById('profileCreatePassword').value
    });
    
    var result = mobilAP.directoryAdminController.saveUser(user, function(_result) {
        if (mobilAP.isError(_result)) {
            alert(_result.error_message);
        } else {
            alert("Your account has been created. You may now login");
            mobilAP.goBack();
        }
    });
    
    if (mobilAP.isError(result)) {
        alert(result.error_message);        
    }
    
}

function sessionQuestionViewResults(event)
{
    mobilAP.questionController.showResults();
}

function sessionClearDiscussion(event)
{
    if (confirm('Are you sure you wish to clear the discussion for this session?')) {
        mobilAP.sessionController.clearDiscussion();
    }
}

function clearQuestionAnswers(event)
{
    if (confirm('Are you sure you want to clear the question results?')) {
        mobilAP.questionController.clearAnswers();
    }
}

function searchDirectory(event)
{
    var searchContent = document.getElementById('directorySearch').value;
    mobilAP.directoryController.filter(searchContent);
}