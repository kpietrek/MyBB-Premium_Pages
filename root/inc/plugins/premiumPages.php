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
if (!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

/**
 * Create plugin object
 *
 */
$plugins->add_hook('admin_config_menu', ['premiumPages', 'adminLink']);
$plugins->add_hook('admin_config_action_handler', ['premiumPages', 'adminHandler']);
$plugins->add_hook('pre_output_page', ['premiumPages', 'pluginThanks']);


function premiumPages_info()
{
    global $lang;

    $lang->load("premiumPages");

    return Array(
        "name" => $lang->premiumPagesName,
        "description" => $lang->premiumPagesDesc,
        "website" => "https://tkacz.pro",
        "author" => 'Lukasz Tkacz',
        "authorsite" => "https://tkacz.pro",
        "version" => "1.0",
        "compatibility" => "18*",
        'codename' => 'premium_pages',
    );
}

// START - Standard MyBB installation functions
function premiumPages_install()
{
    require_once('premiumPages.settings.php');
    premiumPagesInstaller::install();
}

function premiumPages_is_installed()
{
    global $db;

    return ($db->table_exists('premium_pages'));
}

function premiumPages_uninstall()
{
    require_once('premiumPages.settings.php');
    premiumPagesInstaller::uninstall();
}

// END - Standard MyBB installation functions
// START - Standard MyBB activation functions
function premiumPages_activate()
{
    require_once('premiumPages.tpl.php');
    premiumPagesActivator::activate();
}

function premiumPages_deactivate()
{
    require_once('premiumPages.tpl.php');
    premiumPagesActivator::deactivate();
}

// END - Standard MyBB activation functions


// Plugin Class
class premiumPages
{

    public static function adminLink(&$sub_menu)
    {
        global $lang;

        $lang->load("premiumPages");

        $sub_menu[] = array(
            'id' => 'premiumPages',
            'title' => $lang->premiumPagesName,
            'link' => 'index.php?module=config/premiumPages'
        );
    }


    public static function adminHandler(&$action)
    {
        global $lang;

        $lang->load("premiumPages");

        $action['premiumPages'] = array(
            'active' => 'premiumPages',
            'file' => 'premiumPages.php'
        );
    }

    /**
     * Say thanks to plugin author - paste link to author website.
     * Please don't remove this code if you didn't make donate
     * It's the only way to say thanks without donate :)
     */
    public static function pluginThanks(&$content) {
        global $session, $lukasamd_thanks;

        if (!isset($lukasamd_thanks) && $session->is_spider) {
            $thx = '<div style="margin:auto; text-align:center;">This forum uses <a href="https://tkacz.pro">Lukasz Tkacz</a> MyBB addons.</div></body>';
            $content = str_replace('</body>', $thx, $content);
            $lukasamd_thanks = true;
        }
    }

}