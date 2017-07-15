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


if (!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$lang->load("premiumPages");
$page->add_breadcrumb_item($lang->premiumPagesName, "index.php?module=config/premiumPages");

if ($mybb->input['action'] == "add" || $mybb->input['action'] == "edit" || !$mybb->input['action'])
{
    $sub_tabs['premiumPages'] = array(
        'title' => $lang->premiumPagesName,
        'link' => "index.php?module=config/premiumPages",
        'description' => $lang->premiumPagesAdminList,
    );
    $sub_tabs['premiumPages_add'] = array(
        'title' => $lang->premiumPagesAdminAdd,
        'link' => "index.php?module=config/premiumPages&amp;action=add",
        'description' => $lang->premiumPagesAdminAdd,
    );
}

#/\ dodawanie nowych subsub menu.

if ($mybb->input['action'] == "add")
{
    if ($mybb->request_method == "post")
    {
        // Determine the usergroup stuff
        if (is_array($mybb->input['allowgroups']))
        {
            foreach ($mybb->input['allowgroups'] as $key => $gid)
            {
                if ($gid == $mybb->input['usergroup'])
                {
                    unset($mybb->input['allowgroups'][$key]);
                }
            }
            $allowgroups = implode(",", $mybb->input['allowgroups']);
        }
        else
        {
            $allowgroups = '0';
        }
        
        // Determine the usergroup stuff
        if (is_array($mybb->input['fullaccessgroups']))
        {
            foreach ($mybb->input['fullaccessgroups'] as $key => $gid)
            {
                if ($gid == $mybb->input['usergroup'])
                {
                    unset($mybb->input['fullaccessgroups'][$key]);
                }
            }
            $fullaccessgroups = implode(",", $mybb->input['fullaccessgroups']);
        }
        else
        {
            $fullaccessgroups = '0';
        }

        $sql_array = array(
            "name" => $db->escape_string($mybb->input['name']),
            "content" => $db->escape_string($mybb->input['content']),
            "allowgroups" => $allowgroups,
            "fullaccessgroups" => $fullaccessgroups,
            "min_posts" => (int) $db->escape_string($mybb->input['min_posts']),
            "min_time_last_post" => (int) $db->escape_string($mybb->input['min_time_last_post']),
            "min_time_last_post_type" => $db->escape_string($mybb->input['min_time_last_post_type']),
            "min_time_register" => (int) $db->escape_string($mybb->input['min_time_register']),
            "min_time_register_type" => $db->escape_string($mybb->input['min_time_register_type']),
            "max_warn_level" => $db->escape_string($mybb->input['max_warn_level']),
        );

        $db->insert_query("premium_pages", $sql_array);

        flash_message($lang->premiumPagesAdminInfoAdd, 'success');
        admin_redirect("index.php?module=config/premiumPages");
    }


    $page->add_breadcrumb_item($lang->premiumPagesName);
    $page->output_header($lang->premiumPagesName, $lang->premiumPagesAdminAdd);

    $page->output_nav_tabs($sub_tabs, 'premiumPages_add');

    $query = $db->simple_select("premium_pages");
    $admin_options = $db->fetch_array($query);

    $form = new Form("index.php?module=config/premiumPages&amp;action=add", "post", "add");

    if ($errors)
    {
        $page->output_inline_error($errors);
    }

    $result = $db->simple_select("usergroups", "gid, title", "gid != '1'", array('order_by' => 'title'));
    while ($usergroup = $db->fetch_array($result))
    {
        $options[$usergroup['gid']] = $usergroup['title'];
    }
    $options_full = $options; 
    $options[0] = $lang->premiumPagesAdminAll;

    $options_type = array(
        'm' => $lang->premiumPagesAdminMinutes,
        'h' => $lang->premiumPagesAdminHours,
        'd' => $lang->premiumPagesAdminDays,
    );

    $form_container = new FormContainer($lang->premiumPagesFormData);
    $form_container->output_row($lang->premiumPagesFormName, $lang->premiumPagesFormNameDesc, $form->generate_text_box('name'));
    $form_container->output_row($lang->premiumPagesFormContent, $lang->premiumPagesFormContentDesc, $form->generate_text_area('content'));
    $form_container->output_row($lang->premiumPagesFormGroups, $lang->premiumPagesFormGroups, $form->generate_select_box('allowgroups[]', $options, $mybb->input['allowgroups'], array('id' => 'allowgroups', 'multiple' => true, 'size' => 5)), 'allowgroups');
    $form_container->output_row($lang->premiumPagesFormGroupsAllowed, $lang->premiumPagesFormGroupsAllowed, $form->generate_select_box('fullaccessgroups[]', $options_full, $mybb->input['fullaccessgroups'], array('id' => 'fullaccessgroups', 'multiple' => true, 'size' => 5)), 'fullaccessgroups');
    $form_container->output_row($lang->premiumPagesFormMinPosts, $lang->premiumPagesFormMinPostsDesc, $form->generate_text_box('min_posts'));
    $form_container->output_row($lang->premiumPagesFormMinTimePost, $lang->premiumPagesFormMinTimePostDesc, $form->generate_text_box('min_time_last_post'));
    $form_container->output_row($lang->premiumPagesFormMinTimePostType, $lang->premiumPagesFormMinTimePostType, $form->generate_select_box('min_time_last_post_type', $options_type, $mybb->input['min_time_last_post_type'], array('id' => 'min_time_last_post_type', 'size' => 3)), 'min_time_last_post_type');
    $form_container->output_row($lang->premiumPagesFormMinTimeRegister, $lang->premiumPagesFormMinTimeRegisterDesc, $form->generate_text_box('min_time_register'));
    $form_container->output_row($lang->premiumPagesFormMinTimeRegisterType, $lang->premiumPagesFormMinTimeRegisterType, $form->generate_select_box('min_time_register_type', $options_type, $mybb->input['min_time_register_type'], array('id' => 'min_time_register_type', 'size' => 3)), 'min_time_register_type');
    $form_container->output_row($lang->premiumPagesFormMaxWarn, $lang->premiumPagesFormMaxWarnDesc, $form->generate_text_box('max_warn_level'));
    $form_container->end();

    $buttons[] = $form->generate_submit_button($lang->premiumPagesAdminAdd);
    $form->output_submit_wrapper($buttons);
    $form->end();

    $page->output_footer();
}

if ($mybb->input['action'] == "delete")
{
    $result = $db->simple_select("premium_pages", "*", "id = '" . intval($mybb->input['id']) . "'");
    $row = $db->fetch_array($result);

    if (!$row['id'])
    {
        admin_redirect("index.php?module=config/premiumPages");
    }

    if ($mybb->input['no'])
    {
        admin_redirect("index.php?module=config/premiumPages");
    }

    if ($mybb->request_method == "post")
    {
        $db->delete_query("premium_pages", "id='{$row['id']}'");
        admin_redirect("index.php?module=config/premiumPages");
    }
    else
    {
        $page->output_confirm_action("index.php?module=config/premiumPages&amp;action=delete&amp;id={$result['id']}", "");
    }
}

if ($mybb->input['action'] == "edit")
{
    $result = $db->simple_select("premium_pages", "*", "id='" . intval($mybb->input['id']) . "'");
    $row = $db->fetch_array($result);

    if (!$row['id'])
    {
        admin_redirect("index.php?module=config/premiumPages");
    }

    if ($mybb->request_method == "post")
    {
        // Determine the usergroup stuff
        if (is_array($mybb->input['allowgroups']))
        {
            foreach ($mybb->input['allowgroups'] as $key => $gid)
            {
                if ($gid == $mybb->input['usergroup'])
                {
                    unset($mybb->input['allowgroups'][$key]);
                }
            }
            $allowgroups = implode(",", $mybb->input['allowgroups']);
        }
        else
        {
            $allowgroups = '0';
        }
        
        // Determine the usergroup stuff
        if (is_array($mybb->input['fullaccessgroups']))
        {
            foreach ($mybb->input['fullaccessgroups'] as $key => $gid)
            {
                if ($gid == $mybb->input['usergroup'])
                {
                    unset($mybb->input['fullaccessgroups'][$key]);
                }
            }
            $fullaccessgroups = implode(",", $mybb->input['fullaccessgroups']);
        }
        else
        {
            $fullaccessgroups = '0';
        }
        

        $sql_array = array(
            "name" => $db->escape_string($mybb->input['name']),
            "content" => $db->escape_string($mybb->input['content']),
            "allowgroups" => $allowgroups,
            "fullaccessgroups" => $fullaccessgroups,
            "min_posts" => (int) $db->escape_string($mybb->input['min_posts']),
            "min_time_last_post" => (int) $db->escape_string($mybb->input['min_time_last_post']),
            "min_time_last_post_type" => $db->escape_string($mybb->input['min_time_last_post_type']),
            "min_time_register" => (int) $db->escape_string($mybb->input['min_time_register']),
            "min_time_register_type" => $db->escape_string($mybb->input['min_time_register_type']),
            "max_warn_level" => $db->escape_string($mybb->input['max_warn_level']),
        );

        $db->update_query("premium_pages", $sql_array, "id = '" . intval($mybb->input['id']) . "'");

        flash_message($lang->premiumPagesAdminInfoAdd, 'success');
        admin_redirect("index.php?module=config/premiumPages");
    }

    $page->add_breadcrumb_item($lang->premiumPagesFormEdit);
    $page->output_header($lang->premiumPagesAdminEdit);
    $page->output_nav_tabs($sub_tabs, 'premiumPages_edit');

    $result = $db->simple_select("usergroups", "gid, title", "gid != '1'", array('order_by' => 'title'));
    while ($usergroup = $db->fetch_array($result))
    {
        $options[$usergroup['gid']] = $usergroup['title'];
    }
    $options_full = $options; 
    $options[0] = $lang->premiumPagesAdminAll;
    
    $mybb->input['allowgroups'] = explode(',', $row['allowgroups']);
    $mybb->input['fullaccessgroups'] = explode(',', $row['fullaccessgroups']);

    $options_type = array(
        'm' => $lang->premiumPagesAdminMinutes,
        'h' => $lang->premiumPagesAdminHours,
        'd' => $lang->premiumPagesAdminDays,
    );

    $form = new Form("index.php?module=config/premiumPages&amp;action=edit", "post");
    $form_container = new FormContainer($lang->premiumPagesAdminEdit);
    $form_container->output_row($lang->premiumPagesFormName, $lang->premiumPagesFormNameDesc, $form->generate_text_box('name', htmlspecialchars_uni($row['name'])));
    $form_container->output_row($lang->premiumPagesFormContent, $lang->premiumPagesFormContentDesc, $form->generate_text_area('content', ($row['content'])));
    $form_container->output_row($lang->premiumPagesFormGroups, $lang->premiumPagesFormGroups, $form->generate_select_box('allowgroups[]', $options, $mybb->input['allowgroups'], array('id' => 'allowgroups', 'multiple' => true, 'size' => 5)), 'allowgroups');
    $form_container->output_row($lang->premiumPagesFormGroupsAllowed, $lang->premiumPagesFormGroupsAllowed, $form->generate_select_box('fullaccessgroups[]', $options_full, $mybb->input['fullaccessgroups'], array('id' => 'fullaccessgroups', 'multiple' => true, 'size' => 5)), 'fullaccessgroups');
    $form_container->output_row($lang->premiumPagesFormMinPosts, $lang->premiumPagesFormMinPostsDesc, $form->generate_text_box('min_posts', htmlspecialchars_uni($row['min_posts'])));
    $form_container->output_row($lang->premiumPagesFormMinTimePost, $lang->premiumPagesFormMinTimePostDesc, $form->generate_text_box('min_time_last_post', htmlspecialchars_uni($row['min_time_last_post'])));
    $form_container->output_row($lang->premiumPagesFormMinTimePostType, $lang->premiumPagesFormMinTimePostType, $form->generate_select_box('min_time_last_post_type', $options_type, $row['min_time_last_post_type'], array('id' => 'min_time_last_post_type', 'size' => 3)), 'min_time_last_post_type');
    $form_container->output_row($lang->premiumPagesFormMinTimeRegister, $lang->premiumPagesFormMinTimeRegisterDesc, $form->generate_text_box('min_time_register', htmlspecialchars_uni($row['min_time_register'])));
    $form_container->output_row($lang->premiumPagesFormMinTimeRegisterType, $lang->premiumPagesFormMinTimeRegisterType, $form->generate_select_box('min_time_register_type', $options_type, $row['min_time_register_type'], array('id' => 'min_time_register_type', 'size' => 3)), 'min_time_register_type');
    $form_container->output_row($lang->premiumPagesFormMaxWarn, $lang->premiumPagesFormMaxWarnDesc, $form->generate_text_box('max_warn_level', htmlspecialchars_uni($row['max_warn_level'])));
    $form_container->end();

    echo $form->generate_hidden_field("id", $row['id']);
    $buttons[] = $form->generate_submit_button($lang->premiumPagesAdminSave);

    $form->output_submit_wrapper($buttons);
    $form->end();

    $page->output_footer();
}

if (!$mybb->input['action'])
{
    $page->add_breadcrumb_item($lang->premiumPagesName);
    $page->output_header($lang->premiumPagesName, $lang->premiumPagesAdminAdd);
    $page->output_nav_tabs($sub_tabs, 'idoard');

    $table = new Table;
    $table->construct_header($lang->premiumPagesHeaderName, array("class" => "align_center"));
    $table->construct_header($lang->premiumPagesHeaderMinPosts, array("class" => "align_center"));
    $table->construct_header($lang->premiumPagesHeaderTimePost, array("class" => "align_center"));
    $table->construct_header($lang->premiumPagesHeaderTimeRegister, array("class" => "align_center"));
    $table->construct_header($lang->premiumPagesHeaderMaxWarn, array("class" => "align_center"));
    $table->construct_header($lang->premiumPagesHeaderLink, array("class" => "align_center"));
    $table->construct_header($lang->premiumPagesHeaderOptions, array("class" => "align_center", "colspan" => 2));

    $result = $db->simple_select('premium_pages', '*');
    if ($db->num_rows($result))
    {
        while ($row = $db->fetch_array($result))
        {
            $table->construct_cell($row['name']);
            $table->construct_cell($row['min_posts'], array("class" => "align_center"));
            $table->construct_cell($row['min_time_last_post'] . $row['min_time_last_post_type'], array("class" => "align_center"));
            $table->construct_cell($row['min_time_register'] . $row['min_time_register_type'], array("class" => "align_center"));
            $table->construct_cell($row['max_warn_level'], array("class" => "align_center"));
            $table->construct_cell("<a href=\"{$mybb->settings['bburl']}/premiumPages.php?id={$row['id']}\">premiumPages.php?id={$row['id']}</a>");
            $table->construct_cell("<a href=\"index.php?module=config/premiumPages&amp;action=edit&amp;id={$row['id']}\">Edytuj</a>", array("class" => "align_center"));
            $table->construct_cell("<a href=\"index.php?module=config/premiumPages&amp;action=delete&amp;id={$row['id']}&amp;my_post_key={$mybb->post_code}\" onclick=\"return AdminCP.deleteConfirmation(this, 'Czy na pewno chcesz usunąć tą stronę?')\">Usuń</a>", array("class" => "align_center"));
            $table->construct_row();
        }
    }
    else
    {
        $table->construct_cell("<b>" . $lang->premiumPagesAdminInfoNoResults . "</b>", array("class" => "align_center", "colspan" => 8));
        $table->construct_row();
    }
}

$table->output($lang->premiumPagesName);

$page->output_footer();
