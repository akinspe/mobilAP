var baseUrl = '';
var js_script = baseUrl + 'js.php';
var login_script = baseUrl + 'login.php';

//init handler
function load()
{
    //needed by dashcode
    dashcode.setupParts();
    document.title = dashcodePartSpecs.header.rootTitle;
    
    browserController.browserHandler();
    if (!Transition.areTransformsSupported()) {
        mobilAP.hide_login_form();
    }
    
	//generic orientation handler
    window.addEventListener('orientationchange', mobilAP.orientationchanged);

	//incremental search for directory
    document.getElementById('searchfield').addEventListener('keyup', directoryController.search, false);
    document.getElementById('searchCancel').onclick=function() { 
        document.getElementById('searchfield').value=''; 
        directoryController.search(); 
    };

	//session buttons
    document.getElementById('session_info_button').onclick = function() { session.setPanel('info') };
    document.getElementById('session_links_button').onclick = function() { session.setPanel('links') };
    document.getElementById('session_questions_button').onclick = function() { session.setPanel('questions') };
    document.getElementById('session_discussion_button').onclick = function() { session.setPanel('discussion') };
    document.getElementById('session_discussion_post').onclick = function() { session.setPanel('discussion') };
    
	//add a class so desktop safari is constrained to iphone width for better experience
    if (!navigator.userAgent.match(new RegExp('AppleWebKit/.*Mobile/'))) {
        var body = document.getElementsByTagName('body')[0];
        addClassName(body, 'desktop');
    }
    
    mobilAP.textField = document.getElementById('browser').innerText ? 'innerText' : 'textContent';
    
    //setup user stuff
    mobilAP.getConfigs();
    
    //reload current sessions every minute
    programSchedule.reloadTimer = setInterval(programSchedule.setCurrentSession, 60000);
    announcement_controller.reloadTimer = setInterval(announcement_controller.getAnnouncements, 60000);
    announcement_controller.getAnnouncements();
    scrollTo(0,1);
}

var mobilAP = {
	SHOW_ATTENDEE_DIRECTORY: true,
	SHOW_AD_LETTERS: false,
	LOGGING: false,
    ERROR_NO_USER:-1,
    ERROR_USER_ALREADY_SUBMITTED:-2,
    ERROR_USER_REQUIRES_PASSWORD:-4,
    login_form_visible: false,
    user: {},
    isIPhone: navigator.userAgent.match(new RegExp('iPhone;.*AppleWebKit/.*Mobile/')) ? true: false,
    userdata: { questions: null, evaluation: false},
    
    //generic logging function. uses the LOGGING var to turn on or off logging globally
	log: function(msg) {
		if (!mobilAP.LOGGING) return;
		try { console.log(msg); } catch (e) {}		
	},
    is_loggedIn: function() {
        return this.user.mobilAP_userID ? true : false;
    },
    getUserID: function() {
        return this.user.mobilAP_userID;
    },

    //get the current logged in user
    getLogin: function() {
        mobilAP.loadURL(js_script + '?get=user', mobilAP.processUser);
    },
    orientationchanged: function() {
        //mobilAP.log("Orientation changed, now: " + window.orientation);
    },
    show_login_form: function() {

		if (!mobilAP.USE_PASSWORDS) {
			document.getElementById('login_password').style.display = 'none';
			document.getElementById('login_password_label').style.display = 'none';
		}

        //turn on visibility first
        document.getElementById('login_form').style.visibility='visible';

        //some help for desktop browser
        if (!Transition.areTransformsSupported()) {
            document.getElementById('login_form').style.display='block';
        }
        
        //zero out values
        document.getElementById('login_userID').value='';
        document.getElementById('login_password').value='';
        document.getElementById('login_result')[mobilAP.textField]='';
        document.getElementById('login_button').object.setText('Cancel');        
        
        //move it down, set variable and scroll to top
        document.getElementById('login_form').style.webkitTransform='translateY(0)';    
        mobilAP.login_form_visible = true;;
        scrollTo(0,1);
    },
    hide_login_form: function() {
        //move it up and upate login button
        document.getElementById('login_form').style.webkitTransform='translateY(-144px)';    
        document.getElementById('login_button').object.setText(mobilAP.is_loggedIn() ? 'Logout' : 'Login');        
        mobilAP.login_form_visible = false;

        // hide it for non transform browsers
        if (!Transition.areTransformsSupported()) {
            document.getElementById('login_form').style.display='none';
        }
    },
    currentView:function() {
        return document.getElementById('browser').object.getCurrentView().id;
    },
    toggle_login_form: function() {
        if (this.login_form_visible) {  
            this.hide_login_form();
        } else {
            this.show_login_form();
        }
    },
	login_button_handler: function()
	{
		if (mobilAP.is_loggedIn()) {
			mobilAP.logout();
		} else {
			mobilAP.toggle_login_form();
		}
	},
    
    //handler for processing user JSON
    processUser: function(xhr) {
        try {
            var user = eval("(" + xhr.responseText + ")");
            mobilAP.user = user;
            
            //cookie persists for a week
            var d = new Date();
            d.setTime(d.getTime() + 604800000);
            
            //set cookie to persist login
            if (mobilAP.user.mobilAP_userID) {
                document.cookie = 
                mobilAP.SITE_PREFIX + '_mobilAP_userID=' + mobilAP.user.mobilAP_userID+ '; expires=' + d.toGMTString() + '; path=' + mobilAP.mobilAP_base_path;
                if (mobilAP.CONTENT_PRIVATE) {
                	mobilAP.getSchedule();
				    announcement_controller.getAnnouncements();
                }
            } else {
                document.cookie = 
                mobilAP.SITE_PREFIX + '_mobilAP_userID=; expires=Sun, 9 Jul 2006 00:00:00 UTC; path=' + mobilAP.mobilAP_base_path;
            }
        } catch (e) {
            mobilAP.log(e);
            mobilAP.log("Error loading " + xhr.url);
            mobilAP.log(xhr.responseText);
        }
        
        mobilAP.updateUserElements();
    },
    //update elements based on login status
    updateUserElements: function() {
        if (mobilAP.is_loggedIn()) {
            mobilAP.hide_login_form();
            document.getElementById('login_status')[mobilAP.textField]='<span id="mobilAP_userID">' + mobilAP.user.user.FirstName + ' '+ mobilAP.user.user.LastName + '</span> logged in';
            document.getElementById('login_button').object.setText('Logout');
            addClassName('browser', 'logged_in');
        } else {
            document.getElementById('login_status')[mobilAP.textField]='You are not logged in.';            
            document.getElementById('login_button').object.setText('Login');        
            removeClassName('browser', 'logged_in'); 
       }
    },
    //figure out what AJAX method to use. This allows us to use this in other, cough cough IE, cough cough, browsers
 	getXMLHttpRequest: function() {
 		var funcs = [
		  function() {return new XMLHttpRequest()},
		  function() {return new ActiveXObject('Msxml2.XMLHTTP')},
		  function() {return new ActiveXObject('Microsoft.XMLHTTP')}
		];
		var transport = false;
		
		for (var i=0; i<funcs.length; i++) {
			var lambda = funcs[i];
			try {
				transport = lambda();
				break;
			} catch (e) { }
		}
		
		return transport;
	},
	//generic ajax function that takes a url, callback function and some options.
    loadURL: function(url, callback, options) {
    	
        var xhr = mobilAP.getXMLHttpRequest();
        
        //options are basically method and params to handle POST requests
        if (typeof options=='undefined') {
        	options = { method: 'GET', params: '', synchronous: false}
        }

        options.method = options.method ? options.method : 'GET';
        options.params = options.params ? options.params : '';
        options.asynchronous = options.synchronous ? options.synchronous : false;

        mobilAP.log('loading (' + options.method + '/' + (options.synchronous ? 'S' : 'A') +'): ' + url + ' (' + options.params + ')');

    	xhr.open(options.method, url, !options.synchronous);
    	
    	//add the post header
        if (options.method == 'POST') {
			xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        }

		//if we have a callback, set it
        if (callback) {
            xhr.onreadystatechange = function() {
            	if (xhr.readyState==4) {
					callback(xhr);
				}
            }
        }

        xhr.send(options.params);        
            
    },
    
    logout: function()
    {
        var url=login_script + '?action=logout&js=true';
        mobilAP.loadURL(url, mobilAP.processUser);
    },
    login_submit: function() {
		var login_userID = document.getElementById('login_userID').value;
		//if we use passwords
        var login_pass = document.getElementById('login_password').value;
		
		if (mobilAP.login(login_userID, login_pass)) {
			document.getElementById('login_result')[mobilAP.textField]='Logging in...';    
		}
    },
    login: function(login_userID, login_pass)
    {
        if (login_userID.length==0 || (mobilAP.USE_PASSWORDS && login_pass.length==0)) {
            return false;
        }
        var url=login_script;
        var params='login_userID=' + escape(login_userID) + '&login_submit=js&js=true';
        if (login_pass) {
            params += '&login_pword=' + escape(login_pass);
        }
        
        var options = { method: 'POST', params: params };
        
        mobilAP.loadURL(url, mobilAP.processLogin, options);
        return true;
    },
    processLogin: function(xhr) {
    	//get the result, pass it to the login_result element
        try {
            var result = eval("(" + xhr.responseText + ")");
            if (result.error_message) {
                document.getElementById('login_result')[mobilAP.textField]=result.error_message;
                mobilAP.getLogin();
                if (result.error_code==mobilAP.ERROR_USER_REQUIRES_PASSWORD) {
					document.getElementById('login_password').style.display = 'block';
					document.getElementById('login_password_label').style.display = 'block';
                }
                
                return;
            } else {
                document.getElementById('login_result')[mobilAP.textField]='';
            }
        } catch (e) {    
            mobilAP.log("Error with login: " + xhr.responseText);
            return;
        }
        
        //hide the form and then update the user var
        mobilAP.hide_login_form();
        mobilAP.processUser(xhr);
    },
    getToday: function()
    {
        var now = new Date();
        return now.date();
    },    
    //figure out what today is.
    getCurrentDay: function()
    {
        var now = mobilAP.getToday();
        var day = mobilAP.getDay(now);
        if (day==null) {
            var schedule_data = this.getSchedule();
            if (schedule_data.length==0) {
                mobilAP.log("There is no schedule loaded for this mobilAP");
                day = 0;
            } else {
                day = 0;
            }
        }
        
        return day;

    },
    //returns the day for the given date object
    getDay: function(date)
    {
        var d = date.date();
        var schedule_data = this.getSchedule();
        for (var i=0; i< schedule_data.length;i++) {
            if (d.getTime()==schedule_data[i].date.getTime()) {
                return i;
            }
        }
        
        return null;
    },
    getDaySchedule: function(day)
    {
        day = parseInt(day);
        if (isNaN(day)) {
            return null;
        }
        var schedule_data = this.getSchedule();
        if (schedule_data[day]) {
            return schedule_data[day];
        }

        mobilAP.log("Can't find schedule for " + day);
        return null;
    },
    schedule_loaded: false,
    schedule_loading: false,
    getSchedule: function() 
    {
    	//don't try and load the schedule more than once or while its already loading
        if (!this.schedule_loaded && !this.schedule_loading) {
            this.schedule_loading=true;
            var url = js_script + '?get=schedule';
            mobilAP.loadURL(js_script + '?get=schedule', mobilAP.processSchedule);
        }
        return this.schedule_data;
    },
    processSchedule: function(xhr)
    {
        try {
            var schedule_data = eval("(" + xhr.responseText + ")");
            if (schedule_data.error_message) {
				mobilAP.schedule_loading=false;
				mobilAP.log("Error with schedule: " + schedule_data.error_message);
				return;
            }
            for (var i=0; i<schedule_data.length; i++) {                
                schedule_data[i].date = new Date(schedule_data[i].date_str);
                for (var j=0; j<schedule_data[i].schedule.length; j++) {
                    schedule_data[i].schedule[j].start_date = new Date(schedule_data[i].schedule[j].start_date);
                    schedule_data[i].schedule[j].end_date = new Date(schedule_data[i].schedule[j].end_date);
                }
            }
        } catch (e) {
            mobilAP.log(e);
            mobilAP.log("Error with schedule: " + xhr.responseText);
            var schedule_data = [];
        }
        
        mobilAP.setSchedule(schedule_data);
        mobilAP.schedule_loading=false;
        mobilAP.schedule_loaded = true;
    },
    setSchedule: function(schedule_data)
    {
        this.schedule_data = schedule_data;
        document.getElementById('session_menu_list').object.reloadData();
        programSchedule.setDay();
        programSchedule.setCurrentSession();
    },
    schedule_data: [],
    attendee_summary: {},
    getAttendeeSummary: function() {
        mobilAP.loadURL(js_script + '?get=attendee_summary', mobilAP.processAttendeeSummary);
    },
    processAttendeeSummary: function(xhr) {
        try {
            var attendee_summary = eval("(" + xhr.responseText + ")");
        } catch (e) {
            mobilAP.log("Error with attendee summary: " + xhr.responseText);
            var attendee_summary = {};
        }
        mobilAP.setAttendeeSummary(attendee_summary);
    },
    setAttendeeSummary: function (attendee_summary)
    {
        this.attendee_summary = attendee_summary;
        document.getElementById('demo_text')[mobilAP.textField]="There are " + this.attendee_summary.total + ' attendees representing ' + this.attendee_summary.organizations_count + ' organizations from ' + this.attendee_summary.states_count + ' states attending this event';
        
        if (mobilAP.SUMMARY_MAP) {
			var img = document.getElementById('demo_img');
			if (!img) {
				img = document.createElement('img');
				img.id = 'demo_img';
				document.getElementById('demo_container').appendChild(img);
			}
			img.src = this.getChartDemoURL();
		}
    },
    getChartDemoURL: function() {
        var src = 'http://chart.apis.google.com/chart?chtm=usa&chs=280x140&cht=t';
        //&chco=22222200,eeeeee,FECD66&chf=bg,s,000000';
        var states = [];
        var values = [];
        for (var state in this.attendee_summary.states) {
            states.push(state);
            values.push(Math.floor(this.attendee_summary.states[state]*100/this.attendee_summary.total));
        }
        
        if (states.length>0) {
            src += '&chd=t:' + values.join(',');
            src += '&chld=' + states.join('');
        } else {
            src += '&chd=s:_';
        }
        mobilAP.log(src);
        return src;
    },
	showSessionDetail: function() {
		session.setPanel('info');
		browserController.goForward('sessions_page', session.session_title);
	},
	//generic handler to just replace the contents of an element with the results of the "template" defined in the js script
    loadContent: function(element, template) {
        if (!document.getElementById(element)) {
            return;
        }
        mobilAP.loadURL(js_script + '?get=' + template, function(xhr) { mobilAP.processContent(xhr, element)});
    },
    processContent: function(xhr, element) {
        try {
            var content = eval("(" + xhr.responseText + ")");
            document.getElementById(element).innerHTML=content;
        } catch (e) {
            mobilAP.log("Error with content: " + xhr.responseText);
        }
    },
    getConfigs: function()
    {
        var url = js_script + "?get=configs";
        mobilAP.loadURL(js_script + '?get=configs', mobilAP.processConfigs);
    },
    processConfigs: function(xhr)
    {
        try {
            var configs = eval("(" + xhr.responseText + ")");
            for (var config_var in configs) {
            	switch (configs[config_var])
            	{
            		case '0':
            		case '1':
            		case '-1':
    		            mobilAP[config_var] = parseInt(configs[config_var]) ? true : false;
	            		break;
	            	default:
						mobilAP[config_var] = configs[config_var];
						break;
				}
            }
        } catch (e) {  }

	    document.title = mobilAP.SITE_TITLE;
        browserController.reload();
		mobilAP.getLogin();
		mobilAP.getSchedule();
    }
}

