/*
handles app wide methods, state and configuration
*/
base_url = '../mobilAP/';
mobilAP = {};
MobilAP = {
    getElementById: function(id) {
        if (typeof id=='string') {
            return document.getElementById(id);
        } else if(typeof id=='object' && ('nodeType' in id)) {
            return id;
        } else {
            return false;
        }
    },
    hasClassName: function(element, className) {
        if (!(element = MobilAP.getElementById(element))) throw "Element " + element + " not found";
        var elementClassName = element.className;
        return (elementClassName.length > 0 && (elementClassName == className ||
          new RegExp("(^|\\s)" + className + "(\\s|$)").test(elementClassName)));
    },
    addClassName: function(element, className) {
        if (!(element = MobilAP.getElementById(element))) throw "Element " + element + " not found";
        if (!MobilAP.hasClassName(element, className))
          element.className += (element.className ? ' ' : '') + className;
        return element;
    },
    removeClassName: function(element, className) {
        if (!(element = MobilAP.getElementById(element))) throw "Element " + element + " not found";
        element.className = element.className.replace(
          new RegExp("(^|\\s+)" + className + "(\\s+|$)"), ' ').strip();
        return element;
    },
    setClassName: function(element, className, mode) {
        MobilAP[mode ? 'addClassName' : 'removeClassName'](element, className);
    },
    toggleClassName: function(element, className) {
        if (!(element = MobilAP.getElementById(element))) throw "Element " + element + " not found";
        if (MobilAP.hasClassName(element, className))
            MobilAP.removeClassName(element, className);
        else
            MobilAP.addClassName(element, className);
    },
    createNumericArray: function(start, end, step) {
        if (typeof step=='undefined') {
            step=1;
        }
        var length = Math.ceil(((end - start) + 1) / step);
        
        var array = new Array();
        for (var i=0; i<length; i++) {
            array[i]=(start+i)*step;
        }
        return array;
    },
 	createSelectBox: function(values, labels, selectedValue) {
		var select = document.createElement('select');
		if (typeof labels=='undefined') {
			labels = values;
		}
		for (var i=0; i<values.length; i++) {
			try {
				var option = new Option(labels[i], values[i], values[i]==selectedValue);
				select.options[select.options.length] = option;
			} catch(e) {}
		}
		return select;
	},
    setupParts: function(partSpecs) {
        var partsToGetFinishLoading = [];
        for (var id in partSpecs) {        
            var specDict = partSpecs[id];
            var object = dashcode.setupPart(id,specDict.creationFunction,specDict.view,specDict);
            
            if (object && object.finishLoading) {
                partsToGetFinishLoading.push(object);            
            }
        }

        // Call finishedLoading callbacks.
        for (var i=0; i<partsToGetFinishLoading.length; i++) {
            partsToGetFinishLoading[i].finishLoading();
        }    
    },
    loadContent: function(elementOrId, url, options) {
        var element = MobilAP.getElementById(elementOrId);
        options = typeof options == 'object' ? options : {};
        if (typeof options.method=='undefined') {
            options.method = 'get';
        }
        if (!element) {
            throw ("Cannot find element " + elementOrId);
        }

        var request = XHR[options.method](url, options.parameters);
        request.addMethods(function(data) {
            element.innerHTML = data;
            if ('function' == typeof options.callback) {
                options.callback();
            }
        });
    },
    saveConfigs: function(_params, callback)
    {
        var params = {
            post: 'save'
        }
        
        for (var type in _params) {
        	for (var param in _params[type]) {
				params['config[' + type + '][' + param +']'] = _params[type][param];
            }
        }

        var request = XHR.post(base_url + 'config.php', params);
        request.addMethods(function(json) {
            dashcode.getDataSource('config').queryUpdated();
            mobilAP._processXHR(json, callback);
        });
    },
	DataSourceStub: {
		numberOfRows: function() {
			return 0;
		}
	},
	BaseClass: Class.create(DC.Bindable, {
        SESSION_SINGLE_ID:1,
		ERROR_NO_USER: -1,
        ERROR_REQUIRES_PASSWORD: -4,
        CREATE_NEW_USER: -6,
        UNAUTHORIZED: -7,
		LOGGING: true,
		log: function(msg) {
			if (!this.LOGGING) return;
			try { console.log(msg); } catch (e) {}		
		},
		getConfig: function(config_var) {
            try {
                return dashcode.getDataSource('config').content().valueForKey(config_var);
            } catch (e) {
                return null;
            }
		},
		isError: function(obj) {
			if ('object' == typeof obj) {
				if ('string' == typeof obj.error_message) {
					return true;
				}
			}
			
			return false;
		},
        get: function(url, params, callback) {
            var request = XHR.get(url, params);
            var self = this;
            request.addMethods(function(xhr) {
                self._processXHR(xhr, callback);
            });
        },
        post: function(url, params, callback) {
            var request = XHR.post(url, params);
            var self = this;
            request.addMethods(function(xhr) {
                self._processXHR(xhr, callback);
            });
        },
        _processXHR: function(json,callback) {
            try {
                if (json.error_message) {
                    json =  new MobilAP.Error(json.error_message, json.error_code, json.error_userInfo);
                }
                
                if ('function' == typeof callback) {
                    callback(json);
                }
                
                return json;
            } catch (e) {
                return new MobilAP.Error('There was an server error with the request', -1, e);
            }
        },
		getUser: function(userID) {
			var users = dashcode.getDataSource('users').content();
			try {
				for (var i=0; i< users.length; i++) {
					if (users[i].userID==userID) {
						return new MobilAP.User(users[i]);
					}
				}
			} catch (e) {
			}
			return false;
		},
        uploadFile: function(form, params, callback)
        {
            // Create the iframe...
            var iframe = document.createElement("iframe");
            iframe.id="upload_iframe";
            iframe.name="upload_iframe";
            // Add to document...
            form.parentNode.appendChild(iframe);
            window.frames['upload_iframe'].name="upload_iframe";
            iframeId = document.getElementById("upload_iframe");
            var inputs = [];
            for (var param in params) {
                var input = document.createElement('input');
                input.type='hidden';
                input.name=param;
                input.value=params[param];
                inputs.push(input);
                form.appendChild(input);
            }
            
            var self = this;
            // Add event...
            var eventHandler = function()  {
                if (iframeId.detachEvent) {
                    iframeId.detachEvent("onload", eventHandler);
                } else {
                    iframeId.removeEventListener("load", eventHandler, false);
                }
                
                for (var i=0; i< inputs.length;i++) {
                    form.removeChild(inputs[i]);
                }

                // Message from server...
                var content;

                if (iframeId.contentDocument) {
                    content = iframeId.contentDocument.body.innerHTML;
                } else if (iframeId.contentWindow) {
                    content = iframeId.contentWindow.document.body.innerHTML;
                } else if (iframeId.document) {
                    content = iframeId.document.body.innerHTML;
                }
                
                try { 
                    content = eval('('+content+')');
                    if ('object'==typeof content && content.error_message) {
                        content = new MobilAP.Error(content.error_message, content.error_code, content.error_userInfo);
                    }
                } catch(e) {
                    content = new MobilAP.Error("Error processing upload: " +e);
                }

                if (typeof callback=='function') {
                    callback(content);
                }

                // Del the iframe...
                setTimeout(function() { iframeId.parentNode.removeChild(iframeId)}, 250);
            }
            
            if (iframeId.addEventListener) {
                iframeId.addEventListener("load", eventHandler, true);
            }

            if (iframeId.attachEvent) {
                iframeId.attachEvent("onload", eventHandler);
            }

            // Set properties of form...
            form.target='upload_iframe';
            // Submit the form...
            form.submit();
        },

		constructor: function(parameters) {
			this.base(parameters);
		}
	}),
	Error: Class.create({
		constructor: function(error_message, error_code, error_userInfo) {
			this.error_message = error_message;
			this.error_code = error_code;
			this.error_userInfo = error_userInfo;
		},
		isError: function() {
			return true;
		}
	})
}

MobilAP.Controller = Class.create(MobilAP.BaseClass, {
    reloadData: function() {
        return;
    },
    setReloadTimer: function(reloadInterval) {
        if (this.reloadTimer && reloadInterval == this.reloadInterval) {
            return;
        }
        this.stopReloadTimer();
        this.reloadInterval = reloadInterval;
        this.reloadTimer = setInterval(this.reloadData.bind(this), this.reloadInterval*1000);
    },
    stopReloadTimer: function() {
        if (!this.reloadTimer) {
            return;
        }
        clearInterval(this.reloadTimer);
        this.reloadTimer=null;
    },
    constructor: function(parameters) {
        this.base(parameters);
        for (var obj in parameters) {
            // map object and function parameters
            if (typeof parameters[obj] in {'object':1,'function':1}) {
                this[obj] = parameters[obj];
            } else {
                this.log(obj + " is of type " + (typeof parameters[obj]));
            }
        }
    }
});

