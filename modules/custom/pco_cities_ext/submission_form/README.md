# Challenge Submission Module
This module was developed to allow for proposal submissions for specific challenges featured on the Impact Canada website.

### Installation
This module can be installed like any other module. This package relies on two fields that must exist on the current node (route: /challenges/**{node}**/submission/). These two fields are used for the confirmation emails that are sent off:
* `field_challenge_email_contents` - Text field used for email contents.
* `field_challenge_submission_email` - Email that proposals are sent to.

There is a good chance that these fields have already been created and already exist in the database.


### Usage
* Navigate to `/challenges/{node}/submission/`
* Fill out the form

### Further Notes
There is one template that is defined as part of the Drupal theme, this is used by both Controller endpoints. An email template has been defined, and uses a helper method to generate a template string which is then used by MailGun. Libraries have been defined and attached to the theme. **Note:** Any changes made to the template(s), or library files require a clearing of the cache to be seen.
