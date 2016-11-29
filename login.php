<?include("FrontEnd/head.php");?>
<?include("FrontEnd/header.php");?>

	<script src="//www.google.com/recaptcha/api.js?render=explicit&onload=vcRecaptchaApiLoaded" async defer></script>
	<style>
		.form-control {
		    display: block !important;
		    width: 100% !important;
		    height: 34px !important;
		    padding: 6px 12px !important;
		    font-size: 14px !important;
		    line-height: 1.42857143 !important;
		    color: #555 !important;
		    background-color: #fff !important;
		    background-image: none !important;
		    border: 1px solid #ccc !important;
		    border-radius: 4px !important;
		    -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075) !important;
		    box-shadow: inset 0 1px 1px rgba(0,0,0,.075) !important;
		    -webkit-transition: border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s !important;
		    -o-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s !important;
		    transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s !important;
		}

		.forgot_password_link:hover {
			cursor:pointer;
		}
	</style>
	<script>
	var app = angular.module('myApp', ['ngSanitize','ngMessages','vcRecaptcha']);

	app.controller('loginController', ['$scope', '$location','$window', '$http', 'vcRecaptchaService', function($scope, $location, $window, $http, vcRecaptchaService) {
		//alert(reCAPTCHA);
		//reCAPTCHA.setPublicKey('6LerrSMTAAAAABVpbmfo_eLvTA6GbIMWEEb3kF-s');
		

		var vm = this;
		//alert($location.url());
		//alert($window.location);
		var current_location = String($window.location);
		//alert(current_location);
		//alert('in here');
		console.info($window.location.hostname);

		var site_origin = $window.location.protocol + '//' + $window.location.host;
		if ($window.location.hostname == "thebitcoinbeast.com") {
			//site_origin = "https:" + '//' + $window.location.host;
		}

		console.info(site_origin);
		vm.t = {};

		//alert(vcRecaptchaService);

		$scope.form_data = {};
		$scope.phone_prefix = "";
		$scope.phone = "";
		$scope.country = "";
		$scope.verify_otp = "";
		$scope.is_duplicate_phone_number = false;
		$scope.complete_phone_number = "";
		$scope.btc_address = "";
		$scope.get_otp_retrieve_login = "";
		$scope.otp_code = "";
		$scope.captcha_widget_id = null;
		$scope.otp_pop_up_url = 'FrontEnd/get_otp_pop_up.html';
		$scope.is_valid_btc_address = false;

		$scope.is_show_sign_in_form = true;
		$scope.show_get_otp_pop_up = false;
		$scope.is_show_first_time_signup_form = false;
		$scope.is_show_verify_otp_form = false;

		/*
		$scope.is_show_sign_in_form = false;
		$scope.show_get_otp_pop_up = false;
		$scope.is_show_first_time_signup_form = true;
		$scope.is_show_verify_otp_form = false;
		*/


		/*
		$scope.is_show_sign_in_form = false;
		$scope.is_show_first_time_signup_form = true;
		*/

		$scope.know_your_otp_click = function() {
			$scope.show_get_otp_pop_up = true;
			$scope.otp_pop_up_url = 'FrontEnd/get_otp_pop_up.html';
			//alert($scope.captcha.response);
		};

		vm.forgot_password_click = function() {
			$scope.otp_pop_up_url = 'FrontEnd/forgot_password.html';
			$scope.show_get_otp_pop_up = true;
			//alert($scope.captcha.response);
		};

		$scope.password_reset_click = function($form) {
			//alert('form is ');
			//alert($form);
			//alert($scope.form_data.get_otp_retrieve_login);
			//alert($form.get_otp_retrieve_login);
			//alert($form.get_otp_retrieve_login.value);

			var obj_data = {};
			obj_data.login = $scope.form_data.reset_password_for_login;
			console.info($scope.form_data.reset_password_for_login);

			//alert($scope.get_otp_retrieve_login);

			$http({
				method: 'POST',
				url: site_origin + '/platform/password_reset/reset_password_for_login/',
				data: {login:$scope.form_data.reset_password_for_login}
			}).success(function (result) {
				//alert(result);
				//$scope.otp_code = result.otp_code;
				//$scope.form_data.get_otp_retrieve_login = '';
				$scope.otp_pop_up_url = 'FrontEnd/forgot_password_result.html';
			});
		};

		$scope.check_btc_address = function() {
			if ($scope.btc_address != "") {
				var obj_data = {};

				// have to check if not already in system
				obj_data.btc_address = $scope.btc_address;
				$http({
					method: 'POST',
					url: site_origin + '/platform/login/check_btc_address/',
					data: {data:obj_data}
				}).success(function (result) {
					if (result.is_valid) {
						//alert('valid address');
						$scope.is_valid_btc_address = true;
					} else {
						$scope.is_valid_btc_address = false;
					}
					//alert(result);
				});
			}
		};

		$scope.get_my_otp_click = function($form) {
			//alert('form is ');
			//alert($form);
			//alert($scope.form_data.get_otp_retrieve_login);
			//alert($form.get_otp_retrieve_login);
			//alert($form.get_otp_retrieve_login.value);

			var obj_data = {};
			obj_data.login = $scope.form_data.get_otp_retrieve_login;

			//alert($scope.get_otp_retrieve_login);

			$http({
				method: 'POST',
				url: site_origin + '/platform/login/reset_otp_code/',
				data: {data:obj_data}
			}).success(function (result) {
				//alert(result);
				$scope.otp_code = result.otp_code;
				//$scope.form_data.get_otp_retrieve_login = '';
				$scope.otp_pop_up_url = 'FrontEnd/get_otp_pop_up_result.html';
			});
		};

		$scope.check_phone_number = function() {
			if ($scope.phone != "") {
				var obj_data = {};

				$scope.complete_phone_number = $scope.transform_phone_number($scope.phone_prefix, $scope.phone);
				obj_data.phone_number = $scope.complete_phone_number;
				obj_data.login = $scope.login;
				$http({
					method: 'POST',
					url: site_origin + '/platform/login/check_phone_number/',
					data: {data:obj_data}
				}).success(function (result) {
					if (result.is_duplicate_phone_number) {
						//alert('valid address');
						$scope.is_duplicate_phone_number = true;
					} else {
						$scope.is_duplicate_phone_number = false;
					}
				});
			}
		};

		$scope.transform_phone_number = function(prefix, phone_number) {
			var temp_prefix = '';
			var temp_phone_number = '';
			temp_prefix = prefix.replace("+", "");
			temp_prefix = temp_prefix.replace("-", "");
			temp_prefix = temp_prefix.replace(" ", "");

			temp_phone_number = phone_number.replace("+", "");
			temp_phone_number = temp_phone_number.replace("-", "");
			temp_phone_number = temp_phone_number.replace(" ", "");
			var temp = '';
			if (temp_prefix != "1") {
				temp = temp_prefix + temp_phone_number;
			} else {
				temp = temp_phone_number;
			}
			return temp;
		}

		
		vm.t.is_submit_verify_otp_error_message = false;
		vm.t.submit_verify_otp_error_message = "";

		// second page
		$scope.first_time_login_submit = function() {
			var obj_data = {};
			
			var obj_data = {};
			//alert($scope.captcha);

			obj_data.captcha = "";
			obj_data.login = $scope.login;
			obj_data.password = $scope.password;
			obj_data.otp = $scope.verify_otp;
			obj_data.country = $scope.country;
			//alert(obj_data.otp);

			
			$http({
				method: 'POST',
				url: site_origin + '/platform/login/authenticate/',
				data: {data:obj_data}
			}).success(function (result) {
				//alert(result);
				if (result.is_verify_opt_timeout) {
					//$window.location = '/login.php';
					$scope.is_show_sign_in_form = false;
					$scope.is_show_verify_otp_form = false;
					$scope.is_show_first_time_signup_form = true;

					vm.t.is_submit_signup_form_error_message = true;
					vm.t.submit_signup_form_error_message = "OTP Timeout. Please retry.";

					vm.t.is_submit_verify_otp_error_message = false;
					
				} else if (result.is_auth_success && result.is_captcha_success) {
					vm.t.is_submit_verify_otp_error_message = false;
					$window.location = site_origin + '/platform/';
				} else {
					if (!result.is_valid_otp_code) {
						vm.t.is_submit_verify_otp_error_message = true;
						vm.t.submit_verify_otp_error_message = "Invalid OTP Code";
					}
				}
			});
		};

		vm.t.submit_signup_form_error_message = "";
		vm.t.is_submit_signup_form_error_message = false;

		$scope.first_time_signin_submit = function() {
			var obj_data = {};
			
			vm.t.is_submit_signup_form_error_message = false;
			vm.t.submit_signup_form_error_message = "";
			
			//alert('in here');
			//alert($scope.phone_prefix);
			$scope.complete_phone_number = $scope.transform_phone_number($scope.phone_prefix, $scope.phone);
			if ($scope.btc_address != "" && $scope.complete_phone_number != "") {
				var obj_data = {};

				obj_data.btc_address = $scope.btc_address;
				obj_data.phone_number = $scope.complete_phone_number;
				obj_data.login = $scope.login;
				//obj_data.login = "kokjok@s.com";
				$http({
					method: 'POST',
					url: site_origin + '/platform/login/check_first_time_signin_form/',
					data: {data:obj_data}
				}).success(function (result) {
					if (result.is_success) {
						vm.t.is_submit_signup_form_error_message = false;
						$scope.is_show_verify_otp_form = true;
						$scope.is_show_first_time_signup_form = false;
					} else {
						if (result.is_duplicate_info_found) {
							vm.t.is_submit_signup_form_error_message = true;
							vm.t.submit_signup_form_error_message = "Duplicate Information Found. Please provide a different set of information";
						}
					}
				});
			}
		};

		vm.is_verifying_login = false;
		vm.is_invalid_login = false;

		$scope.submit = function() {
			//alert('in here');
			//alert($scope.captcha.response);

			var obj_data = {};
			//alert($scope.captcha);

			obj_data.captcha = $scope.captcha;
			obj_data.login = $scope.login;
			obj_data.password = $scope.password;
			obj_data.otp = $scope.otp;
			obj_data.country = $scope.country;
			vm.is_verifying_login = true;
			vm.is_invalid_login = false;
			//alert(obj_data.otp);

			
			$http({
				method: 'POST',
				url: site_origin + '/platform/login/authenticate/',
				data: {data:obj_data}
			}).success(function (result) {
				//alert(result);
				if (result.is_auth_success && result.is_captcha_success) {
					if (result.is_first_time_login) {
						$scope.is_show_sign_in_form = false;
						$scope.is_show_first_time_signup_form = true;
						vm.is_verifying_login = false;
						vm.is_invalid_login = false;
					} else {
						vm.is_verifying_login = false;
						vm.is_invalid_login = false;
						$window.location = '/platform/';
					}
				} else {
					vm.is_verifying_login = false;
					vm.is_invalid_login = true;
				}
				vcRecaptchaService.reload($scope.captcha_widget_id);
			});

		};

		$scope.goto_login_click = function() {
			$scope.show_get_otp_pop_up = false;
			//alert('in here');
			//alert($scope.captcha.response);
		};

		$scope.set_captcha_widget_id = function (widgetId) {
                    console.info('Created widget ID: %s', widgetId);
                    $scope.captcha_wiget_id = widgetId;
                };

		$scope.cbExpiration = function() {
                    console.info('Captcha expired. Resetting response object');
                    vcRecaptchaService.reload($scope.captcha_widget_id);
		};


		$('.support-section').css('display','');
	}]);

	/*
	app.config(function (reCAPTCHAProvider) {
	       // required, please use your own key :)
	       reCAPTCHAProvider.setPublicKey('6LerrSMTAAAAABVpbmfo_eLvTA6GbIMWEEb3kF-s');
		//reCAPTCHA.setPublicKey('6LerrSMTAAAAABVpbmfo_eLvTA6GbIMWEEb3kF-s');
	       // optional
	       reCAPTCHAProvider.setOptions({
		   theme: 'clean'
	       });
	});
	*/


	</script>