MobilAP.SerialController = Class.create(MobilAP.Controller, {
    serials: false,
	setSerial: function(key, value) 
	{
		this.serials[key] = value;
	},
	serialsUpdated: function(change,keyPath) {
        if (this.serials) {
            var new_serials = change.newValue;
            for (var key in new_serials) {
                if (new_serials[key] != this.serials[key] && !key.match(/__/)) {
                    var re;
                    switch (key)
                    {
                        case 'config':
                        case 'sessions':
                        case 'announcements':
                        case 'evaluation':
                        case 'schedule':
                        case 'users':
                        case 'user':
                            dashcode.getDataSource(key).queryUpdated();
                            break;
                        default:
                            if (re = key.match(/session_(\d+)/)) {
                            	if (mobilAP.sessionController.session_id == re[1]) {
                            		mobilAP.sessionController.reloadData();
								}
                            } else {
                                this.log("unhandled update for key " + key);
                            }
                    }
                
                }
            }
        }
        this.serials = change.newValue;
    },
    reloadData: function() {
        
        dashcode.getDataSource('serials').queryUpdated();
    },
    constructor: function(parameters)
    {
        this.base(parameters);
        
        dashcode.getDataSource('serials').addObserverForKeyPath(this, this.serialsUpdated, "content");
    }

});

MobilAP.ApplicationController = Class.create(MobilAP.Controller, {
    viewControllers: {},
    addViewController: function(view_id, controller) {
        if (!(view_id in this.viewControllers)) {
            this.viewControllers[view_id] = [];
        }

        this.viewControllers[view_id].push(controller);
    },
    viewDidLoad: function(toView) {
		if (toView in this.viewControllers) {
			for (var i=0; i<this.viewControllers[toView].length;i++) {
				try {
					this.viewControllers[toView][i].viewDidLoad(toView);
				} catch(e) {
				}
			}
		}
    },
    viewDidUnload: function(toView) {
		if (toView in this.viewControllers) {
			for (var i=0; i<this.viewControllers[toView].length;i++) {
				try {
					this.viewControllers[toView][i].viewDidUnload(toView);
				} catch(e) {
				}
			}
		}
    },
    loadView: function(toView, title) {
    	this.viewDidLoad(toView);
    },
    configUpdated: function(change,keyPath) {
        document.title = this.getConfig('SITE_TITLE') || 'mobilAP';
        dashcode.getDataSource('homeData').queryUpdated();
    },
    userUpdated: function(change,keyPath) {
        this.user = new MobilAP.User(change.newValue);
        MobilAP.setClassName(document.getElementsByTagName('body')[0], 'adminmode', this.user.isAdmin());
    },
    isLoggedIn: function() {
        return this.user.isLoggedIn();
    },
    isAdmin: function() {
        return this.user.isAdmin();
    },
    constructor: function(parameters)
    {
        this.user = new MobilAP.User();
        this.base(parameters);
        
        //setup KVO
//        dashcode.getDataSource('schedule').addObserverForKeyPath(this, this.scheduleUpdated, "content");
        dashcode.getDataSource('config').addObserverForKeyPath(this, this.configUpdated, "content");
        dashcode.getDataSource('user').addObserverForKeyPath(this, this.userUpdated, "content");
    }
});

MobilAP.LoginController = Class.create(MobilAP.Controller, {
    in_progress: function(bool) {
    },
    logout: function() {
        this.in_progress(true);
        this.post(base_url + 'logout.php',{},this.logoutHandler.bind(this));
    },
    login: function(userID, password) {
        var parameters = {
            login_userID: userID,
            login_pword: password
        }

        if (parameters.login_userID.length==0 || (this.getConfig('USE_PASSWORDS') && parameters.login_pword.length==0))  {
            return false;
        }

        this.in_progress(true);
        this.post(base_url + 'login.php', parameters, this.loginHandler.bind(this));
    },
    logoutHandler: function(json) {
        this.in_progress(false);
        try {
            if (json.error_message) {
                return new MobilAP.Error(json.error_message, json.error_code, json.error_userInfo);
            } else {
                dashcode.getDataSource('user').queryUpdated();
            }

        } catch (e) {
            alert("There was an error logging out: " + e);
            return;
        }
        
    },
    loginHandler: function(json) {
        this.in_progress(false);
        try {
            if (json.error_message) {
                return new MobilAP.Error(json.error_message, json.error_code, json.error_userInfo);
            } else {
                dashcode.getDataSource('user').queryUpdated();
            }

        } catch (e) {
            alert("There was an error logging in: " + e);
            return;
        }
    }
});
    
MobilAP.PartController = Class.create(MobilAP.Controller, {
    constructor: function(part_id, parameters) {
        this.base(parameters);
        try {
            if ('undefined'!=typeof document.getElementById(part_id).object) {
                this.id = part_id;
                this.element = document.getElementById(part_id)
                this.object = this.element.object;
            }
        } catch(e) {
            throw('Unable to find object for ' + part_id);
            return;

        }
    }
});

MobilAP.DataSourceController = Class.create(MobilAP.Controller, {
    content: function() {
        return this._content;
    },
    dataSourceUpdated: function(change, keyPath) {
        this._content = this._dataSource.content();
    },
    constructor: function(dataSourceID, params) {
        this.base(params);
        var dataSource = dashcode.getDataSource(dataSourceID);
        if (dataSource) {
            this.id = dataSourceID;
            this._dataSource = dataSource;
            this._content = this._dataSource.content();
            this._dataSource.addObserverForKeyPath(this, this.dataSourceUpdated, "content");
        }
    }
});

MobilAP.ListController = Class.create(MobilAP.PartController, {
    editMode: false,
    toggleEditMode: function() {
        this.setEditMode(!this.editMode);
    },
    setEditMode: function(editMode) {
        this.editMode = editMode ? true : false;
        MobilAP[this.editMode ? 'addClassName' : 'removeClassName'](this.id, 'mobilAP_editmode');
        if (this.editButton) {
            this.editButton.setText(editMode ? 'Done' : 'Edit');
        }
        this.clearSelection();
    },
    reloadData: function() {
        this.object.reloadData();
    },
    setLoop: function(loop) {
        this.loop = loop ? true : false;
    },
    content: function() {
        return this.object.__content;
    },
    setSelectionIndexes: function(selectionIndexes) {
        return this.object.setSelectionIndexes(selectionIndexes);
    },
    loop: false,
    setSelectedObjectIndex: function(index) {
        this.object.setSelectionIndexes([index]);
    },
    clearSelection: function() {
        this.object.clearSelection();
    },
    selectedObjectIndex: function() {
        var selectionIndexes = this.object.selectionIndexes();
        if (selectionIndexes && (selectionIndexes.length>0)){
            return selectionIndexes[0];
        } else {
            return -1;
        }        
    },
    selectedObjects: function() {
        return this.object.selectedObjects();
    },
    selectedObject: function() {
        var selectedObjects = this.object.selectedObjects();
        if (selectedObjects && (1 == selectedObjects.length)){
            return selectedObjects[0];
        } else {
            if (selectedObjects.length==0) {
                return false;
            } 
        }
    },
    numberOfRows: function() {
        return this.object.rows.length;
    },
    selectNext: function() {
        if (this.selectedObjectIndex()<(this.numberOfRows()-1)) {
            this.setSelectedObjectIndex(this.selectedObjectIndex()+1);
        } else if (this.loop) {
            this.setSelectedObjectIndex(0);
        }
    },
    selectPrevious: function() {
        if (this.selectedObjectIndex()>0) {
            this.setSelectedObjectIndex(this.selectedObjectIndex()-1);
        } else if (this.loop) {
            this.setSelectedObjectIndex(this.numberOfRows()-1);
        }
    
    },
    setSelectedObject: function(obj) {
        this.selectedObject = obj;
    },
    rowSelected: function(change, keyPath) {
        if (this.editMode) {
            this.clearSelection();
            return;
        }
        var selectedObjects = this.object.selectedObjects();
        if (selectedObjects && (1 == selectedObjects.length)){
            this.setSelectedObject(selectedObjects[0]);   
        } else {
            this.setSelectedObject(null);
        }
    },
    dataUpdated: function(change, keyPath) {
        this.object.viewElement().style.display = this.object.rows.length>0 ? 'block' : 'none';
    },
    constructor: function(part_id, parameters) {
        this.base(part_id, parameters);
        if (this.object) {
            this.object.addObserverForKeyPath(this, this.rowSelected, "selectionIndexes");
            this.object.addObserverForKeyPath(this, this.dataUpdated, "dataArray");
            if ('dataArray' in this.object.bindings) {
                this.object.bindings.dataArray.object.addObserverForKeyPath(this, this.dataUpdated, this.object.bindings.dataArray.keypath);
            }
        }
    }
});

