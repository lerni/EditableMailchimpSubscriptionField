<?php

use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\DropdownField;


class MailChimpMergeVar extends DataObject
{
  private static $default_sort = "Sort";
  private static $db = [
    "FormField" => "Varchar(255)",
    "MergeField" => "Varchar(255)",
    "Sort" => "Int"
  ];
  private static $has_one = [
    "NewsletterSignup" => "NewsletterSignup"
  ];
  private static $summary_fields = array(
    'FormField',
    'MergeField'
   );
  public function getCMSFields()
  {
    return new FieldList([
      new DropdownField('FormField', 'FormField', $this->NewsletterSignup()->CurrentFormFields()),
      new DropdownField('MergeField', 'MergeField',$this->NewsletterSignup()->MergeFields())
    ]);
  }
}