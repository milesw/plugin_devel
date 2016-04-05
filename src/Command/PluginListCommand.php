<?php

/**
 * @file
 * Contains \Drupal\plugin_devel\Command\PluginListCommand.
 */

namespace Drupal\plugin_devel\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Command\ContainerAwareCommand;
use Drupal\Console\Style\DrupalStyle;

/**
 * Class PluginListCommand.
 *
 * @package \Drupal\plugin_devel
 */
class PluginListCommand extends ContainerAwareCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('plugin:list')
      ->setDescription($this->trans('Lists all plugin types, or plugins of a particular type.'))
      ->addArgument('type', InputArgument::OPTIONAL, $this->trans('Plugin type'));
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $io = new DrupalStyle($input, $output);

    $pluginType = $input->getArgument('type');

    // No plugin type specified, display a list of plugin types.
    if (!$pluginType) {
      $tableHeader = [
        $this->trans('Plugin type'),
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

    // Valid plugin type specified, display a list of plugin IDs.
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
}
