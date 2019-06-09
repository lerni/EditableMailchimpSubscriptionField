<?php

use SilverStripe\Core\ClassInfo;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\GroupedDropdownField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\View\ArrayData;
use SilverStripe\View\Requirements;
use \DrewM\MailChimp\MailChimp;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\GridField\GridField;

class NewsletterSignup extends EditableFormField {

    private static $singular_name = 'Newsletter Signup Field';
    private static $plural_name = 'Newsletter Signup Fields';
    private static $api_key = "";

    private static $db = [
		'ListId' 			=> 'Varchar(255)',
		'EmailField' 		=> 'Varchar(255)',
		'FirstNameField' 	=> 'Varchar(255)',
		'LastNameField' 	=> 'Varchar(255)',
		'TickedByDefault' 	=> 'Boolean',
		'HideOptIn' 		=> 'Boolean',
		'DoubleOptin' 		=> 'Boolean',
		'SendWelcome' 		=> 'Boolean',
		'HideOptIn' 		=> 'Boolean',
		'ShowGroupsInterests'=> 'Boolean',
		'DefaultInterest' 	=> 'Varchar(255)',
		'UnsubscribeListsId'=> 'Varchar(255)',
    ];

    private static $has_many = [
      "MailChimpMergeVars" => "MailChimpMergeVar"
    ];

	private static $dependencies = [
		'logger'        =>  '%$Psr\Log\LoggerInterface'
	];

	public $logger;

    public $mailchimp_api = null;

    public function Icon() {
      return MOD_DOAP_DIR . '/images/editablemailchimpsubscriptionfield.png';
    }
    public static function set_api_key($key) {
      self::$api_key = $key;
    }
    public static function get_api_key() {
      return self::$api_key;
    }

    public function getCMSFields() {

		$fields = parent::getCMSFields();
		$fields->removeByName(['Default','Validation']);

		$fieldsStatus = !$this->Lists()->Count();
		$arrListMap = $this->Lists()->map("id", "name");

		$fields->addFieldsToTab("Root.Main", [
			LiteralField::create("MailChimpStart", "<h4>Mailchimp Configuration</h4>")->setAttribute("disabled", $fieldsStatus),
			DropdownField::create("ListId", 'Subscribers List', $arrListMap)
				->setEmptyString("Choose a List")
				->setAttribute("disabled", $fieldsStatus)]
		);

		if (!empty($this->ListId)){
			$fields->addFieldsToTab("Root.Main", [
				CheckboxField::create("TickedByDefault")->setAttribute("disabled", $fieldsStatus),
				CheckboxField::create("HideOptIn")->setAttribute("disabled", $fieldsStatus),
				//CheckboxField::create("DoubleOptin")->setAttribute("disabled", $fieldsStatus),
				//CheckboxField::create("SendWelcome")->setAttribute("disabled", $fieldsStatus),
				//CheckboxField::create("ShowGroupsInterests")->setAttribute("disabled", $fieldsStatus),
				DropdownField::create("EmailField", 'Email Field', $this->CurrentFormFields())->setAttribute("disabled", $fieldsStatus),
				DropdownField::create("FirstNameField", 'First Name Field', $this->CurrentFormFields())->setAttribute("disabled", $fieldsStatus),
				DropdownField::create("LastNameField", 'Last Name Field', $this->CurrentFormFields())->setAttribute("disabled", $fieldsStatus),
				//GroupedDropdownField::create("DefaultInterest", 'Add to Interest', $this->InterestsOptions())
				//	->setEmptyString("Choose an Interest")
				//	->setAttribute("disabled", $fieldsStatus),
			]);

			$config =  GridFieldConfig_RelationEditor::create();
			$dataColumns = $config->getComponentByType(GridFieldDataColumns::class);
			//$dataColumns->setDisplayFields(array('FormField' => 'FormField','MergeField'=> 'MergeField'));
			$fields->addFieldToTab('Root.MergeVars',
				GridField::create('MailChimpMergeVars', 'MailChimpMergeVar', $this->MailChimpMergeVars(),  $config)
			);
		}

		//prevent selection of unsubscribe of the list being subscribed to
		if ($this->ListId && isset($arrListMap[$this->ListId])) {
			unset($arrListMap[$this->ListId]);
		}

		$fields->addFieldsToTab("Root.Main", [
			DropdownField::create("UnsubscribeListsId", 'Unsubscribers List', $arrListMap)
				->setEmptyString("Choose a List")
				->setAttribute("disabled", $fieldsStatus)
		]);

		return $fields;
    }

    public function CurrentFormFields(){
		return $this->Parent()->Fields()->map('Name', 'Title')->toArray();
    }

    function mailchimp() {
		if (!$this->mailchimp_api) {
			$this->mailchimp_api = new MailChimp($this->get_api_key());
	        $this->mailchimp_api->verify_ssl = false;
		}
		return $this->mailchimp_api;
	}

