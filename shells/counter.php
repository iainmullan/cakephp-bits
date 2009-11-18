<?php
/**
 * A Console task for CakePHP to update counterCache values. Useful during development 
 * and/or after a bulk data import.
 * 
 * In theory could be adapted to suit any type of beforeSave logic.
 * 
 * @author Iain Mullan
 * @created 16th November 2009
 */
class CounterShell extends Shell {

	function main() {

		$models = Configure::listObjects('model');

		foreach($models as $class) {

			$do_update = false;
			
			$this->out("Checking associations for $class...");
			if ($class != 'AppModel') {
					
				$this->{$class} = ClassRegistry::init($class);
	
				foreach($this->{$class}->belongsTo as $modelName => $properties) {
	
					// If there are no properties specified for this Association,
					// counterCache is implicitly false
					if (!is_numeric($modelName) && is_array($properties)) {
	
						if (!empty($properties['counterCache'])) {
							$this->out("\tcounterCache in $modelName");
	
							// Figure out the name of the counter column
							if (!is_string($properties['counterCache'])) {
								$column_name = Inflector::underscore($class).'_count';
							}

							if (!isset($this->{$modelName})) {
								$this->{$modelName} = ClassRegistry::init($modelName);
							}

							// Check if the column has been created
							if (!isset($this->{$modelName}->_schema[$column_name])) {
								$this->out("\t\t$modelName.$column_name does not exist");
							} else {
								$this->out("\t\t$modelName.$column_name exists");
								$do_update = true;
							}
							
						}
	
					}
	
				}

				// Note we only need to update once for each model, even if it has
				// multiple counters, since the counts will be updated for all associations
				if ($do_update) {
					$this->out("\tSaving all ".Inflector::pluralize($class));
					$records = $this->{$class}->find('all');
					$this->{$class}->saveAll($records);
				}

			}

		}

	}

}
?>
