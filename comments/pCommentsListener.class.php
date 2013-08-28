<?php

class pCommentsListener {

    private $_permissionGroup;

    public function __construct($PermissionGroup_ID) {
        // системный объект
        $nc_core = nc_Core::get_object();

        // прикрепление события
        $nc_core->event->bind($this, "addComment");                     

        // группа получателей рассылки
        $this->_permissionGroup = $PermissionGroup_ID;
    }
    
    public function addComment($Catalogue_ID, $Subdivision_ID, $Sub_Class_ID, $Class_ID, $Message_ID, $Comment_ID) {        
        $nc_core = nc_Core::get_object();
        $system_env = $nc_core->get_settings();
        
        $sitename = $nc_core->catalogue->get_by_id($Catalogue_ID,'Catalogue_Name');
        $siteurl = $nc_core->catalogue->get_by_id($Catalogue_ID,'Domain');
        $subname = p_sub_title( $Subdivision_ID);
        
        $comment_text = $nc_core->db->get_var('SELECT Comment FROM `Comments_Text` WHERE id = '.$Comment_ID);
        
        $subj = 'Новый комментарий: ' . $sitename . ' / ' . $subname;

        //generate title for class
        $classTitle = $nc_core->db->get_var('SELECT `TitleTemplate` FROM  `Class`  WHERE Class_ID = '.$Class_ID);
        if ($classTitle) {
            $field = str_replace('$f_', '', $classTitle);
            if ($field) {
                //p_log($field);
                $m = $nc_core->message->get_by_id($Class_ID,$Message_ID);
                $subj = $subj .  ' / ' . $m[$field];
            }
        }
        
        
        //p_log($subj . ' - '. $Comment_ID .' - '.  $comment_text);

        $text = $subj;
        $text .= '<br />Текст комментария: '.$comment_text;

        $url = 'http://'.$siteurl.nc_message_link($Message_ID,$Class_ID);
        $text .= '<br /><a href="'.$url.'">Перейти к комментарию</a>';
        //$text .= '<br /><a href="http://'.$siteurl.'/netcat/admin/#module.comments.list">Управление комментариями</a>';
        
        $this->notifyCatManagers($Catalogue_ID,$subj,$text);
    }


    public function notifyCatManagers($catalogue,$subj, $text)  {
        global $db;

        $nc_core = nc_Core::get_object();
        $system_env = $nc_core->get_settings();


        $mailer = new CMIMEMail();
        $mailer->mailbody(strip_tags($text),$text);


        // Выбираем все пользователей, которые состоят в группе Рассылки ($this->_permissionGroup)
        // а так же являются
        // 1. Директорами и Супервизорами
        // 2. Или редакторами сайта, на котором сделан коммент
        $tos = $db->get_col("SELECT DISTINCT User.Email FROM `User_Group` 
                            JOIN User ON (User.User_ID = User_Group.User_ID)
                            WHERE 
                            User_Group.User_ID IN (
                            SELECT 
                             User_ID 
                            FROM 
                            `Permission` 

                            WHERE 
                             `AdminType` IN (6,7)
                            OR
                             `AdminType` = 4 AND `Catalogue_ID` IN (0,$catalogue)
                            )
                            AND 
                            User_Group.`PermissionGroup_ID` = ".$this->_permissionGroup);

        if ($tos) {
            p_log('Send email to '. implode(', ', $tos).' Text is: '.$text . ' Subj is: '.$subj);    

            foreach ($tos as $to) {

                 $mailer->send( $to,
                                $system_env['SpamFromEmail'],                   
                                $system_env['SpamFromEmail'], 
                                $subj,
                                $system_env['SpamFromName']); 

            }
        } else {
            p_log('No recipients for '.$catalogue);
        }
    }

}