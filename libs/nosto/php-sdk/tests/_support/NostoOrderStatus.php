<?php

class NostoOrderStatus implements NostoOrderStatusInterface
{
	public function getCode()
	{
		return 'completed';
	}

	public function getLabel()
	{
		return 'Completed';
	}
}
