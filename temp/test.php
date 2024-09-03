<?php
// phpcs:disabled

class App {
	private static $app = null;

	private function __construct() {

	}

	public static function get () : App {
		if (!self::$app) :
			self::$app = new App();
		endif;

		return self::$app;
	}

	public function bootstrap() : void {
		echo 'Bootstrapping the app';
	}
}

$app = App::get();
$app->bootstrap();