var directoryLetters = {
    letters: [],
    _loaded: false,
    _loading: false,
	numberOfRows: function() {
		if (!this._loaded) {
			directoryLetters.getLetters();
		}
		return this.letters.length;
	},
    getLetters: function()
    {
        if (this._loading) return;
        this._loading = true;
        var url = js_script + '?get=attendee_letters';
        mobilAP.loadURL(url, directoryLetters._processLetters);
    },
    _processLetters: function(xhr)
    {
        try {
            var letters = eval("(" + xhr.responseText + ")");
        } catch (e) {
            mobilAP.log(xhr.responseText);
            return;
        }
        directoryLetters.setLetters(letters);
    },
	setLetters: function(letters) {
        this.letters = letters;
        document.getElementById('directory_letters_list').object.reloadData();
    },
	prepareRow: function(rowElement, rowIndex, templateElements) {
		//set the row so we can possibly hide it when searching
        //set list template
        var letter = this.letters[rowIndex];
        templateElements.letter[mobilAP.textField] = letter;

		//set click handler
		rowElement.onclick = function(event) {
            directoryController.setLetter(letter);
            browserController.goForward('directory', 'Attendee Directory');
		};
	}
    
}

//controller for attendee diretory
var directoryController = {
    DIRECTORY_RELOAD_INTERVAL: 120,
	_loaded: false,
	_loading: false,
    attendees: [],
    rowElements: [],
    detail_attendee: null,    
    reloadView: false,
    letter: false,
    setLetter: function(letter) {
        this.letter = letter;
        this.loadAttendees();
    },
    browserHandler: function(view) {
        if (view=='directory') {
            if (!this._loaded) {
                directoryController.loadAttendees();
            }
            directoryController.startReload(directoryController.DIRECTORY_RELOAD_INTERVAL);
        }

    },
    browserBackHandler: function(view) {
        if (view != 'directory') {
            directoryController.stopReload();
        }
    },
    reloadTimer: null,
    reloadInterval: null,
    //function used to start teh auto reload of attendee directory. 
    startReload: function(reloadInterval)
    {
        if (directoryController.reloadTimer && reloadInterval == directoryController.reloadInterval) {
            return;
        }
        directoryController.stopReload();
        directoryController.reloadInterval = reloadInterval;
        mobilAP.log("Starting directory reload timer of " + directoryController.reloadInterval + " seconds");
        directoryController.reloadTimer = setInterval(directoryController.refresh, directoryController.reloadInterval*1000);
    },
    //function used to stop the auto reload of attendee directory. No need to auto refresh if we're not on that page
        stopReload: function()
    {
        if (!directoryController.reloadTimer) {
            return;
        }
        mobilAP.log('Stopping directory reload timer');
        clearInterval(directoryController.reloadTimer);
        directoryController.reloadTimer=null;
    },
    refresh: function()
    {
        directoryController.loadAttendees();
    },
    //process JSON attendees
    _processAttendees: function(xhr) {
        try {
            var attendees = eval("(" + xhr.responseText + ")");
        } catch (e) {
            mobilAP.log("Error with attendees: " + xhr.responseText);
            var attendees = [];
        }
        directoryController._loading = false;
        directoryController.setAttendees(attendees);
        if (directoryController.reloadView) {
            browserController.setCurrentView(directoryController.reloadView);
            directoryController.reloadView = false;
        }
    },
    setAttendees: function(attendees)
    {
    	this.attendees = attendees;
        this.rowElements = [];
        this._loaded = attendees.length>0 ? true : false;
        document.getElementById('directoryList').object.reloadData();
    },
    loadAttendees: function() {
        if (this._loading) return;
        this._loading = true;
        var url = js_script + '?get=attendees';
        if (this.letter) {
            url += '&letter=' + this.letter;
        }
        mobilAP.loadURL(url, directoryController._processAttendees, { synchronous: true} );
    },
    getDirectoryDetail: function(attendee_id)
    {
        var url = js_script + '?get=attendee&attendee_id=' + attendee_id;
        mobilAP.loadURL(url, directoryController._processAttendee);
    },
    _processAttendee: function(xhr)
    {
        try {
            var attendee = eval("(" + xhr.responseText + ")");
        } catch (e) {
            mobilAP.log("Error with attendee: " + xhr.responseText);
            return;
        }
        directoryController.setDirectoryDetail(attendee);
    },
    setDirectoryDetail: function(attendee)
    {
        this.detail_attendee=attendee;
        this.updateDetail();
    },
    updateDetail: function()
    {
        document.getElementById('directory_detail_name')[mobilAP.textField] = this.detail_attendee.FirstName + ' ' + this.detail_attendee.LastName;
        if (mobilAP.SHOW_AD_PHOTOS) {
			document.getElementById('directory_detail_image').src = baseUrl + this.detail_attendee.image_url;
		}
        document.getElementById('directory_detail_organization')[mobilAP.textField] = this.detail_attendee.organization;
        document.getElementById('directory_detail_email')[mobilAP.textField] = this.detail_attendee.email;
        document.getElementById('directory_detail_email').onclick = function() {
            window.location='mailto:' + directoryController.detail_attendee.email;
        }
        document.getElementById('directory_detail_title')[mobilAP.textField] = this.detail_attendee.title;
        document.getElementById('directory_detail_dept')[mobilAP.textField] = this.detail_attendee.dept;
        document.getElementById('directory_bio').innerHTML = this.detail_attendee.bio;
        
    },
    
    search: function() {
        var searchValue = document.getElementById('searchfield').value.toLowerCase();
        
        //turn on or off cancel button depending on length
        document.getElementById('searchCancel').style.display = searchValue.length>0 ? 'block' : 'none';
        
        //match each row
        for (var i=0; i< directoryController.attendees.length; i++) {
            if (directoryController.attendees[i].FirstName.toLowerCase().match(searchValue) ||
                directoryController.attendees[i].LastName.toLowerCase().match(searchValue) ||
                directoryController.attendees[i].organization.toLowerCase().match(searchValue)) {
                directoryController.rowElements[i].style.display='';
            } else {
                directoryController.rowElements[i].style.display='none';
            }
        }
    },

	//called by list part	
	numberOfRows: function() {
		if (!this._loaded) {
			//directoryController.loadAttendees();
		}
		return this.attendees.length;
	},
	
	prepareRow: function(rowElement, rowIndex, templateElements) {
		//set the row so we can possibly hide it when searching
        this.rowElements[rowIndex] = rowElement;
        rowElement.attendee = this.attendees[rowIndex];
        //set list template
        templateElements.name[mobilAP.textField] = rowElement.attendee.FirstName + ' ' + rowElement.attendee.LastName;
        templateElements.organization[mobilAP.textField] = rowElement.attendee.organization;

		//set click handler
		rowElement.onclick = function(event) {
            directoryController.setDirectoryDetail(rowElement.attendee);
            browserController.goForward('directory_detail', rowElement.attendee.FirstName + ' ' + rowElement.attendee.LastName);
		};
	}
};

