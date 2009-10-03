<?
class QuotesHelper extends AppHelper {

	function quote() {
		$quotes = file(APP.DS.'config'.DS.'quotes'.DS.'quotes.txt');
		shuffle($quotes);
		$quote = array_pop($quotes);
		return $this->output($quote);
	}

}
?>