MobilAP.SetupController = Class.create(MobilAP.Controller, {
    partSpecs: {
        "setupStack": { "subviewsTransitions": [{ "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "dissolve" },{ "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "dissolve" },{ "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "dissolve" },{ "direction": "right-left", "duration": "", "timing": "ease-in-out", "type": "dissolve" }], "view": "DC.StackLayout"}
    },
    dbtest: function() {
        this.log('testing db');
        this.dbvalidated = false;
        document.getElementById('db_test_results').innerHTML = 'Testing...';
        var params = {
            post: 'dbtest',
            db_type: this.db_type,
            db_host: document.getElementById('db_host').value,
            db_username: document.getElementById('db_username').value,
            db_password: document.getElementById('db_password').value,
            db_database: document.getElementById('db_database').value
        }
        var request = XHR.post(base_url + 'config.php', params);
        var self = this;
        request.addMethods(this._processDBTest.bind(this));
    },
    _processDBTest: function(json) {
        try {
            var message = 'Test successful';
            this.dbvalidated = true;
            if (json.error_message) {
                message = json.error_message + ' (' + json.userinfo +')';
                this.dbvalidated = false;
            }
            document.getElementById('db_test_results').innerHTML = message;
            
        } catch (e) {
            alert("There was an error testing the database connection: " + e);
            return;
        }
    },
    saveDB: function(callback) {
        var dbparams = {
            db_type: this.db_type,
            db_host: document.getElementById('db_host').value,
            db_username: document.getElementById('db_username').value,
            db_password: document.getElementById('db_password').value,
            db_database: document.getElementById('db_database').value
        }

        var params = {
            post: 'saveDB'
        }
        
        for (var param in dbparams) {
            params['dbconfig[' + param + ']'] = dbparams[param];
        }
        
        var request = XHR.post(base_url + 'config.php', params);
        request.addMethods(this._processDBSave.bind(this));
    },
    _processDBSave: function(json) {
        this.log(json);
        try {
            if (json.error_message) {
                alert(json.error_message);
                this.dbvalidated = false;
                this.setDBType(this.db_type);
                this.loadView('setupDB');
            }
            
        } catch (e) {
            alert("There was an error saving database settings: " + e);
            return;
        }
    },
    setDBType: function(db_type) {
        this.log("setting db_type to " + db_type);
        this.db_type = db_type;
        switch (db_type)
        {
            case 'mysql':
                var show_host_info = true;
                this.dbvalidated = false;
                break;
            case 'sqlite':
                var show_host_info = false;
                this.dbvalidated = true;
                break;
        }
        document.getElementById('db_host_info').style.display=show_host_info ? 'block' : 'none';
            
    },
    scriptDidLoad: function() {
        this._contentDidLoad('script');
    },
    contentDidLoad: function() {
        this._contentDidLoad('content');
    },
    validateView: function() {
        this.log('validate_' + this.stackController.getCurrentView().id);
        return this['validate_'+this.stackController.getCurrentView().id]();
    },
    validate_setupOptions: function() {
        var params = {
        	S: {
				SITE_TITLE: document.getElementById('admin_site_title').value
			},
			B: {
				CONTENT_PRIVATE: document.getElementById('admin_content_private').object.intValue(),
				USE_PASSWORDS: document.getElementById('admin_use_passwords').object.intValue(),
				USE_ADMIN_PASSWORDS: document.getElementById('admin_use_admin_passwords').object.intValue(),
				USE_PRESENTER_PASSWORDS: document.getElementById('admin_use_presenter_passwords').object.intValue(),
				ALLOW_SELF_CREATED_USERS: document.getElementById('admin_allow_self_created_users').object.intValue(),
				SINGLE_SESSION_MODE: document.getElementById('admin_single_session_mode').object.intValue()
			}
        }
        if (params.S.SITE_TITLE.length==0) {
            return new MobilAP.Error("Please enter a site title");
        }
        
        MobilAP.saveConfigs(params, this._processOptions.bind(this));
    },
    _processOptions: function(json) {
        try {
            if (json.error_message && json.error_code != -1) {
                alert(json.error_message);
                this.loadView('setupOptions');
            }
            
            MobilAP.saveConfigs({B: {setupcomplete:-1}});
            
            
        } catch (e) {
            alert("There was an error saving user settings: " + e);
            return;
        }
    },
    validate_setupUser: function() {
        var params = {
            post: 'addUser',
            email: document.getElementById('admin_email').value,
            md5_password: hex_md5(document.getElementById('admin_password').value),
            FirstName: document.getElementById('admin_FirstName').value,
            LastName: document.getElementById('admin_LastName').value,
            admin: -1
        }
        
        if (params.FirstName.length==0 || params.LastName.length==0) {
            return new MobilAP.Error("Name should not be blank");
        }

        if (params.email.length==0) {
            return new MobilAP.Error("Email adddress should not be blank");
        }
        
        if (document.getElementById('admin_password').value != document.getElementById('admin_verify_password').value) {
            return new MobilAP.Error('Please verify the password');
        }

        if (document.getElementById('admin_password').value.length == 0) {
            return new MobilAP.Error('Password should not be blank');
        }

        this.log(params);
        
        var request = XHR.post(base_url + 'user.php', params);
        request.addMethods(this._processUser.bind(this));
    },
    _processUser: function(json) {
        try {
            if (json.error_message && json.error_code != -1) {
                alert(json.error_message);
                this.loadView('setupUser');
            }
            
        } catch (e) {
            alert("There was an error saving user settings: " + e);
            return;
        }
    },
    validate_setupDB: function() {
        this.log('validating db');
        if (!this.dbvalidated) {
            return new MobilAP.Error('Database connection not validated');
        }
        this.saveDB();
    },
    nextView: function() {
        var result = this.validateView();
        if (this.isError(result)) {
            alert(result.error_message);
            return;
        }
        var views = this.stackController.getAllViews();
        
        if (this.viewIndex < views.length-1) {
            this.loadViewIndex(this.viewIndex+1);
        }
    },
    previousView: function() {
        var views = this.stackController.getAllViews();
        if (this.viewIndex > 0) {
            this.loadViewIndex(this.viewIndex-1);
        }
    },
    finishSetup: function() {
        var result = this.validateView();
        if (this.isError(result)) {
            alert(result.error_message);
            return;
        }
    },
    loadView: function(viewID) {
        var views = this.stackController.getAllViews();
        for (var i=0; i<views.length; i++) {
            if (viewID==views[i].id) {
                this.loadViewIndex(i);
                return;
            }
        }
    },
    loadViewIndex: function(index) {
        var views = this.stackController.getAllViews();
        this.stackController.setCurrentView(views[index].id);
        this.view = views[index].id;
        this.viewIndex = index;
        document.getElementById('setupPreviousButton').style.display = index>0 ? '' : 'none';
        document.getElementById('setupNextButton').style.display = index<(views.length-1) ? '' : 'none';
        document.getElementById('setupFinishButton').style.display = index==(views.length-1) ? '' : 'none';
    },
    _contentDidLoad: function(type) {
        this[type+'Loaded'] = true;

        if (!this.scriptLoaded || !this.contentLoaded) {
            this.log('not everything was loaded');
            return;
        }

        MobilAP.setupParts(this.partSpecs);
        this.stackController = document.getElementById('setupStack').object;
        this.loadViewIndex(0);
        new MobilAP.Switch('admin_content_private', false);
        new MobilAP.Switch('admin_use_passwords', false);
        new MobilAP.Switch('admin_use_admin_passwords', true);
        new MobilAP.Switch('admin_use_presenter_passwords', true);
        new MobilAP.Switch('admin_allow_self_created_users', false);
        new MobilAP.Switch('admin_single_session_mode', false);
    }
});

mobilAP.setupController = new MobilAP.SetupController();
mobilAP.setupController.scriptDidLoad();
MobilAP.loadContent('setup', base_url + 'setup.php', { callback: mobilAP.setupController.contentDidLoad.bind(mobilAP.setupController)});
