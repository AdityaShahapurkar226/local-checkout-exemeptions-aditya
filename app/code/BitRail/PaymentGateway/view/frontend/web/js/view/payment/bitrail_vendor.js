/**
BitRail object in global namespace
	init(	client_credentials,       // Vendor's JWT
			options                   // Optional settings
				   api_url:              override URL to API
				   parent_element:       parent element to hold frames or an iframe
				   target                ignored if parent_element is set. target for order window, one of 
				                            iframe (default)
				                            popup (a new window popup over this)
				                            _blank (a new full window)
				                            <target id> The name of a valid window.open target
				   order_frame_url:      URL of order frame
				
	)
	order(	order_token,              // Unique, non-deterministic, sufficiently hard to guess token matching regex [a-zA-Z0-9_-]{10:200} 
	                                  // This token will be returned to you encrypted with your secret to verify order
			destination_vault_handle, // The vault handle where the funds are to be sent
			total_amount,             // Total amount to send
			currency,                 // Currency to transact in. USD or FC
			attributes,               // Key:value pairs of information to attach to the order
			callback                  // Callback takes response object
			                                callback(response) where response has
			                                     status: successful | failed | cancelled
			                                     dest_vault_handle: the handle passed in above
			                                     fc_transaction_id: the BitRail transaction created ,
			                                     verification_token: The verification token is the encrypted order_token joined with a random
			                                         and the key is the shared secret the vendor obtained during onboarding
			                                         AES256("order_token;random", secret)
	)
	login(  username,                 // Not yet completed
	        password, 
	        success, 
	        failure
	)
	retryLoginWithMobile(            // Not yet completed
	        access_token,
	        mobile) {
**/

