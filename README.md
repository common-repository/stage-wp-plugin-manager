# Stage WP Plugin Manager

This WordPress plugin gives you the option to determine which plugins must be automatically activated either on local, staging or productions stages. By hooking in to the list of active plugins, it lets you keep different setups for each stage, and you can even add your own stages in case you need more than the default ones.

**Current stable version:** [1.0](http://github.com/andrezrv/stage-wp-plugin-manager/tree/1.0)

## Description

If you develop in a local machine, at some point you'll have some active plugins there that you don't need in the servers that you're deploying to. Sure, you can deactivate them in your local machine before deploying, or after deploying in the remote ones, but you're gonna need them again to be active if you want to perform local work in the future, specially when you update your local database from a remote one. On such kind of scenarios, the whole process of manually activating and deactivating plugins for each stage can be really frustrating, sometimes even taking a lot of your time.

Stage WP Plugin Manager is meant to solve that problem in an quick and elegant way, by doing an automatic "fake"  activation of the plugins you select for each stage: every plugin you attach to a stage will be immediatly treated as an active plugin on that stage, no matter what its previous status was, or its current status on the other stages. Since the list of active plugins is just filtered instead of rewritten, you can restore the previous status of a plugin by detaching it, and go back to your original setup by deactivating Stage WP Plugin Manager.

Of course, there's always a catch: Stage WP Plugin Manager doesn't work right out of the box in most installations, so besides from installing this plugin, you may need to do a little additional work.

## Getting Started

Stage WP Plugin Manager works on some assumptions about your workflow:

* You have a constant named `WP_STAGE` defined in your WordPress configuration file (often `wp-config.php`).
* The value of `WP_STAGE` is one of the supported stages. The default supported stages are `local`, `staging` and `production`.
* The value of `WP_STAGE` is different in each of your stages.

Some developers prefer to keep different configuration files for each one of their stages, or change the values of their constants based on some evaluation. For example, you could have something like this in your `wp-config.php` file:

```php
if ( file_exists( dirname( __FILE__ ) . '/local-config.php' ) ) {
	define( 'WP_STAGE', 'local' );
} elseif ( file_exists( dirname( __FILE__ ) . '/staging-config.php' ) ) {
	define( 'WP_STAGE', 'staging' );
} else {
	define( 'WP_STAGE', 'production' );
}
```

If you follow this example, note that `local-config.php` should not be included in your deployments to staging and production, and both `local-config.php` and `staging-config.php` should not exist in your production stage.

## Attach & Detach

Once you have installed this plugin, you will notice that a new link appears under each active plugin of the list, which reads "Attach to [your-stage] stage". By clicking that link, you are setting a plugin to be always active in the stage you're working at, and not active on the other stages (unless you attach the plugin to the other stages too).

In case you want to remove a plugin from the list of active plugins for a stage, you just need to click the "Detach from [your-stage] stage".

Additionally, you can make bulk selections of plugins to be attached or detached for each stage by going to ** *Plugins > Stage Management* **.

## Add & Extend Functionality

Stage WP Plugin Manager allows you to extend its functionality by offering hooks for filters and actions.

For example, you can add your custom stages or even remove the default ones by hooking in the `stage_wp_plugin_manager_stages` filter, having something like this in your plugin:

```php
function add_stages( $stages ) {
	$stages[] = 'other';
	return $stages;
}
add_filter( 'stage_wp_plugin_manager_stages', 'add_stages' );
```
Additionally, you can give a nicer name to your new stage by hooking in the `stage_wp_plugin_manager_stages_txt` filter:

```php
function add_stages_txt( $stages_txt ) {
	$stages_txt['other'] = __( 'Other stage' );
	return $stages_txt;
}
add_filter( 'stage_wp_plugin_manager_stages_txt', 'add_stages_txt' );
```

Here's the complete list of actions and filters that Stage WP Plugin Manager offers:

##### Action hooks

* `stage_wp_plugin_manager_before_init`: Perform some process before Stage WP Plugin Manager initializes.
* `stage_wp_plugin_manager_init`: Perform some process after Stage WP Plugin Manager initializes.

##### Filter hooks

* `stage_wp_plugin_manager_stages>`: Modifiy the current supported stages.
* `stage_wp_plugin_manager_default_stage`: Modify the default stage.
* `stage_wp_plugin_manager_managed_plugins`: Modify the list of plugins managed by Stage WP Plugin Manager.
* `stage_wp_plugin_manager_stage_plugins`: Modify the list of plugins attached to the current stage.
* `stage_wp_plugin_manager_non_stage_plugins`: Modify the list of managed plugins that are not attached to the current stage.
* `stage_wp_plugin_manager_nonce_key`: Modify the nonce key used for data validation.
* `stage_wp_plugin_manager_help_description`: Modify contents of "Description" help tab.
* `stage_wp_plugin_manager_help_getting_started`: Modify contents of "Getting Started" help tab
* `stage_wp_plugin_manager_help_attach_detach`: Modify contents of "Attach & Detach Plugins" help tab
* `stage_wp_plugin_manager_help_add_extend`: Modify contents of "Adding Stages & Extending" help tab
* `stage_wp_plugin_manager_help_credits`: Modify contents of "Credits" help tab

## WordPress MultiSite Compatibility

If you're using MultiSite and set this plugin to network activated, you can use it to attach plugins to stages on a sitewide basis :)

## Improve Your Workflow

This plugin was originally meant as a complement for [WP Bareboner](http://github.com/andrezrv/wordpress-bareboner), an advanced Git model repo, and [Stage WP](http://github.com/andrezrv/stage-wp), a deployment tool based in Capistrano. The three projects work really well separated, but their real power can only be seen by using them together.

## Installation

1. `git clone git@github.com:andrezrv/stage-wp-plugin-manager` into `/wp-content/plugins/`, or unzip `stage-wp-plugin-manager.zip` and upload the `stage-wp-plugin-manager` folder to your `/wp-content/plugins/` directory.
2. Activate the plugin through the *Plugins* menu in WordPress.
3. Read carefully the instructions above.

## Contributing

If you feel like you want to help this project by adding something you think useful, you can make your pull request against the master branch :)

## Donating

This plugin took a lot of work. If it is helpful to you on a regular basis, you may want to consider [making a donation](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=B7XQG5ZA36UZ4). I'll be really thankful with that, and try my best to provide a great support for all the people who use it :). Donations are processed securely via [Paypal](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=B7XQG5ZA36UZ4).

## Changelog

#### 1.0
First public release.
