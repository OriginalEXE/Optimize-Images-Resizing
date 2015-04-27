=== Optimize Images Resizing ===
Contributors: OriginalEXE
Tags: images, media, resizing, optimize, cleanup, remove, empty, clean, resize, image
Requires at least: 3.8
Tested up to: 4.2
Stable tag: trunk
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Improve WordPress image sizes generation and save your hosting space.

== Description ==

If you were ever annoyed about the way WordPress handles images resizing, this is a plugin for you.

What this plugin does is it optimizes the image handling in such a way that images are resized only when they are actually needed. What that means is that if your plugin/theme define a lot of image sizes, none of them will be generated on the image upload (like they would be usually), but only if they are actually requested in that size.

Resizing is done only once and later normally served by WordPress, so there is no performance hit.

Plugin also includes a method for removing all of the image sizes generated so far (useful when you install this plugin on a site with a lot of existing media).

To sum up:

*   Resize images only when needed
*   Clean up existing images sizes
*   No performance hit
*   Free up your hosting space

== Installation ==

1. Upload `optimize-images-resizing` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. (optional) Visit Settings -> Media to clean up your media folder
4. ???
5. Profit

== Frequently Asked Questions ==

None so far

== Screenshots ==

Nothing special to show. The only UI is in the Media settings page where a simple button is added for cleaning previously uploaded images

== Changelog ==

= 1.0.7 =
* Fix issue where cleanup process would get stuck on certain images.

= 1.0.6 =
* Fix issue where default image size would not be generated after cleanup, if a custom image size of the same dimensions is defined.

= 1.0.5 =
* Fix issue where full image size would be duplicated when width and height are both set to 0.

= 1.0.4 =
* Fix issue with cleaning up images from media settings screen.

= 1.0.3 =
* Plugin no longer removes any of the default WordPress image sizes.

= 1.0.2 =
* Fixed issue where image sizes were not listed in some places inside admin area.

= 1.0.1 =
* Fixed issue where images would be regenerated even when not needed.

= 1.0.0 =
* Initial version.
