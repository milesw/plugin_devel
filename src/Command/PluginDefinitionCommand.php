<?php

/**
 * @file
 * Contains \Drupal\plugin_devel\Command\PluginDefinitionCommand.
 */

namespace Drupal\plugin_devel\Command;

use Drupal\Component\Render\MarkupInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Command\ContainerAwareCommand;
use Drupal\Console\Style\DrupalStyle;

/**
 * Class PluginDefinitionCommand.
 *
 * @package \Drupal\plugin_devel
 */
class PluginDefinitionCommand extends ContainerAwareCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('plugin:definition')
      ->setDescription($this->trans('Dump the definition for a particular plugin instance'))
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
      $value = is_object($value) && method_exists($value, '__toString') ? (string) $value : $value;
      $value = (is_array($value) || is_object($value)) ? var_export($value, TRUE) : $value;
      $tableRows[$key] = [$key, $value];
    }
    ksort($tableRows);

    return $io->table($tableHeader, array_values($tableRows));
  }
}
