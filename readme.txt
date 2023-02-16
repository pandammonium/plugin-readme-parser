=== Plugin README Parser ===
Contributors: pandammonium, dartiss
Tags: embed, markdown, parser, plugin, readme
Requires at least: 4.6
Tested up to: 6.1.1
Requires PHP: 7.4
Stable tag: 2.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

📑 Embed README content into posts

== Description ==

WordPress README files are formatted using a version of the Markdown language. This plugin converts these files to XHTML and displays it on a post or page of your site.

It's ideal for plugin developers who want to add instructions to their own site without duplication.

Key features include:

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

👉 Please visit the [Github page](https://github.com/pandammonium/plugin-readme-parser "Github") for the latest code development, planned enhancements and known issues 👈

== Getting Started ==

To use, simply add the `[readme]` shortcode to any post or page. For example:

`[readme]WP README Parser[/readme]`

This fetches and displays the README for this plugin. You can specify a filename instead.

The first heading of the README file, which is the name of the plugin, will automatically be suppressed as it is assumed that you have already added it to your post/page or that you are using it as the title.

== Additional Shortcode Parameters ==

**exclude**

Each README is divided into a number of sections. To exclude one or more sections from the output, use the `exclude` parameter.

The README file begins with the head, comprising the plugin's meta data (e.g. tags) and its description.

To exclude the meta data from the output XHTML, use the section name "meta" as the parameter's value. To remove the description, use "description". To remove bother the meta data and the description, use "head".

To remove specific items of meta data, specify `contributors`, `donate`, `tags`, `requires`, `license`, `license uri`, `tested` or `stable`.

For example:

`[readme exclude="Meta,Changelog"]WP README Parser[/readme]`

This will display the entire README except the Changelog and the plugin meta data.

**include**

The opposite of `exclude`, `include` displays only the specified section(s). Using the example from above. we get:

`[readme include="Meta,Changelog"]WP README Parser[/readme]`

This will only show the Meta and Changelog sections of the README file.

The only difference to the exclude command is that you can't include just specific sections of the meta data. If you believe that this option is required then please get in touch.

**ignore**

Different from `exclude`, `ignore` allows specific lines of the README to be ignored. Multiple lines should be separated by double commas (to allow single commas to be be used in the actual line to be ignored; i.e. `,,`). For example:

`[readme ignore="this line,,and this line"]WP README Parser[/readme]`

**target**

Any links generated have a target of `_blank`. To change the target, use `target`. For example:

`[readme target="_self"]WP README Parser[/readme]`

**nofollow**

To generate a link with a nofollow option (i.e. `rel="nofollow"`), specify `nofollow` as "Yes". By default, links don't have this option. For example:

`[readme nofollow="Yes"]WP README Parser[/readme]`

**cache**

The `cache` parameter allows you to specify how long output should be cached for in minutes. By default, caching does not occur. For example, to cache the output for 1 hour:

`[readme cache=60]WP README Parser[/readme]`

**version**

To display a specific version of the README, use th `version` parameter to request it. For example:

`[readme version=1.0]WP README Parser[/readme]`

**mirror**

If your plugin is hosted at a number of other locations, you can use `mirror` to specify download URLs other than the WordPress repository. Simply separate multiple URLs with double commas (i.e. `,,`). For example:

`[readme mirror="http://www.example1.com,,http://www.example2.com"]WP README Parser[/readme]`

**links**

By default, download and other links will be added to the bottom of the README output. By specifying a section name with this parameter, the links will appear before that section. For example, to appear before the description you'd use:

`[readme links="description"]WP README Parser[/readme]`

**name**

If you specify a README filename instead of a name, the plugin name at the top of the README will be assumed to be the correct name. This might not be the case if you've renamed your plugin (as is the case for this plugin). The `name` parameter overrides this:

`[readme name="WP README Parser"]http://plugins.svn.wordpress.org/plugin-readme-parser/trunk/readme.txt[/readme]`

**ext**

The extension that your screenshots are stored as (e.g. PNG or JPG).

**assets**

If your screenshots are in your assets folder, set the `assests` parameter to `yes` so that they can be read from there. For example:

`[readme assets="yes"]WP README Parser[/readme]`

== Using Content Reveal ==

If you also have the plugin [Content Reveal](https://wordpress.org/plugins/simple-content-reveal/ "Content Reveal") installed, each section of the README will be collapsible; that is, you can click on the section heading to hide the section content.

By default, all sections of the output will be revealed.

You may now use three further parameters when using the `[readme]` shortcode:

**hide**

Use the `hide` parameter to hide sections automatically. Simply click on them to reveal them again. For example:

`[readme hide="Changelog"]WP README Parser[/readme]`

**scr_url**

To supply your own hide/reveal images, specify your own folder with the `scr_url` parameter.

The two images (one for when the content is hidden, another for when it's shown) must be named image1 and image2. They can either be GIF or PNG images (see the next parameter).

For example:

`[readme scr_url="https://artiss.blog”]WP README Parser[/readme]`

**scr_ext**

Use this to specify whether PNG or GIF images should be used for your own hide/reveal images; GIF images are the default.

For example:

`[readme scr_url="https://artiss.blog" scr_ext="png"]WP README Parser[/readme]`

== Using a Function Call ==

To code a direct PHP call to the plugin, you can do. The function is named `readme_parser` and accepts two parameters:

1. The first parameter is an array of all the options, the same as the shortcode.
2. The second parameter is the README name or filename.

For example:

`echo readme_parser( array( 'exclude' => 'meta,upgrade notice,screenshots,support,changelog,links,installation,licence', 'ignore' => 'For help with this plugin,,for more information and advanced options ' ), 'YouTube Embed' );`

This may be of particular use to plugin developers because it allows the README for their plugins to be displayed within their administration screens.

== Displaying the plugin banner ==

Some plugins have banners assigned to them. The shortcode `[readme_banner]` can be used to output these banners (responsively). Between the opening and closing shortcode you must specify a plugin name (a URL can't be used) and that's it. For example:

`[readme_banner]YouTube Embed[/readme_banner]`

If no banner image exists then nothing will be output.

== Display specific README information ==

To add your own section to the output (e.g. to provide download links), `exclude` the relevant section, then use an additional shortcode to retrieve the information that you need.

Use the shortcode `[readme_info]` to return one of a number of different pieces of information. Use the required parameter `data` to specify what you need - this can b:

* **download** - Display a download link
* **version** - Output the current version number
* **forum** - Display a link to the forum
* **wordpress** - Display a link to the plugin in the WordPress.org repository

For links, you must specify text between the opening and closing shortcodes to link to.

There are four additional parameters:

* **name** - Specifies the plugin name. This is required
* **target** - If outputting a link, this will assign a target to the output (default is `_blank`)
* **nofollow** - If `yes`, this will be a `nofollow` link. By default, it won't be
* **cache** - By default, any output will be cached for five minutes so that if this shortcode is used multiple times on a page, the data will only be fetched once. Specify a different number (in minutes) to adjust this. Set to `no` to switch off caching

For example:

`[readme_info name="YouTube Embed" data="download"]Download YouTube Embed[/readme_info]'

== Reviews & Mentions ==

[WPCandy](http://wpcandy.com/reports/plugin-readme-parser-plugin-converts-plugins-readme-into-blog-ready-xhtml?utm_source=feedburner&utm_medium=feed&utm_campaign=Feed%3A+wpcandy+%28WPCandy+-+The+Best+of+WordPress%29 "WPCandy") - WP README Parser Plugin converts Plugin's readme into blog-ready XHTML

== Acknowledgements ==

Plugin README Parser uses [PHP Markdown Extra](http://michelf.com/projects/php-markdown/extra/ "PHP Markdown Extra") by Michel Fortin.

== Installation ==

Plugin README Parser can be found and installed via the Plugin menu within WordPress administration (Plugins -> Add New). Alternatively, it can be downloaded from WordPress.org and installed manually:

1. Upload the entire `plugin-readme-parser` folder to your `wp-content/plugins/` directory.
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

Semantic versioning is used, with the first release being 1.0.

= 2.0.0 =
* Bug: Updated markdown library.
* Now maintained by [Caity Ross]{https://pandammonium.org/}.

[Previous version history](https://plugins.trac.wordpress.org/browser/plugin-readme-parser/trunk/changelog.txt)


== Upgrade Notice ==

= 1.3.7 =
* Minor maintenance change
