<?php
/**
 * /classes/DomainMOD/User.php
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

class User
{
    public function __construct()
    {
        $this->system = new System();
    }

    public function getAdminId()
    {
        $tmpq = $this->system->db()->query("
            SELECT id
            FROM users
            WHERE username = 'admin'");
        return $tmpq->fetchColumn();
    }

    public function getFullName($user_id)
    {
        $tmpq = $this->system->db()->prepare("
            SELECT first_name, last_name
            FROM users
            WHERE id = :user_id");
        $tmpq->execute(['user_id' => $user_id]);
        $result = $tmpq->fetch();

        return $result->first_name . ' ' . $result->last_name;
    }

} //@formatter:on
