<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined( '_JEXEC' ) or die( 'Restricted access' );

class plgSystemForceFields extends JPlugin
{
	function plgSystemForceFields(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}

	function onAfterDispatch()
	{		
		$mainframe = JFactory::getApplication();
		if($mainframe->isAdmin()) return;

		//If its frontend, include JomSocial Core Libraries only if they exist
		$jscore = JPATH_ROOT .'/components/com_community/libraries/core.php' ;
			if (file_exists ( $jscore )) {
				include_once ($jscore);
			} else {
				return true;
			}

		// Return if visitor is not registered
		$user = CFactory::getUser();
		if(empty($user) || $user->id == 0){
			return true;
		}
		
		// Force Admin
		$forceadmin = $this->params->get( 'forceadmin' );
		if($forceadmin == 0) {
			if (COwnerHelper::isCommunityAdmin()){
				return true;
			}
		}
		
		//Load the language file - we might dont need this anymore for Joomla 3.1
		JPlugin::loadLanguage( 'plg_system_forcefields', JPATH_ADMINISTRATOR );
		
		// Let Alex figure this out
		// set the jinput
		$jinput 	= $mainframe->input;
		$uri = 		JRequest::getURI();
		$option	=	$jinput->get('option','','GET');
		$task	=	$jinput->get('task','','GET');
		$view	=	$jinput->get('view','','GET');
		
		//Compatibility fix with Force AVATAR - Just a redirection thing
		if($option=='com_community' && $task=='uploadAvatar' && $view=='profile'){
			return true;
		}
		
		if($option=='com_community' && $task=='edit' && $view=='profile'){
			return true;
		}
		if($option=='com_community' && $task=='changeprofile' && $view=='multiprofile'){
			return true;
		}
		if($option=='com_community' && $task=='updateProfile' && $view=='multiprofile'){
			return true;
		}
		if($option=='com_community' && $task=='profileupdated' && $view=='multiprofile'){
			return true;
		}
		
		$model = CFactory::getModel( 'profile' );
		$profile = $model->getEditableProfile($user->id , $user->getProfileType() );		
		$fields = $profile ['fields'];
		
		//verify that required custom profile fields are existing
		foreach ( $fields as $name => $fieldGroup )
		{
			if ($name != 'ungrouped'){
				foreach ( $fieldGroup as $field ){
					if ($field['required']){
						if( !CProfileLibrary::validateField( $field['id'], $field['type'] , $field['value'] , $field['required']) ){
							$url = 'index.php?option=com_community&view=profile&task=edit';
							$message = JText::_('PLG_FORCEFIELDS_MSG');
							$mainframe->enqueueMessage( CTemplate::quote($message) , 'error' );
							$mainframe->redirect($url);
						}
					}
				}
			}
		}
		
		return true;
	}
	
}