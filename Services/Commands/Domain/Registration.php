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
     * @return \SimpleXMLElement
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
     * @return \SimpleXMLElement
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
     * Retrieve TLD characteristics in detail for a specified TLD.
     *
     * @param string $tld Top-level domain
     * @return \SimpleXMLElement
     */
    public function getTLDDetails($tld)
    {
        $this->payload["tld"] = $tld;

        $command = 'GetTLDDetails';
        $data = $this->makeRequest($command, $this->payload);

        return $data->tlds;
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

        return $data->DomainSuggestions;
    }

    /**
     * This command retrieves the extended attributes for a country code TLD (required parameters specific to the country code)
     *
     * @param string $tld The Country Code Top Level Domain to check for extended attributes
     *
     * @return \SimpleXMLElement
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

        return $data->tlds;
    }
    
    /**
     * Check a trademark claim for a domain name from Trademark Clearinghouse (TMCH).
     *
     * @param string $sld Secondary-level domain name
     * @param string $tld Top-level domain name
     * @return \SimpleXMLElement
     */
    public function tmCheck($sld, $tld)
    {
        $this->payload["sld"] = $sld;
        $this->payload["tld"] = $tld;

        $command = 'TM_Check';
        $data = $this->makeRequest($command, $this->payload);

        return $data->TM_Check;
    }
    
    /**
     * Retrieve an itemized list of Trademark Clearinghouse (TMCH) Claims for an SLD using a Lookup Key.
     *
     * @param string sld Second-level domain name
     * @param string $lookupKey A unique Lookup Key for a domain. Use the TM_Check command to retrieve the value.
     * @return \SimpleXMLElement
     */
    public function tmGetNotice($sld, $lookupKey)
    {
        $this->payload["sld"] = $sld;
        $this->payload["lookupKey"] = $lookupKey;

        $command = 'TM_GetNotice';
        $data = $this->makeRequest($command, $this->payload);

        return $data;
    }
    
    /**
     * Delete a domain name registration.
     * (require special agreement with enom)
     *
     * @param string $sld Second-level domain name.
     * @param string $tld Top-level domain name.
     * @param string $ip End User's IP address. (###.###.###.###)
     * @return \SimpleXMLElement
     */
    public function deleteRegistration($sld, $tld, $ip)
    {
        $this->payload["sld"] = $sld;
        $this->payload["tld"] = $tld;
        $this->payload["EndUserIP"] = $ip;

        $command = 'deleteregistration';
        $data = $this->makeRequest($command, $this->payload);

        return $data;
    }
    
    /**
     * Retrieve the up-to-date Registration Agreement.
     *
     * @param string $page Which agreement to retrieve. (See: http://www.enom.com/APICommandCatalog/API%20topics/api_GetAgreementPage.htm)
     * @param string $language Language of agreement. [Eng, English, Ger, German, Por, Portuguese, Spa, Spanish] (Default is English)
     * @return \SimpleXMLElement
     */
    public function getAgreementPage($page = 'agreement', $language = 'Eng')
    {
        $this->payload["page"] = $page;
        $this->payload["language"] = $language;

        $command = 'GetAgreementPage';
        $data = $this->makeRequest($command, $this->payload);

        return $data->content;
    }
    
    /**
     * Generate variations of a domain name that you specify, for .com, .net, .tv, and .cc TLDs.
     *
     * @param string $sld Second-level domain name
     * @param string [$tld] Top-level domain name (Default: 'com')
     * @param array [$tldlist] List of top-level domains
     * @param boolean [$sensitivecontent] Block potentially offensive content. (Default: false)
     * @param int [$maxResults] Maximum number of suggested names to return in addition to your input.
     * @param int [$max] Maximum length of SLD to return.
     * @param boolean [$hyphens] Return domain names that include hyphens. (Default: false)
     * @param boolean [$numbers] Return domains that include numbers. (Default: true)
     * @param string [$basic] Higher values return suggestions that are built by addeding prefixes, suffices and words to sld. (Off, Low, Medium, High) (Default: medium)
     * @param string [$related] Higher vlues return domain names by interpreting the input semnatically and construct suggestions with similar meaning. (Off, Low, Medium, High) (Default: high)
     * @param string [$similar] Higher values return suggestions that are similar to customer's input but not necessarily in meaning. (Off, Low, Medium, High) (Default: medium)
     * @param string [$topical] Higher values return suggestions that reflect current topics and popular words. (Off, Low, Medium, High) (Default: high)
     * @return \SimpleXMLElement
     */
    public function nameSpinner(
        $sld,
        $tld = 'com',
        $tldlist = [],
        $sensitivecontent = false,
        $maxResults = 20,
        $max = 64,
        $hyphens = false,
        $numbers = true,
        $basic = 'medium',
        $related = 'high',
        $similar = 'medium',
        $topical = 'medium'
    ) {
        $this->payload["sld"] = $sld;
        $this->payload["tld"] = $tld;
        $this->payload["tldlist"] = implode(',', $tldlist);
        $this->payload["sensitivecontent"] = $sensitivecontent;
        $this->payload["MaxResults"] = $maxResults;
        $this->payload["maxlength"] = $max;
        $this->payload["usehyphens"] = $hyphens;
        $this->payload["usenumbers"] = $numbers;
        $this->payload["basic"] = $basic;
        $this->payload["related"] = $related;
        $this->payload["similar"] = $similar;
        $this->payload["topical"] = $topical;


        $command = 'NameSpinner';
        $data = $this->makeRequest($command, $this->payload);

        return $data->namespin;
    }
    
    /**
     * Get Reseller Price for domains
     * Product type for domains. Permitted values are:
     * 10 Domain registration
     * 13 DNS hosting
     * 14 DNS hosting renew
     * 16 Domain renewal
     * 17 Domain redemption grace period (RGP)
     * 18 Domain Extended 63 RGP (available at our discretion, and decided by us on a name-by-name basis)
     * 19 transfer
     * 41 Registration and email for- warding by the .name Registry
     * 44 .name registration and email forwarding renewal
     *
     * @param int [$productype] Product type
     *
     * @return \SimpleXMLElement Account Information
     */
    public function getDomainResellerPrice($productype = 10, $tld = 'com', $years = 1)
    {
        $this->payload["ProductType"] = $productype;
        $this->payload["tld"] = $tld;
        $this->payload["Years"] = $years;
        $command = 'PE_GetResellerPrice';
        $data = $this->makeRequest($command, $this->payload);

        return $data->productprice;
    }
    
    /**
     * Get Retail Price for domains
     * Permitted values are:
     * 10 Domain registration
     * 13 DNS hosting
     * 14 DNS hosting renew
     * 16 Domain renewal
     * 17 Domain redemption grace period (RGP)
     * 18 Domain Extended 63 RGP (available at our discretion, and decided by us on a name-by-name basis)
     * 19 transfer
     * 41 Registration and email for- warding by the .name Registry
     * 44 .name registration and email forwarding renewal
     *
     * @param int [$productype] Product type
     * @param string [$productype] tld
     * @param int [$years] Retrieve quantity discount information. For some products like domains, this value represents the price break for multi-year registrations.
     *
     * @return \SimpleXMLElement Account Information
     */
    public function getDomainRetailPrice($productype = 10, $tld = 'com', $years = 1)
    {
        $this->payload["ProductType"] = $productype;
        $this->payload["tld"] = $tld;
        $this->payload["Years"] = $years;
        $command = 'PE_GetRetailPrice';
        $data = $this->makeRequest($command, $this->payload);

        return $data->productprice;
    }
    
    /**
     * Get the retail pricing that this account charges for registrations, renewals, and transfers, by top-level domain.
     *
     * @param int [$useQtyEngine] Product type
     * @param int [$years] Number of years for multiple-year registrations. Permitted values are 1, 2, 5, 2 and 10.
     *
     * @return \SimpleXMLElement Account Information
     */
    public function getDomainRetailPricing($useQtyEngine = 0, $years = 1)
    {
        $this->payload["UseQtyEngine"] = $useQtyEngine;
        $this->payload["Years"] = $years;
        $command = 'PE_GetDomainPricing';
        $data = $this->makeRequest($command, $this->payload);

        return $data;
    }
    
    /**
     * Get TLD list
     *
     * @return \SimpleXMLElement
     */
    public function getTldList()
    {
        $command = 'GetTLDList';
        $data = $this->makeRequest($command, $this->payload);

        return $data->tldlist;
    }
}
