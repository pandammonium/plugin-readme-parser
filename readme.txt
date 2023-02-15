=== Plugin README Parser ===
Contributors: dartiss,pandammonium
Tags: embed, markdown, parser, plugin, readme
Requires at least: 4.6
Tested up to: 6.1
Requires PHP: 7.4
Stable tag: 1.3.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

📑 Embed README content into posts

== Description ==

WordPress README files are formatted using a version of the Markdown language. This plugin can be used to convert these to XHTML and display on a post or page of your site.

It's ideal for plugin developers who wish to add instructions to their own site without having to duplicate effort.

Key features include...

* Convert your markdown README to XHTML and display in any post or page
* Use shortcodes or a direct PHP function call
* Responsive output of screenshots
* Output is cached for maximum performance
* Links automatically added to author and tag information
* Download links added
* Ability to specify which sections of the readme to exclude
* Can also omit specific lines of text
* Extra shortcodes available to display plugin banners and to return specific plugin data (download link, version number, etc)
* Google Translation suppressed on code output
* And much, much more!

Iconography is courtesy of the very talented [Janki Rathod](https://www.fiverr.com/jankirathore) ♥️

👉 Please visit the [Github page](https://github.com/dartiss/plugin-readme-parser "Github") for the latest code development, planned enhancements and known issues 👈

== Getting Started ==

To use, simply add the `[readme]` shortcode to any post or page. An example of use would be...

`[readme]WP README Parser[/readme]`

This would fetch and display the README for this plugin. You can also specify a filename instead.

The first heading, which is the name of the plugin, will be automatically suppressed as it is assumed that you have already added this to your post/page or are using it as the title.

== Additional Shortcode Parameters ==

**exclude**

Each README is divided into a number of sections. If you wish to exclude any from the output then use this parameter to list them.

Before the first section (usually "Description") is a number of pieces of "meta data" about the plugin, including tags, etc. Links are automatically added to these. If, however, you wish to just exclude this data then you should use the section name of "meta". Underneath this data is a short description which will remain in this case. If you want to remove this description and the meta data then use the section name of "head". If you wish to just remove a particular bit of meta data then specify `contributors`, `donate`, `tags`, `requires`, `license`, `license uri`, `tested` or `stable`.

For example...

`[readme exclude="Meta,Changelog"]WP README Parser[/readme]`

This will display the entire README with the exception of the Changelog and the Plugin meta.

**include**

The opposite of `exclude` this allows you to specify ONLY the section that you wish to appear. So, using the example from above...

`[readme include="Meta,Changelog"]WP README Parser[/readme]`

This will ONLY show the Meta and Changelog sections of the README file.

The only difference to the exclude command is that you can't include just specific sections of the meta. If you believe that this option is required then please get in touch via the forum.

**ignore**

Different from `exclude` this allows to ignore specific lines of the README. Multiple lines should be separated by double commas (to allow single commas to be be used in the actual line to be ignored). For example...

`[readme ignore="this line,,and this line"]WP README Parser[/readme]`

**target**

Any links will have a target of `_blank`. If you wish this to be anything else then change it with this parameter. For example...

`[readme target="_self"]WP README Parser[/readme]`

**nofollow**

If you wish a link to have a nofollow option (i.e. the tag of `rel="nofollow"`) then specify this as "Yes". By default it won't. For example...

`[readme nofollow="Yes"]WP README Parser[/readme]`

**cache**

This allows you to specify how long output should be cached for, in minutes. By default caching does not occur. For example, to cache for 1 hour...

`[readme cache=60]WP README Parser[/readme]`

**version**

If you wish to display a specific version of the README, use this parameter to request it. For example...

`[readme version=1.0]WP README Parser[/readme]`

**mirror**

If your plugin is hosted at a number of other locations then you can use this to specify alternative download URLs other than the WordPress repository. Simply seperate multiple URLs with double commas (i.e. ,,). For example...

`[readme mirror="http://www.example1.com,,http://www.example2.com"]WP README Parser[/readme]`

**links**

By default download and other links will be added to the bottom of the README output. By specifying a section name via this parameter, however, then the links will appear before that section. For example, to appear before the description you'd put...

`[readme links="description"]WP README Parser[/readme]`

**name**

If you specify a README filename instead a name then it will be assumed that the plugin name at the top of the README is the correct one. This may not be the case, however, if you've renamed your plugin (as is the case for this plugin). You can therefore use the `name` parameter to override this.

`[readme name="WP README Parser"]http://plugins.svn.wordpress.org/wp-readme-parser/trunk/readme.txt[/readme]`

**ext**

The extension that your screenshots are stored as - i.e. PNG or JPG.

**assets**

Storing your screenshots in your assets folder? Then set this to 'yes' for them to be read from there. For example...

`[readme assets="yes"]WP README Parser[/readme]`

== Using Content Reveal ==

If you also have the plugin [Content Reveal](https://wordpress.org/plugins/simple-content-reveal/ "Content Reveal") installed, then each section of the README will be collapsable - that is, you can click on the section heading to hide the section content.

By default, all sections of the output will be revealed.

You may now use 3 further parameters when using the `[readme]` shortcode...

**hide**

Use this parameter to hide sections automatically - simply click on them to reveal them again.

For example...

`[readme hide="Changelog"]WP README Parser[/readme]`

**scr_url**

If you wish to supply your own hide/reveal images then you can specify your own folder here.

The two images (one for when the content is hidden, another for when it's shown) must be named image1 and image2. They can either by GIF or PNG images (see the next parameter).

For example...

`[readme scr_url="https://artiss.blog”]WP README Parser[/readme]`

**scr_ext**

Use this specify whether you wish to use PNG or GIF images for your own hide/reveal images. If you do not specify it, GIF will be used.

For example...

`[readme scr_url="https://artiss.blog" scr_ext="png"]WP README Parser[/readme]`

== Using a Function Call ==

If you wish to code a direct PHP call to the plugin, you can do. The function is named `readme_parser` and accepts 2 parameters. The first is an array of all the options, the same as the shortcode. The second parameter is the README name or filename.

For example...

`echo readme_parser( array( 'exclude' => 'meta,upgrade notice,screenshots,support,changelog,links,installation,licence', 'ignore' => 'For help with this plugin,,for more information and advanced options ' ), 'YouTube Embed' );`

This may be of particular use to plugin developers as they can then display the README for their plugins within their administration screens.

== Displaying the plugin banner ==

Some plugins have banners assigned to them. The shortcode `[readme_banner]` can be used to output them (responsively too). Between the opening and closing shortcode you must specify a plugin name (a URL can't be used) and that's it. For example...

`[readme_banner]YouTube Embed[/readme_banner]`

If no banner image exists then nothing will be output.

== Display specific README information ==

You may wish to add your own section to the output to provide download links, etc. In which case you can suppress this section and then use an additional shortcode to retrieve the information that you need.

Use the shortcode `[readme_info]` to return one of a number of different pieces of information. Use the required parameter `data` to specify what you need - this can b...

* **download** - Display a download link
* **version** - Output the current version number
* **forum** - Display a link to the forum
* **wordpress** - Display a link to the plugin in the WordPress.org repository

In the cases of the links you must specify text between the opening and closing shortcodes to link to.

There are 4 additional parameters...

* **name** - Use this to specify the plugin name. This is a require parameter
* **target** - If outputting a link this will assign a target to the output (default is _blank)
* **nofollow** - If `Yes` then this will be a `nofollow` link. By default it won't be
* **cache** - By default any output will be cached for 5 minutes so that if you use this shortcode multiple times on a page the data will only be fetched once. Specify a different number (in minutes) to adjust this. Set to `No` to switch off caching entirely

An example of usage may be...

`[readme_info name="YouTube Embed" data="download"]Download YouTube Embed[/readme_info]'

== Reviews & Mentions ==

[WPCandy](http://wpcandy.com/reports/wp-readme-parser-plugin-converts-plugins-readme-into-blog-ready-xhtml?utm_source=feedburner&utm_medium=feed&utm_campaign=Feed%3A+wpcandy+%28WPCandy+-+The+Best+of+WordPress%29 "WPCandy") - WP README Parser Plugin converts Plugin's readme into blog-ready XHTML

== Acknowledgements ==

Plugin README Parser uses [PHP Markdown Extra](http://michelf.com/projects/php-markdown/extra/ "PHP Markdown Extra") by Michel Fortin.

== Installation ==

Plugin README Parser can be found and installed via the Plugin menu within WordPress administration (Plugins -> Add New). Alternatively, it can be downloaded from WordPress.org and installed manually...

1. Upload the entire `wp-readme-parser` folder to your `wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress administration.

Voila! It's ready to go.

== Screenshots ==

1. Example of [Timed Content](https://artiss.blog/simple-timed-content "Timed Content") README being displayed on artiss.blog website.

== Frequently Asked Questions ==

= Can I change the look of the output? =

You can. The whole output is encased in a `<div>` with a `class` of `np-` followed by the plugin name (lower case and spaces converted to dashes).

Each section that has a `<div>` around it with a `class` of `np-` followed by the section name (lower case and spaces converted to dashes).

The download link has an additional `<div>` around it with a `class` of `np-download-link`.

Screenshots have a `<div>` with a `class` of `np-screenshotx`, where `x` is the screenshot number.

Each of these `div`'s can therefore be styled using your theme stylesheet.

== Changelog ==

I use semantic versioning, with the first release being 1.0.

= 1.3.7 =
* Maintenance: Removed donation links

= 1.3.6 =
* Maintenance: Updated this README to better reflect the new plugin directory format
* Maintenance: Plugin now works with a minimum WordPress version of 4.6. This also means that various language changes could be made
* Enhancement: Using Yoda conditions throughout

= 1.3.5 =
* Bug: Fixed a bug in the internationalization code
* Maintenance: Updated branding, inc. adding donation links

= 1.3.4 =
* Maintenance: Updated Markdown script to 1.6.0
* Maintenance: Updated branding
* Maintenance: Removed the arp- prefix from the file names
* Maintenance: Stopped doing the naughty thing of hardcoding the plugin name in the includes

= 1.3.3 =
* Maintenance: Added text domain and domain path

= 1.3.2 =
* Maintenance: Minor update to ensure compatibility with another of my plugins

= 1.3.1 =
* Maintenance: Upgraded PHP Markdown to the latest release.

= 1.3 =
* Maintenance: Removed deprecated functionality.
* Enhancement: Added new INCLUDE parameter to allow you to specify only the README sections that you list.
* Enhancement: Banner function will now return the high DPI banner, if available. It will also check for both PNG and JPG files.
* Enhancement: Added assets parameter which allows you to force the plugin to look in your assets folder for screenshots.
* Enhancement: Added license and license URI to meta section.
* Enhancement: Reduced the ridiculous number of blank lines being output.
* Bug: Fixed issue (not reported but found when testing this release!) where download links won't work if meta content is suppressed.
* Bug: Fixed an error (is anybody actually using this plugin?) when trying to display banners.

= 1.2.1 =
* Maintenance: Changed plugin name
* Maintenance: Correct support forum link

= 1.2 =
* Maintenance: Split out code and improved code quality
* Maintenance: Major update to README
* Maintenance: Updated Artiss Content Reveal function names - was using older, deprecated names
* Enhancement: NOFOLLOW and TARGET information added to tags
* Enhancement: Changed DIVs to use CLASS instead of ID
* Enhancement: You may now specify which version of the README you wish to display
* Enhancement: Output may now be cached (by default it isn't)
* Enhancement: Added option to specify download mirrors
* Enhancement: Code output has a CLASS added that prevents Google translation
* Enhancement: Added responsive output on screenshots
* Enhancement: You can specify where the download/links section will appear
* Enhancement: Added `readme_banner` shortcode to display an assigned banner image
* Enhancement: Added `readme_info` shortcode to output various useful bits of information about the README separately from the main shortcode
* Enhancement: Added new `name` parameter. If a filename was specified and the name at the top of the README was not the same as it's held in the WP repository (this plugin is an example) then it would not work. This new parameter allows you to specify a correct plugin name
* Enhancement: Added internationalization
* Enhancement: Added additional meta information to the plugin settings
* Enhancement: `ext` parameter no longer needed - automatic detection of screenshot extension type
* Bug: Resolved a number of WP Debug errors

= 1.1.1 =
* Bug: Updated Markdown Extra script to latest version - this fixes a number of bugs

= 1.1 =
* Bug: Fixed file fetching bug
* Enhancement: Improved code display - particularly code multi-lines
* Enhancement: New option to suppress specific lines

= 1.0.2 =
* Enhancement: Screenshots will now be picked from trunk or tag folders, depending on stable tag
* Enhancement: Improved handling of download link and version numbers

= 1.0.1 =
* Enhancement: Added check for malformed README file where there are no carriage returnes
* Enhancement: Output download version number
* Bug: Fix bug where download link didn't work if "Stable Tag" meta was excluded

= 1.0 =
* Initial release

== Upgrade Notice ==

= 1.3.7 =
* Minor maintenance change