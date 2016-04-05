<?php

/**
 * @file
 * Contains \Drupal\plugin_devel\Command\PluginDebugCommand.
 */

namespace Drupal\plugin_devel\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Command\ContainerAwareCommand;
use Drupal\Console\Style\DrupalStyle;

/**
 * Class PluginDebugCommand.
 *
 * @package \Drupal\plugin_devel
 */
class PluginDebugCommand extends ContainerAwareCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('plugin:debug')
      ->setDescription($this->trans('Display all plugin types, plugin instances of a specific type, or a specific plugin instance.'))
      ->addArgument('type', InputArgument::OPTIONAL, $this->trans('Plugin type'))
      ->addArgument('id', InputArgument::OPTIONAL, $this->trans('Plugin ID'));
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $io = new DrupalStyle($input, $output);

    $pluginType = $input->getArgument('type');
    $pluginId = $input->getArgument('id');

    // No plugin type specified, show a list of plugin types.
    if (!$pluginType) {
      $tableHeader = [$this->trans('Plugin type'),
        $this->trans('Plugin manager class')
      ];
      $tableRows = [];
      foreach ($this->getServices() as $serviceId) {
        if (strpos($serviceId, 'plugin.manager.') === 0) {
          $service = $this->getContainer()->get($serviceId);
          $typeName = substr($serviceId, 15);
          $class = get_class($service);
          $tableRows[$typeName] = [$typeName, $class];
        }
      }
      ksort($tableRows);
      return $io->table($tableHeader, array_values($tableRows));
    }

    $service = $this->getService('plugin.manager.' . $pluginType);
    if (!$service) {
      return $io->info('Plugin type "' . $pluginType . '" not found. No service available for that type.');
    }

    // Valid plugin type specified, no ID specified, show list of instances.
    if (!$pluginId) {
      $tableHeader = [
        $this->trans('Plugin ID'),
        $this->trans('Plugin class')
      ];
      $tableRows = [];
      foreach ($service->getDefinitions() as $definition) {
        $pluginId = $definition['id'];
        $className = $definition['class'];
        $tableRows[$pluginId] = [$pluginId, $className];
      }
      ksort($tableRows);
      return $io->table($tableHeader, array_values($tableRows));
    }

    // Valid plugin type specified, ID specified, show the instance definition.
    $definition = $service->getDefinition($pluginId);

    $tableHeader = [
      $this->trans('Key'),
      $this->trans('Value')
    ];
    $tableRows = [];
    foreach ($definition as $key => $value) {
      $value = is_object($value) && method_exists($value, '__toString') ? (string) $value : $value;
      $value = (is_array($value) || is_object($value)) ? var_export($value, TRUE) : $value;
      $tableRows[$key] = [$key, $value];
    }
    ksort($tableRows);
    return $io->table($tableHeader, array_values($tableRows));
  }
}
