<?php

use \Nosto\Operation\UpdateSettings as NostoSDKUpdateSettingsOperation;
use \Nosto\Object\Settings as NostoSDKSettings;
use \Nosto\Object\Signup\Account as NostoSDKSignupAccount;

class NostoSettingsService
{
    private $account;

    public function __construct(NostoSDKSignupAccount $account)
    {
        $this->account = $account;
    }

    public function update(NostoSDKSettings $settings)
    {
        try {
            $service = new NostoSDKUpdateSettingsOperation($this->account);
            return $service->update($settings);
        } catch (Exception $e) {
            NostoHelperLogger::error(__CLASS__ . '::' . __FUNCTION__ . ' - ' . $e->getMessage(),
                $e->getCode());
        }
        return false;
    }
}
