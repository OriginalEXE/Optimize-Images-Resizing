=== Optimize Images Resizing ===
Contributors: OriginalEXE
Tags: images, media, resizing, optimize, cleanup, remove, empty, clean, resize, image
Requires at least: 3.8
Tested up to: 4.7
Stable tag: 1.4.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Plugin optimizes the process of generating custom image sizes in WordPress and offers a cleanup functionality for preexisting images.

== Description ==

If you were ever annoyed about the way WordPress handles images resizing, this is a plugin for you.

What this plugin does is it optimizes the image handling in such a way that images are resized only when they are actually needed. What that means is that if your plugins/theme define a lot of image sizes, none of them will be generated on the image upload (like they would be usually), but only if they are actually requested in that size.

Resizing is done only once, images are later normally served by WordPress, so there is no performance hit.

Plugin also includes a method for removing all of the previously generated image sizes (useful when you install this plugin on a site with a lot of existing media).

**TO REMOVE** image sizes generated prior to activating the plugin, visit the 'Tools -> Remove image sizes' and use the button to perform the cleanup.

Other than that, you don't need to do anything, plugin works silently in the background.

To sum up:

*   Resize images only when needed
*   Clean up existing images sizes
*   No performance hit
*   Free up your hosting space

== Installation ==

1. Upload `optimize-images-resizing` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. (optional) Visit 'Tools -> Remove image sizes' to clean up your media folder
4. ???
5. Profit

== Frequently Asked Questions ==

**I just installed the plugin. Is there anything else I need to do?**

Is this a new site with no existing images? If the answer is yes, then there is nothing else you need to do, any uploads that happen after you activated this plugin will be automatically cleaned up. If the answer is no, keep reading.

**How to clean up existing images?**

Images can be cleaned up at any time by visiting 'Tools -> Remove image sizes' in your WordPress Dashboard. Simply click on the "Start new cleanup" button and wait for the process to finish (there is a visual feedback for the duration of the cleanup).

**Some image sizes are not cleaned up, which ones and why?**

Plugin never cleans default image sizes (thumbnail, medium, large), so if your theme/plugins don't define custom image sizes, you don't need this plugin. Why does it not clean up those image sizes? Well the reason for that is that all of those image sizes are used in the Media UI of the WordPress Dashboard. What that means is: if plugin were to clean up all sizes, they would be generated for all of your images as soon as you would visit the Media screen. Since I don't know of anyone that never visits the Media screen, it made sense to exclude those image sizes from the cleaning process and avoid the redundant server load.

**How do I know which files the plugin cleaned up?**

A list of removed files is available only for the manual cleanup request, by checking the checkbox at the top of the plugin page. Once the request finishes, a message will appear stating how many images it removed. Click on the number to show the list of files that were removed in the process.

**Are there any drawbacks to using this plugin?**

Not that I know of. Your WordPress website will continue working as it did before, only your uploads folder will be a bit lighter (depending on the number of images and image sizes you have). It's certainly useful during migrations!

== Screenshots ==

1. Admin UI added by the plugin.
2. Difference between before and after running the plugin under a default theme (Twenty Sixteen).

== Changelog ==

= 1.4.1 =
* Fix a 1.4.0 PHP bug.

= 1.4.0 =
* Move plugin to the Tools menu.
* Add support for resuming image sizes removing.
* Declare WordPress 4.7 compatibility.

= 1.3.0 =
* Improve the plugin UI and UX by providing a more meaningful feedback (plugin will now tell you how many images it removed exactly and show the list of files that got removed).
* Introduce WP-CLI support (props @charlesLF).
* Fix issues with the plugin localization support.
* Declare WordPress 4.5 compatibility.

= 1.2.1 =
* Declare WordPress 4.4 compatibility.

= 1.2.0 =
* Improve performance by returning the correct response after image is resized.
* Improve performance by returning response immediately if image size is already found.
* Avoid removing image sizes that are added via 'image_size_names_choose' filter (they get generated anyway on the Media screen).

= 1.1.0 =
* Improve the process of removing unwanted image sizes on image upload (props @bcole808)

= 1.0.9 =
* Declare WordPress 4.3 support

= 1.0.8 =
* Fix compatibility issue in specific scenarios

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