MobilAP.ScheduleCalendarController = Class.create(MobilAP.Controller, {
    dateUpdated: function(change, newValue) {
        this.setDate(this.scheduleController.date());
    },
    setDate: function(date) {
        this.date = date;
        this.updateCalendar();
    },
    updateCalendar: function() {
    	var thisMonth = new Date(this.date);
        var today = new Date().date();
    	thisMonth.setDate(1);
        document.getElementById('schedule_calendar_month').innerHTML = Date.monthsLong[thisMonth.getMonth()] + ' ' + thisMonth.getFullYear();

    	var prevMonth = new Date(thisMonth.getTime());
    	prevMonth.setMonth(thisMonth.getMonth() > 1 ? thisMonth.getMonth()-1: 11);
    	prevMonth.setFullYear(thisMonth.getMonth() > 1 ? thisMonth.getFullYear(): thisMonth.getFullYear()-1);

    	var nextMonth = new Date(thisMonth.getTime());
    	nextMonth.setMonth(thisMonth.getMonth() < 11 ? thisMonth.getMonth()+1: 0);
    	nextMonth.setFullYear(thisMonth.getMonth() < 11 ? thisMonth.getFullYear(): thisMonth.getFullYear()+1);

		var daysInPrevMonth = prevMonth.daysInMonth();
		var daysInThisMonth = thisMonth.daysInMonth();
        var self = this;
    
        document.getElementById('schedule_calendar_prev').onclick = function() {
            if (prevMonth.daysInMonth()<self.date.getDate()) {
                prevMonth.setDate(prevMonth.daysInMonth());
            } else {
                prevMonth.setDate(self.date.getDate());
            }
            self.scheduleController.setDate(prevMonth);
        }
        document.getElementById('schedule_calendar_next').onclick = function() {
            if (nextMonth.daysInMonth()<self.date.getDate()) {
                nextMonth.setDate(nextMonth.daysInMonth());
            } else {
                nextMonth.setDate(self.date.getDate());
            }
            self.scheduleController.setDate(nextMonth);
        }

		//7 days * 6 weeks
		var day=0,dayText,className,date, _day;
		for (var dayIndex=0; dayIndex<42; dayIndex++) {
			
			if (day==0 && (dayIndex % 7 < thisMonth.getDay())) {
				dayText = daysInPrevMonth - thisMonth.getDay() + dayIndex + 1;
				date = new Date(prevMonth);
				className = 'prevMonth';
			} else if (day >= daysInThisMonth) {
				day++;
				dayText = day - daysInThisMonth;
				if (dayText == 1) {
					document.getElementById('schedule_calendar_week_' + 5).style.display=dayIndex<29 ? 'none' : '';
					document.getElementById('schedule_calendar_week_' + 6).style.display=dayIndex<36 ? 'none' : '';
				}
				className = 'nextMonth';
				date = new Date(nextMonth);
			} else {
				date = new Date(thisMonth);
				day++;
				dayText = day;
                className = (day == this.date.getDate()) ? 'selected' : '';
			}
			
			date.setDate(dayText);
            if (date.getTime()==today.getTime()) {
				className = className ? className + ' today today_' + className : 'today';
            }
			document.getElementById('schedule_calendar_' + dayIndex).innerText=dayText;
			document.getElementById('schedule_calendar_' + dayIndex).className=className;

			_day = this.scheduleController.dateIndexForDate(date);
            var cell = document.getElementById('schedule_calendar_' + dayIndex);
            var self = this;
            cell.date = date;
            cell.onclick = function() {
                self.scheduleController.setDate(this.date);
            }
            
			if (_day != null) {
				cell.className = className + ' event ' + className + '_event';
			}

		}
    },
    constructor: function(container_id, params) {
        this.base(params);
        this.container_id = container_id;
        this.container = document.getElementById(container_id);
        if (!this.container) throw (container_id + ' not found');
        if (!this.scheduleController) throw ("Schedule controller not set");
        this.setDate(this.scheduleController.date());
//        this.updateCalendar();
        this.scheduleController.addObserverForKeyPath(this, this.dateUpdated, "date");
        this.scheduleController.addObserverForKeyPath(this, this.updateCalendar, "schedule");
    }
});

MobilAP.ScheduleController = Class.create(MobilAP.DataSourceController, {
    scheduleTypes: [ 'scheduleList', 'scheduleDay', 'scheduleMonth'],
    _scheduleType: '',
    scheduleItem: function() {
        return this._scheduleItem;
    },
    setScheduleItem: function(schedule_item) {
        this._scheduleItem = schedule_item;
    },
    indexForScheduleType: function(schedule_type) {
        return this.scheduleTypes.indexOf(schedule_type);
    },
    scheduleType: function() {
        return this._scheduleType;
    },
    scheduleTypeIndex: function() {
        return this.indexForScheduleType(this.scheduleType());
    },
    setScheduleType: function(schedule_type) {
        if (this.indexForScheduleType(schedule_type) == -1) {
            throw('Invalid schedule_type ' + schedule_type);
        }
        this._scheduleType = schedule_type;
    },
    _flatSchedule:[],
    
    flatSchedule: function() {
        return this._flatSchedule;
    },
    content: function() {
        var content = this.base();
        return content || [];
    },
    firstDate: function() {
        var content = this.content();
        return content.length>0 ? content[0].date : null;
    },
    lastDate: function() {
        var content = this.content();
        return content.length>0 ? content[content.length-1].date : null;
    },
    isBeforeFirstDay: function() {
        var firstDate = this.firstDate();
        if (firstDate) {
            return this.date().getTime() <= firstDate.getTime();
        } 
        
        return false;
    },
    isAfterLastDay: function() {
        var lastDate = this.lastDate();
        if (lastDate) {
            return this.date().getTime() >= lastDate.getTime();
        } 
        
        return false;
    },
    isFirstDay: function() {
        var index = this.dateIndex();
        if (index == null) {
            return false;
        }
        return index==0;
    },
    isLastDay: function() {
        var index = this.dateIndex();
        if (index == null) {
            return false;
        }
        return (index+1) == this.content().length;
    },
    nextDay: function() {
        var newIndex;
        if (this.isAfterLastDay()) {
            newIndex = this.schedule.length-1;
        } else if (this.dateIndex()!==null) { 
            newIndex = this.dateIndex()+1;
        } else if (this.isBeforeFirstDay()) {
            newIndex = 0;
        } else {
            newIndex = this.closestDateIndexForDate(this.date());
        }
        this.setDateIndex(newIndex);
    },
    previousDay: function() {
        var newIndex;
        if (this.isBeforeFirstDay()) {
            newIndex = 0;
        } else if (this.dateIndex()!==null) { 
            newIndex = this.dateIndex()-1;
        } else if (this.isAfterLastDay()) {
            newIndex = this.content().length-1;
        } else { 
            newIndex = this.closestDateIndexForDate(this.date())-1;
        }
        this.setDateIndex(newIndex);
    },
    setDateIndex: function(dateIndex) {
        var schedule = this.content();
        if (schedule[dateIndex]) {
            this.setDate(schedule[dateIndex].date);
        }
    },
    dateIndexForDate: function(date) {
        var schedule = this.content();
        for (var i=0; i< schedule.length;i++) {
            if (date.getTime()==schedule[i].date.getTime()) {
                return i;
            }
        }
        return null;
    },
    closestDateIndexForDate: function(date) {
        var schedule = this.content();
        if (schedule.length==0) {
            return null;
        }
        
        for (var i=0; i< schedule.length;i++) {
            if (date.getTime()<=schedule[i].date.getTime()) {
                return i;
            }
        }
        
        return schedule.length-1;
    },
    setDate: function(date) {
        var schedule = this.content();
        if (typeof date=='undefined') {
            date = this.firstDate() || new Date();
        }
        this._date = date.date();
        this._dateIndex = this.dateIndexForDate(this._date);
    },
    date: function() {
        return this._date || new Date().date();
    },
    dateIndex: function() {
        return this._dateIndex;
    },
    schedule: function() {
        if (this.scheduleType()=='scheduleList') {
            return this.flatSchedule();
        }
        
        var index = this.dateIndex(this.date());
        return this.content()[index] ?  this.content()[index].schedule : [];
    },
    
    setSelectedObject: function(object) {
        if (!object) {
            this.schedule_id = null;
            this.schedule_type = null;
            return;
        }
        try {
            if (object.valueForKey('schedule_id')) {            
                this.schedule_id = object.valueForKey('schedule_id');
                this.schedule_title = object.valueForKey('title');
                this.schedule_detail = object.valueForKey('detail');
                this.schedule_room = object.valueForKey('room');
                this.schedule_date = new Date(object.valueForKey('date'));
                this.schedule_start = new Date(object.valueForKey('start_date'));
                this.schedule_end = new Date(object.valueForKey('end_date'));
                this.session_group_id = null;
                this.session_id = null;                
                
                if (object.valueForKey("session_group_id")) {
                    this.schedule_type = 'session_group';
                    this.session_group_id = object.valueForKey('session_group_id');
                } else if (object.valueForKey('session_id')) {
                    this.schedule_type = 'session';
                    this.session_id = object.valueForKey('session_id');
                } else {
                    this.schedule_type = 'schedule_item';
                }
            }
        } catch(e) {
        }
    },
    addScheduleItem: function(schedule_item,callback) {
        schedule_item.post = 'add';
        this.post(base_url + 'schedule.php', schedule_item, callback);
    },
    deleteScheduleItem: function(schedule_id,callback) {
        var params = {
            post: 'delete',
            schedule_id: schedule_id
        }
      
        this.post(base_url + 'schedule.php', params,callback);
    },
    updateScheduleItem: function(schedule_item,callback) {
        schedule_item.post = 'update';
        this.post(base_url + 'schedule.php', schedule_item,callback);
    },
    reloadData: function() {
        dashcode.getDataSource('schedule').queryUpdated();
    },
    _processXHR: function(json,callback) {
        var result = this.base(json);
        if (!this.isError(result)) {
            this.reloadData();
        }

        if ("function"==typeof callback) {
            callback(result);
        }
        
        return result;
    },
    dataSourceUpdated: function(change, keyPath) {
        this.willChangeValueForKey('schedule');
        this.base(change, keyPath);
        this._flatSchedule = [];
        var schedule = this.content();
        for (var i=0; i<schedule.length; i++) {                
            schedule[i].date = new Date(schedule[i].date_str);
            this._flatSchedule.push({ date: schedule[i].date, schedule_type:'date', schedule_id: 0 });
            for (var j=0; j<schedule[i].schedule.length; j++) {
                schedule[i].schedule[j].start_date = new Date(schedule[i].schedule[j].start_date);
                schedule[i].schedule[j].end_date = new Date(schedule[i].schedule[j].end_date);
                if (schedule[i].schedule[j].session_id) {
                    schedule[i].schedule[j].schedule_type='session';
                } else if (schedule[i].schedule[j].session_group_id) {
                    schedule[i].schedule[j].schedule_type='session_group';
                } else {
                    schedule[i].schedule[j].schedule_type='schedule_item';
                }
                this._flatSchedule.push(schedule[i].schedule[j]);
            }
        }

        if (!this._date) {
            this.setDate();
        }
        this.didChangeValueForKey('schedule');
    },
    constructor: function(params) {
        this.base('schedule', params);
    }

});

