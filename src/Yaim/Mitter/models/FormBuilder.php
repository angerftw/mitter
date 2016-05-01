<?php namespace Yaim\Mitter;

use View as View;

class FormBuilder {

	protected $generatedFields;
	protected $structure;
	protected $apiController;
	protected $html;
	protected $oldData = null;
	protected $isDeleted;

	/**
	 * The types of inputs to not fill values on by default.
	 *
	 * @var array
	 */
	protected $skipValueTypes = ['file', 'password', 'checkbox', 'radio'];

	/**
	 * The current model instance for the form.
	 *
	 * @var mixed
	 */
	protected $model;

	/**
	 * default class for input
	 *
	 * @var mixed
	 */
	protected $defaultClass = ' form-horizontal row-border form-control';

	public function __construct($structure, $apiController = null, $oldData = null, $id = null )
	{
		// @todo: find a way to get rid of this dummy hack fix
		$laravel = app();
		if (0 === strpos($laravel::VERSION, '5.')) {
			\URL::setRootControllerNamespace('');
		}

		$this->structure = $structure;
		$this->apiController = $apiController;
		$this->oldData = $oldData;
		$this->isDeleted = (isset($oldData['deleted_at'])) ? true : false;
		$this->id = $id;

	}

	public function get()
	{
		$structure = $this->structure;
		if (isset($structure['self'])) {
			$this->formContent($structure['self']);
		}

		if (isset($structure['relations'])) {
			$this->formContent($structure['relations']);
		}
		return $this->__toString();
	}

	public function __toString()
	{
		$structure = $this->structure;
		$isDeleted = $this->isDeleted;
		$id = $this->id;
		$generatedFields = $this->generatedFields;

		return View::make('mitter::layouts.form', compact('structure', 'isDeleted', 'id', 'generatedFields'))->render();
	}

	private function getPreFixedAPI($api)
	{
		if(strpos($api, '%')){
			preg_match_all('~[%](.*?)[%]~', $api, $wildcards);

			foreach ($wildcards[0] as $key => $wildcard) {
				$replacable = @$this->getSelfModel()->$wildcards[1][$key];
				$api = str_replace($wildcard, $replacable, $api);
			}
		}

		$prefix = (isset($this->structure['apiPrefix'])) ? $this->structure['apiPrefix'] : '';

		return str_replace('//', '/', $prefix.$api);
	}

	private function getSelfModel()
	{
		return call_user_func(array($this->structure['model'], 'find'), $this->id);
	}

	private function formContent($structure)
	{
		foreach ($structure as $name => $field) {
			$title = $field['title'];

			if(isset($field['type'])) {
				if ($field['type'] == "divider") {
					$this->divider($title);
					continue;
				}
			}

			$oldData = (isset($this->oldData[snake_case($name)]))? $this->oldData[snake_case($name)] : null;
			$repeat = (isset($field['repeat']))? $field['repeat'] : false;

			if(isset($oldData)) {
				if($repeat) {
					$count = count($oldData);
					$i = 1;
					$repeat = false;

					if (@$field['type'] != "locked") {
						$this->generatedFields .= '<input type="hidden" name="'.$name.'" value="1" data-hidden-placeholder/>';
					}

					foreach ($oldData as $subOldData) {
						if($i == $count) {
							$repeat = true;
						}

						$continious = true;
						$this->rowFetcher($title, $name, $field, $subOldData, $repeat, $num = $i, $continious);
						$i++;
					}
					$this->generatedFields .='<hr/>';
				} else {
					$this->rowFetcher($title, $name, $field, $oldData, $repeat);
				}
			} else {
				$this->rowFetcher($title, $name, $field, $oldData, $repeat);
			}
		}
	}