    function Lists(){
        $arrLists= [];
        $lists = $this->mailchimp()->get("lists/");
        foreach($lists['lists'] as $list) {
          $arrLists[] = ArrayData::create([
          	"id"   => $list["id"],
          	"name" => $list["name"]
          ]);
        }
        return ArrayList::create($arrLists);
    }

    public function InterestsOptions(){
      if(!empty($this->ListId)){
        $categories = $this->mailchimp()->get("lists/{$this->ListId}/interest-categories");
        $mCategories= [];
        foreach($categories['categories'] as $category){
          $mInterests = [];
          $interests = $this->mailchimp()->get("lists/{$this->ListId}/interest-categories/{$category["id"]}/interests");
          foreach ($interests["interests"] as $interest) {
            $mInterests[$interest["id"]] =   $interest["name"];
          }
          $mCategories[$category["title"]] =  $mInterests;
        }
        return $mCategories;
      }
    }

    public function MergeFields(){
      if(!empty($this->ListId)){
        $result = [];
        $response = $this->mailchimp()->get("lists/{$this->ListId}/merge-fields");
        foreach ($response["merge_fields"] as $merge_field) {
          	$result[$merge_field["tag"]] =  $merge_field["name"];
        }
        return $result;
      }
    }

    public function Interests(){
        $categories = $this->mailchimp()->get("lists/{$this->ListId}/interest-categories");
        $mCategories= [];
        foreach($categories['categories'] as $category) {
          $mInterests = [];
          $interests = $this->mailchimp()->get("lists/{$this->ListId}/interest-categories/{$category["id"]}/interests");
          foreach ($interests["interests"] as $interest) {
            	$mInterests[$interest["id"]] =   $interest["name"];
          }
          $mCategories[] = [
          	"id" 		=> $category["id"],
          	"title" 	=> $category["title"],
          	"interests" => $mInterests
          ];
        }
        return $mCategories;
    }

    public function getFormField() {
       //Requirements::themedJavascript("newsletter");
      if($this->ListId && $this->FirstNameField && $this->EmailField && $this->LastNameField){
        return FieldGroup::create(
          $this->optin_field(),
          $this->groups_field()
        );
      }
     return FieldGroup::create();
    }

	public function groups_field(){
		$fields = FieldGroup::create();
		if(!$this->ShowGroupsInterests)
			return $fields;


		foreach($this->Interests() as  $interests)
			$fields->push($field = CheckboxSetField::create("Interests{$interests["id"]}", "{$interests["title"]}", $interests["interests"]));

		$fields->addExtraClass('newsletter-group');
		return $fields;
	}

	public function optin_field(){
		if ($this->HideOptIn){
			return HiddenField::create($this->Name, $this->Title, true);
		}
		$field = CheckboxField::create($this->Name, $this->EscapedTitle, $this->TickedByDefault);
		$field->addExtraClass('newsletter-toggle');
		return $field;
	}

    private function getNewsLetterFieldNames() {
        $values = [];
        foreach ($this->MergeVars as $maper) {
            foreach ($this->Parent()->Fields() as $field) {
                if ($maper['name'] == $field->Title) {
                    $values[$maper['tag']]['name'] = $field->Name;
                    $values[$maper['tag']]['title'] = $field->Title;
                }
            }
        }
        return $values;
    }

    public function get_interests_from_data($data){
      $result = [];
      foreach($data as $iterests){
        if (is_array($iterests)){
          foreach (array_values($iterests) as $item){
            $result[$item]  = true;
          }
        }
      }
      return $result;
    }

    public function merge_fields($data){
      $result = [
		  'FNAME' => $data[$this->FirstNameField],
		  'LNAME' => $data[$this->LastNameField]
      ];
      foreach ($this->MailChimpMergeVars() as $var) {
	        $result[$var->MergeField] = $data[$var->FormField];
      }
      return $result;
    }

	public function getValueFromData($data) {
		if($data[$this->Name]) {
			$mc_data = [
				'email_address'   => $data[$this->EmailField],
				'status'          => 'subscribed',
				'double_optin'    => $this->DoubleOptin,
				'send_welcome'    => $this->SendWelcome,
				'update_existing' => true,
				'merge_fields'    => $this->merge_fields($data)
			];
			if (!empty($this->get_interests_from_data($data))){
				$mc_data['interests'] = $this->get_interests_from_data($data);
			}
			$result = $this->mailchimp()->post("lists/{$this->ListId}/members", $mc_data);
			if(isset($result['errors']) && is_array($result['errors'])){
				$this->logger->warning("oops, mailchimp  error {$result['errors'][0]['message']}\r\n".var_export($mc_data, true) );
				return false;
			}
			return true;
		}
	}
}