MobilAP.ScheduleListController = Class.create(MobilAP.ListController, {
    scheduleUpdated: function(change, keyPath) {
        this.reloadData();
    },
    dateUpdated: function(change, keyPath) {
        this.reloadData();
    },
    constructor: function(list_id, params) {
        this.base(list_id, params);
        this.scheduleController.addObserverForKeyPath(this, this.dateUpdated, "date");
        this.scheduleController.addObserverForKeyPath(this, this.scheduleUpdated, "schedule");
    }
});

MobilAP.ProfileController = Class.create(MobilAP.Controller, {
    setUser: function(user) {
    	this.user = user;
    },
    constructor: function(params) {
        this.base(params);
    }
});

MobilAP.SessionsAdminController = Class.create(MobilAP.ListController, {
	sessions: [],
	sessionsUpdated: function(change,keyPath) {
		this.sessions = this._dataSource.content();
		this.reloadData();
	},
    _processXHR: function(json,callback) {
        var result = this.base(json);
        if (!this.isError(result)) {
            dashcode.getDataSource('sessions').queryUpdated();
        }

        if ("function"==typeof callback) {
            callback(result);
        }
        return result;
    },
    deleteSession: function(session,callback) {
        var params = {
            post: 'deleteSession',
            session_id: session.session_id
        }

        this.post(base_url + 'session.php', params, callback);
    },
	constructor: function(part_id, params) {
        this.base(part_id, params);
        this._dataSource = dashcode.getDataSource('sessions');
        this.sessions = this._dataSource.content();
        this._dataSource.addObserverForKeyPath(this, this.sessionsUpdated, "content");
    }
});

MobilAP.SessionAdminController = Class.create(MobilAP.Controller, {
    addSession: function(session,callback) {
        var params = {
            post: 'add',
            session_title: session.session_title,
            session_description: session.session_description,
            session_flags: session.session_flags
        }

        this.post(base_url + 'session.php', params, callback);
    },
    _processXHR: function(json,callback) {
        var result = this.base(json);
        if (!this.isError(result)) {
            dashcode.getDataSource('sessions').queryUpdated();
        }

        if ("function"==typeof callback) {
            callback(result);
        }
        return result;
    }
});


MobilAP.ScheduleAdminController = Class.create(MobilAP.Controller, {

});

MobilAP.SessionQuestionAdminController = Class.create(MobilAP.Controller, {

});

MobilAP.SessionInfoController = Class.create(MobilAP.Controller, {
});

MobilAP.AdminController = Class.create(MobilAP.Controller, {
    
});

MobilAP.HomeAdminController = Class.create(MobilAP.Controller, {
});

MobilAP.ContentAdminController = Class.create(MobilAP.Controller, {
    
});

