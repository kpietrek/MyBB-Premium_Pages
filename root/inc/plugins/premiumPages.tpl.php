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

class premiumPagesActivator
{
    private static $tpl = array();
    
    
    private static function getTpl()
    {
        global $db;
        
        self::$tpl[] = array( 
            "title"		=> 'premiumPages_pageBody',
            "template"	=> $db->escape_string('
<html>
<head>
<title>{$mybb->settings[\'bbname\']} - {$page[\'name\']}</title>
{$headerinclude}
</head>
<body>
{$header}
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder" style="clear: both;">
  <thead>
    <tr>
      <td class="thead">
        <strong>{$page[\'name\']}</strong>
      </td>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td class="trow1">
        {$page[\'content\']}
      </td>
    </tr>
  </tbody>
</table>
<br />

{$footer}
</body>
</html>'),
            "sid"		=> "-1",
            "version"	=> "1.0",
            "dateline"	=> TIME_NOW,
        );

   
    }
    
    public static function activate()
    {
        global $db;
        self::deactivate();

        for ($i = 0; $i < sizeof(self::$tpl); $i++)
        {
            $db->insert_query('templates', self::$tpl[$i]);
        }
    }

    public static function deactivate()
    {
        global $db;
        self::getTpl();
        
        for ($i = 0; $i < sizeof(self::$tpl); $i++)
        {
            $db->delete_query('templates', "title = '" . self::$tpl[$i]['title'] . "'");
        }
    }

}
