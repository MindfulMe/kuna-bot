<?php

namespace App\Strategy\Traits;

use App\Exception\BreakIterationException;
use App\Exception\StopBotException;
use App\Service\KunaClient;
use madmis\ExchangeApi\Exception\ClientException;

/**
 * Class MinimumTradeAmount
 * @package App\Strategy\Traits
 * @method KunaClient getClient()
 * @method void info(string $message)
 */
trait MinimumTradeAmount
{
    /**
     * @param string $currency
     * @return bool
     * @throws BreakIterationException
     * @throws StopBotException
     */
    public function isBalanceAllowTrading(string $currency): bool
    {
        try {
            $account = $this->getClient()->getCurrencyAccount($currency);
        } catch (ClientException $e) {
            $ex = new BreakIterationException($e->getMessage(), $e->getCode(), $e);
            $ex->setTimeout(10);

            throw $ex;
        } catch (\Throwable $e) {
            throw new StopBotException($e->getMessage(), $e->getCode(), $e);
        }

        $balance = number_format($account->getBalance(), 8);
        $this->info("<g>Available funds: {$balance} {$currency}.</g>");

        $minAmount = $this->getClient()->minTradeAmount($currency);
        if ($account->getBalance() < $minAmount) {

            $this->info(sprintf(
                "\t<y>Current balance less than minimum trading amount (%s)</y>",
                number_format($minAmount, 8)
            ));

            return false;
        }

        return true;
    }
}
