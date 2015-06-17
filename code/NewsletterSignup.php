<?php
/**
 * EditableCheckbox
 * A user modifiable checkbox on a UserDefinedForm
 * 
 * @package userforms
 */
class NewsletterSignup extends EditableFormField {
	static $singular_name = 'Newsletter Signup Field';
	static $plural_name = 'Newsletter Signup Fields';

	public function Icon()  {
		return MOD_DOAP_DIR . '/images/editablemailchimpsubscriptionfield.png';
	}

	// secret
	private static $api_key = "";
	public static function set_api_key($key) {
		self::$api_key = $key;
	}
	public static function get_api_key() {
		return self::$api_key;
	}

	public function getMC($item) {
		$MailChimp = new \drewm\MailChimp($this->get_api_key());

		$lists = $MailChimp->call('lists/list', array());
		if($lists) {
			$map_lists = array();
			foreach($lists['data'] as $t) {
				$map_lists['name'] = $t['name'];
				$map_lists['id'] = $t['id'];
			}
		}

		$groups = $MailChimp->call('lists/interest-groupings', array('id' => $map_lists['id']));
		if (is_array($groups['0']['groups'])) {
			$map_groups = array();
			$i = 0;
			foreach($groups['0']['groups'] as $g) {
				$map_groups[$i] = $g['name'];
				$i++;
			}
		}

		$mergevars = $MailChimp->call('lists/merge-vars', array('id' => array($map_lists['id'])));
		if (is_array($mergevars['data']['0']['merge_vars'])) {
			$map_mergevars = array();
			$i = 0;
			foreach($mergevars['data']['0']['merge_vars'] as $mv) {
				$map_mergevars[$i]['name'] = $mv['name'];
				$map_mergevars[$i]['tag'] = $mv['tag'];
				$i++;
			}
		}
		// debug::dump($map_mergevars);
		if ($item == 'lists') {
			return $map_lists;
		} elseif ($item == 'groups') {
			return $map_groups;
		} elseif ($item == 'mergevars') {
			return $map_mergevars;
		}
	}

	public function getFieldConfiguration() {
		$options = parent::getFieldConfiguration();
		$options->push(new CheckboxField("Fields[$this->ID][CustomSettings][Default]", 'Standardmässig angekreuzt?', $this->getSetting('Default')));
		$i = 0;
		$loop = $this->getMC('mergevars');
		foreach ($loop as $field) {
			$i++;
			$FieldName = $field['name'];
			$options->push(new TextField("Fields[$this->ID][CustomSettings][$FieldName]","Name des Formularfeldes mit " . $FieldName, $this->getSetting($FieldName)));
		}		
		return $options;
	}

	public function getFormField() {
		$map_groups = $this->getMC('groups');
// debug::show($map_groups);
		if (count($map_groups) > 1) {
			Requirements::javascript(MOD_DOAP_DIR . "/javascript/newsletter.js");
			$f = new FieldGroup(
				$a = new CheckboxField($this->Name, $this->Title, $this->getSetting('Default')),
				new CheckboxSetField('Themes','Themen abonnieren',$map_groups)
			);
			$a->addExtraClass('newsletter-toggle');
		}
		$f->addExtraClass('newsletter-group');
		return $f;
	}

	private function getNewsLetterFieldNames() {
		$values = array();
		$mergevars = $this->getMC('mergevars');
		foreach ($mergevars as $maper) {
			foreach($this->Parent()->Fields() as $field) {
				if($maper['name'] == $field->Title) {
					$values[$maper['tag']]['name'] = $field->Name;
					$values[$maper['tag']]['title'] = $field->Title;
				}
			}
		}
		return $values;
	}

	public function getValueFromData($data) {
		$data = Session::get("FormInfo.Form_Form.data");
		$map = $this->getNewsLetterFieldNames();
		$value = (isset($data[$this->Name])) ? $data[$this->Name] : false;
		$list = $this->getMC('lists');
		if($value) {
			$MailChimp = new \drewm\MailChimp($this->get_api_key());
			$result = $MailChimp->call('lists/subscribe', array(
				'id' => $list['id'],
				'email' => array('email' => $data[$map['EMAIL']['name']]),
				'merge_vars' => array('FNAME' => $data[$map['FNAME']['name']], 'LNAME' => $data[$map['LNAME']['name']]),
				'double_optin' => false,
				'update_existing' => true,
				'replace_interests' => false,
				'send_welcome' => true,
			));
			return ($value) ? $value : _t('EditableFormField.NO', 'No');
		}
	}
}	