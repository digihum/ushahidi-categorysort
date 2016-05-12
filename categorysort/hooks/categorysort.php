<?php defined('SYSPATH') or die('No direct script access.');

class categorysort {

	protected $user;

	public function __construct()
	{
		// Hook into routing
		Event::add('system.pre_controller', array($this, 'add'));
	}
	
	public function add()
	{
		Event::add('ushahidi_filter.view_pre_render.layout', array($this, 'sort'));
	}

	public function sort()
	{		
	
		if (strripos(Router::$current_uri, "main") !== false)
		{
			$categories = ORM::factory('incident_category')->find_all();
			$categoriesdata = array();
			foreach($categories as $category)
			{
				if(!isset($categoriesdata[$category->category_id])){
					$categoriesdata[$category->category_id] = 0;
				}
				$categoriesdata[$category->category_id]++;
			}
			
			$nodes = array();
			
			$categories = Event::$data['content']->categories;
			$new_categories = array();
			foreach($categories as $key => $data){
				if(isset($categoriesdata[$key])){
					$data[] = "(" . ($categoriesdata[$key]) . " " . Kohana::lang("categorysort.memories") . ")";
					$data[] = $key;
					$new_categories[ucfirst($data[0])] = $data;
				}
			}
			Event::$data['content']->categories = array();
			foreach($new_categories as $data){
				Event::$data['content']->categories[$data[5]] = $data;
			}
		}
		else
		{
			$this->domDocument = new DOMDocument();
			@$this->domDocument->loadHTML(mb_convert_encoding(Event::$data['content']->kohana_local_data['category_tree_view'], 'HTML-ENTITIES', 'UTF-8'));
			$query = "//li"; 
			$xpath = new DOMXPath($this->domDocument); 
			$result = $xpath->query($query); 
			foreach ($result as $node) {
				$nodes[strtolower($node->childNodes->item(0)->getAttribute("title"))] = $node;
				$node->parentNode->removeChild($node);
			}
			if(isset($nodes)){
				ksort($nodes);
				
				$element = $this->domDocument->createElement("ul");

				// We insert the new element as root (child of the document)
				$this->domDocument->appendChild($element);
				
				foreach($nodes as $node){
					$element->appendChild($node);
				}

				Event::$data['content']->kohana_local_data['category_tree_view'] = $this->domDocument->saveHTML();
			}
		}
	}
	
}
new categorysort;
