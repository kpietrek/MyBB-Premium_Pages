<?php
/**
 * This file is part of Premium Pages plugin for MyBB.
 * Copyright (C) Lukasz Tkacz <lukasamd@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */


// Disallow direct access to this file for security reasons
if (!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

class premiumPagesInstaller
{

    public static function install()
    {
        global $db;
        self::uninstall();

        $sql = "CREATE TABLE IF NOT EXISTS " . TABLE_PREFIX . "premium_pages (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL DEFAULT '',
        content text NOT NULL DEFAULT '',
        allowgroups VARCHAR(255) NOT NULL DEFAULT '',
        fullaccessgroups VARCHAR(255) NOT NULL DEFAULT '',
        min_posts INT UNSIGNED NOT NULL DEFAULT 0,
        min_time_last_post INT UNSIGNED NOT NULL DEFAULT 6,
        min_time_last_post_type VARCHAR(255) NOT NULL DEFAULT 'hour',
        min_time_register INT UNSIGNED NOT NULL DEFAULT 1,
        min_time_register_type VARCHAR(255) NOT NULL DEFAULT 'day',
        max_warn_level INT NOT NULL DEFAULT 0,
        PRIMARY KEY (id)
        ) DEFAULT CHARSET=utf8;";
        $db->query($sql);
    }

    public static function uninstall()
    {
        global $db;

        $db->drop_table('premium_pages');
    }

}