MobilAP.EvaluationQuestionAdminController = Class.create(MobilAP.ListController, {
    response_types: [ 'M', 'T' ],
    deleteQuestion: function(question,callback) {
        var params = {
            post: 'deleteQuestion',
            question_index: question.question_index
        }
        
        this.post(base_url + 'evaluation.php', params,callback);
    },
    updateQuestion: function(question,callback) {
        if (question.question_text.length==0) {
            return new MobilAP.Error('Question text cannot be blank');
        }

        if (question.question_response_type=='M') {
            if (question.responses.length==0) {
                return new MobilAP.Error('Please include at least 1 response');
            }
        }

        var params = {
            post: 'updateQuestion',
            question_index: this.question_index,
            question_text: question.question_text,
            question_response_type: question.question_response_type
        }
        
        for (var i=0; i<question._deletedResponses.length; i++) {
            params['deletedResponses['+i+']'] = question._deletedResponses[i].response_value;
        }

        for (var i=0; i<question._addedResponses.length; i++) {
            params['addedResponses['+i+']'] = question._addedResponses[i].response_text;
        }
        
        this.post(base_url + 'evaluation.php', params,callback);        
    },
    addQuestion: function(question,callback) {
        if (question.question_text.length==0) {
            return new MobilAP.Error('Question text cannot be blank');
        }

        var params = {
            post: 'addQuestion',
            question_text: question.question_text,
            question_response_type: question.question_response_type
        }

        if (question.question_response_type=='M') {
            if (question.responses.length==0) {
                return new MobilAP.Error('Please include at least 1 response');
            }

            for (var i=0; i<question.responses.length; i++) {
                params['addedResponses['+i+']'] = question.responses[i].response_text;
            }
        }

        this.post(base_url + 'evaluation.php', params,callback);
    },
    _processXHR: function(json,callback) {
        var result = this.base(json);
        if (!this.isError(result)) {
            dashcode.getDataSource('evaluation').queryUpdated();
            this.reloadData();
        }

        if ("function"==typeof callback) {
            callback(result);
        }
        
        return result;
    }
});
MobilAP.SessionController = Class.create(MobilAP.Controller, {
    session: {
        session_questions: [],
        session_links: [],
        session_presenters: []
    },
    isPresenter: function() {
		var presenters = this.session.session_presenters;
		for (var i=0;i<presenters.length;i++) {
			if (presenters[i].userID==mobilAP.user.userID) {
				return true;
			}
		}
		return false;
    },
    isAdmin: function() {
        var admin = this.isPresenter() || mobilAP.isAdmin();
        return admin;
    },
    setScheduleData: function(scheduleData) {
        this.scheduleData = scheduleData;
    },
    setSession: function(session_id) {
        this.session_id = session_id;
        
        var changed = (session_id != dashcode.getDataSource('session').parameters.session_id);
        dashcode.getDataSource('session').parameters.session_id=session_id;
        if (!changed) {
            this.reloadData();
        } else {
            dashcode.getDataSource('session').queryUpdated();
        }
    },
    showadmin: function() {
        return this.isAdmin();
    },
    showinfo: function() {
        return true;
    },
    showevaluation: function() {
        return this.session.session_flags_evaluation && dashcode.getDataSource('evaluation').content().length>0;
    },
    showlinks: function() {
        return this.session.session_flags_links;
    },
    showquestions: function() {
        return this.isAdmin() || this.session.session_questions.length>0;
    },
    showdiscussion: function() {
        return this.session.session_flags_discussion;
    },
    sessionFlags: function() {
        return this.session.session_flags;
    },
    configUpdated: function(change, keyPath) {
        if (this.getConfig('SINGLE_SESSION_MODE')) {
            this.setSession(this.SESSION_SINGLE_ID);
        }
    },
    sessionUpdated: function(change, keyPath) {
        if (change.newValue) {
            this.session = change.newValue;
            try {
				mobilAP.serialController.setSerial('session_' + this.session_id, this.session.serial);
			} catch (e) {
			}
        }
    },
    questionById: function(question_id) {
        for (var i=0; i< this.session.session_questions.length; i++) {
            if (this.session.session_questions[i].question_id == question_id) {
                return new MobilAP.SessionQuestion(this.session.session_questions[i]);
            }
        }
    },
    evaluationCompleted: function() {
    	var completed = false;
		try {
			completed = mobilAP.user.userData.sessions[this.session_id].evaluation;
		} catch(e) {
			completed = false;
		}

        return completed;
    },
    questionAnswered: function(question_id) {
    	var answered = false;
		try {
			answered = mobilAP.user.userData.sessions[this.session_id].questions[question_id];
		} catch(e) {
			answered = false;
		}

        return answered;
    },
    clearDiscussion: function(callback) {
        var params = {
            session_id: this.session_id,
            post: 'clearDiscussion'
        }
      
        this.post(base_url + 'session.php', params,callback);
    },
    addPresenter: function(userID,callback) {
        var params = {
            session_id: this.session_id,
            post: 'addPresenter',
            userID: userID
        }
      
        this.post(base_url + 'session.php', params,callback);
    },
    removePresenter: function(userID,callback) {
        var params = {
            session_id: this.session_id,
            post: 'removePresenter',
            userID: userID
        }
      
        this.post(base_url + 'session.php', params,callback);
    },
    postDiscussion: function(post_text,callback) {
        if (post_text.length==0) {
            return new MobilAP.Error("Please enter text to post");
        }
        
        if (!mobilAP.isLoggedIn()) {
            return new MobilAP.Error("Please login to post", mobilAP.ERROR_NO_USER);
        }

        var params = {
            session_id: this.session_id,
            post: 'discussion',
            post_text: post_text
        }
      
        this.post(base_url + 'session.php', params,callback);
    },
    deleteLink: function(link_id,callback) {
        var params = {
            session_id: this.session_id,
            post: 'deleteLink',
            link_id: link_id
        }
      
        this.post(base_url + 'session.php', params,callback);
    },
    addLink: function(link_url, link_title,callback) {
        if (!link_url.match("^https?://.+")) {
            return new MobilAP.Error("Invalid URL");
        }

        if (link_title.length==0) {
            return new MobilAP.Error("Please include a title");
        }

        if (!mobilAP.isLoggedIn()) {
            return new MobilAP.Error("Please login to post", mobilAP.ERROR_NO_USER);
        }
        
        var params = {
            session_id: this.session_id,
            post: 'addLink',
            link_url: link_url,
            link_title: link_title
        }
      
        this.post(base_url + 'session.php', params,callback);
    },
    _processXHR: function(json,callback) {
        var result = this.base(json);
        if (!this.isError(result)) {
            this.reloadData();
        }

        if ("function"==typeof callback) {
            callback(result);
        }
        
        return result;
    },
    setTitle: function(title) {
        if (title.length>0) {
            this.session.session_title = title;
        };
    },
    setDescription: function(description) {
        this.session.session_description = description;
    },
    setFlags: function(flags) {
        this.session.session_flags = flags;
    },
    deleteQuestion: function(question,callback) {
        var params = {
            post: 'deleteQuestion',
            session_id: this.session_id,
            question_id: question.question_id
        }
        
        this.post(base_url + 'session.php', params, callback);
    },
    updateQuestion: function(question, callback) {
        if (question.question_text.length==0) {
            return new MobilAP.Error('Question text cannot be blank');
        }

        if (question.responses.length==0) {
            return new MobilAP.Error('Please include at least 1 response');
        }

        if (question.question_minchoices > question.question_maxchoices) {
            return new MobilAP.Error('Minimum choices should not be greater than maximum choices');
        }
        
        var params = {
            post: 'updateQuestion',
            session_id: this.session_id,
            question_active: question.question_active,
            question_id: question.question_id,
            question_text: question.question_text,
            question_minchoices: question.question_minchoices,
            question_maxchoices: question.question_maxchoices        
        }
        
        for (var i=0; i<question._deletedResponses.length; i++) {
            params['deletedResponses['+i+']'] = question._deletedResponses[i].response_value;
        }

        for (var i=0; i<question._addedResponses.length; i++) {
            params['addedResponses['+i+']'] = question._addedResponses[i].response_text;
        }
        
        var request = XHR.post(base_url + 'session.php', params, callback);
    },
    addQuestion: function(question, callback) {
        if (question.question_text.length==0) {
            return new MobilAP.Error('Question text cannot be blank');
        }

        if (question.responses.length==0) {
            return new MobilAP.Error('Please include at least 1 response');
        }

        if (question.question_minchoices > question.question_maxchoices) {
            return new MobilAP.Error('Minimum choices should not be greater than maximum choices');
        }
        
        var params = {
            post: 'addQuestion',
            session_id: this.session_id,
            question_active: question.question_active,
            question_text: question.question_text,
            question_minchoices: question.question_minchoices,
            question_maxchoices: question.question_maxchoices        
        }
        
        for (var i=0; i<question.responses.length; i++) {
            params['addedResponses['+i+']'] = question.responses[i].response_text;
        }
        
        this.post(base_url + 'session.php', params,callback);
    },
    saveSessionAdmin: function(callback) {
        var params = {
            session_id: this.session_id,
            post: 'updateSession',
            session_title: this.session.session_title,
            session_description: this.session.session_description+ " ",
            session_flags: this.session.session_flags
        }
      
        this.post(base_url + 'session.php', params, callback);
    },
    reloadData: function() {
        dashcode.getDataSource('session').queryUpdated();
    },
    constructor: function(params) {
        this.base(params);
        dashcode.getDataSource('config').addObserverForKeyPath(this, this.configUpdated, "content");
        dashcode.getDataSource('session').addObserverForKeyPath(this, this.sessionUpdated, "content");
    }
});

MobilAP.SessionEvaluationController = Class.create(MobilAP.DataSourceController, {
    setResponse: function(index, response) {
        this.responses[index] = response;
    },
    setQuestionIndex: function(index) {
        if (this.content()[index]) {
            this.setQuestion(this.content()[index]);
        }
    },
    setPreviousQuestion: function() {
        if (this.questionIndex>0) {
            this.setQuestionIndex(this.questionIndex-1);
        }
    },
    setNextQuestion: function() {
        if ( (this.questionIndex+1)<this.content().length) {
            this.setQuestionIndex(this.questionIndex+1);
        }
    },
    responseForResponseValue: function(response_value) {
        for (var i=0; i<this.question.responses.length;i++) {
            if (this.question.responses[i].response_value==response_value) {
                return this.question.responses[i];
            }
        }
    },
    setQuestion: function(question) {
        this.question = question;
        this.questionIndex = question.question_index
    },
    dataSourceUpdated: function(change, keyPath) {
        this.base(change, keyPath);
        this.responses = new Array(this.content().length);
    },
    submitEvaluation: function(callback) {
        if (!mobilAP.isLoggedIn()) {
            return new MobilAP.Error("Please login to post", mobilAP.ERROR_NO_USER);
        }
        
        var params = {
            post: 'evaluation',
            session_id: this.sessionController.session_id
        }
        for (var i=0; i<this.responses.length;i++) {
            params['responses['+i+']'] = this.responses[i];
        }

        this.post(base_url + 'session.php', params, callback);
    },
    _processXHR: function(json,callback) {
        var result = this.base(json);
        if (!this.isError(result)) {
            dashcode.getDataSource('session').queryUpdated();
        }

        if ("function"==typeof callback) {
            callback(result);
        }
        
        return result;
    },
    constructor: function(params) {
        this.base('evaluation',params);
    }
});

MobilAP.LinksListController = Class.create(MobilAP.ListController, {
    openURL: function(url) {
        window.open(url);
    },
    rowSelected: function(change, keyPath) {
        this.base(change, keyPath);
        this.openURL(this.selectedObject.link_url);
        this.clearSelection();
    }
});

MobilAP.DiscussionController = Class.create(MobilAP.ListController, {
});

MobilAP.AnnouncementController = Class.create(MobilAP.ListController, {
    setAnnouncement: function(announcement) {
        this.announcement = announcement;
    },
    deleteAnnouncement: function(announcement, callback) {
        var params = {
            post: 'deleteAnnouncement',
            announcement_id: announcement.announcement_id
        }
      
        this.post(base_url + 'announcements.php', params, callback);        
    },
    updateAnnouncement: function(announcement, callback) {
        var params = {
            post: 'updateAnnouncement',
            announcement_id: announcement.announcement_id,
            announcement_title: announcement.announcement_title,
            announcement_text: announcement.announcement_text
        }
      
        this.post(base_url + 'announcements.php', params, callback);
    },
    addAnnouncement: function(announcement, callback) {
        if (announcement.announcement_title.length==0) {
            return new MobilAP.Error("Please include a title");
        }

        if (announcement.announcement_text.length==0) {
            return new MobilAP.Error("Please include the announcement text");
        }

        if (!mobilAP.isAdmin()) {
            return new MobilAP.Error("Only administrators can post announcements", mobilAP.ERROR_NO_USER);
        }
        
        var params = {
            post: 'addAnnouncement',
            announcement_title: announcement.announcement_title,
            announcement_text: announcement.announcement_text
        }
      
        this.post(base_url + 'announcements.php', params, callback);
    },
    reloadData: function() {
        dashcode.getDataSource('announcements').queryUpdated();
    },
    _processXHR: function(json, callback) {
        var result = this.base(json);
        if (!this.isError(result)) {
            this.reloadData();
        }

        if ("function"==typeof callback) {
            callback(result);
        }
        
        return result;
    }
});

