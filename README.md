EditableMailchimpSubscriptionField
==================================

A Field for UserForms (EditableFormField) that'll let you add Subscribers to a MailChimp list with groups.

Installation
------------
The easiest way is to use [composer](https://getcomposer.org/):

    composer require lerni/editablemailchimpsubscriptionfield

If you just clone/download this repository make sure you have the required packages also installed.

    "require": {
        "silverstripe/userforms": "dev-master",
        "drewm/mailchimp-api": "dev-master"
    },

Add your MailChimp API-Key per _config

    `NewsletterSignup::set_api_key('...');`

Run `dev/build` afterwards.

Status
-------------
The module just basically works :) hope pushing it here keeps me going.

How to use
-------------
Currently the Fieldnames in MailChimp and the Fieldnames in the Form need to match and you still need to map manually in the options of the field.