<?php
/*------------------------------------------------------------------------

# TZ Portfolio Plus Extension

# ------------------------------------------------------------------------

# author    DuongTVTemPlaza

# copyright Copyright (C) 2015 templaza.com. All Rights Reserved.

# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL

# Websites: http://www.templaza.com

# Technical Support:  Forum - http://templaza.com/Forum

-------------------------------------------------------------------------*/

// no direct access
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

jimport('joomla.application.component.controllerform');
jimport('joomla.filesystem.file');

class TZ_Portfolio_PlusControllerArticle extends JControllerForm
{
	protected $view_item = 'form';
	protected $view_list = 'portfolio';

    public function __construct($config = array()){
        JFactory::getLanguage() -> load('com_tz_portfolio_plus',JPATH_ADMINISTRATOR);
        parent::__construct($config);
    }

    public function download(){
		tzportfolioplusimport('phpclass.connection_tools_class');
        $model      = $this -> getModel('Article','TZ_Portfolio_PlusModel') -> download();
        $file       = JPATH_ROOT.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.$model;


        $mainframe  = JFactory::getApplication();
        
        if(JFile::exists($file)){

            tzConnector::sendfile($file,$this->check_filetype($file));
            $mainframe -> close();
        }
        return true;
    }

    private function check_filetype($filename) {

        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet'
        );

