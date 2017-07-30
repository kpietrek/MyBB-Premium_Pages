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


if (!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}


// Filter input data
premiumPagesFilterInput();


$lang->load("premiumPages");
$page->add_breadcrumb_item($lang->premiumPagesName, "index.php?module=config/premiumPages");

// Nav
$sub_tabs['premiumPages_list'] = array(
    'title' => $lang->premiumPagesName,
    'link' => "index.php?module=config/premiumPages",
    'description' => $lang->premiumPagesAdminList,
);
$sub_tabs['premiumPages_edit'] = array(
    'title' => $lang->premiumPagesAdminEdit,
    'link' => "index.php?module=config/premiumPages&amp;action=edit",
    'description' => $lang->premiumPagesAdminEdit,
);


if ($mybb->input['action'] == "delete") {
    $result = $db->simple_select("premium_pages", "*", "id = '" . intval($mybb->input['id']) . "'");
    $row = $db->fetch_array($result);

    if (!$row['id']) {
        admin_redirect("index.php?module=config/premiumPages");
    }

    if ($mybb->input['no']) {
        admin_redirect("index.php?module=config/premiumPages");
    }

    if ($mybb->request_method == "post") {
        $db->delete_query("premium_pages", "id='{$row['id']}'");
        admin_redirect("index.php?module=config/premiumPages");
    } else {
        $page->output_confirm_action("index.php?module=config/premiumPages&amp;action=delete&amp;id={$result['id']}", "");
    }
}


if ($mybb->input['action'] == "edit") {

    $pageId = (int) $mybb->input['id'];
    $result = $db->simple_select("premium_pages", "*", "id='" . $pageId . "'");
    $row = $db->fetch_array($result);

    if (empty($row['id'])) {
        $pageId = 0;
    }

    // Save data
    if ($mybb->request_method == "post") {
        $sql_array = array(
            "enabled" => $mybb->input['enabled'],
            "name" => $mybb->input['name'],
            "mycode" => $mybb->input['mycode'],
            "content" => $mybb->input['content'],
            "allowgroups" => $mybb->input['allowgroups'],
            "fullaccessgroups" => $mybb->input['fullaccessgroups'],
            "min_posts" => $mybb->input['min_posts'],
            "min_time_last_post" => $mybb->input['min_time_last_post'],
            "min_time_last_post_type" => $mybb->input['min_time_last_post_type'],
            "min_time_register" => $mybb->input['min_time_register'],
            "min_time_register_type" => $mybb->input['min_time_register_type'],
            "max_warn_level" => $mybb->input['max_warn_level'],
        );

        if ($pageId) {
            $db->update_query("premium_pages", $sql_array, "id = '" . $pageId . "'");
        } else {
            $db->insert_query("premium_pages", $sql_array);
        }

        flash_message($lang->premiumPagesAdminInfoSave, 'success');
        admin_redirect("index.php?module=config/premiumPages");
    } elseif ($pageId) {
        foreach ($row as $key => $val) {
            $mybb->input[$key] = $val;
        }
        $mybb->input['allowgroups'] = explode(',', $row['allowgroups']);
        $mybb->input['fullaccessgroups'] = explode(',', $row['fullaccessgroups']);
    }

    $page->add_breadcrumb_item($lang->premiumPagesFormEdit);
    $page->output_header($lang->premiumPagesAdminEdit);
    $page->output_nav_tabs($sub_tabs, 'premiumPages_edit');

    $options_type = [];
    foreach (\premiumPages::$TIME_TYPES as $type => $val) {
        $langVal = "premiumPagesTime_{$type}";
        if (!empty($lang->$langVal)) {
            $options_type[$type] = $lang->$langVal;
        }
    }

    $form = new Form("index.php?module=config/premiumPages&amp;action=edit", "post");
    $form_container = new FormContainer($lang->premiumPagesAdminEdit);

    $form_container->output_row($lang->premiumPagesFormEnabled, $lang->premiumPagesFormEnabledDesc, $form->generate_yes_no_radio('enabled', $mybb->input['enabled']));
    $form_container->output_row($lang->premiumPagesFormName, $lang->premiumPagesFormNameDesc, $form->generate_text_box('name', $mybb->input['name']));
    $form_container->output_row($lang->premiumPagesFormMycode, $lang->premiumPagesFormMycodeDesc, $form->generate_yes_no_radio('mycode', $mybb->input['mycode']));
    $form_container->output_row($lang->premiumPagesFormContent, $lang->premiumPagesFormContentDesc, $form->generate_text_area('content', ($mybb->input['content'])));
    $form_container->output_row($lang->premiumPagesFormGroups, $lang->premiumPagesFormGroups, $form->generate_group_select('allowgroups[]', $mybb->input['allowgroups'], array('multiple' => true, 'size' => 5)), 'allowgroups');
    $form_container->output_row($lang->premiumPagesFormGroupsAllowed, $lang->premiumPagesFormGroupsAllowed, $form->generate_group_select('fullaccessgroups[]', $mybb->input['fullaccessgroups'], array('multiple' => true, 'size' => 5)), 'fullaccessgroups');
    $form_container->output_row($lang->premiumPagesFormMinPosts, $lang->premiumPagesFormMinPostsDesc, $form->generate_numeric_field('min_posts', $mybb->input['min_posts']));
    $form_container->output_row($lang->premiumPagesFormMinTimePost, $lang->premiumPagesFormMinTimePostDesc, $form->generate_numeric_field('min_time_last_post', $mybb->input['min_time_last_post']));
    $form_container->output_row($lang->premiumPagesFormMinTimePostType, $lang->premiumPagesFormMinTimePostType, $form->generate_select_box('min_time_last_post_type', $options_type, $mybb->input['min_time_last_post_type'], array('size' => 3)), 'min_time_last_post_type');
    $form_container->output_row($lang->premiumPagesFormMinTimeRegister, $lang->premiumPagesFormMinTimeRegisterDesc, $form->generate_numeric_field('min_time_register', $mybb->input['min_time_register']));
    $form_container->output_row($lang->premiumPagesFormMinTimeRegisterType, $lang->premiumPagesFormMinTimeRegisterType, $form->generate_select_box('min_time_register_type', $options_type, $mybb->input['min_time_register_type'], array('size' => 3)), 'min_time_register_type');
    $form_container->output_row($lang->premiumPagesFormMaxWarn, $lang->premiumPagesFormMaxWarnDesc, $form->generate_numeric_field('max_warn_level', $mybb->input['max_warn_level']));
    $form_container->end();

    if ($pageId) {
        echo $form->generate_hidden_field("id", $row['id']);
    }

    $buttons[] = $form->generate_submit_button($lang->premiumPagesAdminSave);
    $form->output_submit_wrapper($buttons);
    $form->end();

    $page->output_footer();
}