	private function rowFetcher($title, $name, $field, $oldData = null, $repeat = false, $num = 1, $continious = false)
	{
		$extraInputs = '';
		$extraAttributes = '';

		if ($repeat) {
			$extraAttributes .=" data-repeat data-name='$name' ";
		}

		$model = (isset($field['model']))? $field['model'] : null;
		$key = (isset($field['key']))? $field['key'] : null;

		if(isset($field['subs'])) {
			$namePrefix = $name;

			foreach ($field['subs'] as $name => &$subField) {
				$data = $oldData;

				if(isset($oldData)) {
					if(@$key == $name) {
						$data = (isset($oldData[$key]))? $oldData[$key] : null;

						$name = mitterNameFixer($name, $repeat, $namePrefix, $num);
						$this->generatedFields .= $this->getRowContent($subField['type'], $extraAttributes, $continious, $name, @$subField['title'], $subField, $data, $model);
						continue;
					}

					if(isset($oldData['pivot'])) {
						if(array_key_exists($name, $oldData['pivot'])) {
							$data = $oldData['pivot'][$name];
						}
					} else {
						if(array_key_exists($name, $oldData)) {
							$data = $oldData[$name];

							//Dummy Hack Fix For Poly Morphic Ajax Guess start

							if (strpos($name, "_type")) {
								$inputIdName = explode("_type", $name);
								$inputIdName = $inputIdName[0]."_id";

								if(isset($field['subs'][$inputIdName])) {
									$field['subs'][$inputIdName]['model'] = $data;
								}
							}

							//Dummy Hack Fix For Poly Morphic Ajax Guess end
						}
					}

					$model = (isset($subField['model'])) ? $subField['model'] : null;
				}

				$name = mitterNameFixer($name, $repeat, $namePrefix, $num);
				$this->generatedFields .= $this->getRowContent($subField['type'], $extraAttributes, $continious, $name, @$subField['title'], $subField, $data, $model);
			}
		} else {
			$name = mitterNameFixer($name, $repeat, null, $num);
			$this->generatedFields .= $this->getRowContent($field['type'],$extraAttributes, $continious, $name, $field['title'], $field, $oldData, $model);
		}
	}

	public function getRowContent($type,$extraAttributes, $continious, $name, $title, $field, $oldData, $model)
	{
		$content = $this->{$type}($name, $title, $field, $oldData, $model);
		if($type == 'hidden')
		{
			return $content;
		}
		return view('mitter::layouts.row',compact(['extraAttributes','name','content','continious','title']))->render();
	}

	private function ajaxGuess($name, $title, $field, $oldData = null, $model = null, $createNew = false)
	{
		$default = "";
		$text = "";
		$id = "";

		if (isset($oldData)) {
			if(isset($oldData['id'])) {
				$relationId = $oldData['id'];
			} elseif(!is_array($oldData)) {
				$relationId = $oldData;
			}

			if(isset($relationId)) {
				if(!isset($model)) {
					$path = str_replace("/", "", $field["api"]);
					$model = call_user_func([$this->apiController, 'getModelName'], explode('?', $path)[0]);
				}

				$relationModel = call_user_func(array($model, 'find'), $relationId);

				if (isset($relationModel)) {
					$relationEditLink = $relationModel->getEditUrl();

					$id = @$relationModel->id;
					$text = @$relationModel->getGuessText();
				}
			}
		}

		extract($field);

		$minimum = (isset($minimum)) ? $minimum : 1;

		/*
            // @todo create a conditional ajaxGuess for Polyrophic Relations

            $conditional = "";

            if(isset($field['conditional']))
            {
                if($field['conditional'])
                    $conditional = "data-conditional";
            }
        */

		$api = $this->getPreFixedAPI($api);
		$attributes = "data-selectAjax";

		if($createNew) {
			$attributes .= " data-tags	='true'";
		}

		$width = (!isset($width))? 12 : $width;

		if(isset($relationEditLink) && !empty(@$relationEditLink)) {
			$width = (!isset($width))? 11 : $width - 1;
		}

		return View::make('mitter::partials.ajaxGuess', compact('relationEditLink', 'width', 'minimum', 'attributes', 'title', 'api', 'name', 'id', 'text'));
	}

	private function createAjaxGuess($name, $title, $field, $oldData = null, $model = null)
	{
		$this->ajaxGuess($name, $title, $field, $oldData, $model, $createNew = true);
	}

	private function ajaxTag($name, $title, $field, $oldData = null, $createNew = false)
	{
		extract($field);
		$oldDataArray = [];
		$name .= '[]';
		$minimum = (isset($minimum)) ? $minimum : 1;

		if (isset($oldData)) {
			foreach ($oldData as $data) {
				$oldDataArray[] = array_only($data, array('id', 'name'));
			}

			foreach ($oldDataArray as $k => $v) {
				$oldDataArray[$k]['text'] = $oldDataArray[$k]['name'];
				unset($oldDataArray[$k]['name']);
			}
		}

		$width = (!isset($width))? 12 : $width;
		$api = $this->getPreFixedAPI($api);
		$attributes = "data-selectAjax";

		if($createNew) {
			$attributes .= " data-tags	='true'";
		}

		return View::make('mitter::partials.ajaxTag', compact('width', 'attributes', 'minimum', 'title', 'api', 'name', 'oldDataArray'));
	}

