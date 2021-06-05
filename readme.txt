=== Maps for Contact Form 7 ===
Contributors: tanakayasuo
Donate link: 
Tags: cf7, contact form 7, google map
Requires at least: 5.7.2
Tested up to: 5.7.2
Stable tag: 1.0.1
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Addon of the contact form 7 that adds place field.  The places submitted are overlooked by shortcode( 'maps-for-contact-form-7' ).

== Description ==

The Maps for Contact Form 7 collects data with place and analyze the data with google map.

Preparation:
   (1) get google map api key and set the key in the settings of the Maps for Contact Form 7.  recommend that api key should be restricted by referer.
   (2) set google map language and region in the settings of the Maps for Contact Form 7.
   (3) to collect data, put place field on the Contact Form 7 form and add the form to target forms in the settings of the Maps for Contact Form 7. The place field is customized by reserverd keywords and place types.  The reserverd keywords and place types are used in textsearch google places api.
   (4) add the Contact Form 7 form in Pages menu in wordpress. 
   (5) add Maps for Contact Form 7 shortcode( 'maps-for-contact-form-7' ) with the form id of Contact Form 7 form in Pages menu in wordpress.  Examples is the follwoing.
   	'[maps-for-contact-form-7 form-id="the form id"]'

Collect data:
   (1) access the page with Contact Form 7 form and submit the data.
   (2) the data is listed in 'Contact Forms List With Places' menu in admin page.

Analyze data:
   (1) access the page with Maps for Contact Form 7 shortcode.
   (2) if radio buttons are included in the Contact Form 7 form, you can focus by radio button items. In case of that there are multiple radio buttons, it is focused by and condition.

== Frequently Asked Questions ==

= A question that someone might have =

= What about foo bar? =

== Screenshots ==

1. screenshot-1.png

== Changelog ==
= 1.0.2 =
   1. correct typos.

= 1.0.1 =
   1. fix bug of settings that display target forms duplicately.
   2. change css selector.

First release.

== Upgrade Notice ==

None currently.