<section ng-app="myApp" class="support-section" style="display:none;background-image:url(FrontEnd/images/secure.jpg); background-size:cover; background-repeat:no-repeat;">

	<div ng-controller="loginController as login_controller">
		<div class="flex_container" ng-show="is_show_verify_otp_form">
			<div>
				<div style="width:500px; margin-bottom:20px;">
					<div class="max-width:100%" style="padding:10px;border: 3px solid white;
					    margin-top: 10px;
					    margin-bottom: 10px;
					    background-color: rgba(26, 188, 156, 0.18);
					    border-radius: 10px;
						overflow:hidden">
					Welcome to Bit Mutual Help. Please Verify Your OTP Code<br>
					</div>
				</div>
				<div style="width:500px;">
					<div class="max-width:100%" style="padding:10px;border: 3px solid white;
	    margin-top: 10px;
	    margin-bottom: 10px;
	    background-color: rgba(26, 188, 156, 0.18);
	    border-radius: 10px;
		overflow:hidden">
						<h3 class="sign" style="padding-bottom:20px;">
							<div class="col-xs-6 col-ms-6 cols-sm-6">
								<img height="70" src="<?=$t->site_url?>/FrontEnd/images/logo-old2.png" border="0">
							</div>
							<div class="col-xs-6 col-ms-6 cols-sm-6">
								<img height="70" ng-src="/FrontEnd/images/logo.png">
							</div>
						</h3>

						<form name="verify_otp_form" class="form-horizontal" role="form" >
							<div class="form-group">
								<div ng-show="login_controller.t.is_submit_verify_otp_error_message" style="color:#ff0000; font-weight:900;">
									{{login_controller.t.submit_verify_otp_error_message}}
								</div>
								<div ng-show="!login_controller.t.is_submit_verify_otp_error_message" style="" class="alert alert-success" role="alert">
								</div>

							</div>
							<div class="form-group">
								<label class="control-label col-sm-2"  style="text-align:left;">OTP</label>
								<div class="col-sm-10">
									<input ng-model="verify_otp" type="text" name="verify_otp" class="form-control login-field" placeholder="OTP Code">
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-2"  style="margin-top:-8px !important; text-align:left;">Member Name</label>
								<div class="col-sm-10" style="line-height:35px;">
									{{login}}
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-2"  style="margin-top:-8px !important; text-align:left;">BTC Address</label>
								<div class="col-sm-10" style="line-height:35px;">
									{{btc_address}}
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-2"  style="margin-top:-8px !important; text-align:left;">Mobile No</label>
								<div class="col-sm-10" style="line-height:35px;">
									{{complete_phone_number}}
								</div>
							</div>
							<button ng-click="first_time_login_submit()" class="btn btn-primary">LOGIN</button>
						</form>
					</div>
				</div>
			</div>
		</div>
		<div class="flex_container" ng-show="is_show_first_time_signup_form">
			<div>
				<div style="width:500px; margin-bottom:20px;">
					<div class="max-width:100%" style="padding:10px;border: 3px solid white;
					    margin-top: 10px;
					    margin-bottom: 10px;
					    background-color: rgba(26, 188, 156, 0.18);
					    border-radius: 10px;
						overflow:hidden">
					Welcome to Bit Mutual Help. Please Complete Your First Time Login Session.<br>
					Please Fill Out The Below Information Correctly To Get OTP Code.
					</div>
				</div>
				<div style="width:500px;">
					<div class="max-width:100%" style="padding:10px;border: 3px solid white;
	    margin-top: 10px;
	    margin-bottom: 10px;
	    background-color: rgba(26, 188, 156, 0.18);
	    border-radius: 10px;
		overflow:hidden">
						<h3 class="sign" style="padding-bottom:20px;">
							<div class="col-xs-6 col-ms-6 cols-sm-6">
								<img height="70" src="<?=$t->site_url?>/FrontEnd/images/logo-old2.png" border="0">
							</div>
							<div class="col-xs-6 col-ms-6 cols-sm-6">
								<img height="70" ng-src="/FrontEnd/images/logo.png">
							</div>
						</h3>

						<form name="first_time_sign_in_form" class="form-horizontal" role="form" >
							<div class="form-group">
								<div ng-show="login_controller.t.is_submit_signup_form_error_message" style="padding-top:10px; padding-left:10px; color:#FF0000; font-weight:900;">
									<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
									<span class="sr-only">Error:</span>
									{{login_controller.t.submit_signup_form_error_message}}
								</div>
								<div ng-show="login_controller.t.is_submit_verify_otp_error_message" style="" class="alert alert-success" role="alert">
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-2"  style="margin-top:-8px !important; text-align:left;">Username</label>
								<div class="col-sm-10">
									{{login}}
									<!--
									<input ng-model="login" type="text" name="login" class="form-control login-field" auto-focus placeholder="Login">
									-->
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-2" style="margin-top:-10px !important; text-align:left;" for="btc_password" style="text-align:left;">BTC Address</label>
								<div class="col-sm-10">
									<input ng-model="btc_address"  name="btc_address" type="text" class="form-control" placeholder="BTC Address" ng-change="check_btc_address()">
									<div ng-show="is_valid_btc_address" style="font-weight:900; color: green;">Valid Address</div>
									<div ng-show="!is_valid_btc_address && first_time_sign_in_form.btc_address.$dirty" style="font-weight:900; color: red;">Invalid Address</div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-2"  style="margin-top:-8px !important; text-align:left;">Country</label>
								<div class="col-sm-10">
									{{country}}
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-2">
									<input ng-change="check_phone_number()" ng-model="phone_prefix"  name="phone_prefix" type="text" class="form-control" placeholder="+1" style="width:50px !important;">
								</div>
								<div class="col-sm-10">
									<input ng-change="check_phone_number()" ng-model="phone"  name="phone" type="text" class="form-control" placeholder="Mobile No.">
									<div>
										Don't affix '0' or country code.<br>Enter Only Mobile Number.
									</div>
									<div ng-show="is_duplicate_phone_number" style="font-weight:900; color: red;">Duplicate Phone Number</div>
									<div ng-show="!is_duplicate_phone_number && first_time_sign_in_form.phone.$dirty" style="font-weight:900; color: green;">Valid Phone Number</div>
								</div>
							</div>
							<button  ng-click="first_time_signin_submit()" class="btn btn-primary">Update</button>
						</form>
					</div>
				</div>
			</div>
		</div>

		<div class="flex_container" ng-show="is_show_sign_in_form">
			<div style="width:500px;">
				<div class="max-width:100%" style="padding:10px;border: 3px solid white;
    margin-top: 10px;
    margin-bottom: 10px;
    background-color: rgba(26, 188, 156, 0.18);
    border-radius: 10px;
	overflow:hidden">
					<h3 class="sign" style="padding-bottom:20px;">Sign In</h3>

					<div style="position:relative;">
						<div style="color:#000000;position:absolute; top:0px; left:45px; z-index:10000; border:1px solid #a9d2d6; background-color:#FFFFFF; width:390px; height:200px;" ng-show="show_get_otp_pop_up" ng-include="otp_pop_up_url"></div>
					</div>
					<form name="login_form" class="form-horizontal" role="form" >
						<div class="form-group">
							<div class="control-label col-sm-12" style="text-align:left;">
								<div ng-show="login_controller.is_invalid_login" style="color:#FF0000 !important; font-weight:900;" role="alert">
									Unable To Login. Please try again.
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-sm-2" for="login" style="text-align:left;">Email</label>
							<div class="col-sm-10">
								<input ng-model="login" type="text" name="login" class="form-control login-field" auto-focus placeholder="Login">
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-sm-2" for="password" style="text-align:left;">Password</label>
							<div class="col-sm-10">
								<input ng-model="password"  name="password" type="password" class="form-control" ng-keyup="$event.keyCode == 13 && submit()" placeholder="Password">
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-sm-2" for="otp" style="text-align:left;">OTP</label>
							<div class="col-sm-10">
								<input ng-model="otp" type="password" name="otp" class="form-control" placeholder="OTP">
								<div>
								<a href="" ng-click="know_your_otp_click()">KNOW YOUR OTP</a>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-sm-2" for="country" style="text-align:left;">Country</label>
							<div class="col-sm-10">
								<select class="form-control" ng-model="country" required name="Country" >
									<?=file_get_contents("FrontEnd/countries_drop_down.html");?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-sm-2" for="captcha" style="text-align:left;">Captcha*</label>
							<div class="col-sm-10">
								<div vc-recaptcha on-expire="cbExpiration()" on-create="set_captcha_widget_id(widgetId)" key="'6LerrSMTAAAAABVpbmfo_eLvTA6GbIMWEEb3kF-s'" ng-model="captcha"></div>
							</div>
						</div>
							<div style="float:left;">
								<button ng-disabled="login_form.$invalid" type="button" ng-click="submit()" class="btn btn-primary">Login</button>
							</div>
							<div style="float:left; padding-left:10px;" ng-show="login_controller.is_verifying_login">
								<div style="color:green; font-weght:900; padding-top:7px;">Verifying Login....</div>
							</div>
							<div style="float:right; ">
								<a class="forgot_password_link" style="color:#FF0000; font-size:16px;" ng-click="login_controller.forgot_password_click()"><div style="padding-top:8px; padding-left:5px;">forgot password</div></a>
							</div>
							<div style="clear:both;"></div>
					</form>
				</div>
			</div>
		</div>


	</div>
</section>

<?include("FrontEnd/footer.php");?>
<?include("FrontEnd/bottom.php");?>
