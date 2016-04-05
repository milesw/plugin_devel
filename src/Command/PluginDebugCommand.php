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
      ->setDescription($this->trans('Dumps the definition array for a specific plugin.'))
      ->addArgument('type', InputArgument::REQUIRED, $this->trans('Plugin type'))
      ->addArgument('id', InputArgument::REQUIRED, $this->trans('Plugin ID'));
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $io = new DrupalStyle($input, $output);

    $pluginType = $input->getArgument('type');
    $pluginId = $input->getArgument('id');

    $serviceId = "plugin.manager.{$pluginType}";
    $service = $this->getContainer()->get($serviceId);
    if (!$service) {
      return $io->info('Plugin type "' . $pluginType . '" not found. No service available for that type.');
    }

    $definition = $service->getDefinition($pluginId);

    $tableHeader = [
      $this->trans('Key'),
      $this->trans('Value')
    ];
    $tableRows = [];
    foreach ($definition as $key => $value) {
      $value = (is_array($value) || is_object($value)) ? var_export($value) : $value;
      $tableRows[$key] = [$key, $value];
    }
    ksort($tableRows);

    return $io->table($tableHeader, array_values($tableRows));
  }
}