<?php namespace Yaim\Mitter;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Controller;

class ApiController extends Controller
{
	protected $model;

	protected $action;

	private $config = [
		'checkAjax' => false,
		'defaultAction' => 'get',
		'useBasicDataFromModel' => true
	];

	/**
	 * ApiController constructor.
	 * @param $model
	 * @param null $action
	 * @param array $config
	 */
	public function __construct(Model $model, $action = null, array $config = [])
	{
		// setup api config
		$this->config = array_merge($this->config, $config);
		// set model
		$this->model = $model;
		// set action
		$this->action = $action;
	}


	/**
	 * @param string $q
	 * @param int $page
	 * @return array
	 */
	public function get($q = '', $page = 1)
	{

		if ($this->validateRequest()) {
			abort(404);
		}

		return $this->toJson($q, $page);
	}

	/**
	 * check request type is ajax
	 * @return bool
	 */
	private function validateRequest()
	{
		return $this->config['checkAjax'] && !$this->isAjax();
	}

	/**
	 * check request is Ajax
	 * @return bool
	 */
	private function isAjax()
	{
		return @$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
	}

	/**
	 * get model action
	 * @return mixed|string
	 */
	private function getAction()
	{
		$action = $this->action ?: $this->config['defaultAction'];
		$action = "api{$action}";
		// call api action from model and return
		if (method_exists($this->model, $action) || $this->config['useBasicDataFromModel']) {
			return $action;
		}
		return abort(404);
	}

	/**
	 * get data from model and return result
	 * @param string $q
	 * @param int $page
	 * @return mixed
	 */
	private function toJson($q = '', $page = 1)
	{
		$model = $this->model;
		$action = $this->getAction();
		$result = $this->getDataFromModel($model, $action, $page, $q);
		$results = array(
			"results" => $result['items'],
			"pagination" => array(
				"more" => $result['more'],
				"total" => $result['total']
			)
		);
		return $results;
	}

	/**
	 * @param $model
	 * @param $action
	 * @param $page
	 * @param $q
	 * @return array
	 */
	private function getDataFromModel($model, $action, $page, $q)
	{
		$perPage = $model->apiPerPage ?: 25;
		$offset = ($page - 1) * $perPage;
		if (method_exists($this->model, $action)) {
			$query = $model->{$action}($q);
		} else {
			$query = $this->getBasicData($q);
		}
		$count = $query->count();
		$items = $query
			->skip($offset)
			->take($perPage)
			->get()
			->toArray();
		return [
			'items' => $items,
			'more' => ($offset + $perPage) < $count,
			'total' => $count
		];
	}

	/**
	 * @param $q
	 * @return mixed
	 */
	private function getBasicData($q)
	{
		return $this
			->model
			->select(['id', 'name as text'])
			->where('name', 'LIKE', '%' . $q . '%')
			->orderBy('name');
	}
}
