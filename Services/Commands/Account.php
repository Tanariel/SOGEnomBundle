<?php

/*
 * This file is part of the SOG/EnomBundle
 *
 * (c) Shane O'Grady <shane.ogrady@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace SOG\EnomBundle\Services\Commands;

use SOG\EnomBundle\Services\HttpClient;

/**
 * Enom Account Related operations
 *
 * @author Shane O'Grady <shane.ogrady@gmail.com>
 * @link   http://www.enom.com/APICommandCatalog/index.htm
 */
class Account extends HttpClient
{

    /**
     * Get Account Info
     *
     * @return \SimpleXMLElement Account Information
     */
    public function getAccountInfo()
    {
        $command = 'GetAccountInfo';
        $this->makeRequest($command, $this->payload);

        return $this;
    }

    /**
     * Retrieve the customer service contact information for a domain name account.
     *
     * @return \SimpleXMLElement
     */
    public function getServiceContact()
    {
        $command = 'GetServiceContact';
        $this->makeRequest($command, $this->payload);

        return $this;
    }

    /**
     * Get a list of the orders placed through this account.
     * Return sets of 25 records in reverse chronological order
     *
     * @param string $start The record to begin at (i.e. $start=26 returns the 26th through 50th most recent orders)
     * @param string $begin MM/DD/YYYY Beginning date of orders to return
     * @param string $end   MM/DD/YYYY End date of orders to return
     *
     * @return \SimpleXMLElement
     */
    public function getOrderList($start = 1, $begin = null, $end = null)
    {
        $this->payload['start'] = $start;

        if (isset($begin)) {
            $this->payload['begindate'] = $begin;
        }

        if (isset($end)) {
            $this->payload['enddate'] = $end;
        }

        $command = 'GetOrderList';
        $this->makeRequest($command, $this->payload);

        return $this;
    }
    
    /**
     * Get Account Balance
     *
     * @return \SimpleXMLElement Account Information
     */
    public function getBalance()
    {
        $command = 'GetBalance';
        $this->makeRequest($command, $this->payload);

        return $this;
    }
    
    /**
     * Retrieve the settings for email confirmations of orders
     *
     * @return \SimpleXMLElement
     */
    public function getConfirmationSettings()
    {
        $command = 'GetConfirmationSettings';
        $this->makeRequest($command, $this->payload);

        return $this;
    }
    
    /**
     * Retrieve the list of domains for the account.
     *
     * @return \SimpleXMLElement
     */
    public function getAllDomains()
    {
        $command = 'GetAllDomains';
        $this->makeRequest($command, $this->payload);

        return $this;
    }
}
