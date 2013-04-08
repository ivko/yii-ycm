<?php

class DefaultController extends AdminController
{
	/**
	 * Displays a list of all models.
	 */
	public function actionIndex()
	{
		$this->render('index',array(
			'models'=>$this->module->modelsList,
		));
	}

	/**
	 * Displays Google Analytics statistics.
	 */
	public function actionStats()
	{
		$days=30;
		$startDate=date('Y-m-d',strtotime("-$days days"));
		$endDate=date('Y-m-d',strtotime('-1 day'));

		if (!empty($this->module->analytics)
			&& isset($this->module->analytics['trackingId'])
			&& isset($this->module->analytics['profileId'])
			&& isset($this->module->analytics['accessToken'])) {
			$config=array(
				'trackingId'=>$this->module->analytics['trackingId'],
				'profileId'=>$this->module->analytics['profileId'],
				'accessToken'=>$this->module->analytics['accessToken'],
				'startDate'=>$startDate,
				'endDate'=>$endDate,
			);
			$stats=new Stats($config);
			$this->render('stats',array(
				'days'=>$days,
				'deviceData'=>$stats->deviceData,
				'visitorData'=>$stats->visitorData,
				'keywords'=>$stats->keywords,
				'referrers'=>$stats->referrers,
				'pages'=>$stats->pages,
				'usage'=>$stats->usage,
			));
		} else {
			$stats=new Stats();
			$authUrl=$stats->client->createAuthUrl();
			$this->render('setup',array(
				'stats'=>$stats,
				'authUrl'=>$authUrl,
			));
		}
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if ($error=Yii::app()->errorHandler->error) {
			if (Yii::app()->request->isAjaxRequest) {
				echo $error['message'];
			} else {
				$this->render('error', $error);
			}
		}
	}

	/**
	 * Displays the login page.
	 */
	public function actionLogin()
	{
		$model=Yii::createComponent('LoginForm');
		if (isset($_POST['LoginForm'])) {
			$model->attributes=$_POST['LoginForm'];
			if ($model->validate() && $model->login()) {
				$this->redirect(Yii::app()->createUrl($this->module->name));
			}
		}
		$this->render('login',array(
			'model'=>$model,
		));
	}

	/**
	 * Logs out the current user and redirects to home page.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout(false);
		$this->redirect(Yii::app()->createUrl($this->module->name));
	}
}