//handler for session schedule
var programSchedule = {	
    day: '',
    date: new Date(),
    schedule: [],
    currentSessions: [],
    setSchedule: function(data)
    {
        this.schedule=data;
        
    },
    getSchedule: function() {
        return this.schedule;
    },	
	numberOfRows: function() {
		return this.schedule.length;
	},
    setCurrentSession: function()
    {
        var changed = false;
        var currentSessions = [];
        var day, daySchedule;
        var now = new Date();

        if (day = mobilAP.getDay(now)) {
            daySchedule = mobilAP.getDaySchedule(day);
            for (var i=0; i<daySchedule.schedule.length; i++) {
				if ( (daySchedule.schedule[i].session_id || daySchedule.schedule[i].session_group_id) && daySchedule.schedule[i].start_date.getTime()<=now.getTime() && daySchedule.schedule[i].end_date.getTime()>=now.getTime() ) {
					currentSessions.push(daySchedule.schedule[i]);
				}
            }
        }
      
        mobilAP.log("It is " + now + ". There are " + currentSessions.length + " session(s) currently running");
        if (currentSessions.length>0) {
            for (var i=0; i< currentSessions.length; i++) {
                mobilAP.log(currentSessions[i].title);
            }
        }
        
        if (currentSessions.length != programSchedule.currentSessions.length) {
            changed = true;
        } else {
            for (var i=0; i< currentSessions.length; i++) {
                if (currentSessions[i].schedule_id != programSchedule.currentSessions[i].schedule_id) {
                    changed = true;
                }
            }
        }
        
        programSchedule.currentSessions = currentSessions;
        if (changed) {
            mobilAP.log("Reloading home");
            browserController.reload();
        }
    },
    setDay: function(day)
    {
    	//get the schedule for that day
        var daySchedule = mobilAP.getDaySchedule(day);
        //if we cant find it, use "today's" schedule
        if (!daySchedule) {
            day=mobilAP.getCurrentDay(); 
            daySchedule = mobilAP.getDaySchedule(mobilAP.getCurrentDay());
        }
        
        if (!daySchedule) {
            mobilAP.log("Unable to load schedule for " + day);
            return false;
        }
       
        this.day = day;
        this.date = daySchedule.date;
        this.setSchedule(daySchedule.schedule);
        document.getElementById('session_day')[mobilAP.textField] = this.date.formatDate('l F j');
        
        var schedule_data = mobilAP.getSchedule();
        
        //set the day menu to the current day
        for (var i=0; i<schedule_data.length; i++) {
            if (this.day == i) {
                addClassName(document.getElementById('session_menu_' + i), 'active');
            } else {
                removeClassName(document.getElementById('session_menu_' + i), 'active');
            }
        }
        
        //load the list
        document.getElementById('programs_list').object.reloadData();

    },
	prepareRow: function(rowElement, rowIndex, templateElements) {
        var event_data = this.schedule[rowIndex];
        templateElements.programList_time[mobilAP.textField] = event_data.start_date.formatDate('g:i');     

		//if it's a session it'll have a click handler, show the arrow etc		
        if (event_data.session_id) {
			templateElements.programList_title[mobilAP.textField] = event_data.session_id + ' ' + event_data.title;
            templateElements.programList_arrow.style.display='block';
            
            rowElement.onclick = function() {
            	//set the title before loading
                session.setTitle(event_data.title);
                session.setTime(event_data.start_date, event_data.end_date);
                session.setRoom(event_data.room);
                session.loadSessionData(event_data.session_id);
                mobilAP.showSessionDetail();
            }
        } else if (event_data.session_group_id) {
            templateElements.programList_title[mobilAP.textField] = event_data.title;
            templateElements.programList_arrow.style.display='block';            
            rowElement.onclick = function() {
            	//set the title before loading
                session_group.setTitle(event_data.title);
                session_group.loadSessionGroupData(event_data.session_group_id);
				browserController.goForward('session_group', event_data.title);
            }

        } else {
            templateElements.programList_title[mobilAP.textField] = event_data.title;
            templateElements.programList_arrow.style.display='none';            
        }

        if (event_data.detail) {
            templateElements.programList_detail[mobilAP.textField] = event_data.detail;
            templateElements.programList_detail.style.display='block';
        } else {
            templateElements.programList_detail[mobilAP.textField] = '';
            templateElements.programList_detail.style.display=event_data.room ? 'block' : 'none';
        }
            
        templateElements.programList_room[mobilAP.textField] = event_data.room ? event_data.room : '';
        
	}
};

