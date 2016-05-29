<?php namespace Yaim\Mitter;

trait MitterModelActions
{
    public function getIndexAction()
    {
        return $this->getAction('index');
    }

    public function getCreateAction()
    {
        return $this->getAction('create');
    }

    public function getStoreAction()
    {
        return $this->getAction('store');
    }

    public function getShowAction()
    {
        return $this->getAction('show');
    }

    public function getEditAction()
    {
        return $this->getAction('edit');
    }

    public function getUpdateAction()
    {
        return $this->getAction('update');
    }

    public function getDeleteAction()
    {
        return $this->getAction('destroy');
    }

    public function getAction($actionName)
    {
        $aliases = getMitterAliasesByModelName(static::class);
        $controller = $this->controller ?: "\\Yaim\\Mitter\\BaseController";
        return action($controller . '@' . $actionName, ['model' => $aliases, 'id' => $this->id]);
    }
}