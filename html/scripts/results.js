var _GET = { length: 0};
var baseUrl = '';

function processGetVars()
{
	var qs = window.location.search;
	var re, vars;
	if (re = qs.match(/^\?(.*)/)) {
		vars = re[1].split('&');
		for (var i=0; i<vars.length; i++) {
			var x = vars[i].split('=');
			_GET[x[0]] = unescape(x[1]);
			_GET.length++;
		}
	}
}

function getXMLHttpRequest() {
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
}

function loadURL(url, callback, options) {
    	
        var xhr = getXMLHttpRequest();
        
        //options are basically method and params to handle POST requests
        if (typeof options=='undefined') {
        	options = { method: 'GET', params: '', synchronous: false}
        }

        options.method = options.method ? options.method : 'GET';
        options.params = options.params ? options.params : '';
        options.asynchronous = options.synchronous ? options.synchronous : false;

        console.log('loading (' + options.method + '/' + (options.synchronous ? 'S' : 'A') +'): ' + url + ' (' + options.params + ')');

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
            
}

function processQuestion(xhr)
{
	try {
		var result = eval("(" + xhr.responseText + ")");
		if (result.error_message) {
			alert(result.error_message);
		} else {
			question.setQuestion(result);
		}
		
		
	} catch (e) {
	}
}

function load()
{	
	CreateList("question_answers", { dataSourceName: "question",  useDataSource: true, "listStyle": "List.ROUNDED_RECTANGLE" }).reloadData();			
	processGetVars();
	if (_GET.question_id) {
		question.question_id = _GET.question_id;
		question.refreshQuestion();
		setInterval(question.refreshQuestion, 5000);

	}
}

var question = {
	responses: [],
	answers: [],
	refreshQuestion: function() {
		var url = 'js.php?get=question&question_id=' + question.question_id;
        loadURL(url, processQuestion);
	},
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
    setQuestionText: function(text)
    {
        this.question_text = text;
        document.getElementById('question_text').innerHTML = this.question_text;
    },
    setAnswers: function(answers)
    {
        this.answers = answers;
        document.getElementById('question_answers').object.reloadData();
        document.getElementById('question_response_total').innerHTML=this.answers.total+ " responses";
    },
    setResponses: function(responses)
    {
        this.responses = responses;
        this.rowElements = [];
//        document.getElementById('question_responses').object.reloadData();
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
        
        //go through the responses, for pie charts, don't include responses with zero answers
        //max_data value represents the highest value and is used to scale the bar charts
        for (var i=0; i<this.responses.length; i++) {
            if (this.answers[this.responses[i].response_value]>0 || add_zero) {
                data.push(this.answers[this.responses[i].response_value]);
                if (this.answers[this.responses[i].response_value] > max_data) {
                    max_data = this.answers[this.responses[i].response_value];
                }
                
                labels.push(escape(this.responses[i].response_text));
            }
        }

		// base url with type, size and background
        var src = 'http://chart.apis.google.com/chart?cht=' + this.chart_type + '&chf=bg,s,00000000';

		// add the data using text encoding
		src +='&chd=t:'+data.join(",");
        
        //to make the legends easier on bar charts, use the next even number as 100%
        var even_total = this.answers.total % 2 ? (this.answers.total+1) : this.answers.total;
        var even_max = max_data % 2 ? (max_data+1) : max_data;
        
        switch (this.chart_type)
        {
            case 'p':
                src +='&chs=800x200';
                src +='&chl=' + labels.join("|");
                break;
            
            case 'bhs':
                src +='&chs=560x' + (this.responses.length*40);
                src +='&chxt=x,y';
                src +='&chds=0,' + even_max;
                labels.reverse();
                var range=[];
                for (i=0; i<=even_max; i+=2) {
                    range.push(i);
                }               
                                                
                src +='&chxl=0:|' + range.join("|") + '|1:|' + labels.join("|");
                break;
        }
        
        return src;
    },
    
	numberOfRows: function() {
		return this.responses.length;
	},
	
	prepareRow: function(rowElement, rowIndex, templateElements) {
        templateElements.question_response_text_label.innerHTML = this.responses[rowIndex].response_text;
        templateElements.question_response_count.innerHTML = this.answers[this.responses[rowIndex].response_value];

	}
    
}
window.onload = load;