var session_group = {
    session_group_id: null,
    session_group_title: '',
	schedule_items: [],
	_loading: false,
    loadSessionGroupData: function(session_group_id) {
        if (this._loading) {
            return;
        }
        this._loading = true;
        if (session_group_id != session.session_group_id) {
            document.getElementById('session_group_title')[mobilAP.textField]='Loading...';
        }
        mobilAP.loadURL(js_script + '?get=session_group&session_group_id=' + session_group_id, session_group.processSessionGroupData);
   },
    processSessionGroupData: function(xhr) {
        try {
            var session_group_data = eval("(" + xhr.responseText + ")");
            if (session_group_data.session_group_id) {
                session_group.setSessionGroup(session_group_data);
                session_group._loading = false;
            }
        } catch (e) {
            mobilAP.log("Error with session group data " + xhr.responseText);
            session_group._loading = false;
        }
    },    
    setTitle: function(title) {
        this.session_group_title = title;
        this.updateElements();
    },
    setSessionGroup: function (session_group) {
        this.schedule_items = session_group.schedule_items;
		for (var i=0; i<this.schedule_items.length; i++) {
			this.schedule_items[i].start_date = new Date(this.schedule_items[i].start_date);
			this.schedule_items[i].end_date = new Date(this.schedule_items[i].end_date);
		}

        this.session_group_title = session_group.session_group_title;
        this.session_group_detail = session_group.session_group_detail;
        this.updateElements();
    },
    updateElements: function() {
        document.getElementById('session_group_title')[mobilAP.textField] = this.session_group_title;
        document.getElementById('session_group_list').object.reloadData();
    },
	numberOfRows: function() {
		return this.schedule_items.length;
	},
	
	prepareRow: function(rowElement, rowIndex, templateElements) {
        var event_data = this.schedule_items[rowIndex];
		//if it's a session it'll have a click handler, show the arrow etc		
        if (event_data.session_id) {
			templateElements.sessionGroupList_title[mobilAP.textField] = event_data.session_id + ' ' + event_data.title;
            templateElements.sessionGroupList_arrow.style.display='block';
            
            rowElement.onclick = function() {
            	//set the title before loading
                session.setTitle(event_data.title);
                session.setTime(event_data.start_date, event_data.end_date);
                session.setRoom(event_data.room);
                session.loadSessionData(event_data.session_id);
                mobilAP.showSessionDetail();
            }
        } else {
            templateElements.sessionGroupList_title[mobilAP.textField] = event_data.title;
            templateElements.sessionGroupList_arrow.style.display='none';            
        }

        if (event_data.detail) {
            templateElements.sessionGroupList_detail[mobilAP.textField] = event_data.detail;
            templateElements.sessionGroupList_detail.style.display='block';
        } else {
            templateElements.sessionGroupList_detail[mobilAP.textField] = '';
            templateElements.sessionGroupList_detail.style.display=event_data.room ? 'block' : 'none';
        }

        templateElements.sessionGroupList_room[mobilAP.textField] = event_data.room ? event_data.room : '';

	}
};


var current_sessions = {

	numberOfRows: function() {
		return programSchedule.currentSessions.length;
	},
	
	prepareRow: function(rowElement, rowIndex, templateElements) {
        var event_data = programSchedule.currentSessions[rowIndex];
        templateElements.sessions_current_time[mobilAP.textField] = event_data.start_date.formatDate('h:i');     

        templateElements.sessions_current_title[mobilAP.textField] = event_data.session_id + ' ' + event_data.title;
            
        rowElement.onclick = function() {
            //set the title before loading
            session.setTitle(event_data.title);
            session.loadSessionData(event_data.session_id);
            mobilAP.showSessionDetail();
        }

        if (event_data.detail) {
            templateElements.sessions_current_detail[mobilAP.textField] = event_data.detail;
            templateElements.sessions_current_detail.style.display='block';
        } else {
            templateElements.sessions_current_detail[mobilAP.textField] = '';
            templateElements.sessions_current_detail.style.display=event_data.room ? 'block' : 'none';
        }
            
        templateElements.sessions_current_room[mobilAP.textField] = event_data.room ? event_data.room : '';
	}
};

//handler for session. the big boy
var session = {
    SESSION_RELOAD_INTERVAL: 30,
	SESSION_FLAGS_LINKS:1,
	SESSION_FLAGS_ATTENDEE_LINKS:2,
	SESSION_FLAGS_DISCUSSION:4,
	SESSION_FLAGS_EVALUATION:8,
    _loading:false,
    current_panel: null,
    active_button: '',
    session_id: '000',
    isPresenter: false,
    session_title: 'Session',
    session_abstract: '',
    session_question: null,
    session_links: [],
    session_questions: [],
    session_presenters: [],
    session_chat:[],
    session_userdata: [],
    session_flags: 0,
    last_chat: 0,
    browserBackHandler: function(view) {
        mobilAP.log('session.browserBackHandler (' + view + ')');
        switch (view)
        {
            case 'sessions_page':
            case 'sessions_question_ask':
            case 'sessions_question_response':
                return;
        }
        
        session.stopReload();
    },
    browserHandler: function(view) {
        mobilAP.log('session.browserHandler (' + view + ')');
        switch (view)
        {
            case 'sessions':
                mobilAP.getSchedule();
                break;
            case 'sessions_page':
            case 'sessions_question_ask':
            case 'sessions_question_response':
                session.startReload(session.SESSION_RELOAD_INTERVAL);
                break;
        }
    },
    processSessionData: function(xhr) {
        try {
            var session_data = eval("(" + xhr.responseText + ")");
            if (session_data.session_id) {
                session.setSessionData(session_data);
                session._loading = false;
            }
        } catch (e) {
            mobilAP.log("Error wth session data: " + xhr.responseText);
            session._loading = false;
        }
    },    
    setSessionData: function(session_data) {
        this.session_id = session_data.session_id;
        this.isPresenter = session_data.isPresenter;
        this.setTitle(session_data.session_title);
        this.setAbstract(session_data.session_abstract);
        this.setUserData(session_data.session_userdata);
        this.setLinks(session_data.session_links);
        this.setQuestions(session_data.session_questions);
        this.setPresenters(session_data.session_presenters);
        this.setChat(session_data.session_chat);
        this.setSessionFlags(session_data.session_flags);
    },
    reloadTimer: null,
    reloadInterval: null,
    startReload: function(reloadInterval)
    {
        if (session.reloadTimer && reloadInterval == session.reloadInterval) {
            return;
        }
        session.stopReload();
        session.reloadInterval = reloadInterval;
        mobilAP.log("Starting session reload timer of " + session.reloadInterval + " seconds");
        session.reloadTimer = setInterval(session.refresh, session.reloadInterval*1000);
    },
    stopReload: function()
    {
        if (!session.reloadTimer) {
            return;
        }
        mobilAP.log('Stopping session reload timer');
        clearInterval(session.reloadTimer);
        session.reloadTimer=null;
    },
    refresh: function()
    {
        session.loadSessionData(session.session_id);
    },
    
    loadSessionData: function(session_id) {
        if (this._loading) {
            return;
        }
        this._loading = true;
        if (session_id != session.session_id) {
            session.session_question_index = null;
            document.getElementById('session_title')[mobilAP.textField]='Loading...';
            document.getElementById('session_abstract')[mobilAP.textField]='Loading...';
        }
        mobilAP.loadURL(js_script + '?get=session&session_id=' + session_id, session.processSessionData);
   },
   showQuestion: function() {
        if (this.session_userdata.questions[session_question.question_id]) {
           browserController.goForward('sessions_question_response', session_question.question_text);
        } else {
           browserController.goForward('sessions_question_ask', session_question.question_text);
        }
    },

    setPanel: function(panel) {
        var active_button = panel;
        var reloadInterval = session.SESSION_RELOAD_INTERVAL;
        switch (panel)
        {
            case 'info':
            	session_evaluation.getEvaluationQuestions();
                break;
            case 'links_add':
                active_button='links';
            case 'links':
            	if (!(this.session_flags & session.SESSION_FLAGS_LINKS)) {
            		return;
            	}
                break;
            case 'question_response':
            case 'question_ask':
                active_button='questions';
            case 'questions':
                if (this.session_questions.length==0) {
                    return;
                }
                break;
            case 'evaluation':
            case 'evaluation_thanks':
                active_button='info';
            	if (!(this.session_flags & session.SESSION_FLAGS_EVALUATION)) {
            		return;
            	}
                break;
            case 'discussion_view':
                active_button='discussion';
            case 'discussion':
            	if (!(this.session_flags & session.SESSION_FLAGS_DISCUSSION)) {
            		return;
            	}
                reloadInterval = 10;
                break;
            default:
                mobilAP.log("We didn't handle setPanel for " + panel);
                break;
        }
        
        if (this.active_button) {
			removeClassName('session_' + this.active_button + '_button', 'button_active');
		}

        addClassName('session_' + active_button + '_button', 'button_active');        
        document.getElementById('session_data_stack').object.setCurrentView('session_' + panel + '_panel');
        this.current_panel = panel;
        this.active_button = active_button;
        session.startReload(reloadInterval);
        scrollTo(0,1);
    },
    setTitle: function(title) {
        this.session_title = title;
        document.getElementById('session_title')[mobilAP.textField]=this.session_title;
    },

	setTime: function(start_time, end_time)
	{
		this.session_start_time = start_time;
		this.session_end_time = end_time;		
        document.getElementById('session_time')[mobilAP.textField]= this.session_start_time.formatDate('g:i') + ' - ' + this.session_end_time.formatDate('g:i');
	},
    setRoom: function(room) {
        this.session_room = room;
        document.getElementById('session_room')[mobilAP.textField]=this.session_room;
    },
    setAbstract: function(abstract) {
        this.session_abstract = abstract;
        document.getElementById('session_abstract')[mobilAP.textField]=this.session_abstract;
    },
    setLinks: function(links) {
        this.session_links = links;
        document.getElementById('session_links_list').object.reloadData();
        document.getElementById('session_links_list').style.display=this.session_links.length>0 ? 'block' : 'none';
    },
    setSessionFlags: function(flags) {
    	this.session_flags = parseInt(flags);
    	if (this.session_flags & session.SESSION_FLAGS_LINKS) {
            removeClassName('session_links_button', 'button_disabled');
        } else {
            addClassName('session_links_button', 'button_disabled');
        }

    	if (!(this.session_flags & session.SESSION_FLAGS_ATTENDEE_LINKS) && !this.isPresenter) {
            document.getElementById('session_add_link_button').style.display = 'none';
        } else {
            document.getElementById('session_add_link_button').style.display = 'block';
        }

    	if (this.session_flags & session.SESSION_FLAGS_DISCUSSION) {
            removeClassName('session_discussion_button', 'button_disabled');
        } else {
            addClassName('session_discussion_button', 'button_disabled');
        }

    	if (this.session_flags & session.SESSION_FLAGS_EVALUATION) {
            document.getElementById('session_rate_button').style.display = 'block';
        } else {
            document.getElementById('session_rate_button').style.display = 'none';
        }
    },
    setQuestion: function(question_index) 
    {
        if (this.session_questions[question_index]) {
            this.session_question = this.session_questions[question_index];
            session_question.setQuestion(this.session_question);
        } else {
            this.session_question = null;
        }
    },
    setQuestions: function(questions) {
        this.session_questions = questions;
        document.getElementById('session_questions_list').object.reloadData();
        document.getElementById('session_questions_list').style.display=this.session_questions.length>0 ? 'block' : 'none';
        if (questions.length>0) {
            removeClassName('session_questions_button', 'button_disabled');
        } else {
            addClassName('session_questions_button', 'button_disabled');
        }
        
        if (this.session_question) {
            this.setQuestion(this.session_question.index);
        }
    },
    session_chat_last_post_id: null,
    setChat: function(chat) {
        if (chat[0] && chat[0].post_id==this.session_chat_last_post_id) {
            return;
        }
    
        this.session_chat = chat;
        this.session_chat_last_post_id = this.session_chat[0] ? this.session_chat[0].post_id : null;
        for (var i=0; i<this.session_chat.length; i++) {
            this.session_chat[i].date = new Date(this.session_chat[i].date);
        }

        
        document.getElementById('session_discussion_list').object.reloadData();
        document.getElementById('session_discussion_list').style.display = this.session_chat.length>0 ? 'block' : 'none';
        document.getElementById('session_discussion_count')[mobilAP.textField] = 'There have been ' + this.session_chat.length + ' posts to this session.';
        session_chat.updatePage();

    },

    setPresenters: function(presenters) {
        this.session_presenters = presenters;
        document.getElementById('session_presenters_list').object.reloadData();
        document.getElementById('session_presenters_list').style.display = this.session_presenters.length>0 ? 'block' : 'none';
    },
    
    post_chat: function(post_text) {
        var url = js_script + '?post=chat';
        var params='session_id=' + session.session_id+'&post_text=' + escape(post_text);
		var options = { method: 'POST', params: params}
        mobilAP.loadURL(url, session.processChat, options);
        return true;
    },
    processChat: function(xhr)
    {
        try {
            var _session_chat = eval("(" + xhr.responseText + ")");
            if (_session_chat.error_message) {
                if (_session_chat.error_code==mobilAP.ERROR_NO_USER) {
                    mobilAP.show_login_form();
                    document.getElementById('login_result')[mobilAP.textField]='You must login to post';
                    return;
                } else {
                    alert(_session_chat.error_message);
                }
            } else {
                session.setChat(_session_chat);
                document.getElementById('post_text_field').value='';
                session_chat.view_posts();
            }
        } catch (e) {
            mobilAP.log("Error loading chat: " + xhr.responseText);
        }
    },
    setUserData: function(userdata)
    {
        this.session_userdata = userdata;
    },
	show_add_link: function()
	{
		if (!mobilAP.is_loggedIn()) {
			mobilAP.show_login_form();
			document.getElementById('login_result')[mobilAP.textField]='You must login to post';
			return;
		}
		document.getElementById('link_url_field').value='http://';
		document.getElementById('link_title_field').value='';
		session.setPanel('links_add');
	},
	start_evaluation: function() {
		if (!mobilAP.is_loggedIn()) {
			mobilAP.show_login_form();
			document.getElementById('login_result')[mobilAP.textField]='You must login to post';
			return;
		}
		if (session.session_userdata.evaluation) {
			session.setPanel('evaluation_thanks');
		} else {
            session_evaluation.setQuestion(0);
			session.setPanel('evaluation');
		}
	}	
    
};

