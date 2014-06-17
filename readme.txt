=== Optimize Images Resizing ===
Contributors: OriginalEXE
Tags: images, media, resizing, optimize, cleanup, remove, empty, clean, resize, image
Requires at least: 3.8
Tested up to: 3.9.1
Stable tag: trunk
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Improve WordPress image sizes generation and save your hosting space.

== Description ==

If you were ever annoyed about the way WordPress handles images resizing, this is a plugin for you.

What this plugin does is optimizes the image handling in such a way that images are resized only when they are actually needed. WHat that means is that if your plugin/theme define a lot of image sizes, none of them will be generated on the image upload (like they would be usually), but only if they are actually requested in that size.

Resizing is done only once and later normally served by WordPress, so there is no performance hit.

Plugin also includes a method for removing all of the image sizes generated so far (useful when you install this plugin on a site with a lot of existing media).

To sum up:

*   Resize images only when needed
*   Clean up existing images sizes
*   No performance hit
*   Free up your hosting space

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload `optimize-images-resizing` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. (optional) Visit Settings -> Media to clean up your media folder
4. ???
5. Profit

== Frequently Asked Questions ==

None so far

== Screenshots ==

Will be added

== Changelog ==

= 1.0 =
* Initial version.