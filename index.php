<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Kaltura Entries & Metadata To Excel Export</title>
	<!-- Style Includes -->
	<link href="metadataStyle.css" media="screen" rel="stylesheet" type="text/css" />
	<link href="lib/jQueryUI/jquery-ui-1.8.18.custom.css" rel="stylesheet" type="text/css" />
	<!-- Script Includes -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js" type="text/javascript"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js" type="text/javascript"></script>
    <script src="lib/jquery.fileDownload/jquery.fileDownload.js" type="text/javascript"></script>
	<!-- Page Scripts -->
	<script type="text/javascript">
		var kalturaSession = "";
		var partnerId = 0;
		
		// A resonably practical implementation of RFC 5322
		// http://tools.ietf.org/html/rfc5322#section-3.4
		function validEmail(input) {
			var filter = /^[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/i;
			if(!filter.test(input.value)) {
				input.setCustomValidity("Invalid email");
				return false;
			}
			else {
				input.setCustomValidity('');
				return true;
			}
		}

		function validId(input) {
			if(isNaN(input.value) || input.value == "") {
				input.setCustomValidity("Invalid Partner ID");
				return false;
			}
			else {
				input.setCustomValidity('');
				return true;
			}
		}

		function validPassword(input) {
			if(input.value == '') {
				input.setCustomValidity("Please enter a password");
				return false;
			}
			else {
				input.setCustomValidity('');
				return true;
			}
		}
		
		//Calls metadata.php to create and download the excel file for the user's custom metadata information
		function downloadMetadata() {
			//Calls the fileDownload plugin that simulates AJAX for file downloads.
			var downloadFile = "metadata.php?session=" + kalturaSession + "&partnerId=" + partnerId;
			var $preparingFileModal = $("#preparing-file-modal");
	        $preparingFileModal.dialog({ modal: true });
	        $.fileDownload(downloadFile, {
	            successCallback: function(url) {
	                $preparingFileModal.dialog('close');
	            },
	            failCallback: function(responseHtml, url) {
	 
	                $preparingFileModal.dialog('close');
	                $("#error-modal").dialog({ modal: true });
	            }
	        });
	        return true;
		}

		//Calls categories.php to create and download the excel file for the user's category information
		function downloadCategories() {
			//Calls the fileDownload plugin that simulates AJAX for file downloads.
			var downloadFile = "categories.php?session=" + kalturaSession + "&partnerId=" + partnerId;
			var $preparingFileModal = $("#preparing-file-modal");
	        $preparingFileModal.dialog({ modal: true });
	        $.fileDownload(downloadFile, {
	            successCallback: function(url) {
	                $preparingFileModal.dialog('close');
	            },
	            failCallback: function(responseHtml, url) {
	 
	                $preparingFileModal.dialog('close');
	                $("#error-modal").dialog({ modal: true });
	            }
	        });
	        return true;
		}

		//Calls getSession.php to actually sign into the Kaltura API and generate a session key
		function loginSubmit() {
			$('#loginButton').hide();
			$('#loginLoader').show();
			$.ajax({
				type: "POST",
				url: "getSession.php",
				data: {email: $('#email').val(), partnerId: $('#partnerId').val(), password: $('#password').val()}
			}).done(function(msg) {
				$('#loginLoader').hide();
				if(msg == "loginfail") {
					alert("Invalid username/password");
					$('#loginButton').show();
				}
				else if(msg == 'idfail') {
					alert("Invalid Partner ID");
					$('#loginButton').show();
				}
				else {
					kalturaSession = msg;
					partnerId = $('#partnerId').val();
					$('#userLogin').hide();
					$('#loginButton').hide();
					$('#loginFooter').css("background", "#FEFEFE");
					$('#loginForm').animate({height: "163px"}, 400, function() {
						$('#page').slideDown();
						$('#categoryButton').slideDown();
					});
				}
			});
		}
	</script>
</head>
<body>
		<form method="post" id="loginForm" action="javascript:loginSubmit();" class="box login">
			<header style="text-align:center;">
				<label><h1 style="font-weight:bold;">Kaltura Entries & Metadata To Excel Export</h1></label>
				<p style="padding-bottom:10px;">Login to your Kaltura account, then download an excel file with all your entries & custom metadata or all categories.</p>
			</header>
			<div id="userLogin">
				<fieldset class="boxBody">
					<label>Email</label>
					<input type="text" tabindex="1" id="email" oninput="validEmail(this)" autofocus="autofocus" required>
					<label>Partner ID</label>
					<input type="text" tabindex="1" id="partnerId" oninput="validId(this)" required>
					<label>Password</label>
					<input type="password" tabindex="1" id="password" oninput="validPassword(this)" required>
				</fieldset>
			</div>
			<div id="page" class="boxBody" style="display: none;">
				<button id="metadataButton" type="button" class="metadata" onclick="downloadMetadata()">Download Metadata</button>
			</div>
			<footer id="loginFooter">
				<input type="submit" class="btnLogin" value="Login" id="loginButton" tabindex="4">
				<img src="lib/loginLoader.gif" id="loginLoader" style="display: none; margin: 9px 130px;">
				<button id="categoryButton" type="button" class="categories" onclick="downloadCategories()">Download Categories</button>
			</footer>
		</form>
	<div id="preparing-file-modal" title="Preparing report..." style="display: none;">
	    We are preparing your report, please wait...
	     
	    <!--Throw what you'd like for a progress indicator below-->
	    <div class="ui-progressbar-value ui-corner-left ui-corner-right" style="width: 100%; height:22px; margin-top: 20px;"></div>
	</div>
	 
	<div id="error-modal" title="Error" style="display: none;">
	    There was a problem generating your report, please try again.
	</div>
</body>
</html>
