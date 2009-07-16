<?
/**
 *
 * The Sidebar Helper allows you to specify blocks of content from your view files, and then render them all at once from
 * your layout. Content blocks can be rendered as Cake elements, or 'inline'.
 *
 * View code:
 *
 *	$sidebar->addBox(array('title' => 'Sidebox One', 'element'=>'sidebox_1'));
 *
 *	$sidebar->addBox(array('title' => 'Sidebox B', 'element'=>'sidebox_b'));
 *
 *	$sidebar->startBox(array('title' => 'Inline box', 'element'=>'my_inline_box'));
 *		<p> Click <a href="/">here</a> to go to the homepage </p>
 *	$sidebar->endBox();
 *
 * HTML Rendered by $sidebar->getSidebar() :
 *
 *	<div id="sidebar">
 * 		<div id="sidebox_1" class="sidebox">
 * 			<h4>Sidebox One</h4>
 * 			... contents of views/elements/sidebox_1.ctp ...
 *		</div>
 * 		<div id="sidebox_1" class="sidebox">
 * 			<h4>Sidebox B</h4>
 * 			... contents of views/elements/sidebox_b.ctp ...
 *		</div>
 * 		<div id="my_inline_box" class="sidebox">
 * 			<h4>Inline Box</h4>
 * 			<p> Click <a href="/">here</a> to go to the homepage </p>
 *		</div>
 *	</div>
 *
 *
 * @created May 2009
 * @author Iain Mullan , www.ebotunes.com
 *
 */
class SidebarHelper extends Helper {

	var $name = 'Sidebar';
	var $helpers = array('Html');

	var $options = array(
		'sidebar_id' => 'sidebar',
		'sidebox_class' => 'sidebox',
		'title_tag' => 'h4'
	);

	var $enabled = false;

	var $boxes = array();

	var $_defaultBox = array(
		'element' => '',
		'content' => '',
		'title' => null,
		'index' => false,
		'params' => array()
	);

	var $_tmpBox;

	var $controller;

	function setController(&$controller) {
		$this->controller = $controller;
	}

	function enable() { $this->enabled = true; }
	function disable() { $this->enabled = false; }
	function enabled() { return $this->enabled; }

	/**
	 * Override the default options.
	 *
	 * @param $new_options Array with any of the following indexes: sidebar_id , sidebox_class , title_tag
	 */
	function options($new_options) {
		$this->options = array_merge($this->options, $new_options);
	}

	/**
	 *	Takes a single parameter, an indexed array with the following keys:
	 *
	 * 	- content - The HTML content of the box.
	 * 	- element - If 'content' is empty, this will be interpreted as a Cake element (defined in views/elements/<element>.ctp). If 'content' is non-empty, this will be used as the ID of this box's div
	 * 	- title - The title of the box, which will be wrapped in an HTML tag specified by $options['title_tag']. A value of null will prevent the title tag being rendered at all.
	 *  - params - A parameter array to be passed to renderElement
	 *  - index - an optional numeric index, specifying the position of this box in the sidebar, starting at 0
	 *
	 * The passed box array must contain *either* a (non-empty) content or element. All other keys are optional.
	 *
	 * @param box
	 */
    function addBox($box) {
		$this->enable();

		$box = array_merge($this->_defaultBox, $box);

		$elems = $this->boxes;

		if (is_numeric($box['index'])) {
			$elems[$box['index']] = $box;
		} else {
			$elems[] = $box;
		}

		$this->boxes = $elems;
    }

	/**
	 * Remove all sideboxes from the sidebar.
	 */
	function clear() {
		$this->boxes = array();
	}

	/**
	 * Start a content buffer for a sidebox. This allows you to supply the $box parameter with neither the
	 * element or content options supplied, and instead render the content of the box 'inline' in your view.
	 * End the content buffer with $sidebar->endBox();
	 *
	 * Note that the 'element' index may (should!) still be specified, in order to give the box's div an ID. Also
	 * note that if 'content' is specified, it will be ultimately overwritten by the inline content once endBox() is called.
	 *
	 * Example Usage (in your view file):
	 *
	 * 		$sidebar->startBox(array('title'=>'My inline sidebox', 'element'=>'my_inline_box'));
	 *  		<p>This is some content I just want to throw into a <b>sidebox</b> without having to create a <b>.ctp</b> file in <code>views/elements</code>.</p>
	 * 		$sidebar->endBox();
	 *
	 *
	 * @return true if a sidebox buffer is started succesfully, false otherwise (i.e. in case youve already called
	 * startBox without a corresponding endBox)
	 */
    function startBox($box) {

		if ($this->_tmpBox != null) {
			$this->log('Buffered box already started: '.pr($this->_tmpBox, true));
			return false;
		}

		// backwards compatibility with startBox($title)
		if (!is_array($box)) {
			$box = array('title' => $box);
		}
    	$this->_tmpBox = $box;
    	ob_start();

    	return true;
    }

	/**
	 * End the sidebox buffer and add the rendered content to the sidebar.
	 *
	 * @return false if there is no active buffer to end, true otherwise.
	 */
    function endBox() {
		if ($this->_tmpBox == null) {
			$this->log('No buffered box to end!');
			return false;
		}
    	$content = ob_get_clean();
    	$this->_tmpBox['content'] = $content;
    	$this->addBox($this->_tmpBox);
    	$this->_tmpBox = null;
    }

	/**
	 * Generate the output for all sidebar elements, wrapped in a sidebar div.
	 *
	 * 	The sidebar wrapper div will have an id of $this->options['sidebar_id']
	 * 	Each sidebox will have an id of the name of the element which renders it.
	 * 	Each sidebox will have a class of $this->options['sidebox_class']
	 *
	 */
    function getSidebar() {

		$output = '';

		if ($this->enabled()) {

			$view = ClassRegistry::getObject('view');

			$sidebox_elements = $this->boxes;

			foreach($sidebox_elements as $sb) {
				$box_output = '';

				if (!is_null($sb['title'])) {
					$box_output .= $this->Html->tag($this->options['title_tag'], $sb['title']);
				}

				if (!empty($sb['content'])) {
					$box_output .= $sb['content'];
				} else if (!empty($sb['element'])) {
					$box_output .= $view->renderElement($sb['element'], $sb['params']);
				}

				// wrap it all in a div
				$box_output = $this->Html->tag('div', $box_output, array('id' => $sb['element'], 'class' => $this->options['sidebox_class'].' '. $sb['element']));

				$output .= $box_output;
			}

			$output = $this->Html->tag('div', $output, array('id' => $this->options['sidebar_id']));
		}

		return $this->output($output);
    }

}

?>