MobilAP.DirectoryController = Class.create(MobilAP.ListController, {

    reloadData: function() {
        dashcode.getDataSource('users').queryUpdated();
    },
    setSelectedObject: function(object) {
        if (object) {
            this.user = new MobilAP.User(object);
            this.profileController.setUser(this.user);
        } else {
            this.user = null;
        }
    }
});

MobilAP.DirectoryAdminController = Class.create(MobilAP.Controller, {
    resetPassword: function() {
        var params = {
            post: 'resetPassword',
            userID: this.user.userID
        }
        this.post(base_url + 'user.php', params, this._processPasswordReset.bind(this));
    },
    _processPasswordReset: function(json) {
        if (!this.isError(json)) {
            alert('Password reset to email address');
        }
    },
    deleteUser: function(userID, callback) {
        var params = {
            post: 'deleteUser',
            userID: userID
        }
        this.post(base_url + 'user.php', params, callback);
    },
    saveUser: function(user, callback) {
        var mode = user.userID ? 'updateUser' : 'addUser';

        if (user.FirstName.length==0 || user.LastName.length==0) {
            return new MobilAP.Error("Name should not be blank");
        }

        if (user.email.length==0) {
            return new MobilAP.Error("Email adddress should not be blank");
        }

        if (mode=='updateUser' && !mobilAP.isAdmin()) {
            return new MobilAP.Error("Only administrators can update users", mobilAP.ERROR_NO_USER);
        }
        
        if (mode=='addUser' && !mobilAP.getConfig('ALLOW_SELF_CREATED_USERS') && !mobilAP.isAdmin()) {
            return new MobilAP.Error("Only administrators can create users", mobilAP.ERROR_NO_USER);
        }
                
        var params = {
            post: mode,
            userID: user.userID,
            FirstName: user.FirstName,
            LastName: user.LastName,
            organization: user.organization,
            email: user.email,
            admin: user.admin ? -1 : 0
        }
        
        if (mode=='addUser') {
            params.md5_password = user.password ? hex_md5(user.password) : hex_md5(user.email);
        }
        
        this.post(base_url + 'user.php', params, callback);
    },
    _processXHR: function(json, callback) {
        var result = this.base(json);
        if (!this.isError(result)) {
            dashcode.getDataSource('users').queryUpdated();
            dashcode.getDataSource('user').queryUpdated();
        }

        if ("function"==typeof callback) {
            callback(result);
        }
        
        return result;
    }
});

MobilAP.DirectoryImportController = Class.create(MobilAP.Controller, {

});


MobilAP.SessionPresentersAdminController = Class.create(MobilAP.Controller, {
    foundUsers: [],
    setFoundUsers: function(users) {
        this.foundUsers = [];
        for (var i=0; i< users.length; i++) {
            this.foundUsers.push(new MobilAP.User(users[i]));
        }
    },
    clearFoundUsers: function() {
        this.foundUsers = [];
    },
    findUsers: function(term) {
        var params = {
            q:term
        }
        this.get(base_url + 'users.php',params,this._foundUsers.bind(this));
    },
    _foundUsers: function(result) {
        if (!this.isError(result)) {
            this.setFoundUsers(result);
        }
    }
    
});

MobilAP.UserProfileController = Class.create(MobilAP.Controller, {
    setPassword: function(newPassword) {
        if (newPassword.length==0) {
            return new MobilAP.Error("Password was blank");
        }
        
        var params = {
            post: 'setPassword',
            password_md5: hex_md5(newPassword)
        }

        this.post(base_url + 'user.php', params, this._processPasswordChange.bind(this));
    },
    _processPasswordChange: function(json) {
        if (!this.isError(json)) {
            alert('Password changed successfully');
        }
    },
    setUser: function(user) {
        this.user = user;
    },
    userUpdated: function(change,keyPath) {
        this.setUser(new MobilAP.User(change.newValue));
    },
    constructor: function(params) {
        this.base(params);
        dashcode.getDataSource('user').addObserverForKeyPath(this, this.userUpdated, "content");
        
    }
});

MobilAP.QuestionController = Class.create(MobilAP.Controller, {
    question: { responses: [], answers: []},
    setQuestion: function(object) {
        var change = this.question_id != object.question_id;
        this.question_id = object.question_id;
        this.question = object;
        if (change) {
            this.selectedResponses = [];
        }
    },
    submitQuestion: function(callback) {
        if ( (this.selectedResponses.length<this.question.question_minchoices) || (this.selectedResponses.length>this.question.question_maxchoices)) {
            return new MobilAP.Error(this.question.selectMessage());
        }

        if (!mobilAP.isLoggedIn()) {
            return new MobilAP.Error("Please login to post", mobilAP.ERROR_NO_USER);
        }

        var params = {
            session_id: this.question.session_id,
            question_id: this.question.question_id,
            post: 'question'
        }
        
        for (var i=0; i< this.selectedResponses.length; i++) {
            params['response[' + i + ']'] = this.question.responses[this.selectedResponses[i]].response_value;
        }
      
        this.post(base_url + 'session.php', params, callback);
    },
    _processXHR: function(json, callback) {
        var result = this.base(json);
        if (!this.isError(result)) {
            dashcode.getDataSource('session').queryUpdated();
        }

        if ("function"==typeof callback) {
            callback(result);
        }
        
        return result;
    },
    selectedResponses: [],
    clearAnswers: function(callback) {
        var params = {
            session_id: this.question.session_id,
            question_id: this.question.question_id,
            post: 'clearAnswers'
        }
        var self = this;
        
        this.post(base_url + 'session.php', params, function() {
            self.sessionController.reloadData();
        });
    },
	codeMap: "ABCDEFGHIJKLMNOPQRSTUVWXYZ",
	toggleChartType: function() {
		this.question.toggleChartType();
		this.updateChart();
	},
    updateChart: function() {
        if (!this.question) {
            return;
        }
        
        var add_zero = this.question.chart_type != 'p';
    	var data = [];
    	var labels = [];
		var colors = [];
    	var indexes = [];
    	var max_value = 0;
		var r = 0;
		var g = 0;
		var b = 255;

        for (var i=0; i<this.question.responses.length; i++) {
            if (this.question.answers[this.question.responses[i].response_value]>0 || add_zero) {
            	var answer = this.question.answers[this.question.responses[i].response_value];
                data.push(answer);
                if (answer>max_value) {
                	max_value = answer;
                }
                labels.push(escape(this.question.responses[i].response_text));
                indexes.push(i);
                colors.push("#" + RGBtoHEX(r,g,b));
                r += 35;
                g += 35;
                b -= 35;
            }
        }
                
		this.results_chart.className = 'chart_type_' + this.question.chart_type;
		var canvas =  document.getElementById('question_response_canvas');
        if (this.question.answers.total==0) {
            if (canvas) {
                canvas.parentNode.removeChild(canvas);
            }
            return;
        }

        var width = this.getChartWidth();
        var height = this.getChartHeight();
		if (!canvas) {  
			canvas = document.createElement('canvas');
			canvas.id = 'question_response_canvas';
			canvas.width=width;
			canvas.height=height;
			canvas.onclick = this.toggleChartType.bind(this);
			this.results_chart.appendChild(canvas);
			if (!canvas.getContext) G_vmlCanvasManager.initElement(canvas);
		} 

		var label_container =  document.getElementById('question_response_labels');
		if (!label_container) {
			label_container = document.createElement('div');
			label_container.id = 'question_response_labels';
			this.results_chart.appendChild(label_container);
		}
		
		label_container.innerHTML = '';

		var g = canvas.getContext("2d");
		g.clearRect(0,0,canvas.width, canvas.height);

        switch (this.question.chart_type)
        {
            case 'p':

				// All the lines we draw are 2 pixel wide black lines
				g.lineWidth = 1;
				g.strokeStyle = "white";
				var cx = width / 2;
				var cy = height / 2;
				var r = Math.min(width, height)/2.5
				
				// Total the data values
				var total = 0;
				for(var i = 0; i < data.length; i++) total += data[i];
				
				// And compute the angle (in radians) for each one
				var angles = [];
				for(var i = 0; i < data.length; i++) angles[i] = data[i]/total*Math.PI*2;
				
				// Now, loop through the wedges of the pie
				startangle = -Math.PI/2;  // Start at 12 o'clock instead of 3 o'clock
                
				for(var i = 0; i < data.length; i++) {
					// This is the angle where the wedge ends
					var endangle = startangle + angles[i];
					
					// Draw a wedge
					g.beginPath();              // Start a new shape

					// Line to startangle point and arc to endangle
                    if (data.length>1) {
                        g.moveTo(cx,cy);            // Move to center
                    }

                    g.arc(cx,cy,r,startangle, endangle, false); 
                    g.closePath();              // Back to center and end shape
					g.fillStyle = colors[i];    // Set wedge color
					g.fill();                   // Fill the wedge
					g.stroke();                 // Outline ("stroke") the wedge

					// draw the label
					var label = document.createElement('div');
					label.className = 'chart_label';
					label.innerHTML = this.codeMap.charAt(indexes[i]);
					label_container.appendChild(label);
					var offsetx=-label.clientWidth/2;
					var offsety=-label.clientHeight/2;
					
					
					//position.. math math math...
					if (data.length==1) {
						label.style.left = (cx + +offsetx) + 'px';
						label.style.top = (cy + offsety) + 'px';
					} else {
						var labelangle = startangle + ((endangle-startangle)/2);
						var labelr = r/2;
						var labelx = parseInt(labelr * Math.cos(labelangle));
						var labely = parseInt(labelr * Math.sin(labelangle));

						label.style.left = (cx + labelx + offsetx) + "px";
						label.style.top = (cy + labely + offsety) + 'px';
					}
			
					// The next wedge starts where this one ends.
					startangle = endangle;
				}
	
				break;
						
            case 'b':
				g.strokeStyle = '#7F7F7F';
            	var minRowHeight = 10;
            	var maxRowHeight = 100;
            	var barChartXStart = 30;
            	var maxWidth = width - barChartXStart;
            	var rowSpacing = 5;
            	var rowheight = Math.floor(height / data.length);
            	
            	for (var i=0; i<data.length;i++) {
					// draw the label
					var label = document.createElement('div');
					label.className = 'chart_label';
					label.innerHTML = this.codeMap.charAt(indexes[i]);
					label_container.appendChild(label);
					label.style.left = 0;
					label.style.top = rowheight * i + 'px';

					if (data[i]>0) {					
						g.fillStyle = colors[i];    // Set wedge color
						g.fillRect(barChartXStart, rowheight * i, (data[i]/max_value)*maxWidth, rowheight- rowSpacing);
						g.strokeRect(barChartXStart, rowheight * i, (data[i]/max_value)*maxWidth, rowheight- rowSpacing);
					}						
            	}
            	
                break;
        }
    },
    sessionUpdated: function() {
        if (this.question_id) {
            var question = this.sessionController.questionById(this.question_id);
            this.setQuestion(question);
        }
    },
    selectResponse: function(response_index) {
        if (!this.question.responses[response_index]) {
            return false;
        }
        
        if (this.question.question_maxchoices>1) {
        
            var index = this.selectedResponses.indexOf(response_index);
            if (index>=0) {
                this.selectedResponses.splice(index, 1);    
            }   else {
                this.selectedResponses.push(response_index);
            }
            this.selectedResponses.sort();
        } else {
            this.selectedResponses = [response_index];
        }
    },
    constructor: function(params) {
        this.base(params);
        this.sessionController.addObserverForKeyPath(this, this.sessionUpdated, "session");
    }
});