if (!$mybb->input['action']) {
    $page->add_breadcrumb_item($lang->premiumPagesName);
    $page->output_header($lang->premiumPagesName, $lang->premiumPagesAdminEdit);
    $page->output_nav_tabs($sub_tabs, 'premiumPages_list');

    $table = new Table;
    $table->construct_header($lang->premiumPagesHeaderName, array("class" => "align_center"));
    $table->construct_header($lang->premiumPagesHeaderMinPosts, array("class" => "align_center"));
    $table->construct_header($lang->premiumPagesHeaderTimePost, array("class" => "align_center"));
    $table->construct_header($lang->premiumPagesHeaderTimeRegister, array("class" => "align_center"));
    $table->construct_header($lang->premiumPagesHeaderMaxWarn, array("class" => "align_center"));
    $table->construct_header($lang->premiumPagesHeaderLink, array("class" => "align_center"));
    $table->construct_header($lang->premiumPagesHeaderOptions, array("class" => "align_center", "colspan" => 2));

    $result = $db->simple_select('premium_pages', '*');
    if ($db->num_rows($result)) {
        while ($row = $db->fetch_array($result)) {

            $langValType = "premiumPagesTime_{$row['min_time_last_post_type']}";
            if (empty($lang->$langValType)) {
                $lang->$langValType = '-';
            }

            $langValRegister = "premiumPagesTime_{$row['min_time_register_type']}";
            if (empty($lang->$langValRegister)) {
                $lang->$langValRegister = '-';
            }

            $table->construct_cell($row['name']);
            $table->construct_cell($row['min_posts'], array("class" => "align_center"));
            $table->construct_cell($row['min_time_last_post'] . ' ' . $lang->$langValType, array("class" => "align_center"));
            $table->construct_cell($row['min_time_register'] . ' ' . $lang->$langValRegister, array("class" => "align_center"));
            $table->construct_cell($row['max_warn_level'], array("class" => "align_center"));
            $table->construct_cell("<a href=\"{$mybb->settings['bburl']}/premiumPages.php?id={$row['id']}\">premiumPages.php?id={$row['id']}</a>");
            $table->construct_cell("<a href=\"index.php?module=config/premiumPages&amp;action=edit&amp;id={$row['id']}\">" . $lang->premiumPagesFormEdit . "</a>", array("class" => "align_center"));
            $table->construct_cell("<a href=\"index.php?module=config/premiumPages&amp;action=delete&amp;id={$row['id']}&amp;my_post_key={$mybb->post_code}\" onclick=\"return AdminCP.deleteConfirmation(this, '" . $lang->premiumPagesFormConfirm . "')\">" . $lang->premiumPagesFormDelete . "</a>", array("class" => "align_center"));
            $table->construct_row();
        }
    } else {
        $table->construct_cell("<b>" . $lang->premiumPagesAdminInfoNoResults . "</b>", array("class" => "align_center", "colspan" => 8));
        $table->construct_row();
    }
}

