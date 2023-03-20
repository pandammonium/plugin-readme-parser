=== Plugin-readme Parser ===
Contributors: dartiss, pandammonium
Tags: embed, markdown, parser, plugin, readme
Requires at least: 4.6
Tested up to: 6.1
Requires PHP: 8.0
Stable tag: 2.0.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Embeds WordPress plugin readme content into a WP post, page, etc.

== Description ==

WordPress readme files are formatted using a version of the Markdown language. This plugin converts these files to XHTML and displays it on a post or page of your site.

It's ideal for plugin developers who want to add instructions to their own site without duplication.

Key features include:

* Convert your markdown readme to XHTML and display in any post or page
* Use shortcodes or a direct PHP function call
* Output is cached for maximum performance
* Links automatically added to author and tag information
* Download links added
* Ability to specify which sections of the readme to exclude
* Can also omit specific lines of text
* Extra shortcodes available to display plugin banners and to return specific plugin data (download link, version number, etc)
* Google Translation suppressed on code output
* And much, much more!

Iconography is courtesy of the very talented [Janki Rathod](https://www.fiverr.com/jankirathore)

Please visit the [Github page](https://github.com/pandammonium/plugin-readme-parser "Github") for the latest code development, planned enhancements and known issues

== Getting Started ==

To use, simply add the `[readme]` shortcode to any post or page. For example:

`[readme]Plugin-readme Parser[/readme]`

This fetches and displays the readme for this plugin. You can specify a filename instead.

The first heading of the readme file, which is the name of the plugin, will automatically be suppressed as it is assumed that you have already added it to your post/page or that you are using it as the title.

== Additional Shortcode Parameters ==

**exclude**

Each readme is divided into a number of sections. To exclude one or more sections from the output, use the `exclude` parameter.

The readme file begins with the head, comprising the plugin's meta data (e.g. tags) and its description.

To exclude the meta data from the output XHTML, use the section name "meta" as the parameter's value. To remove the description, use "description". To remove both the meta data and the description, use "head".

To remove specific items of meta data, specify `contributors`, `donate`, `tags`, `requires`, `license`, `license uri`, `tested` or `stable`.

For example:

`[readme exclude="Meta,Changelog"]WP readme Parser[/readme]`

This will display the entire readme except the Changelog and the plugin meta data.

**include**

The opposite of `exclude`, `include` displays only the specified section(s). Using the example from above. we get:

`[readme include="Meta,Changelog"]WP readme Parser[/readme]`

This will only show the Meta and Changelog sections of the readme file.

The only difference to the exclude command is that you can't include just specific sections of the meta data. If you believe that this option is required then please get in touch.

**ignore**

Different from `exclude`, `ignore` allows specific lines of the readme to be ignored. Multiple lines should be separated by double commas (to allow single commas to be be used in the actual line to be ignored; i.e. `,,`). For example:

`[readme ignore="this line,,and this line"]WP readme Parser[/readme]`

**target**

Any links generated have a target of `_blank`. To change the target, use `target`. For example:

`[readme target="_self"]WP readme Parser[/readme]`

**nofollow**

To generate a link with a nofollow option (i.e. `rel="nofollow"`), specify `nofollow` as "yes". By default, links don't have this option. For example:

`[readme nofollow="yes"]WP readme Parser[/readme]`

**cache**

The `cache` parameter allows you to specify how long output should be cached for in minutes. By default, caching does not occur. For example, to cache the output for 1 hour:

`[readme cache=60]WP readme Parser[/readme]`

**version**

To display a specific version of the readme, use th `version` parameter to request it. For example:

`[readme version=1.0]WP readme Parser[/readme]`

**mirror**

If your plugin is hosted at a number of other locations, you can use `mirror` to specify download URLs other than the WordPress repository. Simply separate multiple URLs with double commas (i.e. `,,`). For example:

`[readme mirror="https://www.example1.com,,https://www.example2.com"]WP readme Parser[/readme]`

**links**

By default, download and other links will be added to the bottom of the readme output. By specifying a section name with this parameter, the links will appear immediately before that section. For example, to appear before the description you'd use:

`[readme links="description"]WP readme Parser[/readme]`

**name**

If you specify a readme filename instead of a name, the plugin name at the top of the readme will be assumed to be the correct name. This might not be the case if you've renamed your plugin (as is the case for this plugin). The `name` parameter overrides this:

`[readme name="WP readme Parser"]https://plugins.svn.wordpress.org/plugin-readme-parser/trunk/readme.txt[/readme]`

== Using Content Reveal ==

If you also have the plugin [Content Reveal](https://wordpress.org/plugins/simple-content-reveal/ "Content Reveal") installed, each section of the readme will be collapsible; that is, you can click on the section heading to hide the section content.

By default, all sections of the output will be revealed.

You may now use three further parameters when using the `[readme]` shortcode:

**hide**

Use the `hide` parameter to hide sections automatically. Simply click on them to reveal them again. For example:

`[readme hide="Changelog"]WP readme Parser[/readme]`

== Using a Function Call ==

To code a direct PHP call to the plugin, you can do. The function is named `readme_parser` and accepts two parameters:

1. The first parameter is an array of all the options, the same as the shortcode.
2. The second parameter is the readme name or filename.

For example:

`echo readme_parser( array( 'exclude' => 'meta,upgrade notice,support,changelog,links,installation,licence', 'ignore' => 'For help with this plugin,,for more information and advanced options ' ), 'YouTube Embed' );`

This may be of particular use to plugin developers because it allows the readme for their plugins to be displayed within their administration screens.

== Displaying the plugin banner ==

Displaying the plugin banner has been made obsolete because the plugin does not have access to the WordPress server where they are stored. USe of the 'readme_banner' shortcode will result in an error.

== Display specific readme information ==

To add your own section to the output (e.g. to provide download links), `exclude` the relevant section, then use an additional shortcode to retrieve the information that you need.

Use the shortcode `[readme_info]` to return one of a number of different pieces of information. Use the required parameter `data` to specify what you need; this can be:

* **download** – Display a link from where the plugin can be downloaded
* **version** – Output the latest version number
* **forum** – Display a link to the forum
* **wordpress** – Display a link to the plugin in the WordPress.org repository

For links, you must specify text between the opening and closing shortcodes to use as the link text.

There are four additional parameters:

* **name** – Specifies the plugin name. This is required
* **target** – If outputting a link, this will assign a target to the output (default is `_blank`)
* **nofollow** – If `yes`, this will be a `nofollow` link. By default, it won't be
* **cache** – By default, any output will be cached for five minutes so that if this shortcode is used multiple times on a page, the data will only be fetched once. Specify a different number (in minutes) to adjust this. Set to `no` to switch off caching

For example:

`[readme_info name="YouTube Embed" data="download"]Download YouTube Embed[/readme_info]'

== Acknowledgements ==

Plugin-readme Parser is based on a fork [Plugin README Parser](https://github.com/dartiss/plugin-readme-parser) (also known as WP README Parser) by [David Artiss].

Plugin-readme Parser uses [PHP Markdown](https://michelf.ca/projects/php-markdown/) by Michel Fortin.

== Installation ==

Follow [WordPress' instructions for installation of plugins](https://wordpress.org/support/article/managing-plugins/#installing-plugins-1) to install this plugin.

Now you're ready to add a shortcode to show off your plugin readme file.

== Frequently Asked Questions ==

= Can I change the look of the output? =

You can. The whole output is encased in a `<div>` with a `class` of `np-` followed by the plugin name (lower case and spaces converted to dashes).

Each section that has a `<div>` around it with a `class` of `np-` followed by the section name (lower case and spaces converted to dashes).

The download link has an additional `<div>` around it with a `class` of `np-download-link`.

Each of these `div`s can therefore be styled using your theme stylesheet.

== Changelog ==

Semantic versioning is used, with the first release being 1.0.

= 2.0.0 =

[The original plugin](https://github.com/dartiss/plugin-readme-parser) has been forked; this forked version is maintained by [Caity Ross](https://pandammonium.org/).

* Bug fix: updates markdown library; the old version broke the plugin when running on more recent versions of PHP.
* Bug fix: corrects variable name; a typo led to a broken plugin in certain circumstances.
* Bug fix: removes display of screenshots and banners because they aren't accessible from the plugin. The 'readme_banner' shortcode is now obsolete, and displays an error message if used; it will be removed from a later version.
* Bug fix: fixes non-display of 'Upgrade Notice' when explicitly included.
* Bug fix: standardises the quotation marks on the parameters in case the browser or WordPress has created curly ones, which can break the shortcode.
* Enhancement: adds support for block themes.
* Enhancement: changes behaviour of parameter include="meta,…". If this parameter is specified, it is changed to include="head,…"; without this change, no meta data at all would be output. A message indicating the change is written to the WordPress error log.
* Enhancement: refactors the code to reduce function size; moves most code into a class.
* Enhancement: uses exceptions and the WP_Error class to handle errors.

[Previous version history](https://plugins.trac.wordpress.org/browser/plugin-readme-parser/trunk/changelog.txt)

== Upgrade Notice ==

= 2.0.0 =

This reworking of WP README Parser has a new name and works with more recent versions of PHP. It also removes broken features (e.g. the display of screenshots). It is not backwards compatible WP README Parser.

= 1.3.7 =

Minor maintenance change.
