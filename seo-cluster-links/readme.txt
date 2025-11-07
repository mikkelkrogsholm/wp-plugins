=== SEO Cluster Links ===
Contributors: mikkelkrogsholm
Tags: seo, internal linking, pillar content, content clusters, content marketing
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.0
Stable tag: 1.0.0
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

Link pillar posts and cluster posts together automatically for better SEO and user experience.

== Description ==

SEO Cluster Links helps you build a strong internal linking structure between your pillar posts (main articles) and cluster posts (related articles). It improves both SEO and user experience by automatically displaying relevant links.

= Key Features =

* **Pillar Posts** - Mark main articles as pillar posts
* **Cluster Posts** - Mark related articles and link them to a pillar
* **Automatic Links** - Automatically shows related articles at the bottom of posts
* **Cross-linking** - Cluster posts link to pillar + other clusters
* **Clean Design** - Responsive and easy to style with your theme
* **Simple Interface** - Easy-to-use meta box in the post editor

= How It Works =

**On Pillar Posts:**
* Displays a list of all related cluster posts
* Updates automatically when you add new clusters

**On Cluster Posts:**
* Shows a highlighted link back to the pillar post
* Shows links to other cluster posts in the same group

This plugin implements the content cluster SEO strategy, which helps search engines understand the topical authority of your website by organizing content into pillar and cluster relationships.

= Perfect For =

* Content marketers
* SEO specialists
* Bloggers building topical authority
* Websites with extensive content libraries

== Installation ==

1. Upload the `seo-cluster-links` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Start marking your posts as Pillar or Cluster posts

== Frequently Asked Questions ==

= How do I create a Pillar Post? =

1. Write or edit a blog post
2. Find the "SEO Cluster Settings" box in the sidebar
3. Select "Pillar Post"
4. Publish your post

= How do I create a Cluster Post? =

1. Write or edit a blog post
2. Find the "SEO Cluster Settings" box in the sidebar
3. Select "Cluster Post"
4. Choose which Pillar Post this belongs to
5. Publish your post

= Can I customize where the links appear? =

Yes! Use the `[cluster_links]` shortcode to place links anywhere in your content instead of at the bottom.

= Can I style the link display? =

Yes! The plugin includes basic styling that works with most themes. You can override the CSS in your theme:

`.scl-links-container {
    background: #your-color;
    border-left-color: #your-accent;
}`

= What happens to my data if I uninstall the plugin? =

When you uninstall the plugin, all post meta data (post type classifications and pillar associations) will be removed from your database.

= Does this work with any post type? =

Currently, the plugin works with standard WordPress posts. Custom post type support may be added in future versions.

= Is this compatible with page builders? =

Yes, the plugin works with any theme or page builder. The links are added via WordPress hooks and can be controlled with the shortcode.

== Screenshots ==

1. Meta box in post editor for selecting post type (Pillar or Cluster)
2. Pillar post selection dropdown for cluster posts
3. Automatic link display on pillar posts showing all clusters
4. Automatic link display on cluster posts showing pillar and related clusters

== Changelog ==

= 1.0.0 =
* Initial release
* Pillar and Cluster post classification
* Automatic link generation
* Meta box interface
* Frontend display with responsive design
* Shortcode support

== Upgrade Notice ==

= 1.0.0 =
Initial release of SEO Cluster Links.

== Additional Information ==

= Technical Details =

* Uses WordPress singleton pattern for all classes
* Follows WordPress Coding Standards
* KISS (Keep It Simple, Stupid) and DRY (Don't Repeat Yourself) principles
* Clean, maintainable code architecture

= Support =

For bug reports or feature requests, please open an issue on [GitHub](https://github.com/mikkelkrogsholm/wp-plugins).

= Credits =

Developed by Mikkel Krogsholm