$table->output($lang->premiumPagesName);
$page->output_footer();


function premiumPagesFilterInput()
{
    global $db, $mybb;

    $timeTypes = array_keys(\premiumPages::$TIME_TYPES);

    if (empty($mybb->input['min_time_last_post_type']) || !in_array($mybb->input['min_time_last_post_type'], $timeTypes)) {
        $mybb->input['min_time_last_post_type'] = $timeTypes[0];
    }

    if (empty($mybb->input['min_time_register_type']) || !in_array($mybb->input['min_time_register_type'], $timeTypes)) {
        $mybb->input['min_time_register_type'] = $timeTypes[0];
    }

    $mybb->input['name'] = (!empty($mybb->input['name'])) ? $db->escape_string($mybb->input['name']) : '';
    if (!empty($mybb->input['name'])) {
        $mybb->input['name'] = strip_tags($mybb->input['name']);
        $mybb->input['name'] = htmlspecialchars_uni($mybb->input['name']);
    }

    $mybb->input['content'] = (!empty($mybb->input['content'])) ? $db->escape_string($mybb->input['content']) : '';
    $mybb->input['min_posts'] = (!empty($mybb->input['min_posts'])) ? (int) $mybb->input['min_posts'] : 0;
    $mybb->input['min_time_last_post'] = (!empty($mybb->input['min_time_last_post'])) ? (int) $mybb->input['min_time_last_post'] : 0;
    $mybb->input['min_time_register'] = (!empty($mybb->input['min_time_register'])) ? (int) $mybb->input['min_time_register'] : 0;
    $mybb->input['max_warn_level'] = (!empty($mybb->input['max_warn_level'])) ? (int) $mybb->input['max_warn_level'] : 0;
    $mybb->input['enabled'] = (!empty($mybb->input['min_posts'])) ? (int) $mybb->input['enabled'] : 0;
    $mybb->input['mycode'] = (!empty($mybb->input['min_posts'])) ? (int) $mybb->input['mycode'] : 0;

    if (!empty($mybb->input['allowgroups']) && is_array($mybb->input['allowgroups'])) {
        $mybb->input['allowgroups'] = array_map('intval', $mybb->input['allowgroups']);
    } else {
        $mybb->input['allowgroups'] = [];
    }
    $mybb->input['allowgroups'] = implode(",", $mybb->input['allowgroups']);

    if (!empty($mybb->input['fullaccessgroups']) && is_array($mybb->input['fullaccessgroups'])) {
        $mybb->input['fullaccessgroups'] = array_map('intval', $mybb->input['fullaccessgroups']);
    } else {
        $mybb->input['fullaccessgroups'] = [];
    }
    $mybb->input['fullaccessgroups'] = implode(",", $mybb->input['fullaccessgroups']);
}