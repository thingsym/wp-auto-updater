=== WP Auto Updater ===
Contributors: thingsym
Link: https://github.com/thingsym/wp-auto-updater
Donate link: https://github.com/sponsors/thingsym
Tags: updates, auto update, automatic updates, background updates, core updates, theme updates, translation updates, plugin updates
Stable tag: 1.7.0
Tested up to: 6.4.0
Requires at least: 4.9
Requires PHP: 5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WP Auto Updater plugin enables automatic updates of WordPress Core, Themes, Plugins and Translations. Version control of WordPress Core makes automatic update more safely.

== Description ==

WP Auto Updater plugin enables automatic updates of WordPress Core, Themes, Plugins and Translations. Version control of WordPress Core makes automatic update more safely.

= Features =

* Automatically update WordPress Core
* Automatically updates Themes, Plugins and Translations
* Set up a schedule automatic updates
* Disable automatic updating of each Themes and Plugins
* Record update history
* Update notification

**Important**: before updating, please back up your database and files.

= Auto Update Scenario =

First of all, we will make an **Auto Update Scenario** which decide the policy of WordPress automatic updates.

You can choose from the following five automatic updates of WordPress Core.

* Minor Version Update
* Major Version Update
* Minor Only Version Update
* Previous Generation Version Update
* Manual Update

= Minor Version Update =

**Minor Version Update** enable minor updates. Minor updates is default behavior in WordPress for security updates. The transition of the version number is as follows: update from 4.8 to 4.8.1, 4.8.2 ...

= Major Version Update =

**Major Version Update** enable major updates. The transition of the version number is as follows: update from 4.7 to 4.8, 4.9 ...

= Minor Only Version Update =

**Minor Only Version Update** enable major updates and minor updates **except version x.y.0**. It make sense to take a "skip" approach to avoid introducing new vulnerabilities into the latest major version release.

Update the WordPress Core version (eg. x.y.1 or later) with security fixed. Not automatically update the latest major version of x.y.0. The transition of the version number is as follows: update from 4.7.z to 4.8.z, 4.9.z ... skiped 4.7.0, 4.8.0, 4.9.0 ...

= Previous Generation Version Update =

**Previous Generation Version Update** enable major updates and minor updates **except the latest major version**. It make sense to take a "wait and see" approach to ensure the latest major version release is stable before.

With the installed WordPress Core version as 4.6.z. If the latest WordPress Core version released to 4.8.0, automatically update it to version 4.7.z. It will be always automatically updated to the previous generation WordPress Core version with probably security fixed.

= Manual Update =

**Manual Update** disable automatic updates. You update WordPress Core manually on the Dashboard Updates Screen.

**Automatic updates** and **manual updates** are available for themes, plugins and Translations.
It is also possible to disable automatic updating of each Themes and Plugins.

= Scheduled automatic updates =

Next we will set up a schedule for automatic updates.
The update interval can be selected from the following four.

* Twice Daily (12 hours interval)
* Daily
* Weekly
* Monthly

You can also set the day, the day of the week, the hour and the minute of the Update Date.

At the time of automatic update, Automatically updates WordPress Core, Themes, Plugins and Translations to be updated.

= Support =

If you have any trouble, you can use the forums or report bugs.

