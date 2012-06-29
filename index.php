<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Metadata Dump</title>
	<!-- Style Includes -->
	<link href="metadataStyle.css" media="screen" rel="stylesheet" type="text/css" />
	<link href="lib/jquery-ui-1.8.18.custom.css" rel="stylesheet" type="text/css" />
	<!-- Script Includes -->
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js" type="text/javascript"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js" type="text/javascript"></script>
    <script src="lib/jquery.fileDownload.js" type="text/javascript"></script>
	<!-- Page Scripts -->
	<script type="text/javascript">
		var kalturaSession = "";
		var partnerId = 0;
		
		//Validation written with help from http://yensdesign.com/tutorials/validateform/validation.js
		function validEmail(input) {
			var filter = /^[a-zA-Z0-9]+[a-zA-Z0-9_.-]+[a-zA-Z0-9_-]+@[a-zA-Z0-9]+[a-zA-Z0-9.-]+[a-zA-Z0-9]+.[a-z]{2,4}$/;
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

		function downloadMetadata() {
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

		function downloadCategories() {
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
		
		function loginSubmit() {
			$.ajax({
				type: "POST",
				url: "getSession.php",
				data: {email: $('#email').val(), partnerId: $('#partnerId').val(), password: $('#password').val()}
			}).done(function(msg) {
				if(msg == "loginfail")
					alert("Invalid username/password");
				else if(msg == 'idfail')
					alert("Invalid Partner ID");
				else {
					kalturaSession = msg;
					partnerId = $('#partnerId').val();
					$('#userLogin').hide();
					$('#page').show();
				}
			});
		}
	</script>
</head>
<body>
	<div id="userLogin">
		<form method="post" id="loginForm" action="javascript:loginSubmit();" class="box login">
			<header>
				<label style="margin: 2px 46px;">Welcome to the Metadata Dump Tool</label>
			</header>
			<fieldset class="boxBody">
				<label>Email</label>
				<input type="text" tabindex="1" id="email" oninput="validEmail(this)" required>
				<label>Partner ID</label>
				<input type="text" tabindex="1" id="partnerId" oninput="validId(this)" required>
				<label>Password</label>
				<input type="password" tabindex="1" id="password" oninput="validPassword(this)" required>
			</fieldset>
			<footer style=>
				<input type="submit" class="btnLogin" value="Login" id="loginButton" tabindex="4">
			</footer>
		</form>
	</div>
	<div id="page" style="display: none;">
		<div><img src="lib/loadBar.gif" style="display: none;" id="loadBar"></div>
		<div id="downloadMetadata">Download all your metadata: <button id="metadataButton" type="button" onclick="downloadMetadata()">Download</button></div>
		<div id="downloadCategories">Download all your categories: <button id="categoryButton" type="button" onclick="downloadCategories()">Download</button></div>
	</div>
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