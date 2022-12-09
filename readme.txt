=== Ingeni Gravity Forms - Talkbox ===

Contributors: Bruce McKinnon
Tags: gravity forms, talkbox
Requires at least: Gravity Forms 2.6 and a TalkBox account.
Tested up to: 5.3
Stable tag: 2022.02

Send name and email (and optionally a mobile phone) submitted from a Gravity Form to be added to TalkBox Contacts list.


== Description ==

* - Captures newsletter subscribers name and email address from a Gravity Form and sends it to your TalkBox contacts database.


== Installation ==

1. Upload the 'ingeni-gf-talkbox' folder to the '/wp-content/plugins/' directory.

2. Activate the plugin through the 'Plugins' menu in WordPress.

3. Obtain the TalkBox API username and password from the TalkBox Settings > Tools > Developers page

4. Add your TalkBox API username and password into the Forms > Settings > TalkBox Feed Add-On page.

5. To the Settings page of the form. Click TalkBox Feed Add-On.

6. Create a new Feed to link Gravity Forms to TalkBox.

7. Check the 'Enable this Feed' checkbox, and then map the Email, First name, Last name and Phone fields in TalkBox to the appropriate Gravity Form fields.

8. Add any other Conditions.



== Frequently Asked Questions ==

Q - If I unsubsribe, how can I resubscribe?
A - Simply add yourself using the Gravity Form. If your email exists in the TalkBox Contacts, but is unsubscribed, you will be automatically resubscribed.




== Changelog ==

v2022.01 - Initial version

v2022.02 - Added a little extra curl debugging
	- Added protocol scheme for the default TalkBox URL

