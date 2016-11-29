	isFormValid = function ($scope, ngForm) {
		      var i = null;
		      //$scope.$emit('$validate');
		      $scope.$broadcast('$validate');
		      
		      if(! ngForm.$invalid) {
			return true;
		      } else {
			// make the form fields '$dirty' so that the validation messages would be shown
			ngForm.$dirty = true;
			
			for(i in ngForm) {
			  if(ngForm[i] && ngForm[i].hasOwnProperty && ngForm[i].hasOwnProperty('$dirty')) { // TODO: is 'field.$invalid' test required?
			    ngForm[i].$dirty = true;
			  }
			}
		      }
		    };

