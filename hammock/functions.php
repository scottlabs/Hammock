<?php
function _header($path)
{
	header($path);exit;
}
function e($m){
	$allowed_environments = array('development','local');
	if (in_array(environment,$allowed_environments)) {
		echo "<pre>".print_r($m,true)."</pre>";//exit;
	}
}
function l($msg) {
	global $Hammock;
	$Hammock->model->db->query("INSERT INTO ae_logs (log,created) VALUES ('".addslashes($msg)."',NOW())",'none');
}
function stylesheet($file) {
	echo '<link rel="stylesheet" href="'.readFileContents('css/'.$file).'" type="text/css" media="screen" charset="utf-8">
';
}
function javascript($file) {
	echo '<script type="text/javascript" charset="utf-8" src="'.readFileContents('js/'.$file).'"></script>
';
}
function readFileContents($file) {
	if (file_exists($file)) {
		$hash = '?v='.md5(file_get_contents($file));
	} else { $hash = null; }
	return domain.$file.$hash;
}

function textInput($params,&$globalVars) {
	if (! isset($globalVars['tabindex'])) { $globalVars['tabindex'] = 0; }
	$globalVars['tabindex']++;
	
	$disable = $globalVars['disable'];
	$errors = $globalVars['errors'];
	
	$name = $params['name'];
	$value = ucwords(strtolower($params['value']));

	$brs = (isset($params['brs'])) ? $params['brs'] : $globalVars['brs'];
	$label = $params['label'];
	$helper = (isset($params['helper'])) ? '<br /><small>'.$params['helper'].'</small>' : null ;	
	
	$application = (isset($params['container'])) ? $params['container'] : $globalVars['container'];
	
	$appName = (isset($application)) ? $application.'['.$name.']' : $name;
	$required = null;
	
	if (isset($globalVars['required_fields'])) {

		foreach($globalVars['required_fields'] as $req) {	
			if ($req == "'".$appName."'") { 
				$required = '<span class="req">*</span>';
			}
		}
	}
	
	$val = (isset($value)) ? 'value="'.$value.'"' : null;
	$rel = (isset($params['rel'])) ? 'rel="'.$params['rel'].'"' : null;	
	$disable = ($disable && $value) ? 'disabled' : null;
	if ($brs && $brs > 0) {
		$brstring = '';
		for ($i=0;$i<$brs;$i++) {
			$brstring .= '<br />';
		}
		$brs = $brstring;
	} else { $brs = null; }
	//$brs = '<br /><br /><br />';
	$empty = (isset($errors[$name])) ? 'class="empty"' : null;
	$label = '<label '.$empty.'>'.$label.$required.$helper.'</label>';
	return $label.'<input '.$rel.' '.$disable.' tabindex="'.$globalVars['tabindex'].'" type="text" name="'.$appName.'" id="'.$name.'" '.$val.' '.$class.' />'.$brs.'
';
}

function select($params,$globalVars) {
	
	if (! isset($globalVars['tabindex'])) { $globalVars['tabindex'] = 0; }
	$globalVars['tabindex']++;
	
	$options = $params['options'];
	if ($options=='states') {
		
		$options = array(
			"Select your state" => "",
			"Alabama" => "AL",
			"Alaska" => "AK",
			"Arizona" => "AZ",
			"Arkansas" => "AR",
			"California" => "CA",
			"Colorado" => "CO",
			"Connecticut" => "CT",
			"Delaware" => "DE",
			"District Of Columbia" => "DC",
			"Florida" => "FL",
			"Georgia" => "GA",
			"Hawaii" => "HI",
			"Idaho" => "ID",
			"Illinois" => "IL",
			"Indiana" => "IN",
			"Iowa" => "IA",
			"Kansas" => "KS",
			"Kentucky" => "KY",
			"Louisiana" => "LA",
			"Maine" => "ME",
			"Maryland" => "MD",
			"Massachusetts" => "MA",
			"Michigan" => "MI",
			"Minnesota" => "MN",
			"Mississippi" => "MS",
			"Missouri" => "MO",
			"Montana" => "MT",
			"Nebraska" => "NE",
			"Nevada" => "NV",
			"New Hampshire" => "NH",
			"New Jersey" => "NJ",
			"New Mexico" => "NM",
			"New York" => "NY",
			"North Carolina" => "NC",
			"North Dakota" => "ND",
			"Ohio" => "OH",
			"Oklahoma" => "OK",
			"Oregon" => "OR",
			"Pennsylvania" => "PA",
			"Rhode Island" => "RI",
			"South Carolina" => "SC",
			"South Dakota" => "SD",
			"Tennessee" => "TN",
			"Texas" => "TX",
			"Utah" => "UT",
			"Vermont" => "VT",
			"Virginia" => "VA",
			"Washington" => "WA",
			"West Virginia" => "WV",
			"Wisconsin" => "WI",
			"Wyoming" => "WY"
		);
	}
	
	
	$disable = $globalVars['disable'];
	$errors = $globalVars['errors'];
	
	$name = $params['name'];
	$value = $params['value'];

	$brs = (isset($params['brs'])) ? $params['brs'] : $globalVars['brs'];
	$label = $params['label'];
	$helper = (isset($params['helper'])) ? '<br /><small>'.$params['helper'].'</small>' : null ;	
	
	$application = (isset($params['container'])) ? $params['container'] : $globalVars['container'];
	
	$appName = (isset($application)) ? $application.'['.$name.']' : $name;
	$required = null;
	if (isset($globalVars['required_fields'])) {

		foreach($globalVars['required_fields'] as $req) {	
			if ($req == "'".$appName."'") { 
				$required = '<span class="req">*</span>';
			}
		}
	}
	
	$rel = (isset($params['rel'])) ? 'rel="'.$params['rel'].'"' : null;	
	$disable = ($disable && $value) ? 'disabled' : null;
	if ($brs && $brs > 0) {
		$brstring = '';
		for ($i=0;$i<$brs;$i++) {
			$brstring .= '<br />';
		}
		$brs = $brstring;
	} else { $brs = null; }
	//$brs = '<br /><br /><br />';
	$empty = (isset($errors[$name])) ? 'class="empty"' : null;
	$label = '<label '.$empty.'>'.$label.$required.$helper.'</label>';
	
	
	$select_options = '';
	foreach($options as $option=>$abbr) {
		$selected = ($value==$abbr) ? ' selected="selected" ' : null;
		$select_options .= '<option '.$selected.' value="'.$abbr.'">'.$option.'</option>
		';
		
	}
	
	$select = '<select '.$rel.' '.$disable.' tabindex="'.$globalVars['tabindex'].'"  name="'.$appName.'" id="'.$name.'" '.$class.'>'.$select_options.'</select>';
	return $label.$select.$brs;
}
function parsePostalCode($zip) {
	return substr($zip,0,5);
}
function yesNo($val,$image=true) {
	
	if ($val===1||$val==='1') {
		return ($image) ? '<img src="'.domain.'images/yes.png" alt="Yes" />' : 'YES';
	} else if ($val===0||$val==='0') {
		return ($image) ? '<img src="'.domain.'images/no.png" alt="No" />' : 'NO' ;
	}
}