var session_links = {
    processSessionLinks: function(xhr)
    {
    	//we'll get back an error object if a post failed
        try {
            var session_links = eval("(" + xhr.responseText + ")");
            if (session_links.error_message) {
                if (session_links.error_code==mobilAP.ERROR_NO_USER) {
                    mobilAP.show_login_form();
                    document.getElementById('login_result')[mobilAP.textField]='You must login to post';
                    return;
                } else {
                    alert(session_links.error_message);
                    return;
                }
            } else {
                session.setLinks(session_links);
            }
        } catch (e) {
            mobilAP.log("Error loading links " + xhr.responseText);
        }
    },
    submit: function() {
        var link_url = document.getElementById('link_url_field').value;
        if (!link_url.match("^https?://.+")) {
            alert("Invalid url.");
            return;
        }
        var link_text = document.getElementById('link_title_field').value;
        if (link_text.length==0) {
            alert("Please include a title");
            return;
        }
        var url = js_script + '?post=link&session_id=' + session.session_id + '&link_url=' + escape(link_url)+'&link_text='+escape(link_text);
        mobilAP.loadURL(url, session_links.processSessionLinks);
        session.setPanel('links');
    },
    numberOfRows: function() {
        return session.session_links.length;
    },

    prepareRow: function(rowElement, rowIndex, templateElements) {
        addClassName(rowElement, "link_type_" + session.session_links[rowIndex].link_type);
        rowElement.url = session.session_links[rowIndex].link_url;
        templateElements.session_links_label[mobilAP.textField] = session.session_links[rowIndex].link_text;
        rowElement.onclick = function(event) {
        	//youtube links should load in same window so it loads in youtube app
            if (rowElement.url.match('^http://www.youtube.com/watch')) {
                window.location=rowElement.url;
            } else {
                window.open(rowElement.url);
            }
        };
    }
};

var session_questions = {
    numberOfRows: function() {
        return session.session_questions.length;
    },

    prepareRow: function(rowElement, rowIndex, templateElements) {
        rowElement.question_id = session.session_questions[rowIndex].question_id;
        var index = session.session_questions[rowIndex].index+1;
        templateElements.session_question_num[mobilAP.textField] = index+'.';
        templateElements.session_question_text[mobilAP.textField] = session.session_questions[rowIndex].question_list_text ? session.session_questions[rowIndex].question_list_text : session.session_questions[rowIndex].question_text;
        // Assign a click event handler for the row.
        rowElement.onclick = function(event) {
            session.setQuestion(rowIndex);
            session.showQuestion();
        };
    }
	
};

var session_question_answers = {
	
	numberOfRows: function() {
		return session_question.responses.length;
	},
	
	prepareRow: function(rowElement, rowIndex, templateElements) {
        templateElements.question_response_index[mobilAP.textField] = (rowIndex+1)+'.';
        templateElements.question_response_text_label[mobilAP.textField] = session_question.responses[rowIndex].response_text;
        templateElements.question_response_count[mobilAP.textField] = session_question.answers[session_question.responses[rowIndex].response_value];

	}
};

var session_presenters = {

    numberOfRows: function() {
        return session.session_presenters.length;
    },

    prepareRow: function(rowElement, rowIndex, templateElements) {
        rowElement.presenter=session.session_presenters[rowIndex];
        templateElements.presenter_name[mobilAP.textField]=rowElement.presenter.FirstName + ' ' + rowElement.presenter.LastName;
        templateElements.presenter_organization[mobilAP.textField]=rowElement.presenter.organization;
		rowElement.onclick = function(event) {
            directoryController.setDirectoryDetail(rowElement.presenter);
            browserController.goForward('directory_detail', rowElement.presenter.FirstName + ' ' + rowElement.presenter.LastName);
        }
        
    }
	
};