	private function createAjaxTag($name, $title, $field, $oldData = null)
	{
		$this->ajaxTag($name, $title, $field, $oldData, $createNew = true);
	}

	private function bool($name, $title, $field, $oldData = null)
	{
		$checked = "";

		if (isset($oldData)) {
			if ($oldData == 1) {
				$checked = " checked='true' ";
			}
		} else {
			if(isset($field['default'])) {
				if ($field['default']) {
					$checked = " checked='true' ";
				}
			}
		}

		extract($field);
		$width = (!isset($width))? 12 : $width;

		return View::make('mitter::partials.bool', compact('width', 'name', 'checked', 'title'));
	}

	private function divider($title)
	{
		return View::make('mitter::partials.divider', compact('title'));
	}

	private function image($name, $title, $field, $oldData = null)
	{
		extract($field);

		if(is_array($oldData)) {
			$nameField = (isset($field['name_field']))? $field['name_field'] : 'name';
			$oldData = (isset($oldData[$nameField]))? $oldData[$nameField] : '';
		}

		$width = (!isset($width))? 12 : $width;

		if($oldData) {
			$width = ($width >= 3)? $width-2 : 1;
			$removeName = $name."[remove]";

		}

		return View::make('mitter::partials.image', compact('width', 'name', 'title', 'oldData', 'removeName'));
	}

	private function json($name, $title, $field, $oldData = null)
	{
		extract($field);
		$width = (!isset($width))? 12 : $width;
		$name = (isset($name)) ? $name : null;

		$oldData = (is_array($oldData) || is_object($oldData)) ? json_encode($oldData) : $oldData;

		if (isset($oldData) && !empty(json_decode($oldData))) {
			$oldData = json_decode($oldData, true);
			return View::make('mitter::partials.json.filled', compact('name', 'oldData', 'width', 'title', 'field'));
		} else {
			$key = str_random(16);
			return View::make('mitter::partials.json.new', compact('name', 'width', 'key', 'title', 'field'));
		}
	}

	private function link($name, $title, $field, $oldData = null)
	{
		$width = (!isset($width))? 12 : $width;

		return View::make('mitter::partials.link', compact('oldData'));
	}

	private function locked($name, $title, $field, $oldData = null)
	{
		extract($field);

		if(is_array($oldData)) {
			if(isset($oldData['id'])) {
				$relationName = explode('[',$name)[0];
				$relationEditLink = $this->getSelfModel()->$relationName->find($oldData['id'])->getEditUrl();
			}

			$nameField = (isset($field['name_field']))? $field['name_field'] : 'name';
			$oldData = (isset($oldData[$nameField]))? $oldData[$nameField] : '';
		}

		$width = (!isset($width))? 12 : $width;

		if(isset($relationEditLink) && !empty(@$relationEditLink)) {
			$width = (!isset($width))? 11 : $width-1;
		}

		return View::make('mitter::partials.locked', compact('relationEditLink', 'width', 'oldData', 'title'));
	}

	private function select($name, $title, $field, $oldData = null)
	{
		if (strpos($name, "_type") && strpos($name, "[")) {
			preg_match('#\[(.*?)\]#', $name, $match);
			$match = $match[1];

			if (isset($this->oldData[$match])) {
				$oldData = $this->oldData[$match];
			}
		}

		extract($field);

		$width = (!isset($width))? 12 : $width;

		return View::make('mitter::partials.select', compact('width', 'name', 'field', 'selected', 'oldData'));
	}

	public function password($name, $title, $field, $oldData = null)
	{
		extract($field);
		$width = (!isset($width))? 12 : $width;
		return $this->input('password',$name,null,[],$width);
	}

	public function hidden($name, $title = null, $field, $oldData = null)
	{
		return $this->input('hidden', $name, null, []);
	}

	public function text($name, $title, $field, $oldData = null)
	{
		extract($field);
		$width = (!isset($width))? 12 : $width;
		return $this->input('text',$name,null,[],$width);
	}

