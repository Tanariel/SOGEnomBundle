<?php

/*
 * This file is part of the SOG/EnomBundle
 *
 * (c) Shane O'Grady <shane.ogrady@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace SOG\EnomBundle\Services\Commands\Domain;

use SOG\EnomBundle\Services\HttpClient;

/**
 * Enom Domain Registration related operations
 *
 * @author Shane O'Grady <shane.ogrady@gmail.com>
 * @link   http://www.enom.com/APICommandCatalog/index.htm
 */
class Registration extends HttpClient
{
    /**
     * Check the availability of a domain name
     *
     * @param string $domain The domain name to check (e.g. example.com, NOT www.example.com)
     *
     * @return SimpleXMLElement
     */
    public function check($domain)
    {
        if (is_array($domain)) {
            throw new \InvalidArgumentException("Multiple domain name checks are not allowed");
        }

        $pieces = explode(".", $domain);

        $this->payload["sld"] = $pieces[0];
        $this->payload["tld"] = $pieces[1];

        $command = 'Check';
        $data = $this->makeRequest($command, $this->payload);

        return $data;
    }
    
    /**
     * Check the avilability of multiple domain names.
     * Provide a list of domains or a single domain with list of tld.
     *
     * @param string|array $list A string of a single second-level domain or an array of multiple full domains.
     * @param array [$tlds] An array of Top-level domains (if $list isn't an array)
     * @return |SimpleXMLElement
     */
    public function batchCheck($slds, $tlds = false)
    {
        if (is_array($slds) && is_array($tlds)) {
            throw new \InvalidArgumentException("DomainList and Tld list is not allowed in the same request");
        }

        if (is_array($slds)) {
            $this->payload["DomainList"] = implode(',', $slds);
        } elseif (is_array($tlds)) {
            $this->payload["SLD"] = $slds;
            $this->payload["TLDList"] = implode(',', $tlds);
        }

        $command = 'Check';
        $data = $this->makeRequest($command, $this->payload);

        return $data;
    }
    
    /**
     * Generate variations of a domain name based on a search term.
     *
     * @param string $search Term to use to generate suggestions.
     * @param int $maxResults
     * @param int $spinType
     * @param array $includeTlds
     * @param array $onlyTlds
     * @param array $excludeTlds
     * @param bool $adult
     * @param bool $premium
     * @return \SimpleXMLElement
     * @throws \SOG\EnomBundle\Services\EnomException
     */
    public function getNameSuggestions($search, $maxResults = 50, $spinType = 0, $includeTlds = [], $onlyTlds = [], $excludeTlds = [], $adult = false, $premium = true)
    {
        $this->payload["SearchTerm"] = $search;
        $this->payload["Adult"] = $adult ? 'True' : 'False';
        $this->payload["Premium"] = $premium ? 'True' : 'False';
        $this->payload["MaxResults"] = $maxResults;
        $this->payload["SpinType"] = $spinType;
        
        if (!empty($includeTlds)) {
            $this->payload['TldList'] = implode(',', $includeTlds);
        }
        if (!empty($onlyTlds)) {
            $this->payload['OnlyTldList'] = implode(',', $onlyTlds);
        }
        if (!empty($excludeTlds)) {
            $this->payload['ExcludeTldList'] = implode(',', $excludeTlds);
        }
        
        $command = 'GetNameSuggestions';
        $data = $this->makeRequest($command, $this->payload);

        return $data;
    }

    /**
     * Retrieve the settings for email confirmations of orders
     *
     * @return SimpleXMLElement
     */
    public function getConfirmationSettings()
    {
        $command = 'GetConfirmationSettings';
        $data = $this->makeRequest($command, $this->payload);

        return $data->ConfirmationSettings;
    }

    /**
     * This command retrieves the extended attributes for a country code TLD (required parameters specific to the country code)
     *
     * @param string $tld The Country Code Top Level Domain to check for extended attributes
     *
     * @return SimpleXMLElement
     */
    public function getExtAttributes($tld)
    {
        // Strip out any leading periods, e.g. ".co.uk" or ".de"
        $tld = ltrim($tld, " .");
        if (empty($tld)) {
            throw new \InvalidArgumentException("TLD cannot be empty");
        }

        $this->payload["tld"] = $tld;

        $command = 'GetExtAttributes';
        $data = $this->makeRequest($command, $this->payload);

        return $data->Attributes;
    }

    /**
     * Purchase a domain name in real time.
     *
     * @param string $tld The Country Code Top Level Domain to check for extended attributes
     *
     * @return SimpleXMLElement
     */
    public function purchase($domain, $numyears = 1, $autorenew = false, array $nameservers = null, $unlock = false, $password = null )
    {
        // Domain name
        $domain = trim($domain);
        if (empty($domain)) {
            throw new \InvalidArgumentException("Domain cannot be empty");
        }
        $pieces = explode(".", $domain);
        $this->payload["sld"] = $pieces[0];
        $this->payload["tld"] = $pieces[1];

        // Name servers
        if (is_array($nameservers) && (count($nameservers) > 0)) {
            foreach ($nameservers as $i => $server) {
                $this->payload["ns".($i+1)] = $server;
            }
        } else {
            $this->payload["usedns"] = "default";
        }

        // Locked or Not
        $this->payload["unlockregistrar"] = (int) $unlock;

        // AutoRenew
        $this->payload["renewname"] = (int) $autorenew;

        // Password
        if (!empty($password)) {
            $this->payload["domainpassword"] = $password;
        }

        // Number of Years to register for
        $this->payload["numyears"] = (int) $numyears;

        $command = 'Purchase';
        $data = $this->makeRequest($command, $this->payload);

        return $data;
    }
    
    /**
     * Retrieve a list of language codes currently supported for a TLD.
     *
     * @return \SimpleXMLElement
     */
    public function getIDNCodes($tld)
    {
        $this->payload["tld"] = $tld;

        $command = 'GetIDNCodes';
        $data = $this->makeRequest($command, $this->payload);

        return $data;
    }
}