var session_evaluation = {
	
	
	 evaluation_questions: [],
     selected_responses: [],
     rows: [],
     loading: false,
     
	getEvaluationQuestions: function() 
	{
		if (this.loading || this.evaluation_questions.length>0) {
			return;
		}
        mobilAP.loadURL(js_script + '?get=evaluation_questions', session_evaluation.processQuestions);
	},
	processQuestions: function(xhr)
	{
		var evaluation_questions = [];
        try {
            evaluation_questions = eval("(" + xhr.responseText + ")");
        } catch (e) {
            evaluation_questions = [];
            mobilAP.log(xhr.responseText);
        }
        session_evaluation.setQuestions(evaluation_questions);
     	session_evaluation.loading = false;   
	},
	setQuestions: function(questions)
	{
		this.evaluation_questions = questions;
		var stack =document.getElementById('session_evaluation_stack');
		stack[mobilAP.textField] = null;
		stack.object = null;
		var stack_ops = { 'subviewsTransitions' : [] }
		var transition = { "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "push" };

		for (var i=0; i<this.evaluation_questions.length; i++) {
			var evaluation_question = this.evaluation_questions[i];
			var div = document.createElement('div');
			div.id = 'evaluation_question' + i;
			var label = document.createElement('div');
			label.className = 'evaluation_label';
			label[mobilAP.textField] = evaluation_question.question_text;
			div.appendChild(label);
			
			switch (evaluation_question.question_response_type)
			{
				case 'T':
					var textarea = document.createElement('textarea');
					textarea.id = 'evaluation_response' + i;
					textarea.className = 'evaluation_text';
					div.appendChild(textarea);
					break;
				case 'M':
					var hidden = document.createElement('input');
					hidden.type = 'hidden';
					hidden.id = 'evaluation_response' + i;
					div.appendChild(hidden);
					var list = document.createElement('ul');
					list.className = 'evaluation_response_list';
					this.rows[i] = [];
					for (var j=0; j<evaluation_question.responses.length;j++) {
						var li = document.createElement('li');
						li.className='listRowTemplate_template';
						li.questionIndex = i;
						li.responseIndex = j;
						li.responseValue = evaluation_question.responses[j].response_value;
						list.appendChild(li);
						this.rows[i].push(li);
						var label = document.createElement('div');
						label.className='label_template';
						label[mobilAP.textField] = evaluation_question.responses[j].response_text;
						li.appendChild(label);
						var arrow = document.createElement('div');
						arrow.className='listCheck_template';
						li.appendChild(arrow);

						li.onclick = function() {
							session_evaluation.selectResponse(this.questionIndex, this.responseIndex);
						};

					}
					div.appendChild(list);
					break;
			}			
			
			stack.appendChild(div);
			stack_ops.subviewsTransitions.push(transition);
		}
	
		CreateStackLayout('session_evaluation_stack', stack_ops);
		
	},
                               	
    currentQuestion: 0,
    setQuestion: function(questionIndex) 
    {
        if (questionIndex>=0 && questionIndex<this.evaluation_questions.length) {
            document.getElementById('session_evaluation_stack').object.setCurrentView('evaluation_question' + questionIndex);
            this.currentQuestion = questionIndex;
        } else {
            mobilAP.log("Invalid evaluation question: " + questionIndex);
        }
        
        document.getElementById('submit_evaluation_prev').style.display = this.currentQuestion>0 ? '' : 'none';
        document.getElementById('submit_evaluation_next').object.setText(this.currentQuestion<(this.evaluation_questions.length-1) ? 'Next' : 'Finish');
    },
    next: function() 
    {
        if (session_evaluation.currentQuestion<(session_evaluation.evaluation_questions.length-1)) {
            session_evaluation.setQuestion(session_evaluation.currentQuestion+1);
        } else {
            session_evaluation.submit();
        }
    },
    previous: function() 
    {
        if (session_evaluation.currentQuestion>0) {
            session_evaluation.setQuestion(session_evaluation.currentQuestion-1);
        }
    },
    selectResponse: function(questionIndex, rowIndex)
    {
    	
        mobilAP.log("Selecting " + rowIndex + " from question " + questionIndex);
        for (var i=0; i<this.rows[questionIndex].length; i++) {
            removeClassName(this.rows[questionIndex][i], 'row_selected');
        }

        addClassName(this.rows[questionIndex][rowIndex], 'row_selected');
        i = questionIndex;
        for (var j=0; j<this.rows[i].length; j++) {
            if (hasClassName(this.rows[i][j], 'row_selected')) {
                this.selected_responses[i]=this.rows[i][j].responseValue;
            }
        }
    },
	
    processEvaluation: function(xhr) {
        try {
            var result = eval("(" + xhr.responseText + ")");
            if (result.error_message) {
                switch (result.error_code)
                {
                    case mobilAP.ERROR_NO_USER:
                        mobilAP.show_login_form();
                        document.getElementById('login_result')[mobilAP.textField]='You must login to post';
                        return;
                    case mobilAP.ERROR_USER_ALREADY_SUBMITTED:
                        break;
                    default:
                        alert(result.error_message);
                        return;
                }
            } else {
            	//evaluation has been submitted
            	session.setUserData(result);
            }
            
            //show thanks panel, then go back to info panel in a few seconds.
            session.setPanel('evaluation_thanks');
            setTimeout(function() { session.setPanel('info'); }, 3000);
        } catch (e) {
            mobilAP.log(e);
            mobilAP.log("Error loading " + xhr.url);
            mobilAP.log(xhr.responseText);
        }    
    },
    submit:function()
    {
        //build up the url to send. 
        var url = js_script + '?post=evaluation';
        
        var params='session_id=' + session.session_id;
        
        for (var i=0; i<this.evaluation_questions.length; i++) {
        	switch (this.evaluation_questions[i].question_response_type)
        	{
        		case 'T':
			        params+='&responses[' +i+ ']='+ escape(document.getElementById('evaluation_response'+i).value);
			        break;
        		case 'M':
					if (typeof this.selected_responses[i]!='undefined') {
						params+='&responses['+i+']='+this.selected_responses[i];
					}
					break;
        	}
        }
        
        var options = { method: 'POST', params: params };   
        mobilAP.loadURL(url, session_evaluation.processEvaluation, options);
        return;
    }
};

var session_days = {
    
	numberOfRows: function() {
        //var schedule_data = mobilAP.getSchedule();
        return mobilAP.schedule_data.length;
	},
	
	prepareRow: function(rowElement, rowIndex, templateElements) {
        //var schedule_data = mobilAP.getSchedule();
        var data = mobilAP.schedule_data[rowIndex];
        rowElement.id='session_menu_' + data.index;
        
		/* 100% bug */
		if ((Math.floor(100/mobilAP.schedule_data.length) * mobilAP.schedule_data.length) == 100) {
	        rowElement.style.width=Math.floor(100/mobilAP.schedule_data.length) - 1 + '%';
		} else {
	        rowElement.style.width=Math.floor(100/mobilAP.schedule_data.length) + '%';
		}
        
        rowElement.style.borderTopWidth='';
        if (rowIndex==session_days.numberOfRows()-1) {
            rowElement.style.borderRightWidth='0';            
        }
        rowElement.day = data.index;
        
        // use long day when there's only a few days, use short day for longer mobilAPs so it all can fit on one line
        var dayFormat = mobilAP.schedule_data.length<4 ? 'l' : 'D';
        templateElements.session_menu_label[mobilAP.textField]=data.date.formatDate(dayFormat);

		rowElement.onclick = function(event) {
            programSchedule.setDay(rowElement.day);
		};
	}
};

var session_chat = {
    pages: 1,
    current_page: 0,
    max_rows: 6,
    updatePage: function() {
        document.getElementById('session_discussion_prev').style.display = this.current_page>0 ? 'block' : 'none';
        document.getElementById('session_discussion_next').style.display = (this.current_page+1)<this.pages ? 'block' : 'none';
        document.getElementById('session_discussion_paging').style.display = this.pages>0 ? 'block' : 'none';
        document.getElementById('session_discussion_page_count')[mobilAP.textField] = this.pages;
        document.getElementById('session_discussion_page')[mobilAP.textField] = this.current_page+1;
    },
    setPage:function(page) {
        var change = page != this.current_page;
        if (page>=0 && page<this.pages) {
            this.current_page = page;
            mobilAP.log("current page:" + this.current_page);
            if (change) {
                document.getElementById('session_discussion_list').object.reloadData();
            }
        }
        this.updatePage();
    },
    nextPage:function() {
        mobilAP.log("next page");
        if (session_chat.current_page<session_chat.pages) {
            session_chat.setPage(session_chat.current_page+1);
        }
    },
    prevPage:function() {
        mobilAP.log("previous page");
        if (session_chat.current_page>0) {
            session_chat.setPage(session_chat.current_page-1);
        }
        mobilAP.log("current page:" + session_chat.current_page);
        document.getElementById('session_discussion_list').object.reloadData();
    },
	numberOfRows: function() {
        var total = session.session_chat.length;
        this.pages = Math.ceil(total / this.max_rows);
        var last_page = total % this.max_rows;
        if ( (this.current_page+1) < this.pages ) {
        
            return total > this.max_rows ? this.max_rows : total;
        } else if (this.pages>0) {
            return (total % this.max_rows) ? (total % this.max_rows) : this.max_rows;
        } else {
            return 0;
        }
	},
	prepareRow: function(rowElement, rowIndex, templateElements) {
        var chat_index = rowIndex + (session_chat.current_page * session_chat.max_rows);
        templateElements.post_timestamp[mobilAP.textField] = session.session_chat[chat_index].date.formatDate("m/d h:i:s");
        templateElements.post_user[mobilAP.textField] = session.session_chat[chat_index].post_name;
        templateElements.post_text[mobilAP.textField] = session.session_chat[chat_index].post_text;
        rowElement.onclick = function() {
            directoryController.getDirectoryDetail(session.session_chat[chat_index].post_user);
            browserController.goForward('directory_detail', session.session_chat[chat_index].post_name);
        }
	},
    view_posts: function() {
        session_chat.setPage(0);
        session.setPanel('discussion_view');
    },
    submit:function() {
        var post_text = document.getElementById('post_text_field').value;
		if (post_text.length==0) {
			return;
		}
		session.post_chat(post_text);
	}	
};

//this is the active question
var session_question = {
    rowElements: [],
    setQuestion: function(question) {
        this.session_id = question.session_id;
        this.question_id = question.question_id;
        this.setResponses(question.responses);
        this.setAnswers(question.answers);
        this.setQuestionText(question.question_text);
        this.question_minchoices = question.question_minchoices;
        this.question_maxchoices = question.question_maxchoices;
        this.chart_type = question.chart_type;
        this.response_type = question.response_type;
        this.updateResults();
        return;
    },
    answers: [],
	responses: [],
    selected_responses: [],
	numberOfRows: function() {
		return this.responses.length;
	},
	prepareRow: function(rowElement, rowIndex, templateElements) {
        this.rowElements[rowIndex] = rowElement;
        rowElement.response_value = this.responses[rowIndex].response_value;
        templateElements.question_response_label[mobilAP.textField] = this.responses[rowIndex].response_text;
		rowElement.onclick = function(event) {
            session_question.selectResponse(rowIndex);
		};
	},
    setQuestionText: function(text)
    {
        this.question_text = text;
        document.getElementById('question_text')[mobilAP.textField] = this.question_text;
        document.getElementById('question_response_text')[mobilAP.textField] = this.question_text;
    },
    setAnswers: function(answers)
    {
        this.answers = answers;
        document.getElementById('question_answers').object.reloadData();
        document.getElementById('question_response_total')[mobilAP.textField]=this.answers.total+ " responses";
    },
    setResponses: function(responses)
    {
        this.responses = responses;
        this.rowElements = [];
        document.getElementById('question_responses').object.reloadData();
    },
    selectResponse: function(rowIndex) {
        if (this.question_maxchoices>1) {
            toggleClassName(this.rowElements[rowIndex], 'row_selected');
        } else {
            for (var i=0; i<this.rowElements.length; i++) {
                if (i==rowIndex) {
                    addClassName(this.rowElements[i], 'row_selected');
                } else {
                    removeClassName(this.rowElements[i], 'row_selected');
                }
            }
        }

        this.selected_responses=[];
        for (var i=0; i<this.rowElements.length; i++) {

            if (hasClassName(this.rowElements[i], 'row_selected')) {
                this.selected_responses.push(this.responses[i].response_value);
            }
        }        
    },
    submit: function() {
        if (!mobilAP.is_loggedIn()) {
            mobilAP.show_login_form();
            document.getElementById('login_result')[mobilAP.textField]='You must login to post';
            return;
        }       
        
        //check to make sure right number of responses selected
        if (session_question.selected_responses.length < session_question.question_minchoices ||session_question.selected_responses.length>session_question.question_maxchoices) {
            if (session_question.question_minchoices==0) {
                alert("Please select up to " + session_question.question_maxchoices + " choices.");
            } else {
                alert("Please select between " + session_question.question_minchoices + " and " + session_question.question_maxchoices + " choices.");
            }
            return;
        }

        //build the url
        var url = js_script + '?post=question&question_id=' + session_question.question_id;
        
        for (var i=0; i<session_question.selected_responses.length; i++) {
            url+='&response[]='+session_question.selected_responses[i];
        }
        
        mobilAP.loadURL(url, session_question.processSubmitQuestion);
        return;
    },
    processSubmitQuestion: function(xhr) {
        try {
            var result = eval("(" + xhr.responseText + ")");
            if (result.error_message) {
                switch (result.error_code)
                {
                    case mobilAP.ERROR_NO_USER:
                        mobilAP.show_login_form();
                        document.getElementById('login_result')[mobilAP.textField]='You must login to post';
                        return;
                    case mobilAP.ERROR_USER_ALREADY_SUBMITTED:
                        alert(result.error_message);
                        break;
                    default:
                        alert(result.error_message);
                        return;
                }
            } else {
                session.refresh();
                session_question.setAnswers(result.answers);
            } 
            
            session_question.view_results();

        } catch(e) {
            mobilAP.log(e);
            mobilAP.log(xhr.responseText);
            alert("Error saving answer");
            return;
        }
    },
    updateResults: function() {
        var img = document.getElementById('question_response_chart');

        //create the chart
        if (!img) {
            var img = document.createElement('img');
            img.id = 'question_response_chart';
            document.getElementById('question_response_box').appendChild(img);
        }

        if (this.answers.total==0) {
            img.style.display='none';
            return;
        }
        
        img.style.display='';
        var src = this.getChartURL();
        if (img.src != src) {
            img.src=src;
        }
    },
    
    //build the chart based on the answers
    getChartURL: function() {
        
        var data = [];
        var labels = [];
        var max_data = 0;
        var add_zero = this.chart_type != 'p';
        var max_label_length=15;
        
        //go through the responses, for pie charts, don't include responses with zero answers
        //max_data value represents the highest value and is used to scale the bar charts
        for (var i=0; i<this.responses.length; i++) {
            if (this.answers[this.responses[i].response_value]>0 || add_zero) {
                data.push(this.answers[this.responses[i].response_value]);
                if (this.answers[this.responses[i].response_value] > max_data) {
                    max_data = this.answers[this.responses[i].response_value];
                }
                
                if (this.responses[i].response_text.length>max_label_length) {
                    var label=i+1;
                } else {
					var label = escape(this.responses[i].response_text);
				}
                labels.push(label);
            }
        }

		// base url with type, size and background
        var src = 'http://chart.apis.google.com/chart?cht=' + this.chart_type + '&chf=bg,s,00000000';

		// add the data using text encoding
		src +='&chd=t:'+data.join(",");
        
        //make no more than 10 x-axis legends, use the next whole factor for the max
        var step = 0;
        do {
            step +=2;
            var even_max = max_data % step ? (max_data+(max_data%step)) : max_data;
        } while (even_max / step > 10);
        
        switch (this.chart_type)
        {
            case 'p':
                src +='&chs=280x140';
                src +='&chl=' + labels.join("|");
                break;
            
            case 'bhs':
                src +='&chs=280x' + ((this.responses.length*33)+20);
                src +='&chxt=x,y';
                src +='&chds=0,' + even_max;
                labels.reverse();
                var range=[];
                for (i=0; i<=even_max; i+=step) {
                    range.push(i);
                }               
                                                
                src +='&chxl=0:|' + range.join("|") + '|1:|' + labels.join("|");
                break;
        }
        
        return src;
    },

    view_results: function() {
		session_question.updateResults();
        session.startReload(10);
		browserController.setCurrentView('sessions_question_response');
    }
    
};

var welcomeController = {
    browserHandler: function() {
        mobilAP.getAttendeeSummary();
    }
}

function goCurrentSession()
{
    if (programSchedule.currentSessions.length==1) {
    	if (programSchedule.currentSessions[0].session_id) {
			session.loadSessionData(programSchedule.currentSessions[0].session_id);
			mobilAP.showSessionDetail();        
 		} else if (programSchedule.currentSessions[0].session_group_id) {
			session_group.setTitle(programSchedule.currentSessions[0].title);
			session_group.loadSessionGroupData(programSchedule.currentSessions[0].session_group_id);
			browserController.goForward('session_group', programSchedule.currentSessions[0].title);
		}
   } else {
        browserController.goForward('sessions_current', 'Current Sessions');
    }
}

var homeController =
{
    browserHandler: function() {
        programSchedule.setCurrentSession();
    }
}

var announcement_controller = {
    new_announcements: 0,
    announcements: [],
    announcement: null,
    
    setAnnouncements: function(announcements) {
        this.announcements = announcements;
        this.new_announcements = 0;
        for(var i=0; i<announcements.length; i++) {
            if (!announcements[i].read) {
                this.new_announcements++;                
            }
        }
        
        browserController.reload();
        document.getElementById('announcements_list').object.reloadData();
    },

    setAnnouncement: function(announcement) {
        this.announcement = announcement;
        document.getElementById('announcement_title')[mobilAP.textField] = announcement.announcement_title;
        var date = new Date(announcement.announcement_timestamp*1000);
        document.getElementById('announcement_timestamp')[mobilAP.textField] = "Posted " + date.formatDate('m/d g:ia');
        document.getElementById('announcement_text')[mobilAP.textField] = announcement.announcement_text;
        mobilAP.loadURL(js_script + '?post=readAnnouncement&announcement_id=' + announcement.announcement_id, announcement_controller.getAnnouncements);
        return;
    },
    
	
    getAnnouncements: function() {
        mobilAP.loadURL(js_script + '?get=announcements', announcement_controller.processAnnouncements);
    },

    processAnnouncements: function(xhr) {
        try {
            var announcements = eval("(" + xhr.responseText + ")");
            
        } catch (e) {
            mobilAP.log(e);
            mobilAP.log("Error loading " + xhr.url);
            mobilAP.log(xhr.responseText);
            var announcements = [];
        }
        
        announcement_controller.setAnnouncements(announcements);
    },
	numberOfRows: function() {
		return this.announcements.length;
	},
	
	prepareRow: function(rowElement, rowIndex, templateElements) {
        var announcement = this.announcements[rowIndex];
        templateElements.announcement_list_title[mobilAP.textField] = announcement.announcement_title;
        if (!announcement.read) {
            rowElement.className += " announcement_new";
        }
		rowElement.onclick = function(event) {
            announcement_controller.setAnnouncement(announcement);
            browserController.goForward('announcement_detail', announcement.announcement_title);
        };
	}
};

var browserController = {
    goForward: function(toView, title) {        
        mobilAP.hide_login_form();
        var browser = document.getElementById('browser').object;
        browser.goForward(toView, title, this.browserBackHandler);
        browserController.browserHandler(toView);
    },
	setCurrentView: function(toView)
	{
		document.getElementById('stackLayout').object.setCurrentView(toView);
	},
    _sections: [ 
        { tag:'home', name:'mobilAP', scroll:true, home:false, content_private: false, controller: homeController},
        { tag:'welcome', name:'Welcome', scroll:true, home:true, content_private: true, controller: welcomeController},
        { tag:'sessions', name:'Sessions', scroll:true, home:true, content_private: true,controller: session},
        { tag:'session_group', name:'Session Group', scroll:true, home:false, content_private: true,controller: session_group},
        { tag:'current_session', name:'Current Session', scroll: true, home:false, content_private: true,nextController: goCurrentSession},
        { tag:'directory', name: 'Attendee Directory', scroll:false, home: !mobilAP.SHOW_AD_LETTERS, content_private: true, controller: directoryController},
        { tag:'directory_letters', name: 'Attendee Directory', scroll:true, home: mobilAP.SHOW_AD_LETTERS, content_private: true, controller: directoryController},
        { tag:'announcements', name: 'Announcements', scroll:true, home:true, content_private: true,controller: announcement_controller},
        { tag:'announcement_detail', name: 'Detail', scroll:true, content_private: true,home:false},
        { tag:'generic_list', name: 'List', scroll:true, content_private: true,home:false},
        { tag:'generic_detail', name: 'Detail', scroll:true, content_private: true,home:false}
    ],
    reload: function() {        
        this._homeSections=[];
        document.getElementById('homeList').object.reloadData();
        document.getElementById('sessions_current_list').object.reloadData();
    },
    _homeSections:[],
     getSections:function() {
        if (this._homeSections.length) {
            return this._homeSections;
        }

        var sections = [];
        for (var i=0; i<this._sections.length; i++) {
        	switch (this._sections[i].tag)
        	{
        		case 'current_session':
					this._sections[i].home = programSchedule.currentSessions.length>0;
					break;
				case 'directory_letters':
					this._sections[i].home = mobilAP.SHOW_ATTENDEE_DIRECTORY && mobilAP.SHOW_AD_LETTERS;
					break;
				case 'directory':
					this._sections[i].home = mobilAP.SHOW_ATTENDEE_DIRECTORY && !mobilAP.SHOW_AD_LETTERS;
					break;
				case 'announcements':
					var name = 'Announcements';
					if (announcement_controller.new_announcements>0) {
						name += ' (' + announcement_controller.new_announcements + ' new)';
					}
					this._sections[i].name=name;
					break;
            }
            
            if (this._sections[i].home) {
                sections.push(this._sections[i]);
            }
        }
        
        this._homeSections = sections;
        document.getElementById('logo').style.display = this._homeSections.length > 5 ? 'none' : 'block';
        return sections;
    },
    
    getSectionByTag:function(tag)
    {
        for (var idx in this._sections) {
            var section = this._sections[idx];
            if (tag.match('^' + section.tag)) {
               return section;
            }
        }
                
        mobilAP.log("getSectionByTag: Couldn't find section: " + tag);
    },
    browserBackHandler: function()
    {
        mobilAP.hide_login_form();
        var browser = document.getElementById('browser').object;

        // we need to use timers until a valid call back is made once the selection has been changed
        var startView = browser.getCurrentView().id;
        var changed = function(prevView) {
            var view = browser.getCurrentView().id;        
            if (startView==view) {
                setTimeout(changed, 20, prevView);
                return;
            }

            var section = browserController.getSectionByTag(prevView);
            if (section && section.controller && section.controller.browserBackHandler) {
                section.controller.browserBackHandler(view);
            }

            browserController.browserHandler(view);
        }
        
        changed(startView);
        return;        
    },

    browserHandler: function(toView)
    {
        var browser = document.getElementById('browser').object;
        var view = browser.getCurrentView();
        var section = this.getSectionByTag(view.id);
        if (section) {

            if (section.scroll) {
                scrollTo(0,1);
            }
            
            if (section.controller) {
                if (section.controller.browserHandler) {
                    section.controller.browserHandler(view.id);
                }
            }
        }
    },
    
    numberOfRows: function() {
        return this.getSections().length;
    },
    
    prepareRow: function(rowElement, rowIndex, templateElements) {
        var sections = this.getSections();
        templateElements.listTitle[mobilAP.textField] = sections[rowIndex].name;
        
        rowElement.onclick = function() {
            var section = sections[rowIndex];
        	if (section.content_private && mobilAP.CONTENT_PRIVATE && !mobilAP.is_loggedIn()) {
				mobilAP.show_login_form();
				document.getElementById('login_result')[mobilAP.textField]='You must login to view this content';
				return;        	
        	}
        
            if (section.nextController) {
                section.nextController();
            } else {
                browserController.goForward(section.tag, section.name);
            }
        };
    }
};


function back_to_questions(event)
{
    document.getElementById('browser').object.goBack();
}
    
var genericListController = {
    type: 'url',
    list_items: [],

    setListItems: function(list, type) {
        this.list_items = list;
        this.type = type;
        document.getElementById('generic_list_list').object.reloadData();
    },
    
	numberOfRows: function() {
		return this.list_items.length;
	},

	setDetail: function(detail_data) {
        this.detail=detail_data;
        this.updateDetail();
    },
    updateDetail: function()
    {
    	var item = this.detail;
        document.getElementById('detail_name')[mobilAP.textField] = item.label
        if (item.text) {
			document.getElementById('detail_text').style.display='block';
            document.getElementById('detail_text')[mobilAP.textField] = item.text;
        } else {
			document.getElementById('detail_text').style.display='none';
        }

        if (item.url) {
			document.getElementById('detail_link').style.display='block';
			document.getElementById('detail_link')[mobilAP.textField] = item.url;
			if (item.new_window) {
				document.getElementById('detail_link').onclick = function() { window.open(item.url) };
			} else {
				document.getElementById('detail_link').onclick = function() { window.location=item.url };
			}
        } else {
			document.getElementById('detail_link').style.display='none';
		}

        if (item.phone) {
			document.getElementById('detail_phone').style.display='block';
			document.getElementById('detail_phone')[mobilAP.textField] = item.phone;
			if (mobilAP.isIPhone) {
				document.getElementById('detail_phone').onclick = function() { window.location= "tel:" + item.phone; }
			}
		} else {
			document.getElementById('detail_phone').style.display='none';
		}

        if (item.address) {
			document.getElementById('detail_address').style.display='block';
			document.getElementById('detail_address')[mobilAP.textField] = item.address;
			document.getElementById('detail_address').onclick = function() { window.location="http://maps.google.com/maps?q=" + escape(item.address); }
		} else {
			document.getElementById('detail_address').style.display='none';
		}
    },
	
	prepareRow: function(rowElement, rowIndex, templateElements) {
        templateElements.genericList_label[mobilAP.textField] = this.list_items[rowIndex].label;
        rowElement.data = this.list_items[rowIndex];
        var list_type = this.type;
		rowElement.onclick = function(event) {
			//for maps app to work you have to load in same window, otherwise be nice and load in new window 
            switch (list_type)
            {
                case 'url':
                    if (rowElement.data.url) {
                        if (rowElement.data.new_window) {
                            window.open(rowElement.data.url);
                        } else {
                            window.location=rowElement.data.url;
                        }
                    } else if (rowElement.data.view) {
                        browserController.goForward(rowElement.data.view, rowElement.data.label);
                    }
                    break;
                case 'detail':
                    genericListController.setDetail(rowElement.data);
                    browserController.goForward('generic_detail', rowElement.data.label);
                    break;
            }
		};
	}
};



// This object implements the dataSource methods for the list.
var directory_letters = {
	
	// Sample data for the content of the list. 
	// Your application may also fetch this data remotely via XMLHttpRequest.
	_rowData: ["Item 1", "Item 2", "Item 3"],
	
	// The List calls this method to find out how many rows should be in the list.
	numberOfRows: function() {
		return this._rowData.length;
	},
	
	// The List calls this method once for every row.
	prepareRow: function(rowElement, rowIndex, templateElements) {
		// templateElements contains references to all elements that have an id in the template row.
		// Ex: set the value of an element with id="label".
		if (templateElements.label) {
			templateElements.label.innerText = this._rowData[rowIndex];
		}

		// Assign a click event handler for the row.
		rowElement.onclick = function(event) {
			// Do something interesting
			alert("Row "+rowIndex);
		};
	}
};
