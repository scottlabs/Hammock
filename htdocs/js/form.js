var formValidation, camelize;

(function($){
	camelize = function(str) {
		var camelized, parts, len, i;
	    parts = str.split(' '); 
		len = parts.length;
	    if (len === 1) { return parts[0]; }

	    camelized = str.charAt(0) === '-'
	      ? parts[0].charAt(0).toUpperCase() + parts[0].substring(1)
	      : parts[0];

	    for (i = 1; i < len; i += 1) {
			camelized += parts[i].charAt(0).toUpperCase() + parts[i].substring(1); 
		}

	    return camelized;
	};
	formValidation = {
		
		isValidCard_cardType : function(val) {
			if (val) { return true; }
			return false;
		},
		
		isValidCard_expirationYear : function(val) {
			val = String(parseInt(val));
			if (val.length === 4) { return true; }
			return false;			
		},
		
		isValidCard_expirationMonth : function(val) {
			val = String(parseInt(val));			
			if (val.length === 1 || val.length === 2) { return true; }
			return false;
		},
		
		isValidZip : function(val) {
			return /^\d{5}(-\d{4})?$/.test(val);
		},

		isValidPhone : function(val) {
			//return true;

		    val = val.replace(/[\(\)\.\-\ ]/g, '');
			val = String(parseInt(val,10));
			if (! (val.length===7||val.length===10)) {
				return false;
			} else if (isNaN(parseInt(val,10))) {
				return false;
		    } else {
				return true;
			}
		},

		isValidDateOfBirth : function(val) {

			if (val) {
				var i;
			    val = val.replace(/[\(\)\.\-\ ]/g, '');
				val = val.split('/');


				for (i = 0; i < val.length; i += 1) {
					val[i] = parseInt(val[i],10);

					if (! isNaN(val[i])) {
						val[i] = String(val[i]);
					}
				}	

				if (
					val.length===3 && 
					val[0].length >= 1 &&
					val[0].length <= 2 &&
					val[1].length >= 1 &&
					val[1].length <= 2 &&
					val[2].length >= 2 &&
					val[2].length <= 4
				) {
					return true;
				}
				return false;



			}

			return true;
		}
	};
	
}(jQuery));