        $ext = strtolower(array_pop(explode('.',$filename)));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
        elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        }
        else {
            return 'application/octet-stream';
        }
    }

	/**
	 * Method to add a new record.
	 *
	 * @return	boolean	True if the article can be added, false if not.
	 * @since	1.6
	 */
	public function add()
	{
		if (!parent::add()) {
			// Redirect to the return page.
			$this->setRedirect($this->getReturnPage());
		}
	}

	/**
	 * Method override to check if you can add a new record.
	 *
	 * @param	array	An array of input data.
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	protected function allowAdd($data = array())
	{
		// Initialise variables.
		$user		= TZ_Portfolio_PlusUser::getUser();
		$categoryId	= ArrayHelper::getValue($data, 'catid', $this -> input -> getInt('catid'), 'int');
		$allow		= null;

		if ($categoryId) {
			// If the category has been passed in the data or URL check it.
			$allow	= $user->authorise('core.create', 'com_tz_portfolio_plus.category.'.$categoryId);
		}

		if ($allow === null) {
			// In the absense of better information, revert to the component permissions.
			return parent::allowAdd();
		}
		else {
			return $allow;
		}
	}

	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param	array	$data	An array of input data.
	 * @param	string	$key	The name of the key for the primary key.
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		// Initialise variables.
		$recordId	= (int) isset($data[$key]) ? $data[$key] : 0;
		$user		= TZ_Portfolio_PlusUser::getUser();
		$asset		= 'com_tz_portfolio_plus.article.'.$recordId;

		// Check general edit permission first.
		if ($user->authorise('core.edit', $asset)) {
			return true;
		}

        // Check edit own on the record asset (explicit or inherited)
        if ($user->authorise('core.edit.own', 'com_tz_portfolio_plus.article.' . $recordId))
        {
            // Existing record already has an owner, get it
            $record = $this->getModel()->getItem($recordId);

            if (empty($record))
            {
                return false;
            }

            // Grant if current user is owner of the record
            return $user->get('id') == $record->created_by;
        }

		// Since there is no asset tracking, revert to the component permissions.
		return false;
	}

	/**
	 * Method to cancel an edit.
	 *
	 * @param	string	$key	The name of the primary key of the URL variable.
	 *
	 * @return	Boolean	True if access level checks pass, false otherwise.
	 * @since	1.6
	 */
	public function cancel($key = 'a_id')
	{
		parent::cancel($key);

		// Redirect to the return page.
		$this->setRedirect($this->getReturnPage());
	}

	/**
	 * Method to edit an existing record.
	 *
	 * @param	string	$key	The name of the primary key of the URL variable.
	 * @param	string	$urlVar	The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return	Boolean	True if access level check and checkout passes, false otherwise.
	 * @since	1.6
	 */
	public function edit($key = null, $urlVar = 'a_id')
	{
		$result = parent::edit($key, $urlVar);

		return $result;
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param	string	$name	The model name. Optional.
	 * @param	string	$prefix	The class prefix. Optional.
	 * @param	array	$config	Configuration array for model. Optional.
	 *
	 * @return	object	The model.
	 * @since	1.5
	 */
	public function getModel($name = 'form', $prefix = '', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param	int		$recordId	The primary key id for the item.
	 * @param	string	$urlVar		The name of the URL variable for the id.
	 *
	 * @return	string	The arguments to append to the redirect URL.
	 * @since	1.6
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'a_id')
	{
		// Need to override the parent method completely.
		$tmpl		= $this -> input -> getCmd('tmpl');
		$layout		= $this -> input -> getCmd('layout', 'edit');
		$append		= '';

		// Setup redirect info.
		if ($tmpl) {
			$append .= '&tmpl='.$tmpl;
		}

		// TODO This is a bandaid, not a long term solution.
//		if ($layout) {
//			$append .= '&layout='.$layout;
//		}
		$append .= '&layout=edit';

		if ($recordId) {
			$append .= '&'.$urlVar.'='.$recordId;
		}

		$itemId	= $this -> input -> getInt('Itemid');
		$return	= $this->getReturnPage();
		$catId = $this -> input -> getInt('catid', null, 'get');

		if ($itemId) {
			$append .= '&Itemid='.$itemId;
		}

		if($catId) {
			$append .= '&catid='.$catId;
		}

		if ($return) {
			$append .= '&return='.base64_encode($return);
		}

		return $append;
	}

	/**
	 * Get the return URL.
	 *
	 * If a "return" variable has been passed in the request
	 *
	 * @return	string	The return URL.
	 * @since	1.6
	 */
	protected function getReturnPage()
	{
		$return = $this->input->get('return', null, 'base64');

		if (empty($return) || !JUri::isInternal(base64_decode($return))) {
			return JURI::base();
		}
		else {
			return base64_decode($return);
		}
	}

	/**
	 * Function that allows child controller access to model data after the data has been saved.
	 *
	 * @param	JModel	$model		The data model object.
	 * @param	array	$validData	The validated data.
	 *
	 * @return	void
	 * @since	1.6
	 */
//	protected function _postSaveHook(JModelLegacy $model, $validData = array())
//	{
//		$task = $this->getTask();
//
//		if ($task == 'save') {
//			$this->setRedirect(JRoute::_('index.php?option=com_tz_portfolio_plus&view=category&id='.$validData['catid'], false));
//		}
//	}
//
//    protected function __postSaveHook(JModel $model, $validData = array())
//	{
//		$task = $this->getTask();
//
//		if ($task == 'save') {
//			$this->setRedirect(JRoute::_('index.php?option=com_tz_portfolio_plus&view=category&id='.$validData['catid'], false));
//		}
//	}

	/**
	 * Method to save a record.
	 *
	 * @param	string	$key	The name of the primary key of the URL variable.
	 * @param	string	$urlVar	The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return	Boolean	True if successful, false otherwise.
	 * @since	1.6
	 */
	public function save($key = null, $urlVar = 'a_id')
	{
        // Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $result    = parent::save($key, $urlVar);
        $app       = JFactory::getApplication();
        $articleId = $app->input->getInt('a_id');

        // Load the parameters.
        $params   = $app->getParams();
        $menuitem = (int) $params->get('redirect_menuitem');

//        // Check for redirection after submission when creating a new article only
//        if ($menuitem > 0 && $articleId == 0)
//        {
//            $lang = '';
//
//            if (JLanguageMultilang::isEnabled())
//            {
//                $item = $app->getMenu()->getItem($menuitem);
//                $lang =  !is_null($item) && $item->language != '*' ? '&lang=' . $item->language : '';
//            }
//
//            // If ok, redirect to the return page.
//            if ($result)
//            {
//                $this->setRedirect(JRoute::_('index.php?Itemid=' . $menuitem . $lang, false));
//            }
//        }
//        else
//        {
//            // If ok, redirect to the return page.
//            if ($result)
//            {
//                $this->setRedirect(JRoute::_($this->getReturnPage(), false));
//            }
//        }
//        var_dump($this); die();

        return $result;

//        // Load the backend helper for filtering.
//		require_once JPATH_ADMINISTRATOR.'/components/com_tz_portfolio_plus/helpers/tz_portfolio_plus.php';
//
//		// Initialise variables.
//		$app   = JFactory::getApplication();
//		$input = $app -> input;
//		$lang  = JFactory::getLanguage();
//		$model = $this->getModel();
//		$table = $model->getTable();
//		$data  = $input -> post -> get('jform', array(), 'array');
//		$checkin = property_exists($table, 'checked_out');
//		$context = "$this->option.edit.$this->context";
//		$task = $this->getTask();
//
//		// Determine the name of the primary key for the data.
//		if (empty($key))
//		{
//			$key = $table->getKeyName();
//		}
//
//		// To avoid data collisions the urlVar may be different from the primary key.
//		if (empty($urlVar))
//		{
//			$urlVar = $key;
//		}
//
//		$recordId = $input -> getInt($urlVar);
//
//		if (!$this->checkEditId($context, $recordId))
//		{
//			// Somehow the person just went to the form and tried to save it. We don't allow that.
//			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $recordId));
//			$this->setMessage($this->getError(), 'error');
//
//			$this->setRedirect(
//				JRoute::_(
//					'index.php?option=' . $this->option . '&view=' . $this->view_list
//					. $this->getRedirectToListAppend(), false
//				)
//			);
//
//			return false;
//		}
//
//		// Populate the row id from the session.
//		$data[$key] = $recordId;
//
//		// The save2copy task needs to be handled slightly differently.
//		if ($task == 'save2copy')
//		{
//			// Check-in the original row.
//			if ($checkin && $model->checkin($data[$key]) === false)
//			{
//				// Check-in failed. Go back to the item and display a notice.
//				$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
//				$this->setMessage($this->getError(), 'error');
//
//				$this->setRedirect(
//					JRoute::_(
//						'index.php?option=' . $this->option . '&view=' . $this->view_item
//						. $this->getRedirectToItemAppend($recordId, $urlVar), false
//					)
//				);
//
//				return false;
//			}
//
//			// Reset the ID and then treat the request as for Apply.
//			$data[$key] = 0;
//			$task = 'apply';
//		}
//
//		// Access check.
//		if (!$this->allowSave($data, $key))
//		{
//			$this->setError(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
//			$this->setMessage($this->getError(), 'error');
//
//			$this->setRedirect(
//				JRoute::_(
//					'index.php?option=' . $this->option . '&view=' . $this->view_list
//					. $this->getRedirectToListAppend(), false
//				)
//			);
//
//			return false;
//		}
//
//		// Validate the posted data.
//		// Sometimes the form needs some posted data, such as for plugins and modules.
//		$form = $model->getForm($data, false);
//
//		if (!$form)
//		{
//			$app->enqueueMessage($model->getError(), 'error');
//
//			return false;
//		}
//
//		// Test whether the data is valid.
//		$validData = $model->validate($form, $data);
//
//		// Check for validation errors.
//		if ($validData === false)
//		{
//			// Get the validation messages.
//			$errors = $model->getErrors();
//
//			// Push up to three validation messages out to the user.
//			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
//			{
//				if ($errors[$i] instanceof Exception)
//				{
//					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
//				}
//				else
//				{
//					$app->enqueueMessage($errors[$i], 'warning');
//				}
//			}
//
//			// Save the data in the session.
//			$app->setUserState($context . '.data', $data);
//
//			// Redirect back to the edit screen.
//			$this->setRedirect(
//				JRoute::_(
//					'index.php?option=' . $this->option . '&view=' . $this->view_item
//					. $this->getRedirectToItemAppend($recordId, $urlVar), false
//				)
//			);
//
//			return false;
//		}
//
//		// Attempt to save the data.
//		if (!$model->save($validData))
//		{
//			// Save the data in the session.
//			$app->setUserState($context . '.data', $validData);
//
//			// Redirect back to the edit screen.
//			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
//			$this->setMessage($this->getError(), 'error');
//
//			$this->setRedirect(
//				JRoute::_(
//					'index.php?option=' . $this->option . '&view=' . $this->view_item
//					. $this->getRedirectToItemAppend($recordId, $urlVar), false
//				)
//			);
//
//			return false;
//		}
//
//		// Save succeeded, so check-in the record.
//		if ($checkin && $model->checkin($validData[$key]) === false)
//		{
//			// Save the data in the session.
//			$app->setUserState($context . '.data', $validData);
//
//			// Check-in failed, so go back to the record and display a notice.
//			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
//			$this->setMessage($this->getError(), 'error');
//
//			$this->setRedirect(
//				JRoute::_(
//					'index.php?option=' . $this->option . '&view=' . $this->view_item
//					. $this->getRedirectToItemAppend($recordId, $urlVar), false
//				)
//			);
//
//			return false;
//		}
//
//		$this->setMessage(
//			JText::_(
//				($lang->hasKey($this->text_prefix . ($recordId == 0 && $app->isSite() ? '_SUBMIT' : '') . '_SAVE_SUCCESS')
//					? $this->text_prefix
//					: 'JLIB_APPLICATION') . ($recordId == 0 && $app->isSite() ? '_SUBMIT' : '') . '_SAVE_SUCCESS'
//			)
//		);
//
//		// Redirect the user and adjust session state based on the chosen task.
//		switch ($task)
//		{
//			case 'apply':
//				// Set the record data in the session.
//				$recordId = $model->getState($this->context . '.id');
//				$this->holdEditId($context, $recordId);
//				$app->setUserState($context . '.data', null);
//				$model->checkout($recordId);
//
//				// Redirect back to the edit screen.
//				$this->setRedirect(
//					JRoute::_(
//						'index.php?option=' . $this->option . '&view=' . $this->view_item
//						. $this->getRedirectToItemAppend($recordId, $urlVar), false
//					)
//				);
//				break;
//
//			case 'save2new':
//				// Clear the record id and data from the session.
//				$this->releaseEditId($context, $recordId);
//				$app->setUserState($context . '.data', null);
//
//				// Redirect back to the edit screen.
//				$this->setRedirect(
//					JRoute::_(
//						'index.php?option=' . $this->option . '&view=' . $this->view_item
//						. $this->getRedirectToItemAppend(null, $urlVar), false
//					)
//				);
//				break;
//
//			default:
//				// Clear the record id and data from the session.
//				$this->releaseEditId($context, $recordId);
//				$app->setUserState($context . '.data', null);
//
//				// Redirect to the list screen.
//				$this->setRedirect(
//					JRoute::_(
//						'index.php?option=' . $this->option . '&view=' . $this->view_list
//						. $this->getRedirectToListAppend(), false
//					)
//				);
//				break;
//		}

		// Invoke the postSave method to allow for the child class to access the model.
		$this->_postSaveHook($model, $validData);

		return true;
	}
}