* Forum: [https://wordpress.org/support/plugin/wp-auto-updater/](https://wordpress.org/support/plugin/wp-auto-updater/)
* Issues: [https://github.com/thingsym/wp-auto-updater/issues](https://github.com/thingsym/wp-auto-updater/issues)

= Contribution =

Small patches and bug reports can be submitted a issue tracker in Github. Forking on Github is another good way. You can send a pull request.

Translating a plugin takes a lot of time, effort, and patience. I really appreciate the hard work from these contributors.

If you have created or updated your own language pack, you can send gettext PO and MO files to author. I can bundle it into plugin.

* [VCS - GitHub](https://github.com/thingsym/wp-auto-updater)
* [Homepage - WordPress Plugin](https://wordpress.org/plugins/wp-auto-updater/)
* [Translate WP Auto Updater into your language.](https://translate.wordpress.org/projects/wp-plugins/wp-auto-updater)

You can also contribute by answering issues on the forums.

* Forum: [https://wordpress.org/support/plugin/wp-auto-updater/](https://wordpress.org/support/plugin/wp-auto-updater/)
* Issues: [https://github.com/thingsym/wp-auto-updater/issues](https://github.com/thingsym/wp-auto-updater/issues)

= Contribute guidlines =

If you would like to contribute, here are some notes and guidlines.

* All development happens on the **develop** branch, so it is always the most up-to-date
* The **master** branch only contains tagged releases
* If you are going to be submitting a pull request, please submit your pull request to the **develop** branch
* See about [forking](https://help.github.com/articles/fork-a-repo/) and [pull requests](https://help.github.com/articles/using-pull-requests/)

= Test Matrix =

For operation compatibility between PHP version and WordPress version, see below [Github Actions](https://github.com/thingsym/wp-auto-updater/actions).

== Installation ==

1. Download and unzip files. Or install **WP Auto Updater** using the WordPress plugin installer. In that case, skip 2.
2. Upload **wp-auto-updater** to the "/wp-content/plugins/" directory.
3. Activate the plugin through the ' Plugins' menu in WordPress.
4. Configure settings through the **Dashboard > Auto Updater** menu in WordPress.
5. Have fun!

= How do I use it ? =

1. Make an Auto Update Scenario
2. Set up a schedule automatic updates
3. Disable Auto Update Themes and Plugins if necessary
4. Automatically updates WordPress Core, Themes, Plugins and Translations to be updated at the time of automatic update
5. The update history will be recorded

== Frequently Asked Questions ==

= Why not update on scheduled time ? =

The possible causes are as follows:

* The cron schedule was updated somewhere else.
* The cron schedule has been reset.

For example, when updating with wp-cli, the cron schedule may be updated.
The cron schedule does not match the one set in WP Auto Updater.
In that case, an alert is displayed on the settings screen.

= Why are themes or plugins not updating at once ? =

Depending on the update interval, it may not be surely updated.
If you update monthly, there are too many themes and plugins to update and you cannot update at once.
We recommend shortening the update interval.

== Screenshots ==

1. Auto Update settings
2. Auto Update History
3. WordPress Update Process Chart

== Changelog ==

= 1.7.0 =
* tested up to 6.2.0
* update japanese translation
* update pot
* add test case
* add last day to schedule
* fix composer scripts
* update github actions
* set auto_update_core_major to disable when activate
* add support section and enhance contribution section
* add support section and enhance contribution section to README
* fix license
* fix wp-plugin-unit-test.yml

= 1.6.3 =
* change makepot from php script to wp cli
* change plugin initialization to plugins_loaded hook
* replace assert from assertEquals to assertSame

= 1.6.2 =
* change requires at least to wordpress 4.9
* change requires to PHP 5.6
* add test case

= 1.6.1 =
* update composer dependencies
* fix test case
* separate the method into print_update_message
* add load_plugin_data method
* change from protected variable to public variable for unit test
* add timeout-minutes to workflows
* add phpunit-polyfills
* update install-wp-tests.sh
* fix .editorconfig
* tested up to 5.8.0

= 1.6.0 =
* update japanese translation
* update pot
* add an option to delete logs for a specified period range
* add per_page screen option
* fix cron schedule warning
* add timezone string
* add correction for timestamp when the time has passed

= 1.5.1 =
* tested up to 5.7.0
* update japanese translation
* update pot
* add comment for translators
* fix composer scripts
* add test case
* add sponsor link
* add FUNDING.yml
* add donate link
* update wordpress-test-matrix
* fix Trying to access array offset on value of type null on PHP7.4
* add GitHub actions for CI/CD, remove .travis.yml

= 1.5.0 =
* update japanese translation
* update pot
* imporve code with phpcs, phpmd and phpstan
* update testunit configuration
* fix composer.json
* fix test case
* disable auto-update UI elements
* disable theme and plugin auto-update notification email
* change hook tag
* fix to send only built-in core update notification email

= 1.4.0 =
* fix validate
* fix Unexpected deprecated notice for WP_User->id
* update screenshot
* fix indent and reformat with phpcs and phpcbf
* refactoring with phpunit
* bump up phpunit version to 7
* add phpunit coverage composer script
* add test case
* remove duplicate load_textdomain
* change how options are merged
* display warning in case the cron schedule is out of sync
* fix phpdoc and add phpdoc
* add notification function

= 1.3.0 =
* add link to WordPress Update Process Chart screenshot
* fix pot
* remove jQuery dependency in form_controller function

= 1.2.3 =
* add installed info
* add reset-wp-tests.sh, uninstall-wp-tests.sh
* fix pagination

= 1.2.2 =
* add WordPress Update Process Chart screenshot
* fix test case
* add test case for floating point
* refactoring with phpmd and phpstan

= 1.2.1 =
* fix float comparison with version number difference

= 1.2.0 =
* update japanese language
* update pot
* change hook name
* refactoring with phpcs
* fix indent and reformat with phpcs and phpcbf
* add composer.json for test
* add static code analysis config

= 1.1.0 =
* improve CI environment
* add version number before the update in the logs
* gather present version
* add user in the logging
* improve table creation prosess
* add admin notice
* add table migration function
* add user column, update history table v1.0.1

= 1.0.1 =
* fix readme
* add PHPDoc comments

= 1.0.0 =
* initial release

== Upgrade Notice ==

= 1.6.2 =
* Requires at least version 4.9 of the WordPress
* Requires PHP version 5.6
