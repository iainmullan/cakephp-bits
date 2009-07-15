<?
/**
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

	var $elements = array();

	var $_tmpTitle;

	var $controller;

	function setController(&$controller) {
		$this->controller = $controller;
		$this->log('setting controller in Sidebar helper');
	}

	function enable() { $this->enabled = true; }
	function disable() { $this->enabled = false; }
	function enabled() { return $this->enabled; }

	function options($new_options) {
		$this->options = array_merge($this->options, $new_options);
	}

	function addFeed($url) {
		return;
		$news = $this->Simplepie->feed($url);
                $items = $news->get_items();
                $this->add($news->get_feed_title(), 'news_feed', array('news' => $items, 'limit' => 10));
	}

	function add($title, $element_name, $params, $index = false) {
		$this->addElement($title, $element_name, $params, $index);
    }

	function addElement($title, $element_name, $params, $index = false) {
        $elem = array('title' => $title, 'element' => $element_name, 'params' => $params);
		$this->addBox($elem, $index);
    }

	function addContent($title, $content, $index = false) {
        $elem = array('title' => $title, 'content' => $content, 'element' => '');
		$this->addBox($elem, $index);
    }

    function addBox($elem, $index = false) {
       $this->enable();
        $elems = $this->elements;

        if (is_numeric($index)) {
        	$elems[$index] = $elem;
        } else {
        	$elems[] = $elem;
        }

        $this->elements = $elems;
    }

    function clear() {
        $this->elements = array();
    }

    function startBox($title) {
    	$this->_tmpTitle = $title;
    	ob_start();
    }

    function endBox() {
    	$content = ob_get_clean();
    	$this->addContent($this->_tmpTitle, $content);
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

		if ($this->enabled()) {

			$view = ClassRegistry::getObject('view');

			$sidebox_elements = $this->elements;
			?><div id="<?= $this->options['sidebar_id'] ?>"><?

			foreach($sidebox_elements as $sb) {
				?>
				<div id="<?= $sb['element'] ?>" class="<?= $this->options['sidebox_class'] ?> <?= $sb['element'] ?>">
					<?
					if (!is_null($sb['title'])) {
						echo $this->Html->tag($this->options['title_tag'], $sb['title']);
					}
					if (!empty($sb['element'])) {
						echo $view->renderElement($sb['element'], $sb['params']);
					} else if (isset($sb['content'])) {
						echo $sb['content'];
					}
				?></div><?
			}
			?></div><?
		}

    }

}

?>