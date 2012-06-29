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

		$(document).ready(function() {
			$('#emailCheck').hide();
			$('#idCheck').hide();
			$('#passwordCheck').hide();
			$('#email').blur(validEmail);
			$('#partnerId').blur(validId);
			$('#password').blur(validPassword);
			$('#email').keyup(validEmail);
			$('#partnerId').keyup(validId);
			$('#loginForm').submit(function() {
				if(validEmail() & validId() & validPassword())
					return true;
				else
					return false;
			});
		});
		
		//Validation written with help from http://yensdesign.com/tutorials/validateform/validation.js
		function validEmail() {
			var a = $("#email").val();
			var filter = /^[a-zA-Z0-9]+[a-zA-Z0-9_.-]+[a-zA-Z0-9_-]+@[a-zA-Z0-9]+[a-zA-Z0-9.-]+[a-zA-Z0-9]+.[a-z]{2,4}$/;
			if(!filter.test(a)) {
				$('#emailCheck').show();
				return false;
			}
			else {
				$('#emailCheck').hide();
				return true;
			}
		}

		function validId() {
			if(isNaN($('#partnerId').val()) || $('#partnerId').val() == "") {
				$('#idCheck').show();
				return false;
			}
			else {
				$('#idCheck').hide();
				return true;
			}
		}

		function validPassword() {
			if($('#password').val() == '') {
				$('#passwordCheck').show();
				return false;
			}
			else {
				$('#passwordCheck').hide();
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
		<form method="post" id="loginForm" action="javascript:loginSubmit();">
			<div id="emailDiv">
				Email: <input type="text" id="email" autofocus="autofocus" />
				<span id="emailCheck">Invalid email</span>
			</div>
			<div id="idDiv">
				Partner ID: <input type="text" id="partnerId"></input>
				<span id="idCheck">Invalid Partner ID</span>
			</div>
			<div id="passwordDiv">
				Password: <input type="password" id="password"></input>
				<span id="passwordCheck">Enter a password!</span>
			</div>
			<div>
				<input type="submit" value="Sign in" id="loginButton" />
			</div>
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