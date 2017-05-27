<?php
/**
 * /classes/DomainMOD/DwZones.php
 *
 * This file is part of DomainMOD, an open source domain and internet asset manager.
 * Copyright (c) 2010-2017 Greg Chetcuti <greg@chetcuti.com>
 *
 * Project: http://domainmod.org   Author: http://chetcuti.com
 *
 * DomainMOD is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * DomainMOD is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with DomainMOD. If not, see
 * http://www.gnu.org/licenses/.
 *
 */
//@formatter:off
namespace DomainMOD;

class DwZones
{
    public function __construct()
    {
        $this->system = new System();
        $this->time = new Time();
    }

    public function createTable()
    {
        $this->system->db()->query("
            CREATE TABLE IF NOT EXISTS dw_dns_zones (
                id INT(10) NOT NULL AUTO_INCREMENT,
                server_id INT(10) NOT NULL,
                domain VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                zonefile VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
                insert_time DATETIME NOT NULL,
                PRIMARY KEY  (id)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1");
    }

    public function getApiCall()
    {
        return "/xml-api/listzones";
    }

    public function insertZones($api_results, $server_id)
    {
        if ($api_results !== false) {

            $xml = simplexml_load_string($api_results);

            $tmpq = $this->system->db()->prepare("
                INSERT INTO dw_dns_zones
                (server_id, domain, zonefile, insert_time)
                VALUES
                (:server_id, :domain, :zonefile, :insert_time)");

            foreach ($xml->zone as $hit) {

                $tmpq->execute(['server_id' => $server_id,
                                'domain' => $hit->domain,
                                'zonefile' => $hit->zonefile,
                                'insert_time' => $this->time->stamp()]);

            }

        }
    }

    public function getInsertedZones($server_id)
    {
        $tmpq = $this->system->db()->prepare("
            SELECT id, domain
            FROM dw_dns_zones
            WHERE server_id = :server_id
            ORDER BY domain");
        $tmpq->execute(['server_id' => $server_id]);
        return $tmpq->fetchAll();
    }

    public function processEachZone($result_zones, $server_id, $protocol, $host, $port, $username, $api_token, $hash)
    {
        foreach ($result_zones as $row_zones) {

            $build = new DwBuild();
            $records = new DwRecords();

            $api_call = $records->getApiCall($row_zones->domain);
            $api_results = $build->apiCall($api_call, $host, $protocol, $port, $username, $api_token, $hash);
            $records->insertRecords($api_results, $server_id, $row_zones->id, $row_zones->domain);

        }
    }

    public function getTotalDwZones()
    {
        $tmpq = $this->system->db()->query("
            SELECT count(*)
            FROM `dw_dns_zones`");
        return $tmpq->fetchColumn();
    }

} //@formatter:on
