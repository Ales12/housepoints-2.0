<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.");
}

if(class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
    $plugins->add_hook("global_start", "housepoints_alerts");
}


function housepoints_info()
{
    return array(
        "name"			=> "Hauspunkte",
        "description"	=> "Hier kannst die Hauspunkte verwalten.",
        "website"		=> "https://github.com/Ales12",
        "author"		=> "Ales",
        "authorsite"	=> "https://github.com/Ales12",
        "version"		=> "2.0",
        "guid" 			=> "",
        "codename"		=> "",
        "compatibility" => "*"
    );
}

function housepoints_install()
{
    global $db, $cache, $mybb;

    //Datenbank erstellen
    $collation = $db->build_create_table_collation();

    $db->write_query("
        CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."housepoints` (
          `hp_id` int(10) NOT NULL auto_increment,
          `hp_reason` varchar(500) CHARACTER SET utf8 NOT NULL, 
          `hp_points` varchar(500) CHARACTER SET utf8 NOT NULL,
          `hp_link` varchar(500) CHARACTER SET utf8 NOT NULL,
          `hp_uid` int(10) NOT NULL,
          `hp_ok` int(10) NOT NULL,
          PRIMARY KEY (`hp_id`)
           ) ENGINE=MyISAM{$collation};
    ");

    $db->query("ALTER TABLE `".TABLE_PREFIX."users` ADD `user_hp` int(10) NOT NULL;");

    $db->add_column("usergroups", "canaddhp", "tinyint NOT NULL default '1'");
    $cache->update_usergroups();

    // Einstellungen

    $setting_group = array(
        'name' => 'houespoints_settings',
        'title' => 'Einstellungen für Hauspunkte',
        'description' => 'Hier kannst du die Einstellungen für die Hauspunkte vornehmen.',
        'disporder' => 5, // The order your setting group will display
        'isdefault' => 0
    );

    $gid = $db->insert_query("settinggroups", $setting_group);

    $setting_array = array(
        // Usergruppen wählen
        'housepoints_setting_fid' => array(
            'title' => 'Profilfeld für Gruppen',
            'description' => 'Trage hier die FID des Profilfelds ein, in diesem der User seine Gruppe auswählt.',
            'optionscode' => 'numeric',
            'value' => 4, // Default
            'disporder' => 1
        ),
        'housepoints_setting_inplay' => array(
            'title' => 'Inplaykategorie',
            'description' => 'Wähle hier die Kategorie des Inplays aus.',
            'optionscode' => 'forumselectsingle',
            'value' => 4, // Default
            'disporder' => 2
        ),

        // Anzahl der Punkte
        'housepoints_setting_postcountpoints' => array(
            'title' => 'Punkteanzahl',
            'description' => 'Wie viele Punkte gibt für die minimale Länge von Posts?',
            'optionscode' => 'numeric ',
            'value' => 10,
            'disporder' => 5
        ),
        // Titel für ersten offenen Infopunkt
        'housepoints_setting_postcountextra' => array(
            'title' => 'Extra Postpunkte',
            'description' => 'Sollen ab einer gewissen Zeichenzahl weitere Punkte vergeben werden?',
            'optionscode' => 'yesno',
            'value' => 0,
            'disporder' => 6
        ),
        // Anzahl der Punkte
        'housepoints_setting_postcountnumberextranumber' => array(
            'title' => 'Zusätzliche Punkte',
            'description' => 'Ab welcher Länge gibt es zusätzliche Punkte?',
            'optionscode' => 'numeric ',
            'value' => '8000',
            'disporder' => 7
        ),
        // Anzahl der Punkte
        'housepoints_setting_postcountnumberextrapoints' => array(
            'title' => 'Zusätzliche Punkte',
            'description' => 'Ab welcher Länge gibt es zusätzliche Punkte?',
            'optionscode' => 'numeric ',
            'value' => '10',
            'disporder' => 8
        ),
    );


    foreach($setting_array as $name => $setting)
    {
        $setting['name'] = $name;
        $setting['gid'] = $gid;

        $db->insert_query('settings', $setting);
    }

    // templates
    $insert_array = array(
        'title' => 'hp_header',
        'template' => $db->escape_string('<table><tr><td class="thead"><strong>{$lang->hp_header}</strong></td></tr>
	<tr><td class="trow1">
{$hp_header_houses}
		</td></tr>
</table>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);
    $insert_array = array(
        'title' => 'hp_header_houses',
        'template' => $db->escape_string('<b>{$house}</b> {$total_score}
		'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);
    $insert_array = array(
        'title' => 'hp_misc',
        'template' => $db->escape_string('<html>
<head>
<title>{$mybb->settings[\'bbname\']} - {$lang->hp_overview}</title>
{$headerinclude}
</head>
<body>
{$header}

<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead"><strong>{$lang->hp_overview}</strong></td>
</tr>
<tr>
<td class="trow1" align="center">
	{$housepounts_form}
	<br /><br />
	{$hp_misc_bit}
</td>
</tr>
</table>
{$footer}
</body>
</html>
		'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'hp_misc_bit',
        'template' => $db->escape_string('<div>
	<div class="tcat"><strong>{$house} {$total_score} Hauspunkte</strong></div>
	<div class="trow1">
		{$hp_misc_chara}
	</div>
</div>
		'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'hp_misc_chara',
        'template' => $db->escape_string('<div>{$charaname}</div>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'hp_misc_form',
        'template' => $db->escape_string('<form action="misc.php?action=housepoints_overview" method="post">
	<table>
		<tr><td class="thead" colspan="4"><strong>{$lang->hp_response}</strong></td></tr>
		<tr>
			<td class="tcat"><strong>{$lang->hp_charas}</strong></td>
			<td class="tcat"><strong>{$lang->hp_reason}</strong></td>
			<td class="tcat"><strong>{$lang->hp_points}</strong></td>
			<td class="tcat"><strong>{$lang->hp_link}</strong></td>
		</tr>
		<tr>
			<td class="trow1">
				<select name="hp_chara">
					{$select_charas}
				</select>
			</td>
			<td class="trow2">
				<select name="hp_reason">
					<option>[COMMUNITY] Gesucherstellung</option>
					<option>[COMMUNITY] Gesuchübernahme</option>
					<option>[COMMUNITY] Setanfrage bearbeitet</option>
					<option>[INPLAY] Regel gebroche</option>
				</select>
			</td>
			<td class="trow1">
				<input type="number" placeholder="5" name="hp_points"  required  class="textbox" >
					</td>
					<td class="trow2">
				<input type="text" placeholder="https://" name="hp_link"   class="textbox" >
					</td>
		</tr>
		<tr><td class="trow1" colspan="4"><input type="submit" name="add_housepoints" value="{$lang->hp_submit_points}" id="submit" class="button"></td></tr>
	</table>
</form>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'hp_modcp',
        'template' => $db->escape_string('<html>
<head>
<title>{$mybb->settings[\'bbname\']} - {$lang->modcp}</title>
{$headerinclude}
</head>
<body>
{$header}
<table width="100%" border="0" align="center">
<tr>
{$modcp_nav}
<td valign="top">
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead" align="center"><strong>{$lang->hp_modcp}</strong></td>
</td>
</tr>
	<tr><td class="trow1">
		<table width="80%" style="margin: auto;">
			<tr>
				<td class="tcat"><strong>{$lang->hp_modcp_charas}</strong></td>
				<td class="tcat"><strong>{$lang->hp_modcp_reason}</strong></td>
				<td class="tcat"><strong>{$lang->hp_modcp_points}</strong></td>
				<td class="tcat"><strong>{$lang->hp_modcp_link}</strong></td>
				<td class="tcat"><strong>{$lang->hp_modcp_option}</strong></td>
			</tr>
			{$hp_modcp_charas}
		</table>

	<br /><br />
		<h1>Gesamte Protokoll</h1>
		{$multipage}
			<table width="80%" style="margin: auto;">
			<tr>
				<td class="tcat"><strong>{$lang->hp_modcp_charas}</strong></td>
				<td class="tcat"><strong>{$lang->hp_modcp_reason}</strong></td>
				<td class="tcat"><strong>{$lang->hp_modcp_points}</strong></td>
						<td class="tcat"><strong>{$lang->hp_modcp_option}</strong></td>
			</tr>
			{$hp_modcp_charas_protocol}
		</table>
		</td>
	</tr>
</table>		</td>
	</tr>
</table>
</td>
</tr>
</table>
{$footer}
</body>
</html>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'hp_modcp_charas',
        'template' => $db->escape_string('<tr>
	<td class="trow1">{$charaname}</td>
	<td class="trow2">{$hp_reason}</td>
	<td class="trow1">{$hp_points}</td>
	<td class="trow2">{$hp_link}</td>
	<td class="trow1">{$accept_offer} || {$refuse_offer}</td>
</tr>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'hp_modcp_charas_protocol',
        'template' => $db->escape_string('<tr>
	<td class="trow1">{$charaname}</td>
	<td class="trow2">{$hp_reason}
	<br />{$hp_link}
	</td>
	<td class="trow1">{$hp_points} 
	</td>
	<td class="trow2">{$delete_entry}</td>
</tr>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'hp_newrequest',
        'template' => $db->escape_string('<div class="red_alert">
	<a href="modcp.php?action=housepoints">{$lang->hp_alert_newrequest}</a>
</div>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'hp_own_protocol',
        'template' => $db->escape_string('<html>
<head>
<title>{$mybb->settings[\'bbname\']} - {$lang->hp_ownoverview}</title>
{$headerinclude}
</head>
<body>
{$header}

<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead"><strong>{$lang->hp_ownoverview}</strong></td>
</tr>
<tr>
<td class="trow1" align="center">
	<h1>{$total_charascore} {$lang->hp_totalscore}</h1>
	
	{$multipage}
	<table>
		<tr>
			<td class="tcat" colspan="3">{$lang->hp_housepoints}</td>
		</tr>
		{$hp_own_protocol_bit}
	</table>

</td>
</tr>
</table>
{$footer}
</body>
</html>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'hp_own_protocol_bit ',
        'template' => $db->escape_string('<tr>
	<td class="trow1">{$hp_points} {$lang->hp_housepoints}</td>
	<td class="trow2">{$hp_reason}</td>
	<td class="trow1">{$hp_link}</td>
</tr>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'hp_profile',
        'template' => $db->escape_string('<strong>{$lang->hp_profile}</strong> {$chara_points}'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);


}

function housepoints_is_installed()
{
    global $db;
    if($db->table_exists("housepoints"))
    {
        return true;
    }
    return false;
}

function housepoints_uninstall()
{
    global $db, $cache;

    // Datenbanken löschen
    if ($db->table_exists("housepoints")) {
        $db->drop_table("housepoints");
    }

    if ($db->field_exists("canaddhp", "usergroups")) {
        $db->drop_column("usergroups", "canaddhp");
    }

    if ($db->field_exists("user_hp", "users")) {
        $db->drop_column("users", "user_hp");
    }

    // Einstellungen löschen
    $db->query("DELETE FROM " . TABLE_PREFIX . "settinggroups WHERE name='houespoints_settings'");
    $db->query("DELETE FROM " . TABLE_PREFIX . "settings WHERE name='housepoints_setting_fid'");
    $db->query("DELETE FROM " . TABLE_PREFIX . "settings WHERE name='housepoints_setting_inplay'");
    $db->query("DELETE FROM " . TABLE_PREFIX . "settings WHERE name='housepoints_setting_postcountpoints'");
    $db->query("DELETE FROM " . TABLE_PREFIX . "settings WHERE name='housepoints_setting_postcountextra'");
    $db->query("DELETE FROM " . TABLE_PREFIX . "settings WHERE name='housepoints_setting_postcountnumberextranumber'");
    $db->query("DELETE FROM " . TABLE_PREFIX . "settings WHERE name='housepoints_setting_postcountnumberextrapoints'");


    $db->delete_query('templates', "title LIKE '%hp%'");
}

function housepoints_activate()
{
    global $db, $cache;

    //Alertseinstellungen
    if(class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
        $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

        if (!$alertTypeManager) {
            $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
        }

        $alertType = new MybbStuff_MyAlerts_Entity_AlertType();
        $alertType->setCode('hp_accepted'); // The codename for your alert type. Can be any unique string.
        $alertType->setEnabled(true);
        $alertType->setCanBeUserDisabled(true);

        $alertTypeManager->add($alertType);

        $alertType = new MybbStuff_MyAlerts_Entity_AlertType();
        $alertType->setCode('hp_rejected'); // The codename for your alert type. Can be any unique string.
        $alertType->setEnabled(true);
        $alertType->setCanBeUserDisabled(true);

        $alertTypeManager->add($alertType);
    }

    //templates
    include MYBB_ROOT."/inc/adminfunctions_templates.php";
    find_replace_templatesets("header", "#".preg_quote('{$awaitingusers}')."#i", '{$awaitingusers}{$hp_alert_newrequests}{$hp_header}');
    find_replace_templatesets("member_profile", "#".preg_quote('{$online_status}')."#i", '{$profile_points}{$online_status}');
    find_replace_templatesets("modcp_nav_users", "#".preg_quote('{$nav_ipsearch}')."#i", '{$nav_ipsearch}{$housepoints_modcp}');

}

function housepoints_deactivate()
{
    global $db, $cache;

    //Alertseinstellungen
    if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
        $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

        if (!$alertTypeManager) {
            $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
        }

        $alertTypeManager->deleteByCode('hp_accepted');
        $alertTypeManager->deleteByCode('hp_rejected');

    }

    // Templates

    include MYBB_ROOT."/inc/adminfunctions_templates.php";
    find_replace_templatesets("header", "#".preg_quote('{$hp_alert_newrequests}{$hp_header}')."#i", '', 0);
    find_replace_templatesets("member_profile", "#".preg_quote('{$profile_points}')."#i", '', 0);
    find_replace_templatesets("modcp_nav_users", "#".preg_quote('{$housepoints_modcp}')."#i", '', 0);

}

// ADMIN-CP PEEKER
$plugins->add_hook('admin_config_settings_change', 'housepoints_settings_change');
$plugins->add_hook('admin_settings_print_peekers', 'housepoints_settings_peek');
function housepoints_settings_change(){
    global $db, $mybb, $housepoints_settings_peeker;

    $result = $db->simple_select('settinggroups', 'gid', "name='houespoints_settings'", array("limit" => 2));
    $group = $db->fetch_array($result);
    $housepoints_settings_peeker = ($mybb->input['gid'] == $group['gid']) && ($mybb->request_method != 'post');
}
function housepoints_settings_peek(&$peekers){
    global $mybb, $housepoints_settings_peeker;

    if ($housepoints_settings_peeker) {
        $peekers[] = 'new Peeker($(".setting_housepoints_setting_postcountextra"), $("#row_setting_housepoints_setting_postcountnumberextranumber"),/1/,true)';
        $peekers[] = 'new Peeker($(".setting_housepoints_setting_postcountextra"), $("#row_setting_housepoints_setting_postcountnumberextrapoints"),/1/,true)';
    }
}


// Backend Hooks
$plugins->add_hook("admin_formcontainer_end", "housepoints_usergroup_permission");
$plugins->add_hook("admin_user_groups_edit_commit", "housepoints_usergroup_permission_commit");

// Usergruppen-Berechtigungen
function housepoints_usergroup_permission()
{
    global $mybb, $lang, $form, $form_container, $run_module;

    if($run_module == 'user' && !empty($form_container->_title) & !empty($lang->misc) & $form_container->_title == $lang->misc)
    {
        $housepoints_options = array(
            $form->generate_check_box('canaddhp', 1, "Kann Hauspunkte hinzufügen?", array("checked" => $mybb->input['canaddhp'])),
        );
        $form_container->output_row("Einstellung für Hauspunkte", "", "<div class=\"group_settings_bit\">".implode("</div><div class=\"group_settings_bit\">", $housepoints_options)."</div>");
    }
}

function housepoints_usergroup_permission_commit()
{
    global $db, $mybb, $updated_group;
    $updated_group['canaddhp'] = $mybb->get_input('canaddhp', MyBB::INPUT_INT);
}

$plugins->add_hook('misc_start', 'housepoints_misc');

// In the body of your plugin
function housepoints_misc()
{
    global $mybb, $templates, $lang, $header, $headerinclude, $footer, $db, $select_charas, $select_reasons, $points_reasons, $points_reasons_setting, $fid, $charaname, $total_charascore;
    $lang->load('housepoints');
    $group_fid = $mybb->settings['housepoints_setting_fid'];
    $fid = "fid".$group_fid;



    if($mybb->get_input('action') == 'housepoints_overview') {
        // Do something, for example I'll create a page using the hello_world_template

        // Add a breadcrumb
        add_breadcrumb($lang->hp_overview, "misc.php?action=housepoints_overview");

        //welcher user ist online
        $this_user = intval($mybb->user['uid']);

        //für den fall nicht mit hauptaccount online
        $as_uid = intval($mybb->user['as_uid']);


        // Lese nur für Gruppen das Formular aus, welche es auch dürfen

        if ($mybb->usergroup['canaddhp'] == 1) {
            // Alle Charaktere auslesen, die zum aktuellen User gehören

// suche alle angehangenen accounts
            if ($as_uid == 0) {
                $select = $db->query("SELECT * 
                FROM " . TABLE_PREFIX . "users 
                WHERE (as_uid = $this_user) 
                OR (uid = $this_user) 
                 and not usergroup = '2'
                ORDER BY username ASC");
            } else if ($as_uid != 0) {
//id des users holen wo alle angehangen sind
                $select = $db->query("SELECT * 
                FROM " . TABLE_PREFIX . "users 
                WHERE (as_uid = $as_uid) 
                OR (uid = $this_user) 
                OR (uid = $as_uid)    
                and not usergroup = '2'
                ORDER BY username ASC");
            }

            while ($own_charas = $db->fetch_array($select)) {
                $select_charas .= "<option value='{$own_charas['uid']}'>{$own_charas['username']}</option>";
            }


            eval("\$housepounts_form = \"" . $templates->get("hp_misc_form") . "\";");
        }

        // Hauspunkte Anfragen
        if (isset($mybb->input['add_housepoints'])) {
            $addhp = array(
                "hp_reason" => $db->escape_string($mybb->input['hp_reason']),
                "hp_points" => (int)$mybb->input['hp_points'],
                "hp_link" => $db->escape_string($mybb->input['hp_link']),
                "hp_uid" => (int)$mybb->input['hp_chara'],
                "hp_ok" => 0,
            );

            $db->insert_query("housepoints", $addhp);
            redirect("misc.php?action=housepoints_overview");
        }


        $allhouses = $db->fetch_field($db->query("SELECT type FROM " . TABLE_PREFIX . "profilefields WHERE fid = '".$group_fid."'"), "type");

        $houses = explode("\n", $allhouses);


        array_shift($houses);

        foreach ($houses as $house) {
            if($house != 'keine Angabe') {
                $hp_misc_chara = "";
                $total_score = 0;
                $chara_select = $db->query("SELECT *
            FROM " . TABLE_PREFIX . "users u
            LEFT JOIN " . TABLE_PREFIX . "userfields uf
            on (u.uid = uf.ufid)
            WHERE uf.$fid = '" . $house . "'
            ORDER BY username ASC
            ");

                while ($chara = $db->fetch_array($chara_select)) {
                    $username = format_name($chara['username'], $chara['usergroup'], $chara['displaygroup']);
                    $charaname = build_profile_link($username, $chara['uid']) . " (" . $chara['user_hp'] . " Hauspunkte)";

                    $total_score = $total_score + $chara['user_hp'];

                    eval("\$hp_misc_chara .= \"" . $templates->get("hp_misc_chara") . "\";");
                }

                eval("\$hp_misc_bit.= \"" . $templates->get("hp_misc_bit") . "\";");
            } else{
                $hp_misc_bit = "";
            }
        }


        // Using the misc_help template for the page wrapper
        eval("\$page = \"".$templates->get("hp_misc")."\";");
        output_page($page);
    }


    if($mybb->get_input('action') == 'housepoints_ownoverview') {
        // Do something, for example I'll create a page using the hello_world_template

        // Add a breadcrumb
        add_breadcrumb($lang->hp_ownoverview, "misc.php?action=housepoints_ownoverview");

        $uid = $mybb->user['uid'];

        $total_charascore = $db->fetch_field($db->simple_select("users", "user_hp", "uid = '".$uid."'"),"user_hp");


        $select_ownhp = $db->query("SELECT COUNT(*) AS hp
        FROM ".TABLE_PREFIX."housepoints hp
        WHERE hp_uid = '".$uid."'
        and hp_ok = 1
        ");

        $count = $db->fetch_field($select_ownhp, "hp");
        $perpage = 16;
        $page = intval($mybb->input['page']);

        if($page) {
            $start = ($page-1) *$perpage;
        }
        else {
            $start = 0;
            $page = 1;
        }
        $end = $start + $perpage;
        $lower = $start+1;
        $upper = $end;
        if($upper > $count) {
            $upper = $count;
        }

        $url = "{$mybb->settings['bburl']}/misc.php?action=housepoints_ownoverview";

        $multipage = multipage($count, $perpage, $page, $url);

        $select = $db->query("SELECT *
        FROM ".TABLE_PREFIX."housepoints hp
        WHERE hp_uid = '".$uid."'
        and hp_ok = 1
        ");

        while($hpinfo = $db->fetch_array($select)){

            $hp_points = 0;
            $hp_link = "";
            $hp_reason = "";

            // Infos auslesen
            $hp_reason = $hpinfo['hp_reason'];
            $hp_points = $hpinfo['hp_points'];
            $hp_link = "<a href='{$hpinfo['hp_link']}'>{$lang->hp_modcp_link}</a>";
            eval("\$hp_own_protocol_bit .= \"" . $templates->get("hp_own_protocol_bit") . "\";");

        }
        eval("\$page = \"" . $templates->get("hp_own_protocol") . "\";");
        output_page($page);
    }
}

// Post zählen

$plugins->add_hook('newthread_do_newthread_start', 'houspoints_newthread');
//Postlänge in einer neuen Szene
function houspoints_newthread()
{
    global $db, $mybb, $templates, $forum, $tid, $lang;
    $lang->load('housepoints');
    $uid = $mybb->user['uid'];
    //Inplaykategorie
    $ip_id = $mybb->settings['housepoints_setting_inplay'];
    
    //Punkte
    $postpoints = $mybb->settings['housepoints_setting_postcountpoints'];
    $postcharsextra = $mybb->settings['housepoints_setting_postcountnumberextranumber'];
    $postpointsextra = $mybb->settings['housepoints_setting_postcountnumberextrapoints'];

    $forum['parentlist'] = ",".$forum['parentlist'].",";

    $housepoints = 0;

    if(preg_match("/,$ip_id,/i", $forum['parentlist'])){

        $message = $mybb->get_input('message');
        $messengelength = strlen($message);
        $scene_link = "showthread.php?tid={$tid}";

        if($mybb->settings['housepoints_setting_postcountextra'] == 1) {
            if($messengelength < $postcharsextra) {

                $housepoints = $postpoints;

                $prot_entry = array(
                    "hp_reason" => $db->escape_string($lang->hp_prot_startscene),
                    "hp_points" => (int) $housepoints,
                    "hp_link" => $db->escape_string($scene_link),
                    "hp_uid" => (int)$uid,
                    "hp_ok" => 1
                );
            }

            if ($messengelength >= $postcharsextra) {
                $housepoints = $postpointsextra + $postpoints;

                $prot_entry = array(
                    "hp_reason" => $db->escape_string($lang->hp_prot_startscene_extra),
                    "hp_points" => (int) $housepoints,
                    "hp_link" => $db->escape_string($scene_link),
                    "hp_uid" => (int)$uid,
                    "hp_ok" => 1
                );
            }
        } else{

            $housepoints = $postpoints;

            $prot_entry = array(
                "hp_reason" => $db->escape_string($lang->hp_prot_startscene),
                "hp_points" => (int) $housepoints,
                "hp_link" => $db->escape_string($scene_link),
                "hp_uid" => (int)$uid,
                "hp_ok" => 1
            );
        }

        //Punkte pro Post werden drauf gerechnet.

        $db->insert_query("housepoints",$prot_entry);

        $db->query("UPDATE " . TABLE_PREFIX . "users SET user_hp = user_hp + '" . $housepoints . "'  WHERE uid = $uid");

    }
}

$plugins->add_hook('newreply_do_newreply_start', 'houspoints_reply');
//Postlänge in einer neuen Antwort
function houspoints_reply()
{
    global $db, $mybb, $templates, $forum, $thread, $pid, $lang;
    $lang->load('housepoints');
    $uid = $mybb->user['uid'];
    //Inplaykategorie
    $ip_id = $mybb->settings['housepoints_setting_inplay'];

    //Punkte
    $postpoints = $mybb->settings['housepoints_setting_postcountpoints'];
    $postcharsextra = $mybb->settings['housepoints_setting_postcountnumberextranumber'];
    $postpointsextra = $mybb->settings['housepoints_setting_postcountnumberextrapoints'];

    $forum['parentlist'] = ",".$forum['parentlist'].",";

    $housepoints = 0;

    if(preg_match("/,$ip_id,/i", $forum['parentlist'])){

        $message = $mybb->get_input('message');
        $messengelength = strlen($message);
        $scene_link = "showthread.php?tid=".$thread['tid']."?pid=".$pid."#pid=".$pid;

        if($mybb->settings['housepoints_setting_postcountextra'] == 1) {
            if($messengelength < $postcharsextra){
                $housepoints = $postpoints;

                // Was soll in das Protokoll eingetragen werden
                $prot_entry = array(
                    "hp_reason" => $db->escape_string($lang->hp_prot_post),
                    "hp_points" => (int)$housepoints,
                    "hp_link" => $db->escape_string($scene_link),
                    "hp_uid" => (int)$uid,
                    "hp_ok" => 1
                );



            }
            elseif ($messengelength > $postcharsextra or $messengelength = $postcharsextra) {
                $housepoints = $postpointsextra + $postpoints;

                // Was soll ins Protokoll eingetragen werden.
                $prot_entry = array(
                    "hp_reason" => $db->escape_string($lang->hp_prot_extra_post),
                    "hp_points" => (int)$housepoints,
                    "hp_link" => $db->escape_string($scene_link),
                    "hp_uid" => (int)$uid,
                    "hp_ok" => 1
                );
            }
        } else{
            $housepoints = $postpoints;

            // Was soll in das Protokoll eingetragen werden
            $prot_entry = array(
                "hp_reason" => $db->escape_string($lang->hp_prot_post),
                "hp_points" => (int)$housepoints,
                "hp_link" => $db->escape_string($scene_link),
                "hp_uid" => (int)$uid,
                "hp_ok" => 1
            );

        }
        // Ins Protokoll eintragen
        $db->insert_query("housepoints",$prot_entry);
        //Punkte pro Post werden drauf gerechnet.

        $db->query("UPDATE " . TABLE_PREFIX . "users SET user_hp = user_hp + '" . $housepoints . "'  WHERE uid = $uid");
    }
}


// Profilanzeige
$plugins->add_hook("member_profile_start", "housepoints_profile");
function housepoints_profile(){
    global $mybb, $templates, $db, $memprofile, $chara_points, $lang, $profile_points;
    $lang->load('housepoints');

    $chara = $memprofile['uid'];

    $chara_points = $db->fetch_field($db->simple_select("users", "user_hp", "uid = '".$chara."' "), "user_hp");

    eval("\$profile_points = \"".$templates->get("hp_profile")."\";");
}


// mod cp
$plugins->add_hook("modcp_nav", "housepoints_modcp_nav");

function housepoints_modcp_nav(){
    global $housepoints_modcp, $lang;
    //Die Sprachdatei
    $lang->load('housepoints');
    $housepoints_modcp = "<tr><td class=\"trow1 smalltext\"><a href=\"modcp.php?action=housepoints\" class=\"modcp_nav_item modcp_nav_banning\">{$lang->hp_modcp_nav}</a></td></tr>";
}


$plugins->add_hook('modcp_start', 'housepoints_modcp');

// In the body of your plugin
function housepoints_modcp()
{
    global $mybb, $templates, $lang, $header, $headerinclude, $theme, $footer, $group, $page, $db, $parser, $options, $modcp_nav,$delete_entry;
    $lang->load('housepoints');
    $lang->load('modcp');

    add_breadcrumb($lang->nav_modcp, "modcp.php");
    // Canons ausgeben :D
    if($mybb->get_input('action') == 'housepoints') {
        add_breadcrumb($lang->hp_modcp_nav, "modcp.php?action=housepoints");

        $select_newhp = $db->query("SELECT *
        FROM ".TABLE_PREFIX."housepoints h
        LEFT JOIN ".TABLE_PREFIX."users u
        on (h.hp_uid = u.uid)
        WHERE h.hp_ok = 0
        ");

        while($row = $db->fetch_array($select_newhp)){
            $refuse_offer = "";
            $accept_offer = "";

            $username = format_name ($row['username'], $row['usergroup'], $row['displaygroup']);
            $charaname = build_profile_link ($username, $row['uid']);

            // Infos auslesen
            $hp_reason = $row['hp_reason'];
            $hp_points = $row['hp_points']. " Hauspunkte";
            $hp_link = "<a href='{$row['hp_link']}'>{$lang->hp_modcp_link}</a>";

            // Anfrage annehmen
            $accept_offer = "<a href='modcp.php?action=housepoints&accept={$row['hp_id']}&housepoints={$hp_points}'>{$lang->hp_modcp_accept}</a>";

            // Anfrage Ablehnen
            $refuse_offer = "<a href='modcp.php?action=housepoints&refuse={$row['hp_id']}'>{$lang->hp_modcp_refuse}</a>";

            eval("\$hp_modcp_charas .= \"".$templates->get("hp_modcp_charas")."\";");

        }

        $acceptoffer = $mybb->input['accept'];
        if($acceptoffer) {
            $accept_select = $db->simple_select("housepoints", "*", "hp_id = '".$acceptoffer."'");
            $row = $db->fetch_array($accept_select);

            $uid = $row['hp_uid'];
            $from_uid = $mybb->user['uid'];
            $points_reason = $row['hp_reason'];
            $get_points = $row['hp_points'];


            $housepoints = $mybb->input['housepoints'];

            // Alerts

            if(class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
                $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('hp_accepted');
                if ($alertType != NULL && $alertType->getEnabled() && $from_uid != $uid) {
                    $alert = new MybbStuff_MyAlerts_Entity_Alert((int)$uid, $alertType);
                    $alert->setExtraDetails([
                        'points_reason' => $points_reason,
                        'housepoints' => $get_points
                    ]);
                    MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                }
            }

            $db->query("UPDATE " . TABLE_PREFIX . "housepoints SET hp_ok = 1  WHERE hp_id = '" . $acceptoffer . "'");
            $db->query("UPDATE " . TABLE_PREFIX . "users SET user_hp = user_hp + '" . $housepoints . "'  WHERE uid = '" . $uid . "'");
            redirect ("modcp.php?action=housepoints");
        }

        $refuseoffer = $mybb->input['refuse'];
        if($refuseoffer) {
            $refuse_select = $db->simple_select("housepoints", "hp_uid", "hp_id = '".$refuseoffer."'");
            $row = $db->fetch_array($refuse_select);

            $uid = $row['hp_uid'];
            $from_uid = $mybb->user['uid'];

            // Alerts

            if(class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
                $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('hp_rejected');
                if ($alertType != NULL && $alertType->getEnabled() && $from_uid != $uid) {
                    $alert = new MybbStuff_MyAlerts_Entity_Alert((int)$uid, $alertType);
                    MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                }
            }

                     $db->delete_query("housepoints", "hp_id = '".$refuseoffer."'");
            redirect ("modcp.php?action=housepoints");
        }

        $select_canons = $db->query("SELECT COUNT(*) AS hp
       FROM ".TABLE_PREFIX."housepoints h
        LEFT JOIN ".TABLE_PREFIX."users u
        on (h.hp_uid = u.uid)
        WHERE h.hp_ok = 1
        ");

        $count = $db->fetch_field($select_canons, "hp");
        $perpage = 8;
        $page = intval($mybb->input['page']);

        if($page) {
            $start = ($page-1) *$perpage;
        }
        else {
            $start = 0;
            $page = 1;
        }
        $end = $start + $perpage;
        $lower = $start+1;
        $upper = $end;
        if($upper > $count) {
            $upper = $count;
        }

        $url = "{$mybb->settings['bburl']}/modcp.php?action=housepoints";

        $multipage = multipage($count, $perpage, $page, $url);

        $select_point = $db->query("SELECT *
       FROM ".TABLE_PREFIX."housepoints h
        LEFT JOIN ".TABLE_PREFIX."users u
        on (h.hp_uid = u.uid)
        WHERE h.hp_ok = 1
         LIMIT $start, $perpage 
        ");

        while($row = $db->fetch_array($select_point)){
            $hp_points = 0;
            $hp_link = "";
            $hp_reason = "";
            $delete_entry = "";


            $username = format_name ($row['username'], $row['usergroup'], $row['displaygroup']);
            $charaname = build_profile_link ($username, $row['uid']);
            $uid = $row['uid'];

            // Infos auslesen
            $hp_reason = $row['hp_reason'];
            $hp_points = $row['hp_points']. " Hauspunkte";
            $hp_link = "<a href='{$row['hp_link']}'>{$lang->hp_modcp_link}</a>";
            $delete_entry = "<a href='modcp.php?action=housepoints&delete_entry={$row['hp_id']}&uid={$uid}&housepoints={$row['hp_points']}'>{$lang->hp_modcp_deleteentry}</a>";

            eval("\$hp_modcp_charas_protocol .= \"".$templates->get("hp_modcp_charas_protocol")."\";");
        }

        $delete_entry = $mybb->input['delete_entry'];

        if($delete_entry){
            $uid = $mybb->input['uid'];
            $housepoints = $mybb->input['housepoints'];
            $db->query("UPDATE " . TABLE_PREFIX . "users SET user_hp = user_hp - '" . $housepoints . "'  WHERE uid = '" . $uid . "'");
            $db->delete_query("housepoints", "hp_id = '".$delete_entry."'");
            redirect ("modcp.php?action=housepoints");
        }

        // Using the misc_help template for the page wrapper
        eval("\$page = \"".$templates->get("hp_modcp")."\";");
        output_page($page);
    }
}

// Headerbereich
$plugins->add_hook('global_start', 'housepoints_global');

function housepoints_global(){
    global $db, $mybb, $templates, $lang, $hp_alert_newrequests;
    $lang->load('housepoints');
    // Teamalert

    if($mybb->usergroup['canmodcp'] == 1){
        $new_hp_query = $db->query("SELECT hp_ok
        FROM ".TABLE_PREFIX."housepoints
        where hp_ok = 0
        ");

        $count_request = mysqli_num_rows($new_hp_query);

        if($count_request > 0){
            eval("\$hp_alert_newrequests = \"".$templates->get("hp_newrequest")."\";");
        }

    }
}
$plugins->add_hook('global_intermediate', 'housepoints_header');

function housepoints_header()
{
    global $db, $mybb, $templates, $lang, $hp_header, $hp_header_houses, $total_score;
    $lang->load('housepoints');

    // Settings
    $group_fid = $mybb->settings['housepoints_setting_fid'];
    $fid = "fid".$group_fid;

    /* ZUERST LESEN WIR DIE OPTIONSMÖGLICHKEITEN AUS, UM ES GLEICH ETWAS EINFACHER ZU HABEN */
    $allhouses = $db->fetch_field($db->query("SELECT type FROM " . TABLE_PREFIX . "profilefields WHERE fid = '" . $group_fid . "'"), "type");

    /* JETZT BÜNDELN WIR UNSER ERGEBNIS IN EIN ARRAY */
    $houses = explode("\n", $allhouses);

    /* DAS ERSTE ELEMENT HIER IST IMMER DER FELDTYP, WESWEGEN WIR DEN DIREKT RAUSNEHMEN */
    array_shift($houses);

    foreach ($houses as $house) {
        if ($house != 'keine Angabe') {
            $total_score = 0;
            $chara_select = $db->query("SELECT *
            FROM " . TABLE_PREFIX . "users u
            LEFT JOIN " . TABLE_PREFIX . "userfields uf
            on (u.uid = uf.ufid)
            WHERE uf.$fid = '" . $house . "'
            ORDER BY username ASC
            ");

            while ($chara = $db->fetch_array($chara_select)) {

                // Total Score berechnen
                $total_score = $total_score + $chara['user_hp'];
            }

            eval("\$hp_header_houses .= \"" . $templates->get("hp_header_houses") . "\";");
        }else{
            $hp_header_houses = "";
        }
    }
    eval("\$hp_header .= \"" . $templates->get("hp_header") . "\";");

}


function housepoints_alerts()
{
    global $mybb, $lang;
    $lang->load('housepoints');

    class MybbStuff_MyAlerts_Formatter_myalerts_AcceptedPointsFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
    {
        /**
         * Format an alert into it's output string to be used in both the main alerts listing page and the popup.
         *
         * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to format.
         *
         * @return string The formatted alert string.
         */
        public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert, array $outputAlert)
        {
            $alertContent = $alert->getExtraDetails();
            return $this->lang->sprintf(
                $this->lang->hp_accepted,
                $outputAlert['from_user'],
                $alertContent['points_reason'],
                $alertContent['housepoints'],
                $outputAlert['dateline']
            );
        }


        /**
         * Init function called before running formatAlert(). Used to load language files and initialize other required
         * resources.
         *
         * @return void
         */
        public function init()
        {
        }

        /**
         * Build a link to an alert's content so that the system can redirect to it.
         *
         * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to build the link for.
         *
         * @return string The built alert, preferably an absolute link.
         */
        public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert)
        {
            $alertContent = $alert->getExtraDetails();
            return $this->mybb->settings['bburl'] . '/misc.php?action=housepoints_overview';
        }

    }
    if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
        $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();

        if (!$formatterManager) {
            $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
        }

        $formatterManager->registerFormatter(
            new MybbStuff_MyAlerts_Formatter_myalerts_AcceptedPointsFormatter($mybb, $lang, 'hp_accepted')
        );
    }

    class MybbStuff_MyAlerts_Formatter_myalerts_RejectedPointsFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
    {
        /**
         * Format an alert into it's output string to be used in both the main alerts listing page and the popup.
         *
         * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to format.
         *
         * @return string The formatted alert string.
         */
        public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert, array $outputAlert)
        {
            $alertContent = $alert->getExtraDetails();
            return $this->lang->sprintf(
                $this->lang->hp_rejected,
                $outputAlert['from_user'],
                $outputAlert['dateline']
            );
        }


        /**
         * Init function called before running formatAlert(). Used to load language files and initialize other required
         * resources.
         *
         * @return void
         */
        public function init()
        {
        }

        /**
         * Build a link to an alert's content so that the system can redirect to it.
         *
         * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to build the link for.
         *
         * @return string The built alert, preferably an absolute link.
         */
        public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert)
        {
            $alertContent = $alert->getExtraDetails();
            return $this->mybb->settings['bburl'] . '/misc.php?action=housepoints_overview';
        }
    }

    if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
        $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();

        if (!$formatterManager) {
            $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
        }

        $formatterManager->registerFormatter(
            new MybbStuff_MyAlerts_Formatter_myalerts_RejectedPointsFormatter($mybb, $lang, 'hp_rejected')
        );
    }

}
