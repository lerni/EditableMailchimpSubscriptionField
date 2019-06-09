EditableMailchimpSubscriptionField
==================================

A Field for UserForms (EditableFormField) that'll let you add Subscribers to a MailChimp list with groups.

Initially a fork/ re-write of https://github.com/lerni/EditableMailchimpSubscriptionField

Installation
------------
The easiest way is to use [composer](https://getcomposer.org/):

    composer require asecondwill/mailchimp-subscription-field

If you just clone/download this repository make sure you have the required packages also installed.

    "require": {
        "silverstripe/userforms": "dev-master",
        "drewm/mailchimp-api": "dev-master"
    },

Add your MailChimp API-Key per _config

    `NewsletterSignup::set_api_key('...');`

Run `dev/build` afterwards.

## Adding a Field to a form
Add a Newsletter signup field to your user form. Save your form to save that.

Click edit for that field.  Choose a list from the dropdown. Save to set that.

Now fill in the other fields as you need them.

![mailchimp settings](https://github.com/asecondwill/mailchimp-subscription-field/blob/master/mailchimp-settings.png)

Status
-------------
The module is actively being developed.  Suggestions, pull requests and issues are welcome.

How to use
-------------
Currently the Fieldnames in MailChimp and the Fieldnames in the Form need to match and you still need to map manually in the options of the field.
