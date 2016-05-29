<?php namespace Yaim\Mitter;

use View as View;

class FormBuilder {

	protected $generatedFields;
	protected $structure;
	protected $apiController;
	protected $html;
	protected $oldData = null;
	protected $isDeleted;

	public function __construct($structure, $apiController = null, $oldData = null, $id = null )
	{
		$this->structure = $structure;
		$this->apiController = $apiController;
		$this->oldData = $oldData;
		$this->isDeleted = (isset($oldData['deleted_at'])) ? true : false;
		$this->id = $id;
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

    public function getRowContent($type, $extraAttributes, $continuous, $name, $title, $options, $value, $model)
    {
        $element = $this->{$type}($name, $value, $options, $model) . '';
        if ($type == 'hidden') {
            return $element;
        }
        $width = isset($options['width']) ? $options['width'] : 12;
        return view('mitter::layouts.row', compact(['extraAttributes', 'name', 'element', 'continuous', 'title', 'width']))->render();
    }

    public function text($name, $value = null, $options = [])
    {
        return $this->getElement($name, $value, $options);
    }

    public function password($name, $value = null, $options = [])
    {
        return $this->getElement($name, $value, $options, 'password');
    }

    public function date($name, $value = null, $options = [])
    {
        return $this->getElement($name, $value, $options, 'date');
    }

    public function time($name, $value = null, $options = [])
    {
        return $this->getElement($name, $value, $options, 'time');
    }

    public function dateTime($name, $value = null, $options = [])
    {
        return $this->getElement($name, $value, $options, 'dateTime');
    }

    public function hidden($name, $value = null, $options = [])
    {
        return $this->getElement($name, $value, $options, 'hidden');
    }

    public function link($name, $value = null, $options = [])
    {
        return $this->getElement($name, $value, $options, 'link');
    }

    public function bool($name, $value = null, $options = [])
    {
        return $this->getElement($name, $value, $options, 'bool');
    }

    public function textarea($name, $value = null, $options = [])
    {
        return $this->getElement($name, $value, $options, 'textarea');
    }

    public function editor($name, $value = null, $options = [])
    {
        return $this->getElement($name, $value, $options, 'editor');
    }

    public function select($name, $value = null, $options)
    {
        return $this->getElement($name, $value, $options, 'select');
    }

    public function image($name, $value = null, $options)
    {
        return $this->getElement($name, $value, $options, 'image');
    }

    public function locked($name, $value = null, $options = [])
    {
        $relationEditLink = null;
        if (is_array($value) and isset($value['id'])) {
            $relationName = explode('[', $name)[0];
            $relationEditLink = $this->getSelfModel()->{$relationName}->find($value['id'])->getEditUrl();
        }
        $options['link'] = $relationEditLink;
        return $this->getElement($name, $value, $options, 'locked');
    }

    public function json($name, $value = null, $options)
    {
        $value = (is_array($value) || is_object($value)) ? json_encode($value) : $value;
        $value = json_decode($value, true);
        if (!$value) {
            $value[str_random(16)] = null;
            $options['new'] = true;
        }
        $options['col'] = @$options['col'] ?: 4;
        return $this->getElement($name, $value, $options, 'json');
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

	public function divider($title)
	{
		return view('mitter::partials.divider', compact('title'));
	}

    /**
     * Create a form element.
     *
     * @param $name
     * @param null $value
     * @param array $options
     * @param string $type
     * @return View
     */
    public function getElement($name, $value = null, $options = [], $type = 'text')
    {
        $value = $this->getValue($value, $options, $name);
        $title = isset($options['title']) ? $options['title'] : '';

        $class = isset($options['class']) ? $options['class'] : '';

        return view("mitter::partials.{$type}", compact('value', 'name', 'title', 'class', 'options'));
    }

    /**
     * get value from field
     *
     * @param $value
     * @param $options
     *
     * @param $name
     * @return mixed|string
     */
    private function getValue($value, $options, $name)
    {
        if (is_array($value)) {
            $nameField = isset($options['name_field']) ? $options['name_field'] : 'name';
            $value = isset($value[$nameField]) ? $value[$nameField] : $value;
        }
        if (is_string($name) and strpos($name, "_type") && strpos($name, "[")) {
            preg_match('#\[(.*?)\]#', $name, $match);
            $match = $match[1];
            if (isset($this->oldData[$match])) {
                return $this->oldData[$match];
            }
        }
        return $value;
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

	/**
	 * @return string form
     */
	public function get()
	{
		$structure = $this->structure;
		$isDeleted = $this->isDeleted;
		$id = $this->id;
		$model = $this->getSelfModel();
		if (isset($structure['self'])) {
			$this->formContent($structure['self']);
		}
		if (isset($structure['relations'])) {
			$this->formContent($structure['relations']);
		}
		$generatedFields = $this->generatedFields;
		return View::make('mitter::layouts.form', compact('structure', 'isDeleted', 'id', 'generatedFields', 'model'))->render().'';
	}

	/**
	 * @return string form
	 */
	public function __toString()
	{
		return $this->get();
	}
}
