# Plugin Devel

![Screenshot of terminal commands](https://raw.githubusercontent.com/milesw/plugin_devel/media/screenshot.png)

This is a Drupal 8 module containing Drupal Console commands useful for developing and debugging plugins.

**Only plugin types exposed as services are listed.** When creating a custom plugin type, be sure to expose it as a service by adding it to your services.yml with the name "plugin.manager.PLUGIN_TYPE".

Documentation on plugin types: https://www.drupal.org/node/1637730

## Commands

### drupal plugin:list
Lists all plugin types and the corresponding plugin manager class.

### drupal plugin:list [type]
Lists all plugins instances of a particular type.

### drupal plugin:debug [type] [id]
Dumps the plugin definition for a particular plugin instance.
