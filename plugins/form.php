<?php
/*******************************************************************
*
*	Hammock
*
*
*	Hammock is free software
*
*	Free for commercial use, free for redistribution and modification
*
*	It is an extremely lightweight framework, loosely modeled after 
* 	the Rails framework
*
*	It abstracts the database layer and routing.
*
*	2010
*
*
*******************************************************************/
class form extends plugin {
	public $nl = '
';
	public $parentName;
	private $parent;
	
	function __construct(&$hammock,&$parent) {
		$this->hammock = $hammock;
		$this->parent = $parent;
		$this->vars = array_merge($hammock->get,$hammock->post);
	}
	function write($opts) {
		$formName = $opts['title'];
		$action = $opts['action'];
		
		
		if ($action) { $action = 'action="'.$action.'" ';}

		
		if ($this->parent->name || $this->parentName) {
			
			if (! $this->parentName) { $this->parentName = $this->parent->name; }
			
			if (! $this->parent->formElements) {
				$model = (isset($this->model)) ? $this->model : $this->parentName;
				$model = $model.'Model';
				$model = new $model();
				$formElements = array();
				$formName = ($formName) ? $formName : null;
				$parentName = $this->parentName;
			} else {
				$model = $this->parent;
				$formName = substr(get_class($model),0,strpos(get_class($model),'Model'));
				$parentName = $formName;
			}
			
			foreach($model->formElements as $name=>$formElement) {
				$inputname = $parentName.'['.$name.']';
				if ((isset($this->vars[$name]))) {
					$val = $this->vars[$name];
				} else if ($model->$name) {

					$val = $model->$name;
				} else {
					$val = null;
				}
				$type = ((isset($formElement['type']))) ? $formElement['type'] : 'text';
				if (is_array($type)) {
					$options = '<option>Select Format</option>';
					foreach($type as $option) {
						$options .= '<option>'.$option.'</option>';
					}
					$input = '<select name="'.$inputname.'" id="'.$name.'" value="'.$val.'">'.$options.'</select>';
				} else {
					if ($type=='password') {
						$val = '';
					}
					$input = '<input type="'.$type.'" name="'.$inputname.'" id="'.$name.'" value="'.$val.'" />';
				}
				$formElements[] = '<label for="'.$inputname.'">'.$formElement['label'].'</label>'.$input;
			}
			$nl = $this->nl;
			$formElements = implode('<br />'.$nl,$formElements);
			$legend = '<legend>'.$formName.'</legend>';


			$form_session_key = md5(rand(0,10000000000));
			$_SESSION['form_session_key'] = $form_session_key;
			$fieldset = '<fieldset>'.$nl.$legend.$nl.$formElements.$nl.'<br /><input type="submit" value="Submit" /></fieldset>';
			$fieldset .= '<input type="hidden" name="form_session_key" value="'.$form_session_key.'" />';
			$form = '<form enctype="multipart/form-data" method="post" '.$action.'>'.$nl.$fieldset.$nl.'</form>';
			return $form;
		} else {
			return 'no parent specified';
		}
	}
}