<?php
if (!defined('_PS_VERSION_'))
  exit;

/**
 * NostoTagging module that integrates Nosto marketing automation service.
 */
class NostoTagging extends Module
{
    /**
     * Constructor.
     *
     * Defines module attributes.
     */
    public function __construct()
    {
        $this->name = 'nostotagging';
        $this->tab = 'advertising_marketing';
        $this->version = '1.0.0';
        $this->author = 'Nosto Solutions Ltd';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('Nosto Tagging');
        $this->description = $this->l('Integrates Nosto marketing automation service.');
    }

    /**
     * Installs the module.
     *
     * Initializes config, adds custom hooks and registers used hooks.
     *
     * @return bool
     */
    public function install()
    {
        return parent::install();
    }

    /**
     * Uninstalls the module.
     *
     * Removes used config values. No need to un-register any hooks,
     * as that is handled by the parent class.
     *
     * @return bool
     */
    public function uninstall()
    {
        return parent::uninstall();
    }
}