	private function editor($name, $title, $field, $oldData = "")
	{
		extract($field);
		$width = (!isset($width))? 12 : $width;
		return $this->textarea('text',$name,null,['class'=>'ckeditor','placeholder'=>$title,'cols'=>50,'rows'=>5],$width);
	}

	private function textarea($name, $title, $field, $oldData = "")
	{
		extract($field);

		$width = (!isset($width))? 12 : $width;

		return View::make('mitter::partials.textarea', compact('width', 'name', 'title', 'oldData'));
	}

	private function date($name, $title, $field, $oldData = null)
	{
		extract($field);
		$width = (!isset($width))? 12 : $width;
		return $this->input('text', $name, null, ['data-datePicker'], $width);
	}

	private function dateTime($name, $title, $field, $oldData = null)
	{
		extract($field);
		$default = (@$default) ? "data-default" : "";
		$width = (!isset($width))? 12 : $width;
		return $this->input('text', $name, null, ['data-dateTimePicker', $default], $width);
	}

	private function time($name, $title, $field, $oldData = null)
	{
		extract($field);
		$width = (!isset($width))? 12 : $width;
		return $this->input('text', $name, null, ['data-timePicker'], $width);
	}

	/**
	 * Create a form input field.
	 *
	 * @param  string $type
	 * @param  string $name
	 * @param  string $value
	 * @param  array $options
	 *
	 * @param int $width
	 * @return \Illuminate\Support\HtmlString
	 */
	public function input($type, $name, $value = null, $options = [], $width = 12)
	{
		if (!isset($options['name'])) {
			$options['name'] = $name;
		}

		$id = $this->getIdAttribute($name, $options);

		if (! in_array($type, $this->skipValueTypes)) {
			$value = $this->getValueAttribute($name, $value);
		}

		$merge = compact('type', 'value', 'id');

		if(!isset($options['class']))
		{
			$options['class'] = '';
		}
		$options['class'] .= $this->defaultClass;

		$options = array_merge($options, $merge);

		return "<div class='col-sm-{{$width}}'><input {$this->attributes($options)} /></div>";
	}

	/**
	 * Get the value that should be assigned to the field.
	 *
	 * @param  string $name
	 * @param  string $value
	 *
	 * @return mixed
	 */
	public function getValueAttribute($name, $value = null)
	{
		if (is_null($name)) {
			return $value;
		}

		if (! is_null(old($name))) {
			return old($name);
		}

		if (! is_null($value)) {
			return $value;
		}

		if (isset($this->model)) {
			return $this->getModelValueAttribute($name);
		}
	}

	/**
	 * Get the model value that should be assigned to the field.
	 *
	 * @param  string $name
	 *
	 * @return mixed
	 */
	protected function getModelValueAttribute($name)
	{
		if (method_exists($this->model, 'getFormValue')) {
			return $this->model->getFormValue($name);
		}

		return data_get($this->model, $this->transformKey($name));
	}

	/**
	 * @param mixed $model
	 * @return FormBuilder
	 */
	public function setModel($model)
	{
		$this->model = $model;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getModel()
	{
		return $this->model;
	}

	/**
	 * Transform key from array to dot syntax.
	 *
	 * @param  string $key
	 *
	 * @return mixed
	 */
	protected function transformKey($key)
	{
		return str_replace(['.', '[]', '[', ']'], ['_', '', '.', ''], $key);
	}

	/**
	 * Get the ID attribute for a field name.
	 *
	 * @param  string $name
	 * @param  array  $attributes
	 *
	 * @return string
	 */
	public function getIdAttribute($name, $attributes)
	{
		if (array_key_exists('id', $attributes)) {
			return $attributes['id'];
		}
		return $name;
	}
	/**
	 * Build an HTML attribute string from an array.
	 *
	 * @param array $attributes
	 *
	 * @return string
	 */
	public function attributes($attributes)
	{
		$html = [];

		foreach ((array) $attributes as $key => $value) {
			$element = $this->attributeElement($key, $value);

			if (! is_null($element)) {
				$html[] = $element;
			}
		}

		return count($html) > 0 ? ' ' . implode(' ', $html) : '';
	}

	/**
	 * Build a single attribute element.
	 *
	 * @param string $key
	 * @param string $value
	 *
	 * @return string
	 */
	protected function attributeElement($key, $value)
	{
		if (is_numeric($key)) {
			$key = $value;
		}

		if (! is_null($value)) {
			return $key . '="' . e($value) . '"';
		}
	}
}
