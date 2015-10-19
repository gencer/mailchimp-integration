Kayako MailChimp Integration
=======================

This library is maintained by Kayako.

Overview
=======================

Kayako Mailchimp integration for Kayako version 4.

MailChimp is a well known mailing service, which helps you to design email newsletters share them on social networks you already use, and track your results. It's like your own personal publishing platform.
Kayako integration for MailChimp helps to synchronize the kayako help desk users in your MailChimp account.

Features
=======================

* MailChimp can be easily integrated with the current users of Kayako.
* New users can directly subscribe to Newsletter by clicking the checkbox of subscribe newsletters. Doing this will be conditional to authentication by the link sent on user's email account.
* Users can subscribe / unsubscribe to newsletters by changing their profiles.
* Subscription is subjected to be authentication from user's email.
* There is a schedule task activity that runs in every 10 minutes and updates the batch subscription in MailChimp.

Supported versions
=======================
* Kayako: 4.51.1891 and above

Installation Steps
=======================
1. Download and extract MailChimp integration.
2. Make a symlink of /src in helpdesk_installation/__apps/mailchimp and make sure all files of src folder are available under helpdesk_installation/__apps/mailchimp.
4. Now navigate to Admin Panel of your helpdesk and click on 'Apps' in left side menu.
5. Click on MailChimp and then click on Install button, this will install this app.
6. Click on Settings option from left side menu and click on MailChimp.
7. View the Mailchimp Settings and configure the fields.
8. Get the API Key from Mailchimp account. Go To Accounts->API and Authorized Apps. If there is no API Key generated, then click on 'Add A Key' otherwise copy the existing API Key.
9. After entering the API Keys and 'Send Users an Email to Confirm Subscription' to Yes , Click on Update button on above tool bar. View the updated settings and Mailchips lists will be shown there.
10. Now if new users subscribe to the newsletters by Checking the box 'Subscribe to newsletter', will be sent an email to subscribe the mailchimp. By authorizing the subscription, user will be opted to the newsletter.
11. View the list of subscribed users in Mailchimp. 