MobilAP.EvaluationQuestion =Class.create({
    question_index: null,
	question_text: '',
    question_response_type: 'M',
    setQuestionText: function(question_text) {
        this.question_text = question_text;
    },
    setResponseType: function(response_type)
    {
        this.question_response_type = response_type;
        if (this.question_response_type=='T') {   
            this.responses = [];
        }
    },
    removeResponse: function(index) {
        if (typeof this.responses[index] != 'undefined') {
            this._deletedResponses.push(this.responses.splice(index, 1)[0]);
        } 
    },
    addResponse: function(response_text) {
        if (response_text.length==0) {
            return new MobilAP.Error('Response cannot be blank');
        }
        var response = new MobilAP.EvaluationQuestionResponse({
            question_index: this.question_index,
            index: this.responses.length,
            response_text: response_text});
        this.responses.push(response);
        this._addedResponses.push(response);
    },
    constructor: function(params) {
        this.responses = [];
        Object.extend(this,params);
        this._deletedResponses = [];
        this._addedResponses = [];
    }
});

MobilAP.SessionQuestion = Class.create({
    question_id: null,
	question_text: '',
	question_list_text: '',
	question_minchoices:0,
	question_maxchoices:1,
	question_active: -1,
	chart_type: 'p',
    selectMessage: function() {
        var message = 'Please select ';
        if (this.question_maxchoices==1) {
            if (this.question_minchoices==0) {
                message += 'up to ';
            }
            message += '1 choice';
        } else if (this.question_minchoices==this.question_maxchoices) {
            message += '' + this.question_maxchoices + ' choices';
        } else if (this.question_minchoices==0) {
            message += 'up to ' + this.question_maxchoices + ' choices';
        } else {
            message += ' between ' + this.question_minchoices + ' and ' + this.question_maxchoices + ' choices';
        }
        return message;
    },
    setQuestionActive: function(question_active) {
    	this.question_active = question_active ? -1 : 0;
    },
    setQuestionText: function(question_text) {
        this.question_text = question_text;
    },
    setMinimumChoices: function(choices) {
        this.question_minchoices = parseInt(choices);
    },
    setMaximumChoices: function(choices) {
        this.question_maxchoices = parseInt(choices);
    },
    removeResponse: function(index) {
        if (typeof this.responses[index] != 'undefined') {
            this._deletedResponses.push(this.responses.splice(index, 1)[0]);
        } 
    },
    addResponse: function(response_text) {
        if (response_text.length==0) {
            return new MobilAP.Error('Response cannot be blank');
        }
        var response = new MobilAP.SessionQuestionResponse({
            question_id: this.question_id,
            index: this.responses.length,
            response_text: response_text});
        this.responses.push(response);
        this._addedResponses.push(response);
    },
    toggleChartType: function() {
    	this.setChartType(this.chart_type=='p' ? 'b' : 'p');
    },
    setChartType: function(chart_type) {
    	this.chart_type = chart_type;
    },
    constructor: function(params) {
        this.responses = [];
        Object.extend(this,params);
        this._deletedResponses = [];
        this._addedResponses = [];
    }
});

MobilAP.EvaluationQuestionResponse =Class.create({
    constructor: function(params) {
        Object.extend(this,params);
    }

});


MobilAP.SessionQuestionResponse =Class.create({
    constructor: function(params) {
        Object.extend(this,params);
    }

});

MobilAP.Announcement =Class.create({
    setAnnouncementText: function(text) {
        this.announcement_text = text;
    },
    setAnnouncementTitle: function(title) {
        this.announcement_title = title;
    },
    constructor: function(params) {
        Object.extend(this,params);
        if (params) {
			this.user = mobilAP.getUser(params.userID);
			this.announcement_date = new Date(params.announcement_date);
		} else {
			this.user = new MobilAP.User();
			this.announcement_date = new Date();
		}
    }
});

MobilAP.Session =Class.create({
    session_title: '',
    session_id: null,
    session_description: '',
    session_flags: 15,
    setTitle: function(title) {
        this.session_title = title;
    },
    setDescription: function(description) {
        this.session_description = description;
    },
    setFlags: function(session_flags) {
        this.session_flags = session_flags;
    }, 
    constructor: function() {
    }
});

MobilAP.ScheduleItem =Class.create({
    schedule_id: null,
    room: '',
    detail: '',
    session_id: '',
    session_group_id: '',
    constructor: function() {
        this.start_time = new Date();
        this.end_time = new Date();
    }
});

MobilAP.User = Class.create({    
    admin: 0,
    userID: '',
    FirstName: '',
    LastName: '',
    organization: '',
    email: '',
    userData: { 
    	sessions:{},
    	announcements:{}
	},
    isAdmin: function() {
        return this.admin;
    },
    isLoggedIn: function() {
        return this.userID ? true : false;
    },
    getUserID: function() {
        return this.userID;
    },
    getFullName: function() {
		return this.FirstName + ' ' + this.LastName;
	},
    getEmail: function() {
		return this.email;
	},
    constructor: function(userData) {
        Object.extend(this, userData);
    }
});

MobilAP.Switch = Class.create(MobilAP.BaseClass,{
    toggleValue: function()
    {
        this.setValue(!this.value);
    },
    intValue: function() {
        return this.value ? -1 : 0;
    },
    setValue: function(value) {
        this.value = value ? true : false;
        this.image.src = this.value ? 'Images/switch_on.png' : 'Images/switch_off.png';
    },
    constructor: function(container, value) {
        this.base();
        this.container = MobilAP.getElementById(container);
        if (!typeof this.container == 'object') {
            throw("Unable to make MobilAP.Switch out of " + container) ;
        }
        this.container.object = this;
        this.image = new Image();
        var self = this;
        this.image.onclick = function() {
            self.toggleValue();
        }
        //this.image.className = 'mobilAP_switch';
        this.container.appendChild(this.image);
        this.setValue(value);
    }
});

