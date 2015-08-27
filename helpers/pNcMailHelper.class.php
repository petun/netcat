<?php


class pNcMailHelper {
	
	/**
	 * Send mail with netcat helper class
	 * $fields (array)
	 * $addText (string) text before params
	 * */
	public static function sendMail($to, $from, $fromName, $reply, $subject, $fields, $addText) {
		$mailer = new CMIMEMail();

		$text = '<p>'.$addText.'</p>' . "\n";
		$text .= '<ul>' . "\n\n";
		foreach ($fields as $name=>$value) {
			$text .= sprintf("<li>%s: %s</li>\n", $name, $value);
		}
		$text .= '</ul>';

		$mailer->mailbody( strip_tags($text), $text);
		return $mailer->send($to, $from, $reply, $subject, $fromName);
	}	

}