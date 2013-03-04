<?php
if (!defined('_PS_VERSION_'))
  exit;

/**
 * NostoTagging module that integrates Nosto marketing automation service.
 */
class NostoTagging extends Module
{
    const NOSTOTAGGING_CONFIG_KEY_SERVER_ADDRESS = 'NOSTOTAGGING_SERVER_ADDRESS';
    const NOSTOTAGGING_CONFIG_KEY_ACCOUNT_NAME = 'NOSTOTAGGING_ACCOUNT_NAME';
    const NOSTOTAGGING_DEFAULT_SERVER_ADDRESS = 'connect.nosto.com';

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
        return parent::install() && $this->initConfig();
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
        return parent::uninstall() && $this->deleteConfig();
    }

    /**
     * Renders the HTML for the module configuration form.
     *
     * @return string The admin form HTML
     */
    public function getContent()
    {
        $messages = array();
        $errors = array();

        if (Tools::isSubmit($this->name.'_admin_submit'))
        {
            $errors = $this->validateAdminForm();
            if (empty($errors))
            {
                $this->setServerAddress(Tools::getValue($this->name.'_server_address'));
                $this->setAccountName(Tools::getValue($this->name.'_account_name'));
                $messages[] = $this->l('Configuration saved');
            }
        }

        $form_action = AdminController::$currentIndex.'&configure='.$this->name;
        $form_action.= '&token='.Tools::getAdminTokenLite('AdminModules');

        $this->smarty->assign(array(
            'messages' => $messages,
            'errors' => $errors,
            'form_action' => $form_action,
            'server_address' => $this->getServerAddress(),
            'account_name' => $this->getAccountName(),
        ));

        return $this->display(__FILE__, 'views/templates/admin/form.tpl');
    }

    /**
     * Getter for the Nosto server address.
     *
     * @return string
     */
    public function getServerAddress()
    {
        return Configuration::get(self::NOSTOTAGGING_CONFIG_KEY_SERVER_ADDRESS);
    }

    /**
     * Setter for the Nosto server address.
     *
     * @param string $server_address
     * @return bool
     */
    public function setServerAddress($server_address)
    {
        return Configuration::updateValue(
            self::NOSTOTAGGING_CONFIG_KEY_SERVER_ADDRESS,
            $server_address
        );
    }

    /**
     * Getter for the Nosto account name.
     *
     * @return string
     */
    public function getAccountName()
    {
        return Configuration::get(self::NOSTOTAGGING_CONFIG_KEY_ACCOUNT_NAME);
    }

    /**
     * Setter for the Nosto account name.
     *
     * @param string $account_name
     * @return bool
     */
    public function setAccountName($account_name)
    {
        return Configuration::updateValue(
            self::NOSTOTAGGING_CONFIG_KEY_ACCOUNT_NAME,
            $account_name
        );
    }

    /**
     * Validates the admin form post data.
     *
     * @return array List of error messages
     */
    protected function validateAdminForm()
    {
        $errors = array();

        $server_address = Tools::getValue($this->name.'_server_address');
        $account_name = Tools::getValue($this->name.'_account_name');

        if (!Validate::isUrl($server_address))
            $errors[] = $this->l('Server address is not a valid URL.');

        if (preg_match('@^https?://@i', $server_address))
            $errors[] = $this->l('Server address cannot contain the protocol (http or https).');

        if (empty($account_name))
            $errors[] = $this->l('Account name cannot be empty.');

        return $errors;
    }

    /**
     * Adds initial config values for Nosto server address and account name.
     *
     * @return bool
     */
    protected function initConfig()
    {
        return ($this->setServerAddress(self::NOSTOTAGGING_DEFAULT_SERVER_ADDRESS)
            && $this->setAccountName(''));
    }

    /**
     * Deletes all config entries created by the module.
     *
     * @return bool
     */
    protected function deleteConfig()
    {
        return (Configuration::deleteByName(self::NOSTOTAGGING_CONFIG_KEY_SERVER_ADDRESS)
            && Configuration::deleteByName(self::NOSTOTAGGING_CONFIG_KEY_ACCOUNT_NAME));
    }
}
