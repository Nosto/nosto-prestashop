<?php

use Nosto\Operation\UpdateSettings;
use Nosto\Types\SettingsInterface;
use Nosto\Types\Signup\AccountInterface;

class NostoSettingsService
{
    private $account;

    public function __construct(AccountInterface $account)
    {
        $this->account = $account;
    }

    public function update(SettingsInterface $settings)
    {
        try {
            $service = new UpdateSettings($this->account);
            return $service->update($settings);
        } catch (Exception $e) {
            NostoHelperLogger::error(__CLASS__ . '::' . __FUNCTION__ . ' - ' . $e->getMessage(),
                $e->getCode());
        }
        return false;
    }
}