(function(){

	var tempIframes = {};
	var messageHandlers = {};
	var clientOptions = {
		domain: window.checkoutConfig.payment.bitrail_gateway.apiUrl,
		client_credentials: {jwt: null},
		parent_element: null,
		login_success: null,
		login_fail: null,
	}
	var order_frame_url = null;
	var getOrderFrameUrl = function(){
		if (order_frame_url)
			return order_frame_url;
		return clientOptions.domain+"orders/form?code="+clientOptions.client_credentials.jwt;
	}
	var create_agreement_frame_url = null;
	var getCreateAgreementFrameUrl = function(){
		if (create_agreement_frame_url)
			return create_agreement_frame_url;
		return clientOptions.domain+"agreements/form?code="+clientOptions.client_credentials.jwt;
	}

	if (!window.addEventListener) {
		console.log("This environment does not support event listeners");
		return;
	}
	if (!window.document || !window.document.createElement) {
		console.log("This environment does not support DOM operations.");
		return;
	}
	window.addEventListener("message", function(evt){
		var data = ""+evt.data;
		if (data.indexOf("fc.evt:")===0) {
			/*Might be our message*/
			var sections = data.split("|");
			if (sections.length > 1) {
				var header = sections.shift().split(":");
				var body = sections.join("|");
				if (header.length >= 3) {
					/*Looks like our message*/
					if (messageHandlers[header[2]]) {
						messageHandlers[header[2]](body, JSON.parse(body));
						delete messageHandlers[header[2]];
					}
					if (header[1]=="iframe" && header[2]!="") {
						if (tempIframes[header[2]]) {
							if (tempIframes[header[2]] != clientOptions.parent_element)
								tempIframes[header[2]].remove();
							delete header[2];
						}
					}

				}
			}
		}
	});

	/** Only use double-quote to simplify minification storage in a var */
	function _wwwEncodeParams(params) {
		if (params === Object(params) && Object.prototype.toString.call(params) !== "[object Array]") {
			var p = "";
			for (var prop in params) { if (params.hasOwnProperty(prop)) {
				if (p!="") p = p+"&";
				if (prop)
					p = p+ encodeURIComponent(prop) +"="+ encodeURIComponent(params[prop]);
			}}
			params = p;
		}
		if (params=="")
			params = null;
		return params;
	}

	/** Only use double-quote to simplify minification storage in a var */
	function _doAjax(method, url, params, jwt, success, failure) {
		//return _doCrossOriginAjax(method, url, params, jwt, success, failure);
		method = method.toUpperCase();
		params = _wwwEncodeParams(params)
		if (method=="GET" && params){
			if (url.indexOf("?")<0) url+="?";
			url = url+"&"+params;
			params=null;
		}
		var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
		xhr.open(method, url);
		xhr.onreadystatechange = function() {
			if (xhr.readyState>3 && xhr.status>=200 && xhr.status<300) success(xhr, xhr.responseText);
			if (xhr.readyState>3 && xhr.status>=300 && failure) failure(xhr, xhr.responseText);
		};
		if (jwt) {
			xhr.setRequestHeader("Authorization", "Bearer "+jwt);
		}
		if (method!="GET")
			xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
		xhr.send(params);
		return xhr;
	}
	var _minifiedJs = 
		'function _wwwEncodeParams(e){if(e===Object(e)&&"[object Array]"!==Object.prototype.toString.call(e)){var t="";for(var n in e)e.hasOwnProperty(n)&&(""!=t&&(t+="&"),n&&(t=t+encodeURIComponent(n)+"="+encodeURIComponent(e[n])));e=t}return""==e&&(e=null),e}'+
		'function _doAjax(e,t,n,o,r,a){e=e.toUpperCase(),n=_wwwEncodeParams(n),"GET"==e&&n&&(t.indexOf("?")<0&&(t+="?"),t=t+"&"+n,n=null);var s=window.XMLHttpRequest?new XMLHttpRequest:new ActiveXObject("Microsoft.XMLHTTP");return s.open(e,t),s.onreadystatechange=function(){3<s.readyState&&200<=s.status&&s.status<300&&r(s,s.responseText),3<s.readyState&&300<=s.status&&a&&a(s,s.responseText)},o&&s.setRequestHeader("Authorization","Bearer "+o),"GET"!=e&&s.setRequestHeader("Content-Type","application/x-www-form-urlencoded"),s.setRequestHeader("X-Requested-With","XMLHttpRequest"),s.send(n),s}';
	var _callbackJs = 
		'function _cb(xhr,d){window.parent.postMessage("fc.evt:thisframetype:thisframeid|"+d,"*")};'+
		'function _s(xhr,d){_cb(xhr,d)};'+
		'function _f(xhr,d){_cb("{success:false,errors:[{code:"+xhr.status+",message:\""+xhr.responseText+"\"}]}")}';

	function _doCrossOriginAjax(method, url, params, jwt, success, failure) {
		var frameid = 'genajax-'+Date.now();
		var iframe = document.createElement('iframe');
		tempIframes[frameid] = iframe;
		messageHandlers[frameid] = function(txt, data){
			if (data && data.success) {
				success(data);
			} else {
				failure(txt, data);
			}
		}
		var html =  '<body><'+'script>'+_minifiedJs + _callbackJs.replace("thisframetype", "iframe").replace("thisframeid", frameid) +
					'_doAjax("'+method+'","'+url+'","'+_wwwEncodeParams(params)+'","'+jwt+'",_cb,_cb);'+
					'</'+'script>Waiting for authorization response.</body>';
		iframe.id = frameid;
		iframe.src = 'data:text/html;charset=utf-8,' + encodeURI(html);
		document.body.appendChild(iframe);
	}

	function _doAgreementCreate(agreement_token, dest_vault_handle, freq, amount, currency, description, callback) {
		if (!callback) {
			//assume no description
			callback = description;
			description = null;
		}
		description = (description||"").replace('"', "'");

		_doFrame(getCreateAgreementFrameUrl(), "fc.create_agreement",
			'{"agreement_token":"'+agreement_token+
			'", "dest_vault_handle":"'+dest_vault_handle+
			'", "frequency":"'+freq+
			'", "amount":"'+amount+
			'", "currency":"'+currency+
			'", "description":"'+description+
			'"}', callback);
	}

	function _doOrderFrame(order_token, dest_vault_handle, amount, currency, description, attributes, callback) {
		if (!callback) {
			//assume no description
			callback = attributes;
			attributes = description;
			description = null;
		}
		description = (description||"").replace('"', "'");

		_doFrame(getOrderFrameUrl(), "fc.start_order",
			'{"jwt":"'+clientOptions.client_credentials.jwt+
			'","order_token":"'+order_token+
			'", "dest_vault_handle":"'+dest_vault_handle+
			'", "amount":"'+amount+
			'", "currency":"'+currency+
			'", "description":"'+description+
			'", "attributes":'+JSON.stringify(attributes)+
			'}', callback);
	}

	function _doFrame(frame_url, event_type, event_text, callback) {
		var frameid, iframe;
		if (clientOptions.target!="iframe" && !clientOptions.parent_element) {
			frameid = 'fcmodal-'+Date.now();
			// TODO: make window
		} else {
			frameid = 'fcgenajax-'+Date.now();
			if (!clientOptions.parent_element || clientOptions.parent_element.nodeName!="IFRAME")
				iframe = document.createElement('iframe');
			else
				iframe = clientOptions.parent_element;
			
			iframe.width="100%";
			iframe.height="100%";
			iframe.setAttribute("style", "width:100%;height:100%;min-height:575px");

			if (!iframe.id)
				iframe.id = frameid;
			else
				frameid = iframe.id;
			tempIframes[frameid] = iframe;
			iframe.src = frame_url;
			if (!clientOptions.parent_element || clientOptions.parent_element.nodeName!="IFRAME")
				document.body.appendChild(iframe);

			iframe.onload=function(){
				setTimeout(function(){
					iframe.contentWindow.postMessage(
						"fc.evt:"+event_type+":"+frameid+"|"+event_text,"*");
				}, 1000);
			}
		}
		messageHandlers[frameid] = function(txt, data){
			if (data) {
				callback(data);
			} else {
				callback(txt);
			}
		}
	}


	function _info(access_token, callback) {
		var delay = 3000; // 3 seconds
		var cnt = 3*60 * 1000 / delay; // 3 minutes div delay
		var loop = function(){
			if (cnt-- <= 0) {
				_fail();
				return;
			}
			_doAjax("GET", clientOptions.domain+"auth/info", null, access_token,
				function(data){
					data = JSON.parse(data.response);
			 		if (data.success && data[0] && data.user_id) {
						if (callback) 
							callback(access_token, data);
			 		} else {
			 			window.setTimeout(loop, delay);
			 		}
			 }, function(xhr,_,status){
				_fail();
			 });
		};
		window.setTimeout(loop, delay);
	}

	function _tfaAuth(link, access_token) {
		_doCrossOriginAjax("GET", link, null, null, 
			function(d){}, function(d){
				_fail()
			});
	}
	function _tfaLink(access_token, mobile) {
		if (!mobile) mobile = "";
		_doAjax("POST", clientOptions.domain+"auth/link", {mobile: mobile}, access_token, 
			function(data){
				_info(access_token, clientOptions.login_success);
			}, function(xhr,d){
				_fail(d);
		});
	}

	var _fail = function(xhr,txt){
		if (!clientOptions.login_fail) return;
		var d=JSON.parse(txt);
		var e = ""+xhr.status;
		if (d && d.errors && d.errors[0]) {
			e = e+": "+d.errors[0].status;
			if (d.errors[0].detail) e = e+ ", "+d.errors[0].detail;
		}
		clientOptions.login_fail(e, d);
	};
	var _handleState = function(data){
		var state = data.data[0].state || "fail";
		switch (state) {
			case "user": 
				// we are logged in
				if(clientOptions.login_success) clientOptions.login_success(data.data[0].access_token);
				break;
			case "fail":
				// we won't login
				_fail(state);
				break;
			case "tfa_code":
				_fail(state);
				break;
			case "tfa_link":
				_tfaLink(data.data[0].access_token)
				break;
			case "tfa_auth":
				_tfaAuth(data.data[0].tfa_auth, data.data[0].access_token)
		}
	};
	var _parseClientCredentials = function(client_credentials){
		var clientId=null;
		var clientSecret=null;
		var jwt=null;
		if (typeof client_credentials == "string") {
			jwt = client_credentials;
		} else if (Array.isArray(client_credentials) && client_credentials.length == 2) {
			clientId = client_credentials[0];
			clientSecret = client_credentials[1];
		} else if (client_credentials === Object(client_credentials) && Object.prototype.toString.call(client_credentials) !== "[object Array]") {
			clientId = client_credentials.client_id;
			clientSecret = client_credentials.client_secret;
			jwt = client_credentials.jwt;
		}
		return {client_id: clientId,
				client_secret: clientSecret,
				jwt: jwt};
	};

	var login = function(client_credentials, username, password, success, fail){
		client_credentials = _parseClientCredentials(client_credentials);
		var params = {
			username: username,
			password: password,
			grant_type: "password",
			scope: "everything"
		};
		if (client_credentials.client_id) {
			params.client_id = client_credentials.client_id;
			params.client_secret = client_credentials.client_secret;
		}
		_doAjax("POST", clientOptions.domain+"auth/token", params, client_credentials.jwt, 
			function(xhr, txt){
				var d=JSON.parse(txt);
				if (!d || !d.success || !d.data || !d.data[0]) {
					_fail(xhr,txt); 
				} else {
					_handleState(d);
				}
			}, _fail);
	};

	window.BitRail = {
		init: function(client_credentials, opts){
			clientOptions.client_credentials = _parseClientCredentials(client_credentials);
			if (opts) {
				if (opts.parent_element && typeof opts.parent_element != "undefined") clientOptions.parent_element = opts.parent_element;
				if (opts.target) clientOptions.target = opts.target;
				if (opts.order_frame_url) order_frame_url = opts.order_frame_url;
				if (opts.create_agreement_frame_url) create_agreement_frame_url = opts.create_agreement_frame_url;
				if (opts.api_url) clientOptions.domain = opts.api_url;
			}
		},
		login: function(username, password, success, failure){
			if (typeof success != "undefined") clientOptions.login_success = success;
			if (typeof failure != "undefined") clientOptions.login_fail = failure;
			login(clientOptions.client_credentials, username, password);
		},
		retryLoginWithMobile: function(access_token, mobile) {
			_tfaLink(access_token, mobile)
		},
		order: function(orderToken, dest_vault_handle, amount, currency, description, attributes, callback){
			_doOrderFrame(orderToken, dest_vault_handle, amount, currency, description, attributes, callback);
		},
		agreements: {
			create: function(agreement_token, dest_vault_handle, frequency, amount, currency, description, callback){
				_doAgreementCreate(agreement_token, dest_vault_handle, frequency, amount, currency, description, callback);
			},
		},
		ajax: _doAjax,
		makeApiUrl: function(fragment) { return clientOptions.domain + fragment; }
	};
})();
