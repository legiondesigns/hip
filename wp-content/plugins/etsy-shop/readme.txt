=== Etsy Shop ===
Contributors: fsheedy
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=9RPPQUY4M2AHL&lc=CA&item_name=Etsy%2dShop%20Wordpress%20Plugin&currency_code=CAD&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted
Tags: etsy, etsy listing, bracket, shortcode, shopping, shop, store, sell
Tested up to: 6.6.1
Requires at least: 5.0
Stable tag: 3.0.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Plugin that allow you to insert Etsy Shop sections in pages or posts using the bracket/shortcode method.

== Description ==

Plugin that allow you to insert Etsy Shop sections in pages or posts using the bracket/shortcode method. This enable Etsy users to share their products through their blog!

[Feature plan](http://fsheedy.wordpress.com/etsy-shop-plugin/ "Feature plan")

== Installation ==

1. Download the plugin through the `Plugins` menu in WordPress or upload it manually to the `/wp-content/plugins/` directory;
2. Activate the plugin through the `Plugins` menu in WordPress;
3. Get your own Etsy Developer API key: [Etsy Developers](https://www.etsy.com/developers/register);
4. Enter your API key in the Etsy Shop Options page;
5. Place `[etsy-shop shop_name="*your-etsy-shop-name*" section_id="*your-etsy-shop-setion-id*"]` in your page or post;
6. Viewers will be able to click on your your items.

== Frequently Asked Questions ==

= How may I find the shop section id? =

Here is an example:

URL: http://www.etsy.com/shop/sushipot?section_id=11502395

So, in this example:
sushipot is **etsy-shop-name**
11502395 is **etsy-shop-section-id**

= I got Etsy Shop: empty arguments =

See below 'Etsy Shop: missing arguments'.

= I got Etsy Shop: missing arguments =

2 arguments are mandatory:

* etsy-shop-name
* etsy-shop-section-id

So, you should have someting like this: `[etsy-shop shop_name="Laplume" section_id="10088437"]`

More argument:
* show_available_tag [0 or 1]

= I got Etsy Shop: Your section ID is invalid =

Please use a valid section ID, to find your section ID, see [How to find section ID](http://fsheedy.wordpress.com/etsy-shop-plugin/how-to-find-section-id/ "How to find section ID")

= I got Etsy Shop: API reponse should be HTTP 200 =

Please open a new topic in Forum, with all details.

= I got Etsy Shop: Error on API Request =

Please make sure that your API Key is valid.

= I want to limit results =

You may use 2 additional arguments:

* limit
* offset

So, you may have a shortcode like this: `[etsy-shop shop_name="Laplume" section_id="10088437" limit="10" offset="5"]`

= How to integrate directly in template? =
Use `<?php echo do_shortcode( '[etsy-shop shop_name="*your-etsy-shop-name*" section_id="*your-etsy-shop-setion-id*"]' ); ?>`

== Screenshots ==

1. Options Page
2. Etsy listing rendering
3. Edit Post to include Etsy Shop

== Changelog ==

= 3.0.5 =
* Make show_available_tag work again
* Escaping some data to prevent Cross-Site Scripting

= 3.0.4 =
* Use Nonces for Quick Start, to prevent CSRF

= 3.0.3 =
* Use Nonces for Settings Page, to prevent CSRF

= 3.0.2 =
* Workaround for a bug on Etsy Open API v3 to retrieve the Shop ID
* Update error message

= 3.0.1 =
* Update price calculation to Etsy Open API v3

= 3.0 =
* Compatible with WP 5.9.3
* Compatible with Etsy Open API v3
* Added clear message for invalid API Key
* Added function to delete all the cache
* Added function to clean all parameters
* Removed old translation code
* Removed old bracket style code

= 2.3.2 =
* Compatible with WP 5.6
* Automatically detect NOK currency
* Fix: cache for section with different options

= 2.3.1 =
* Add note on new button used to delete cached content

= 2.3 =
* Compatible with WP 5.4
* Cached content are in the database now, no more cache file
* Debug mode only for users that can edit post
* Add filter for limit and offset options
* Add FAQ for limit and offset options

= 2.2 =
* Compatible with WP 5.3.2
* New limit and offset options
* New warning about the tmp folder status
* Do not show the dollar sign for Danish Krone

= 2.1 =
* Compatible with WP 5.1.1
* Full item description for SEO better support
* Remove trunk duplicate folder

= 2.0 =
* Compatible with WP 4.9.4
* Now responsive design
* New Quickstart form to generate short code

= 1.1 =
* Compatible with WP 4.8.1
* Translations update

= 1.0 =
* Cache life now as option
* Update logic added
* Jump to version 1.0, follow the Semantic Versioning

= 0.18 =
* Add version for css file
* Compatible with WP 4.5

= 0.17 =
* Automatically detect GBP currency 
* Compatible with WP 4.3

= 0.16.2 =
* Correction for show_available_tag

= 0.16.1 =
* Mistake, default columns is 3, not 4
* You can add **columns="5"**, to your short code. Replace number 5 by the number you want

= 0.16 =
* Option to select number of columns to show
* Option to select a single listing ID
* Option to height & width of thumbnail
* Option to choose thumbnail size
* CSS Update
* Portion of code from Steague, thanks!

= 0.15 =
* Add Option to choose translation language for the content
* Automatically choose Dollard Sign or Euro sign

= 0.14 =
* Italian Translation (thanks to Pierantonio Bonato)
* Compatible with WP 4.2.1

= 0.13.1 =
* Correct old shortcode method to avoid PHP Warning

= 0.13 =
* Option to show or not the Available Status Tag for each item

= 0.12 =
* Permit reset of the cache in the admin page (sponsor Michael Kellar)

= 0.11 =
* Add the option to change time out value for requests to etsy servers
* Time out value by default is 10 seconds instead of 5

= 0.10 =
* Compatible with WP 3.9.2
* Now use WP Shortcode API
* Allow maximum of 100 items per section instead of 25 items

= 0.9.5 =
* HTTPS for all Etsy requests, now mandatory for new Etsy Apps.
* Add detection for bad section ID.

= 0.9.4 =
* Centering items by default (sponsor Jsay Designs).
* Add opening in a new window link feature (sponsor Jsay Designs).
* Add filter for Shop ID and Section ID.
* Better filter for API Key.

= 0.9.3 =
* Corrections on Debug Mode.
* Automatically Remove spaces in API Key.

= 0.9.2 =
* Debug Mode more verbose.

= 0.9.1 =
* Debug Mode available.
* Options page compatible with PHP 5.2.X.

= 0.9.0 =
* Optimization of API request.
* Add error message if empty arguments.
* Now using Wordpress HTTP API, cURL is no more require.
* Update the Options Page.
* Code follow WordPress Coding Standards.

= 0.8.1 =
* Update installation steps.
* Translation of listing status.
* Correct listing table generation.

= 0.8 =
* First release.