MobilAP.DateTimePicker = Class.create(MobilAP.BaseClass, {
    setDate: function(date) {
        date.setSeconds(0);
        date.setMilliseconds(0);
        date.setMinutes( Math.floor(date.getMinutes() / this.minuteStep) * this.minuteStep );
        this.willChangeValueForKey('date');
        this._date = date;
        this.didChangeValueForKey('date');
        this._updateControls();
    },
    date: function() {
        return this._date;
    },
    constructor: function(container, options) {
        if (!document.getElementById(container)) {
            throw("Unable to find container " + container);
            return;
        }
        this.container = document.getElementById(container);
        this.container.object = this;

        this.minuteStep = 5;

        if (typeof options == 'object') {
            Object.extend(this, options);
            if (this.minuteStep <= 0) {
                this.minuteStep = 1;
            }
        }
    }
}); 

MobilAP.DatePicker = Class.create(MobilAP.DateTimePicker, {
    _updateControls: function() {
        this.monthControl.selectedIndex = this.date().getMonth();
        
        // update the number of days in this month
        if (this.dayControl.options.length != this.date().daysInMonth()) {
            var prevLength = this.dayControl.options.length;
            this.dayControl.options.length = this.date().daysInMonth();
            for (var i=prevLength; i<this.date().daysInMonth();i++) {
                this.dayControl.options[i] = new Option(i+1, i+1);
            }
        } 
        this.dayControl.selectedIndex = this.date().getDate()-1;
        this.yearControl.selectedIndex = this.date().getFullYear()-this.startYear;
    },
    _dateChanged: function() {
        var month = this.monthControl.options[this.monthControl.selectedIndex].value;
        var day = this.dayControl.options[this.dayControl.selectedIndex].value;
        var year = this.yearControl.options[this.yearControl.selectedIndex].value;
        var date = new Date(this.date());
        date.setMonth(month-1); //javascript month indexes begin with 0
        date.setFullYear(year);
        
        //if the day is over the number of days in the month, just change it to the last day of the month
        if (day>date.daysInMonth()) {
            day = date.daysInMonth();
        }
        date.setDate(day);
        this.setDate(date);
    },
    _timeChanged: function(change,keyPath) {
        var date = new Date(this.date());
        date.setHours(this.timePicker.date().getHours());
        date.setMinutes(this.timePicker.date().getMinutes());
        this.setDate(date);
    },
    constructor: function(container, options) {
        this.base(container, options);

        var now = new Date();
        //defaults
        this.startYear = now.getFullYear()-5;
        this.endYear = now.getFullYear()+5;

        
        this.dateContainer = document.createElement('div');
        this.dateContainer.className='mobilAP_dateContainer';
        this.container.appendChild(this.dateContainer);
        this.monthControl = MobilAP.createSelectBox(MobilAP.createNumericArray(1,12), Date.monthsLong);
        this.monthControl.object = this;
        this.monthControl.onchange = this._dateChanged.bind(this);
        this.dateContainer.appendChild(this.monthControl);
        this.dayControl = MobilAP.createSelectBox(MobilAP.createNumericArray(1,31));
        this.dayControl.object = this;
        this.dayControl.onchange = this._dateChanged.bind(this);
        this.dateContainer.appendChild(this.dayControl);
        this.yearControl = MobilAP.createSelectBox(MobilAP.createNumericArray(this.startYear, this.endYear));
        this.yearControl.object = this;
        this.yearControl.onchange = this._dateChanged.bind(this);
        this.dateContainer.appendChild(this.yearControl);
        
        now.setHours(0);
        now.setMinutes(0);
        
        this.setDate(now);

        if ('object' == typeof this.timePicker) {
            this.timePicker.addObserverForKeyPath(this, this._timeChanged, "date");
            this._timeChanged();
        }
    }
});

MobilAP.TimePicker = Class.create(MobilAP.DateTimePicker, {
    _updateControls: function() {
        this.hourControl.selectedIndex = this.date().getCivilianHours()-1;
        this.minuteControl.selectedIndex = Math.floor(this.date().getMinutes() / this.minuteStep);
        this.meridianControl.selectedIndex = this.date().getHours()>11 ? 1 : 0;
    },
    _dateChanged: function(change,keyPath) {
        var date = new Date(this.date());
        date.setMonth(this.datePicker.date().getMonth());
        date.setDate(this.datePicker.date().getDate());
        date.setFullYear(this.datePicker.date().getFullYear());
        this.setDate(date);
    },
    _timeChanged: function() {
        var hour = parseInt(this.hourControl.options[this.hourControl.selectedIndex].value);
        var minute = parseInt(this.minuteControl.options[this.minuteControl.selectedIndex].value);
        var meridian = this.meridianControl.options[this.meridianControl.selectedIndex].value;
        var date = new Date(this.date());
        
        if (hour == 12) {
            hour -= 12;
        }
        hour = meridian == 'pm' ? hour+12 : hour;
        date.setHours(hour);
        date.setMinutes(minute);
        this.setDate(date);
    },
    constructor: function(container, options) {
        this.base(container, options);

        var now = new Date();
        
        this.timeContainer = document.createElement('div');
        this.timeContainer.className='mobilAP_timeContainer';
        this.container.appendChild(this.timeContainer);
        this.hourControl = MobilAP.createSelectBox(MobilAP.createNumericArray(1,12));
        this.hourControl.object = this;
        this.hourControl.onchange = this._timeChanged.bind(this);
        this.timeContainer.appendChild(this.hourControl);            
        var minutes = MobilAP.createNumericArray(0,59,this.minuteStep);
            for (var i=0; i*this.minuteStep<10; i++) {
                minutes[i] = '0' + minutes[i];
            }
            
        this.minuteControl = MobilAP.createSelectBox(minutes);
        this.minuteControl.object = this;
        this.minuteControl.onchange = this._timeChanged.bind(this);
        this.timeContainer.appendChild(this.minuteControl);
        this.meridianControl = MobilAP.createSelectBox(Date.meridians);
        this.meridianControl.object = this;
        this.meridianControl.onchange = this._timeChanged.bind(this);
        this.timeContainer.appendChild(this.meridianControl);
        
        this.setDate(now);

        if ('object' == typeof this.datePicker) {
            this.datePicker.addObserverForKeyPath(this, this._dateChanged, 'date');
            this._dateChanged();
        }
    }
});


/*************** 

TRANSFORMERS (more than meets the eye)
	
****************/	

var dayTransformer = Class.create(DC.ValueTransformer,{
    transformedValue: function(value){
        var date = new Date(value);
        return Date.daysLong[date.getDay()];
    }
});

longDateTransformer = Class.create(DC.ValueTransformer,{
    transformedValue: function(value){
        var date = new Date(value);
        var d = Date.daysLong[date.getDay()] + ' ' + Date.monthsLong[date.getMonth()] + ' ' + date.getDate() + ', ' + date.getFullYear();
        return d;
    }
});


shortDateTransformer = Class.create(DC.ValueTransformer,{
    transformedValue: function(value){
        var date = new Date(value);
        var d = Date.monthsShort[date.getMonth()] + ' ' + date.getDate() + ', ' + date.getFullYear();
        return d;
    }
});

timeTransformer = Class.create(DC.ValueTransformer,{
    transformedValue: function(value) {
        var date = new Date(value);
        if (date == 'Invalid Date') {
            throw ("Invalid date for " + value);
        }
        var hour = date.getHours() > 12? date.getHours()-12 : (date.getHours() > 0 ? date.getHours() : 12);
        var minute = date.getMinutes().leadingZero(2);
        return hour.toString() + ":" + minute + (date.getHours() > 11? "p" : "a");

    }
});



timestampTransformer = Class.create(DC.ValueTransformer,{
    transformedValue: function(value){
        var date = new Date(value*1000);
        var d = Date.monthsShort[date.getMonth()] + ' ' + date.getDate() + ', ' + date.getFullYear();
        var hour = date.getHours() > 12? date.getHours()-12 : (date.getHours() > 0 ? date.getHours() : 12);
        var minute = date.getMinutes().leadingZero(2);
        var t = hour.toString() + ":" + minute + (date.getHours() > 11? "p" : "a");
        return d + ' @ ' +t;
    }
});

mobilAP_UserTransformer = Class.create(DC.ValueTransformer,{
    transformedValue: function(value){
        // this transformer has an O(n) scaling problem
        var users = dashcode.getDataSource('users').content();
        for (var i=0; i< users.length; i++) {
            if (users[i].userID==value) {
                return users[i].FirstName + ' ' + users[i].LastName;
            }
        }

		return value;
    }
});



function RGBtoHEX(r, g, b) {
	var hex = (r << 16 | g << 8 | b).toString(16).toUpperCase();
	while (hex.length<6) {
		hex = "0"+hex;
	}
	return hex;
}
