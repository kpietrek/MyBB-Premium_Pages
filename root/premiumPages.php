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


define("IN_MYBB", 1);
define('THIS_SCRIPT', 'premiumPages.php');
require_once "./global.php";

// Get page data from DB
$id = (int) $mybb->input['id'];
$result = $db->simple_select("premium_pages", "*", "id='{$id}'");

// Is page exist?
if (!$db->num_rows($result))
{
    redirect('index.php');
}
$pageData = $db->fetch_array($result);



// Get user groups and check if full access
// Zero -> all groups allowed
$check_restrictions = false;
$fullaccessgroups = explode(",", $pageData['fullaccessgroups']);
$fullaccessgroups = array_map('intval', $fullaccessgroups);

if (!in_array($mybb->user['usergroup'], $fullaccessgroups))
{
    $user_groups = explode(',', $mybb->user['additionalgroups']);
    $allow_count = array_intersect($fullaccessgroups, $user_groups);

    // Is there any good options?
    if (!sizeof($allow_count))
    {
        $check_restrictions = true;
    }
}


// Check all restrictions
if ($check_restrictions)
{
    // Get user groups and check access
    // Zero -> all groups allowed
    $allowgroups = explode(",", $pageData['allowgroups']);
    $allowgroups = array_map('intval', $allowgroups);
    
    if (!in_array("0", $allowgroups) && !in_array($mybb->user['usergroup'], $allowgroups))
    {
        $user_groups = explode(',', $mybb->user['additionalgroups']);
        $allow_count = array_intersect($allowgroups, $user_groups);
    
        // Is there any good options?
        if (!sizeof($allow_count))
        {
            error_no_permission();
        }
    }
    
    // Minimal posts restriction
    if ($mybb->user['postnum'] < $pageData['min_posts'])
    { 
        error_no_permission();
    }
    
    // Minimal time from last post restriction
    // Only if user have < 2x minimal posts
    if ($pageData['min_time_last_post'] > 0 && 
            $mybb->user['postnum'] < ($pageData['min_posts'] * 2))
    { 
        switch ($pageData['min_time_last_post_type'])
        {
            case 'm':
            default:
                $time_limit = $pageData['min_time_last_post'] * 60;
                break;
    
            case 'h':
                $time_limit = $pageData['min_time_last_post'] * 3600;
                break;
    
            case 'd':
                $time_limit = $pageData['min_time_last_post'] * 86400;
                break;
        }
        
        $sql_array = array(
            'order_by' => 'pid',
            'limit_start' => $pageData['min_posts'],
            'limit' => '1',
        );
        $result = $db->simple_select("posts", "dateline", "uid='{$mybb->user['uid']}'", $sql_array);
        $post_time = $db->fetch_field($result, "dateline");
        
        if ((TIME_NOW - $post_time) < $time_limit)
        {
            error_no_permission();
        }
    }
    
    // Minimal time from user registration
    if ($pageData['min_time_register_type'] > 0)
    {
        $time_limit = TIME_NOW;
        switch ($pageData['min_time_register_type'])
        {
            case 'm':
            default:
                $time_limit -= $pageData['min_time_register_type'] * 60;
                break;
    
            case 'h':
                $time_limit -= $pageData['min_time_register_type'] * 3600;
                break;
    
            case 'd':
                $time_limit -= $pageData['min_time_register_type'] * 86400;
                break;
        }
    
        if ($mybb->user['regdate'] > $time_limit)
        {
            error_no_permission();
        }
    }
    
    // Maximum warn points restriction
    if ($mybb->user['warningpoints'] > $pageData['max_warn_level'])
    {
        error_no_permission();
    }
}


// Prepere page data
$page = array(
    'name' => stripslashes($pageData['name']),
    'content' => stripslashes($pageData['content']),
);

add_breadcrumb($pageData['name']);
eval("\$output = \"".$templates->get("premiumPages_pageBody")."\";");

output_page($output);